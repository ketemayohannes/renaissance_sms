# Walkthrough: Academic Reports & General Reports Extensions

We have successfully implemented three new report types in the **Academic Reports** module and created a brand new **General Reports** module with downloadable summaries.

---

## Part 1: Academic Reports Extension

### 1. Section Top 10 Report
- **Goal:** Display students ranked 1-10 in a given section, highlighting the top 3 with custom badges.
- **Implementation:**
  - Added the `section_top_10` report type option to the [index.blade.php](file:///c:/Users/RIS%20IT%20Admin/Downloads/Antigravity/renaissance_sms/resources/views/admin/academic-reports/index.blade.php) UI.
  - Added support in [AcademicReportController.php](file:///c:/Users/RIS%20IT%20Admin/Downloads/Antigravity/renaissance_sms/app/Http/Controllers/Admin/AcademicReportController.php#L95-L107) to slice and format the roster data for the top 10 students.
  - Created a beautiful new view [section-top10.blade.php](file:///c:/Users/RIS%20IT%20Admin/Downloads/Antigravity/renaissance_sms/resources/views/admin/academic-reports/section-top10.blade.php) with gold, silver, and bronze podium cards for ranks 1, 2, and 3. Fully optimized to support yearly and semester/quarter rows.

### 2. Category Ranks Report
- **Goal:** Display the top-ranked students across grade groups (Grades 1–6, 7–8, and 9–12), filtered by division.
- **Implementation:**
  - Added the `category_ranks` report type option in [index.blade.php](file:///c:/Users/RIS%20IT%20Admin/Downloads/Antigravity/renaissance_sms/resources/views/admin/academic-reports/index.blade.php).
  - Added division-filtering logic in [AcademicReportController.php](file:///c:/Users/RIS%20IT%20Admin/Downloads/Antigravity/renaissance_sms/app/Http/Controllers/Admin/AcademicReportController.php#L318-L432) to map Elementary to Grades 1-6 & 7-8 categories and High School to Grade 9-12 categories.
  - Created [category-ranks.blade.php](file:///c:/Users/RIS%20IT%20Admin/Downloads/Antigravity/renaissance_sms/resources/views/admin/academic-reports/category-ranks.blade.php) with solid, non-dynamic color CSS backgrounds to display side-by-side tables.

### 3. Academic Excellence Report
- **Goal:** Display students with overall term averages of 90% and above.
- **Implementation:**
  - Added the `academic_excellence` report type option in [index.blade.php](file:///c:/Users/RIS%20IT%20Admin/Downloads/Antigravity/renaissance_sms/resources/views/admin/academic-reports/index.blade.php).
  - Added `academicExcellence` method in [AcademicReportController.php](file:///c:/Users/RIS%20IT%20Admin/Downloads/Antigravity/renaissance_sms/app/Http/Controllers/Admin/AcademicReportController.php#L434-L529) that queries averages >= 90% and allows filtering by section or grade level, or displaying school-wide.
  - Created [academic-excellence.blade.php](file:///c:/Users/RIS%20IT%20Admin/Downloads/Antigravity/renaissance_sms/resources/views/admin/academic-reports/academic-excellence.blade.php) with stars and honor roll details.

---

## Part 2: General Reports Module (New)

We created a new, extensible reporting module to house downloadable documents.

### 1. Routes
- Added `GET /admin/reports` mapping to the dashboard index.
- Added `GET /admin/reports/top3-per-section` mapping to the PDF generator.

### 2. Reports Controller
- Created [ReportsController.php](file:///c:/Users/RIS%20IT%20Admin/Downloads/Antigravity/renaissance_sms/app/Http/Controllers/Admin/ReportsController.php) to filter, query, and gather ranking data for both per-section and per-grade summary honor rolls.

### 3. UI and PDF Templates
- Created the general reports index dashboard [index.blade.php](file:///c:/Users/RIS%20IT%20Admin/Downloads/Antigravity/renaissance_sms/resources/views/admin/reports/index.blade.php) for choosing timelines and divisions.
- Created the DomPDF-compatible template [top3-per-section-pdf.blade.php](file:///c:/Users/RIS%20IT%20Admin/Downloads/Antigravity/renaissance_sms/resources/views/admin/reports/top3-per-section-pdf.blade.php).
- Modified the main sidebar [admin-sidebar.blade.php](file:///c:/Users/RIS%20IT%20Admin/Downloads/Antigravity/renaissance_sms/resources/views/layouts/partials/admin-sidebar.blade.php#L320-L330) to include a link to the new reports dashboard.

---

## 3. Promoted to Grade — Yearly Report Card

The yearly report card now auto-fills the **"Promoted to Grade:"** field from the `student_promotions` table.

### Applies To
- **Elementary** (division_id = 2) and **High School** (division_id = 3) only
- **Kindergarten** (division_id = 1) is excluded — those report cards don't have promotion tracking

### What Was Changed

| File | Change |
|---|---|
| [`ReportCardService.php`](file:///c:/Users/RIS%20IT%20Admin/Downloads/Antigravity/renaissance_sms/app/Services/ReportCardService.php) | Added `StudentPromotion` lookup in `getStudentReportParams()` and batch lookup in `getSectionReportParams()`. Passes `$promotedToGrade` (grade name string or null) to view. |
| [`yearly-pdf.blade.php`](file:///c:/Users/RIS%20IT%20Admin/Downloads/Antigravity/renaissance_sms/resources/views/admin/report-cards/yearly-pdf.blade.php) | Both "Promoted to Grade:" lines (Q4 comment box + student details panel) now show the actual grade name from DB. Falls back to blank underline if no promotion record exists yet. |
| [`bulk-yearly-pdf.blade.php`](file:///c:/Users/RIS%20IT%20Admin/Downloads/Antigravity/renaissance_sms/resources/views/admin/report-cards/bulk-yearly-pdf.blade.php) | Same fix for both locations. Extracts `$promotedToGrade` from per-student `$data` in the loop. |
| [`GenerateSectionReportCards.php`](file:///c:/Users/RIS%20IT%20Admin/Downloads/Antigravity/renaissance_sms/app/Jobs/GenerateSectionReportCards.php) | Background ZIP export job's `prepareViewData()` also now looks up and passes `promotedToGrade`. |

### Display Logic
- Q4 comment box: shows the grade number only (e.g., **"9"**) on the correct line based on whether the student is promoted or detained (retained), centered on the underline.
- Checkboxes: the active option (Promoted vs Detained) dynamically shows a checkmark (`✓`), while the other option stays unchecked.
- No literal `&nbsp;` is printed on the empty underline when there is no grade value.
- Student details panel (back of card): shows the label dynamically as **"Promoted to Grade:"** or **"Detained in Grade:"** with the full grade name (e.g. **"Grade 9"**), centered on the underline.
- Grade Suffix Suffixes (Streams): dynamically strips stream names from grade levels (e.g. `Grade 11 (Natural)` or `Grade 11 (Social)` prints as **`11`** in Q4 box and **`Grade 11`** on the back page details).
- Homeroom Teacher: resolved relationship attribute to render the homeroom teacher's name dynamically in nice title case (e.g., `Samuel Mengiste Aynalem` instead of blank).

### Test Results
```
PASS  Tests\Feature\GeneralReportsModuleTest
  ✓ admin can access general reports dashboard   75.47s
  ✓ admin can download top 3 per section pdf      1.52s
  ✓ admin can download below 75 pdf               0.18s

  Tests: 3 passed (13 assertions)
```
