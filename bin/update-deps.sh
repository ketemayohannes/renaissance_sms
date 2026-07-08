#!/usr/bin/env bash
#
# Regenerate composer.lock / package-lock.json on the HOST so dependency updates
# survive a Docker image rebuild, then rebuild the app image.
#
# Why this exists: the image is built from the host's lock files (see Dockerfile —
# `COPY composer.json composer.lock` + `composer install`, and `COPY package-lock.json`
# + `npm ci`). Updating packages *inside* a running container only changes that
# container's throwaway layer, which is discarded on rebuild. The lock files on the
# host are the source of truth — update them, commit them, and every rebuild follows.
#
# Usage:
#   bin/update-deps.sh                 # update all PHP + JS deps (within constraints)
#   bin/update-deps.sh php             # only composer
#   bin/update-deps.sh js              # only npm
#   bin/update-deps.sh php monolog/monolog   # bump a specific composer package
#   bin/update-deps.sh js vite@latest        # bump a specific npm package
#
# Requires Docker running. Does NOT commit, push, or rebuild without you seeing the
# lock diffs — it prints the commit command at the end for you to run.
set -euo pipefail

cd "$(dirname "$0")/.."

APP_CONTAINER="renaissance_app"
NODE_IMAGE="node:22-alpine"   # keep in sync with the Dockerfile assets stage

target="${1:-all}"
shift || true
pkgs=("$@")

# Stop Git Bash / MSYS from rewriting absolute container paths in -v mounts on Windows.
export MSYS_NO_PATHCONV=1

update_php() {
  echo "==> Updating PHP dependencies (composer)…"
  if docker ps --format '{{.Names}}' | grep -qx "$APP_CONTAINER"; then
    # Reuse the running app container: it already has composer + every required PHP
    # extension, so the resolved lock matches the production platform exactly.
    docker exec "$APP_CONTAINER" composer update ${pkgs[@]+"${pkgs[@]}"}
    docker cp "${APP_CONTAINER}:/var/www/html/composer.lock" ./composer.lock
    docker cp "${APP_CONTAINER}:/var/www/html/composer.json" ./composer.json
  else
    echo "    (app container not running — using a throwaway composer container)"
    docker run --rm -v "$PWD:/app" -w /app composer:2 \
      update --ignore-platform-reqs ${pkgs[@]+"${pkgs[@]}"}
  fi
}

update_js() {
  echo "==> Updating JS dependencies (npm)…"
  # The runtime image has no Node (it lives only in the build stage), so run a one-off
  # node container that mounts the project and rewrites package-lock.json on the host.
  if [ "${#pkgs[@]}" -gt 0 ]; then
    docker run --rm -v "$PWD:/app" -w /app "$NODE_IMAGE" npm install "${pkgs[@]}"
  else
    docker run --rm -v "$PWD:/app" -w /app "$NODE_IMAGE" npm update
  fi
}

case "$target" in
  all) update_php; update_js ;;
  php) update_php ;;
  js)  update_js ;;
  *)   echo "Unknown target: '$target' (use: all | php | js)"; exit 1 ;;
esac

echo
echo "==> Rebuilding the app image with the new lock files…"
docker compose up -d --build app

echo
echo "==> Done. Review the lock diffs, then commit so the update is permanent:"
echo "    git add composer.json composer.lock package.json package-lock.json"
echo "    git commit -m 'chore: update dependencies'"
