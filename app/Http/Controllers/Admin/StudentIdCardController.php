<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CachedData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\BulkStudentIdsRequest;
use App\Models\AcademicYear;
use App\Models\IdCardSetting;
use App\Models\ReportCardSetting;
use App\Models\Section;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentIdCardController extends Controller
{
    public function idCardsIndex()
    {
        $academicYear = CachedData::activeAcademicYear();
        $sections = Section::with('gradeLevel')
            ->where('academic_year_id', $academicYear->id)
            ->withCount('students')
            ->get();

        return view('admin.students.id-cards-index', compact('sections'));
    }

    public function generateIdCard(Student $student)
    {
        $student->load(['currentEnrollment.section.gradeLevel', 'primaryGuardian']);
        $academicYear = AcademicYear::where('is_active', true)->first();
        $settings = ReportCardSetting::first();
        $idSettings = IdCardSetting::first();

        $pdf = Pdf::loadView('admin.students.id-card-pdf', compact('student', 'academicYear', 'settings', 'idSettings'))
            ->setPaper([0, 0, 242.65, 153.07], 'landscape'); // 8.56cm x 5.40cm in points

        return $pdf->stream("id-card-{$student->student_id}.pdf");
    }

    public function bulkIdCards(Section $section)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $students = Student::whereHas('enrollments', function ($q) use ($section, $academicYear) {
            $q->where('section_id', $section->id)
                ->where('academic_year_id', $academicYear->id)
                ->where('status', 'active');
        })->with(['currentEnrollment.section.gradeLevel', 'primaryGuardian'])->orderBy('first_name')->get();

        $settings = ReportCardSetting::first();
        $idSettings = IdCardSetting::first();

        $pdf = Pdf::loadView('admin.students.id-cards-bulk-pdf', compact('students', 'section', 'academicYear', 'settings', 'idSettings'))
            ->setPaper([0, 0, 242.65, 153.07], 'landscape');

        return $pdf->stream("id-cards-{$section->name}.pdf");
    }

    public function bulkIdCardsSelected(BulkStudentIdsRequest $request)
    {
        $students = Student::whereIn('id', $request->ids)
            ->with(['currentEnrollment.section.gradeLevel', 'primaryGuardian'])
            ->orderBy('first_name')
            ->get();

        $academicYear = AcademicYear::where('is_active', true)->first();
        $settings = ReportCardSetting::first();

        // We can reuse the bulk PDF view but pass students directly
        // The existing view expects $students and $section (for title)
        // We'll pass a dummy section or adjust the view to handle mixed sections

        $pdf = Pdf::loadView('admin.students.id-cards-bulk-pdf', [
            'students' => $students,
            'section' => null, // View should handle null section
            'academicYear' => $academicYear,
            'settings' => $settings,
        ])->setPaper([0, 0, 243.78, 153.07], 'landscape');

        return $pdf->stream('selected-students-id-cards.pdf');
    }
}
