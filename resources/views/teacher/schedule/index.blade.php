<x-teacher-layout>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 font-heading">My Schedule</h1>
                <p class="text-slate-500 mt-1">Your weekly teaching timetable at a glance.</p>
            </div>
            <div class="flex gap-3">
                <button class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-slate-600 font-medium hover:bg-slate-50 transition-colors shadow-sm">
                    Print Schedule
                </button>
            </div>
        </div>

        <!-- Weekly Timetable Grid -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            @php
                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            @endphp

            @foreach($days as $day)
                <div class="flex flex-col gap-4">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 border-t-4 border-t-indigo-500">
                        <h3 class="font-bold text-slate-900 text-center">{{ $day }}</h3>
                    </div>

                    @if(isset($schedule[$day]))
                        @foreach($schedule[$day] as $session)
                            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 hover:border-indigo-300 transition-all group relative overflow-hidden">
                                <!-- Time Indicator -->
                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-50 group-hover:bg-indigo-500 transition-colors"></div>
                                
                                <div class="flex flex-col gap-2">
                                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md inline-block w-fit">
                                        {{ $session['time'] }}
                                    </span>
                                    <h4 class="font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">
                                        {{ $session['subject'] }}
                                    </h4>
                                    <div class="flex items-center justify-between mt-1 pt-2 border-t border-slate-50">
                                        <span class="text-xs font-medium text-slate-500">Section {{ $session['section'] }}</span>
                                        <span class="text-xs font-medium text-slate-400">Room {{ $session['room'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="bg-slate-50/50 p-4 rounded-2xl border border-dashed border-slate-200 text-center">
                            <span class="text-xs text-slate-400 font-medium italic">No sessions</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Legend -->
        <div class="mt-8 bg-white p-6 rounded-2xl border border-slate-200">
            <h4 class="text-sm font-bold text-slate-900 mb-4">Timetable Notes</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-500">
                <div class="flex items-start gap-2">
                    <div class="w-2 h-2 rounded-full bg-indigo-500 mt-1.5 flex-shrink-0"></div>
                    <p>Standard periods are 45 minutes long with 5-minute transition times.</p>
                </div>
                <div class="flex items-start gap-2">
                    <div class="w-2 h-2 rounded-full bg-indigo-500 mt-1.5 flex-shrink-0"></div>
                    <p>Lunch break is daily from 12:30 PM to 01:30 PM.</p>
                </div>
            </div>
        </div>
    </div>
</x-teacher-layout>
