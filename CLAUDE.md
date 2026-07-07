# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Renaissance SMS — a school management system for an Ethiopian K–12 school, built on **Laravel 12 / PHP 8.2**. It uses **SQLite** locally (`database/database.sqlite`) and MySQL in the Docker/production setup. Dates support the Ethiopian calendar via `andegna/calender`; PDFs use DomPDF; spreadsheets use PhpSpreadsheet.

### Build history
`docs/build-history/` holds the implementation plans and walkthroughs from when this project was originally built with Google Antigravity (organized by the device each came from). When asked to explain or refactor an older part of the code, check there first — the original plan/walkthrough for that feature is often the fastest way to recover the intent behind it.

## Commands

```bash
# First-time setup (installs deps, copies .env, generates key, migrates, builds assets)
composer setup

# Run everything for development at once (server + queue worker + logs + vite)
composer dev

# Or run pieces individually:
php artisan serve                 # app at http://127.0.0.1:8000
npm run dev                       # vite dev server (hot asset rebuild)
npm run build                     # production asset build
php artisan queue:listen          # process queued jobs (report-card ZIP exports, notice fan-out)

# Tests (Pest + PHPUnit; runs against SQLite :memory:)
php artisan test                  # full suite
php artisan test tests/Feature/PromotionControllerTest.php   # one file
php artisan test --filter=executes_graduation_correctly       # one test by name

# Formatting (Laravel Pint)
vendor/bin/pint                   # fix
vendor/bin/pint --test            # check only

# Database
php artisan migrate               # apply migrations
php artisan migrate:fresh --seed  # rebuild + seed (see DatabaseSeeder)
```

**Never point tests at the real database.** `phpunit.xml` uses `:memory:` and every feature test uses `RefreshDatabase` (drops all tables). If that env is ever changed to a file path, running the suite will wipe dev data.

## Architecture

### Role-based multi-portal
There is one Laravel app serving four portals, chosen by the user's Spatie role. `Auth\RedirectController` sends each user to their dashboard after login (`admin.*`, `teacher.*`, `student.*`, `parent.*`). In `routes/web.php`, each portal is a route group gated first by `role:` middleware and then, for sensitive actions, by an additional `permission:` middleware (e.g. `permission:promote students`). Roles and permissions are defined in `database/seeders/RolePermissionSeeder.php` — that seeder is the source of truth for what each role can do. Controllers are namespaced by portal (`App\Http\Controllers\{Admin,Teacher,Student,Parent}`).

Global middleware (registered in `bootstrap/app.php`, applied to every web request): `SessionTimeout`, `ForcePasswordChange` (redirects users with a `temp_password` to set a real one), and `NoCache`. Middleware aliases of note: `role`, `permission`, `parent_access` (`VerifyParentAccessToStudent`).

### Division data isolation (`HasDivisionRestriction` trait) — important
Many models (`Student`, `Section`, `GradeLevel`, `StudentMark`, `Employee`, `TeacherAssignment`, `StudentAttendance`, `AcademicActivity`, `Division`) use the `HasDivisionRestriction` trait, which adds a **global scope** filtering rows to the logged-in employee's `division_id` (Kindergarten / Elementary / High School). Consequences to keep in mind:
- A handful of roles bypass it entirely (Super Admin, IT / System Admin, Registrar, General Manager, HR Manager, Senior Finance Officer, Librarian), and the scope is **skipped when running in console**.
- In **feature tests** using `actingAs()`, the scope **is** active — a division-restricted acting user will not see other divisions' data. Use a bypassing role or `Model::withoutGlobalScopes()` when that's not what you want.
- To query across divisions deliberately, use `withoutGlobalScopes()`.

### Service + Action layers
Controllers stay thin by delegating to `app/Services/*` (e.g. `GradingService`, `ReportCardService`, `StudentService`, `AttendanceService`, `DisciplineEscalationService`). Single-purpose write operations live in `app/Actions/*` (e.g. `Grades/StoreBulkGrades`, `Students/ProcessStudentWithdrawal`). When adding behavior, prefer extending the relevant service over fattening the controller.

### Grading and the term hierarchy
Academic time is modeled in `Term` as a self-referential hierarchy: **yearly → semester → quarter**, linked by `parent_term_id` and typed by `type` (`yearly`/`semester`/`quarter`) with `term_number`. `GradingService` (~1200 lines) is the calculation core — section/student totals, averages, rankings, semester and yearly rollups. Report cards are produced by `ReportCardService` + DomPDF views under `resources/views/admin/report-cards/`. Bulk report-card ZIP exports run as the queued job `GenerateSectionReportCards`, tracked by the `ExportRequest` model and written to a **private** disk (not public storage).

Two grading invariants confirmed by the system owner (do not "fix" these the other way):
- **Rankings exclude withdrawn/dropped-out students.** Every ranking path filters enrollments to `['active', 'completed', 'graduated']` — this is intentional and current. A `docs/build-history` walkthrough (`desktop/af6ba61a-*`) describes the opposite decision (including withdrawn students for historical accuracy); that document is **outdated/superseded** — do not restore that behavior.
- **Live component marks always beat `TERM_TOTAL` marks.** A `TERM_TOTAL` `StudentMark` (written by the Master Sheet or Auto-Calculate Semester) is only a fallback for subject+term combinations that have no component marks. Component marks entered/edited later must win everywhere; `TERM_TOTAL` rows can go stale and must never override them.

### Notifications (channels are opt-in per class)
Notifications resolve their delivery channels through the `DeterminesChannels` trait, which combines three inputs: the global toggles and per-event matrix in `config/communication.php` (`events.{key}.{sms|email|in_app}`), the recipient's per-user preferences, and an **allowed-channels list passed by each Notification's `via()`**. A channel that isn't in that allowed list is never sent even if config/prefs enable it — so a notification with a `toDatabase()` method must include `'in_app'` in its `via()` allowed list to show in the bell. SMS routes to a custom channel (`AfricasTalkingChannel` or `SmsEthiopiaChannel`) based on the configured provider.

### Auditing
Models using the `Auditable` trait (`Student`, `StudentMark`, `StudentAttendance`, `Subject`, `GradeLevel`, `AcademicActivity`, `ActivitySubmission`) automatically write change records to `audit_logs`, surfaced in the admin audit-log viewer.

### Promotion flow (end-of-year)
Student promotion is a once-a-year, end-of-year operation with specific invariants enforced in `PromotionController`:
- **Timing gate:** `processForm`, `preview`, and `execute` are blocked until the active academic year's final quarter (Quarter 4) has ended; otherwise they redirect to the promotions index with a message.
- **One decision per source year:** a unique index on `student_promotions (student_id, from_academic_year_id)` plus a matching `updateOrCreate` key means re-running never duplicates — it updates the single record in place. Concurrent double-submits hit the DB constraint and are caught (SQLSTATE 23000) into a clean message.
- **Hold-until-enroll:** `execute` records the decision but does not create the next-year enrollment (except graduation, which closes the current enrollment). A registrar later enrolls held students from the History page; reversal deletes the promotion record and restores prior state. Promoted/retained students keep their current-year enrollment `active` so gradebook/master-sheet data stays visible.

### Security-sensitive conventions
- `users.temp_password` is **encrypted at rest** via an `Attribute` cast on the `User` model and cleared to `null` once the user completes the forced password change. It is also in `$hidden`.
- Uploaded student/employee documents and report-card exports are served from a **private** disk through controller actions, never linked from public storage.
