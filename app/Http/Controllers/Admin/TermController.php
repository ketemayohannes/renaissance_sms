<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;

class TermController extends Controller
{
    public function index()
    {
        // PERFORMANCE: Use cached data
        $academicYears = \App\Helpers\CachedData::academicYears();
        $terms = Term::with(['academicYear', 'quarters', 'semester'])
            ->orderBy('academic_year_id', 'desc')
            ->orderBy('start_date')
            ->get();
        
        return view('admin.terms.index', compact('terms', 'academicYears'));
    }

    public function create()
    {
        $academicYears = AcademicYear::where('is_active', true)->get();
        return view('admin.terms.create', compact('academicYears'));
    }

    public function store(Request $request)
    {
        $validationRules = [
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:quarter,semester',
            'term_number' => 'required|integer|min:1|max:4',
        ];

        // For quarters, require start and end dates
        if ($request->type === 'quarter') {
            $validationRules['start_date'] = 'required|date';
            $validationRules['end_date'] = 'required|date|after:start_date';
        }

        // For semesters, require quarter selection
        if ($request->type === 'semester') {
            $validationRules['quarter_ids'] = 'required|array|size:2';
            $validationRules['quarter_ids.*'] = 'exists:terms,id';
        }

        $request->validate($validationRules);

        // If creating a semester, validate and link quarters
        if ($request->type === 'semester') {
            $quarters = Term::whereIn('id', $request->quarter_ids)
                ->where('type', 'quarter')
                ->where('academic_year_id', $request->academic_year_id)
                ->orderBy('term_number')
                ->get();

            if ($quarters->count() !== 2) {
                return back()->withErrors(['quarter_ids' => 'Please select exactly 2 quarters.'])->withInput();
            }

            // Validate consecutive quarters (1,2 or 3,4)
            $quarterNumbers = $quarters->pluck('term_number')->toArray();
            if (!in_array($quarterNumbers, [[1, 2], [3, 4]])) {
                return back()->withErrors(['quarter_ids' => 'Please select consecutive quarters (Q1+Q2 or Q3+Q4).'])->withInput();
            }

            // Auto-calculate semester dates from quarters
            $startDate = $quarters->min('start_date');
            $endDate = $quarters->max('end_date');

            // Create the semester
            $semester = Term::create([
                'academic_year_id' => $request->academic_year_id,
                'name' => $request->name,
                'type' => 'semester',
                'term_number' => $request->term_number,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_grading_open' => $request->has('is_grading_open'),
                'is_master_grading_open' => $request->has('is_master_grading_open'),
            ]);

            // Link quarters to the semester
            Term::whereIn('id', $request->quarter_ids)->update(['parent_term_id' => $semester->id]);

            return redirect()->route('admin.terms.index')
                ->with('success', 'Semester created successfully and linked to quarters.');
        }

        // Create quarter normally
        $data = $request->all();
        $data['is_grading_open'] = $request->has('is_grading_open');
        $data['is_master_grading_open'] = $request->has('is_master_grading_open');
        
        Term::create($data);

        return redirect()->route('admin.terms.index')
            ->with('success', 'Term created successfully.');
    }

    public function edit(Term $term)
    {
        // PERFORMANCE: Use cached data
        $academicYears = \App\Helpers\CachedData::academicYears();
        $term->load('quarters');
        return view('admin.terms.edit', compact('term', 'academicYears'));
    }

    public function update(Request $request, Term $term)
    {
        $validationRules = [
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:quarter,semester',
            'term_number' => 'required|integer|min:1|max:4',
        ];

        // For quarters, require start and end dates
        if ($request->type === 'quarter') {
            $validationRules['start_date'] = 'required|date';
            $validationRules['end_date'] = 'required|date|after:start_date';
        }

        // For semesters, require quarter selection
        if ($request->type === 'semester') {
            $validationRules['quarter_ids'] = 'required|array|size:2';
            $validationRules['quarter_ids.*'] = 'exists:terms,id';
        }

        $request->validate($validationRules);

        // Prepare data with checkbox handling
        $data = $request->all(); // We will use this unless it's a semester special case, but actually simpler to prep vars
        $gradingOpen = $request->has('is_grading_open');
        $masterGradingOpen = $request->has('is_master_grading_open');

        // If updating a semester
        if ($request->type === 'semester') {
            $quarters = Term::whereIn('id', $request->quarter_ids)
                ->where('type', 'quarter')
                ->where('academic_year_id', $request->academic_year_id)
                ->orderBy('term_number')
                ->get();

            if ($quarters->count() !== 2) {
                return back()->withErrors(['quarter_ids' => 'Please select exactly 2 quarters.'])->withInput();
            }

            // Validate consecutive quarters
            $quarterNumbers = $quarters->pluck('term_number')->toArray();
            if (!in_array($quarterNumbers, [[1, 2], [3, 4]])) {
                return back()->withErrors(['quarter_ids' => 'Please select consecutive quarters (Q1+Q2 or Q3+Q4).'])->withInput();
            }

            // Auto-calculate semester dates
            $startDate = $quarters->min('start_date');
            $endDate = $quarters->max('end_date');

            // Update the semester
            $term->update([
                'academic_year_id' => $request->academic_year_id,
                'name' => $request->name,
                'type' => 'semester',
                'term_number' => $request->term_number,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_grading_open' => $gradingOpen,
                'is_master_grading_open' => $masterGradingOpen,
            ]);

            // Unlink old quarters and link new ones
            Term::where('parent_term_id', $term->id)->update(['parent_term_id' => null]);
            Term::whereIn('id', $request->quarter_ids)->update(['parent_term_id' => $term->id]);

            return redirect()->route('admin.terms.index')
                ->with('success', 'Semester updated successfully.');
        }

        // Update quarter normally
        $data['is_grading_open'] = $gradingOpen;
        $data['is_master_grading_open'] = $masterGradingOpen;
        
        $term->update($data);

        return redirect()->route('admin.terms.index')
            ->with('success', 'Term updated successfully.');
    }

    public function destroy(Term $term)
    {
        // If deleting a semester, unlink its quarters first
        if ($term->isSemester()) {
            Term::where('parent_term_id', $term->id)->update(['parent_term_id' => null]);
        }

        $term->delete();

        return redirect()->route('admin.terms.index')
            ->with('success', 'Term deleted successfully.');
    }

    // API endpoint to get quarters for a specific academic year
    public function getQuarters(Request $request, $academicYearId)
    {
        $includeAssignedTo = $request->get('include_assigned_to');

        $quarters = Term::where('academic_year_id', $academicYearId)
            ->where('type', 'quarter')
            ->where(function($q) use ($includeAssignedTo) {
                $q->whereNull('parent_term_id');
                if ($includeAssignedTo) {
                    $q->orWhere('parent_term_id', $includeAssignedTo);
                }
            })
            ->orderBy('term_number')
            ->get(['id', 'name', 'term_number', 'start_date', 'end_date']);

        return response()->json($quarters);
    }
}
