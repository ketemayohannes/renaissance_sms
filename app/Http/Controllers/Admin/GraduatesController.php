<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Division;
use Illuminate\Http\Request;

class GraduatesController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with([
            'latestPromotion.fromGradeLevel',
            'latestPromotion.fromAcademicYear',
            'latestPromotion.toAcademicYear',
            'statusHistory' => fn($q) => $q->where('new_status', 'graduated')->latest()->limit(1),
        ])->graduated();

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Gender
        if ($request->filled('gender')) {
            $query->byGender($request->gender);
        }

        // Graduation Academic Year
        if ($request->filled('graduation_year_id')) {
            $query->whereHas('latestPromotion', function ($q) use ($request) {
                $q->where('from_academic_year_id', $request->graduation_year_id)
                  ->where('status', 'graduated');
            });
        }

        // Division (based on the grade they graduated from)
        if ($request->filled('division_id')) {
            $query->whereHas('latestPromotion.fromGradeLevel', function ($q) use ($request) {
                $q->where('division_id', $request->division_id);
            });
        }

        // Sorting
        $sort      = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');

        if ($sort === 'name') {
            $query->orderBy('first_name', $direction)
                  ->orderBy('father_name', $direction)
                  ->orderBy('grandfather_name', $direction);
        } elseif (in_array($sort, ['student_id', 'gender'])) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        $perPage    = min($request->input('per_page', 50), 100);
        $graduates  = $query->paginate($perPage)->withQueryString();

        $academicYears   = AcademicYear::orderBy('start_date', 'desc')->get();
        $divisions       = Division::where('is_active', true)->orderBy('sort_order')->get();
        $totalGraduates  = Student::graduated()->count();
        $maleGraduates   = Student::graduated()->where('gender', 'M')->count();
        $femaleGraduates = Student::graduated()->where('gender', 'F')->count();

        return view('admin.graduates.index', compact(
            'graduates', 'academicYears', 'divisions',
            'totalGraduates', 'maleGraduates', 'femaleGraduates'
        ));
    }
}
