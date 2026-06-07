<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Notice;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::active()
            ->forAudience('Teacher')
            ->with('postedBy')
            ->orderByDesc('publish_date')
            ->paginate(15);

        return view('teacher.notices.index', compact('notices'));
    }

    public function show($id)
    {
        $notice = Notice::find($id);
        if (!$notice || !$notice->is_active || !in_array($notice->target_audience, ['Teacher', 'All'])) {
            return redirect()->route('teacher.notices.index')
                ->with('error', 'This announcement is no longer available.');
        }
        return view('teacher.notices.show', compact('notice'));
    }
}
