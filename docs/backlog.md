# Backlog

Items here are planned but deliberately deferred — do not build until the current
grading bug-fix work (see branch `fix/grade-calculation`) is deployed and stable.

## Feature: Auto-calculate Semester and Yearly results

**Trigger:** Fully automatic, based on the quarter's existing `end_date` field on the
`Term` model (no manual "close" button needed) — likely needs a scheduled task/cron
job (`app/Console`, Laravel scheduler) checking for quarters whose `end_date` has
just passed.

**Behavior:**
- When Quarter 4's `end_date` passes → auto-calculate Semester 2 (and Yearly, since
  Yearly = average of Semester 1 + Semester 2).
- When Quarter 2's `end_date` passes → auto-calculate Semester 1.
- Also re-trigger this recalculation whenever "Sync" (Recalculate) is clicked on
  report card or roster views.

**Open design questions to resolve before building:**
- Should late grade entries after the auto-trigger date still be allowed to
  re-sync/recalculate, or does auto-closing lock the quarter permanently?
  (Needs owner decision.)
- Check whether a "close quarter" status/concept already exists anywhere in the
  system to build on, or if this is entirely new. (Starting point: `Term` already
  has `is_grading_open` and `is_master_grading_open` boolean flags, toggled manually
  via `Admin\TermController` — there is currently no date-driven closing.)
- This may make the existing manual "Auto-Calculate Semester" button
  (`SectionGradeController::calculateSemester` → `GradingService::calculateSemesterGrades`)
  redundant — decide whether to remove, repurpose, or keep as a manual override.

**Implementation notes (context from the 2026-07 bug-fix work):**
- Any auto-calculation must follow the components-first rule documented in CLAUDE.md
  ("Live component marks always beat TERM_TOTAL marks"). The current
  `calculateSemesterGrades()` still prefers stored quarter TERM_TOTAL rows and
  resolves quarters by name rather than `parent_term_id` — both should be corrected
  before (or as part of) building this feature.
