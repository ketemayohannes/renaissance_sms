{{-- Academic context for a date (from AcademicCalendarService::getAcademicContext). --}}
@if(! empty($academicContext))
    @php
        $c = $academicContext;
        $labels = [
            'holiday' => 'Holiday', 'closure' => 'School closed', 'break' => 'Semester break', 'weekend' => 'Weekend',
            'non_school_day' => 'No classes scheduled', 'outside_year' => 'Outside the academic year',
            'examination' => 'Examination period', 'revision' => 'Revision week', 'event' => 'School event', 'instructional' => 'School day',
        ];
    @endphp
    <div class="rounded-2xl border px-4 py-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm {{ $c['isSchoolDay'] ? 'bg-indigo-50/60 border-indigo-100 text-indigo-900 dark:bg-indigo-500/10 dark:border-indigo-500/20 dark:text-indigo-100' : 'bg-amber-50 border-amber-200 text-amber-900 dark:bg-amber-500/10 dark:border-amber-500/30 dark:text-amber-100' }}" role="status">
        <span class="font-black">{{ $c['ethiopianDate']['formatted'] }} E.C.</span>
        @if($c['quarter'])
            <span class="font-semibold">Quarter {{ $c['quarter'] }}{{ $c['academicWeek'] ? ' · Week '.$c['academicWeek'] : '' }}{{ $c['semester'] ? ' · Semester '.$c['semester'] : '' }}</span>
        @endif
        <span class="font-bold">{{ $labels[$c['dayType']] ?? $c['dayType'] }}{{ ! $c['isSchoolDay'] && $c['dayTypeReason'] && $c['dayType'] !== 'weekend' ? ': '.$c['dayTypeReason'] : '' }}</span>
        @unless($c['isSchoolDay'])
            <span class="basis-full text-xs">The academic calendar does not list this as a school day. You can still record attendance if classes were held.</span>
        @endunless
    </div>
@endif
