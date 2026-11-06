#!/usr/bin/env bash
#
# Deploy origin/main to the production server, safely. Run from your own machine.
#
# Every step must succeed before the next one starts:
#   1. Back up the production database ON the server (written to a temp file, then
#      verified: gzip integrity + mariadb-dump's "Dump completed" marker).
#   2. Copy that backup down to ./backups/ and verify it again here.
#   3. Show the commits about to go live and ask for confirmation.
#   4. git pull --ff-only on the server (stops if the server checkout has diverged).
#   5. Build the new image while the old container keeps serving, then swap. Runs under
#      nohup on the server, so a dropped SSH connection cannot kill it half-way.
#   6. Wait for the site to answer 200 and check the new container's logs for errors.
#
# Why it exists: a deploy once carried on after the backup had been truncated by a
# dropped SSH connection. Here a failed or partial backup stops everything.
#
# Usage:
#   bin/deploy.sh                 # back up, confirm, deploy origin/main
#   bin/deploy.sh --yes           # same, without the confirmation prompt
#   bin/deploy.sh --backup-only   # steps 1-2 only: verified backup, nothing deployed
#   bin/deploy.sh --force         # rebuild and restart even if already up to date
#
# Expect ~1 minute of 502s while the new container starts (it runs migrations and
# caches config before Apache comes up). Deploy outside school hours.
#
# Settings can be overridden with environment variables (defaults below).
#
# The single-quoted strings passed to remote() are scripts that run ON the server, so
# their $1, $HOME etc. are meant to expand there, not here.
# shellcheck disable=SC2016,SC2029
set -euo pipefail

cd "$(dirname "$0")/.."

HOST="${DEPLOY_HOST:-renaissance-server}"        # an ~/.ssh/config Host entry
APP_DIR="${DEPLOY_DIR:-renaissance_sms}"         # relative to the server user's home
BRANCH="${DEPLOY_BRANCH:-main}"
URL="${DEPLOY_URL:-https://ris.bytetech.com.et/login}"
DB_CONTAINER="${DEPLOY_DB_CONTAINER:-renaissance_db}"
APP_CONTAINER="${DEPLOY_APP_CONTAINER:-renaissance_app}"
KEEP_SERVER_BACKUPS="${DEPLOY_KEEP_BACKUPS:-14}"

export MSYS_NO_PATHCONV=1

assume_yes=false
backup_only=false
force=false
for arg in "$@"; do
  case "$arg" in
    --yes|-y)      assume_yes=true ;;
    --backup-only) backup_only=true ;;
    --force)       force=true ;;
    *) echo "Unknown option: $arg (use --yes, --backup-only, --force)"; exit 1 ;;
  esac
done

SSH_OPTS=(-o BatchMode=yes -o ConnectTimeout=30 -o ServerAliveInterval=15 -o ServerAliveCountMax=4)

step() { echo; echo "==> $*"; }
die()  { echo; echo "!!! $*" >&2; echo "!!! Deploy stopped. Nothing after this step was run." >&2; exit 1; }

# Run a bash script on the server. Extra arguments become $1, $2, ... there.
remote() {
  local script="$1"; shift
  ssh "${SSH_OPTS[@]}" "$HOST" "bash -s --$(printf ' %q' "$@")" <<<"$script"
}

# Like remote(), but retried: only for read-only checks that are safe to repeat.
remote_retry() {
  local i
  for i in 1 2 3 4 5; do
    if remote "$@"; then return 0; fi
    sleep $((i * 5))
  done
  return 1
}

# --- One deploy at a time ---------------------------------------------------------
step "Connecting to $HOST and taking the deploy lock"
remote 'mkdir ~/.deploy.lock 2>/dev/null' \
  || die "Another deploy holds the lock (~/.deploy.lock on the server). If no deploy is running, remove it with: ssh $HOST rmdir ~/.deploy.lock"
release_lock() { remote_retry 'rmdir ~/.deploy.lock 2>/dev/null || true' || echo "!!! Could not release the lock; remove it with: ssh $HOST rmdir ~/.deploy.lock" >&2; }
trap release_lock EXIT

# --- 0. What would be deployed ------------------------------------------------------
step "Checking the server checkout"
state=$(remote '
  set -e
  cd "$1"
  [ "$(git branch --show-current)" = "$2" ] || { echo "WRONG_BRANCH $(git branch --show-current)"; exit 0; }
  git fetch -q origin "$2"
  echo "LIVE $(git rev-parse --short HEAD)"
  echo "TARGET $(git rev-parse --short "origin/$2")"
' "$APP_DIR" "$BRANCH") || die "Could not read the server checkout."
case "$state" in WRONG_BRANCH*) die "Server checkout is on '${state#WRONG_BRANCH }', expected '$BRANCH'." ;; esac
live=$(sed -n 's/^LIVE //p' <<<"$state")
target=$(sed -n 's/^TARGET //p' <<<"$state")
echo "    live: $live   origin/$BRANCH: $target"

if [ "$live" = "$target" ] && ! $force && ! $backup_only; then
  echo "    Already up to date. Nothing to deploy (use --force to rebuild anyway)."
  exit 0
fi

# --- 1. Backup on the server --------------------------------------------------------
stamp=$(date +%Y%m%d_%H%M%S)
name="renaissance_sms_predeploy_${stamp}.sql.gz"
step "Backing up the production database on the server"
# The dump runs detached on the server (like the build), so a dropped SSH connection
# cannot cut it short. It prints "OK <bytes>" or "FAIL" into a status file we poll.
backup_script=$(cat <<'BACKUP'
set -uo pipefail
container=$1 name=$2 keep=$3
tmp=~/backups/.$name.partial
trap 'rm -f "$tmp"' EXIT   # a failed or partial dump never survives this script
if docker exec "$container" sh -c 'mariadb-dump -u root -p"$MYSQL_ROOT_PASSWORD" --single-transaction --quick --routines --triggers --events --databases "$MYSQL_DATABASE"' | gzip > "$tmp" \
   && gzip -t "$tmp" && zcat "$tmp" | tail -n 1 | grep -q "Dump completed"; then
  mv "$tmp" ~/backups/"$name"
  # Keep only the newest N backups on the server.
  ls -1t ~/backups/renaissance_sms_*.sql.gz | tail -n +"$(( keep + 1 ))" | xargs -r rm -f
  echo "OK $(stat -c %s ~/backups/"$name")"
else
  echo "FAIL"
fi
BACKUP
)
status_file="backups/.$name.status"
uploaded=false
for i in 1 2 3 4 5; do
  if ssh "${SSH_OPTS[@]}" "$HOST" 'mkdir -p ~/backups && cat > ~/backups/.run-backup.sh' <<<"$backup_script"; then
    uploaded=true; break
  fi
  sleep $((i * 5))
done
$uploaded || die "Could not upload the backup script."
# Idempotent start: the status file is created first, so a retried call never starts a second dump.
remote_retry '[ -e ~/"$1" ] || { : > ~/"$1"; nohup bash ~/backups/.run-backup.sh "$2" "$3" "$4" > ~/"$1" 2>&1 < /dev/null & }' \
  "$status_file" "$DB_CONTAINER" "$name" "$KEEP_SERVER_BACKUPS" || die "Could not start the backup."
backup_status=""
for _ in $(seq 1 120); do
  sleep 5
  backup_status=$(remote_retry 'tail -n 1 ~/"$1" 2>/dev/null || true' "$status_file" || true)
  case "$backup_status" in OK*|FAIL*) break ;; esac
done
remote_retry 'rm -f ~/"$1"' "$status_file" || true
case "$backup_status" in
  OK*) size=${backup_status#OK } ;;
  *) die "Backup failed or was incomplete (${backup_status:-no result after 10 minutes}). The partial file was not kept." ;;
esac
echo "    ~/backups/$name on the server ($size bytes), verified"

# --- 2. Copy it here ----------------------------------------------------------------
step "Copying the backup to ./backups/"
mkdir -p backups
partial="backups/$name.partial"
copy_failed() { rm -f "$partial"; die "$1 (the verified copy is still on the server: ~/backups/$name)"; }
copied=false
for i in 1 2 3 4 5; do
  if scp "${SSH_OPTS[@]}" -q "$HOST:backups/$name" "$partial"; then copied=true; break; fi
  sleep $((i * 5))
done
$copied || copy_failed "Could not copy the backup down."
local_size=$(wc -c < "$partial" | tr -d ' ')
[ "$local_size" = "$size" ] || copy_failed "Copied backup is $local_size bytes, expected $size."
gzip -t "$partial" || copy_failed "Copied backup failed its integrity check."
mv "$partial" "backups/$name"
echo "    backups/$name ($local_size bytes), verified"

if $backup_only; then
  step "Backup only: done. Nothing was deployed."
  exit 0
fi

# --- 3. Confirm ---------------------------------------------------------------------
step "Commits going live ($live -> $target)"
remote 'cd "$1" && git log --oneline --no-decorate HEAD..origin/"$2" | head -n 30' "$APP_DIR" "$BRANCH" || true
if ! $assume_yes; then
  read -r -p "Deploy these to production now? [y/N] " answer
  [[ "$answer" =~ ^[Yy]$ ]] || die "Cancelled. The verified backup was kept."
fi

# --- 4. Pull ------------------------------------------------------------------------
step "Pulling origin/$BRANCH on the server"
# Retried: a fast-forward pull is safe to repeat if the connection drops mid-way.
remote_retry 'set -e; cd "$1"; git pull -q --ff-only origin "$2"; git log --oneline -1' "$APP_DIR" "$BRANCH" \
  || die "git pull --ff-only failed (has the server checkout diverged?). The running site was not touched."

# --- 5. Build and swap (detached on the server) -------------------------------------
step "Building the new image (the old container keeps serving)"
remote '
  set -e
  cd "$1"
  rm -f ~/deploy-status
  nohup sh -c "docker compose build app > ~/deploy-build.log 2>&1 && docker compose up -d --no-deps app >> ~/deploy-build.log 2>&1; echo DEPLOY_EXIT=\$? > ~/deploy-status" > /dev/null 2>&1 &
' "$APP_DIR" || die "Could not start the build."

started=$(date +%s)
status=""
while [ -z "$status" ]; do
  sleep 15
  status=$(remote_retry 'cat ~/deploy-status 2>/dev/null || true' || true)
  elapsed=$(( $(date +%s) - started ))
  [ "$elapsed" -lt 2700 ] || die "No result after 45 minutes. Check ~/deploy-build.log on the server."
  [ -n "$status" ] || printf '    still building... %dm%02ds\r' $((elapsed / 60)) $((elapsed % 60))
done
echo
[ "$status" = "DEPLOY_EXIT=0" ] || {
  remote_retry 'tail -n 30 ~/deploy-build.log' || true
  die "Build or swap failed ($status). If the build failed, the old container is still serving."
}
echo "    new container started after $(( ($(date +%s) - started) / 60 )) min"

# --- 6. Health check ----------------------------------------------------------------
step "Waiting for $URL to answer 200"
code=""
for _ in $(seq 1 36); do
  code=$(curl -s -o /dev/null -m 20 -w '%{http_code}' "$URL" || true)
  [ "$code" = "200" ] && break
  sleep 5
done
[ "$code" = "200" ] || die "Site still answers '$code' after 3 minutes. Check: ssh $HOST docker logs $APP_CONTAINER --tail 50"
echo "    $URL -> 200"

errors=$(remote_retry '
  docker logs "$1" --since 10m 2>&1 | grep -ciE "\" 500 |production\.ERROR|exception" || true
' "$APP_CONTAINER")
echo "    errors in the new container's log: $errors"

step "Deployed $target. Backup: backups/$name"
[ "$errors" = "0" ] || echo "    Some errors were logged. Check: ssh $HOST docker logs $APP_CONTAINER --since 10m"
