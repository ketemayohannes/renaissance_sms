<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CachedData;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\DisciplinaryRecord;
use App\Models\StudentAttendance;
use App\Models\StudentEnrollment;
use App\Models\StudentMark;
use App\Models\StudentTermRecord;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return view('admin.academic-years.index', compact('academicYears'));
    }

    public function create()
    {
        return view('admin.academic-years.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active');

        // If setting as active, deactivate all others
        if ($data['is_active']) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
        }

        AcademicYear::create($data);

        CachedData::flush();

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Academic Year created successfully.');
    }

    public function edit(AcademicYear $academicYear)
    {
        return view('admin.academic-years.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active');

        // If setting as active, deactivate all others
        if ($data['is_active']) {
            AcademicYear::where('id', '!=', $academicYear->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $academicYear->update($data);

        CachedData::flush();

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Academic Year updated successfully.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        // Check for student data
        $hasData = StudentEnrollment::where('academic_year_id', $academicYear->id)->exists()
            || StudentMark::where('academic_year_id', $academicYear->id)->exists()
            || StudentAttendance::where('academic_year_id', $academicYear->id)->exists()
            || DisciplinaryRecord::where('academic_year_id', $academicYear->id)->exists()
            || StudentTermRecord::where('academic_year_id', $academicYear->id)->exists();

        if ($hasData) {
            return back()->with('error', 'Cannot delete academic year with existing student data (enrollments, marks, etc.).');
        }

        if ($academicYear->is_active && AcademicYear::count() > 1) {
            return back()->with('error', 'Cannot delete an active academic year if it is not the only year.');
        }

        $academicYear->delete();

        CachedData::flush();

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Academic Year deleted successfully.');
    }
}
