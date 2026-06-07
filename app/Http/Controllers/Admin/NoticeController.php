<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DispatchNoticeNotifications;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $query = Notice::with('postedBy')->latest('publish_date');

        if ($request->filled('audience')) {
            $query->where('target_audience', $request->audience);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $notices = $query->paginate(20);
        $totalActive = Notice::active()->count();

        return view('admin.notices.index', compact('notices', 'totalActive'));
    }

    public function create()
    {
        return view('admin.notices.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'content'         => 'required|string',
            'target_audience' => 'required|in:All,Parent,Teacher,Student',
            'publish_date'    => 'required|date',
            'expiry_date'     => 'nullable|date|after:publish_date',
            'is_active'       => 'boolean',
            'attachment'      => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx|max:5120',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('notices', 'public');
        }

        $notice = Notice::create([
            'title'           => $validated['title'],
            'content'         => $validated['content'],
            'target_audience' => $validated['target_audience'],
            'publish_date'    => $validated['publish_date'],
            'expiry_date'     => $validated['expiry_date'] ?? null,
            'is_active'       => $request->boolean('is_active', true),
            'posted_by'       => auth()->id(),
            'attachment'      => $attachmentPath,
        ]);

        // Dispatch notifications in the background — does not block the HTTP request
        DispatchNoticeNotifications::dispatch($notice);

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice published successfully.');
    }

    public function edit(Notice $notice)
    {
        return view('admin.notices.edit', compact('notice'));
    }

    public function update(Request $request, Notice $notice)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'content'         => 'required|string',
            'target_audience' => 'required|in:All,Parent,Teacher,Student',
            'publish_date'    => 'required|date',
            'expiry_date'     => 'nullable|date|after:publish_date',
            'is_active'       => 'boolean',
            'attachment'      => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx|max:5120',
        ]);

        if ($request->hasFile('attachment')) {
            if ($notice->attachment) {
                Storage::disk('public')->delete($notice->attachment);
            }
            $validated['attachment'] = $request->file('attachment')->store('notices', 'public');
        }

        $notice->update([
            'title'           => $validated['title'],
            'content'         => $validated['content'],
            'target_audience' => $validated['target_audience'],
            'publish_date'    => $validated['publish_date'],
            'expiry_date'     => $validated['expiry_date'] ?? null,
            'is_active'       => $request->boolean('is_active', true),
            'attachment'      => $validated['attachment'] ?? $notice->attachment,
        ]);

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice updated successfully.');
    }

    public function destroy(Notice $notice)
    {
        // Clean up associated database notifications
        \Illuminate\Support\Facades\DB::table('notifications')
            ->where('type', \App\Notifications\NewNoticePublished::class)
            ->where('data->notice_id', $notice->id)
            ->delete();

        if ($notice->attachment) {
            Storage::disk('public')->delete($notice->attachment);
        }
        $notice->delete();

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice deleted.');
    }

    // Notification fan-out is handled by DispatchNoticeNotifications job (see app/Jobs/)
}
