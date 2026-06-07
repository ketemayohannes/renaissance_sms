<?php

namespace App\Jobs;

use App\Models\Notice;
use App\Models\User;
use App\Notifications\NewNoticePublished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class DispatchNoticeNotifications implements ShouldQueue
{
    use Queueable;

    /** How many times to retry if the job fails. */
    public int $tries = 3;

    /** Timeout in seconds before the job is considered failed. */
    public int $timeout = 120;

    public function __construct(public readonly Notice $notice) {}

    /**
     * Fan out NewNoticePublished notifications to all targeted users.
     * Runs in the background queue — does NOT block the HTTP request.
     */
    public function handle(): void
    {
        $query = User::where('id', '!=', $this->notice->posted_by);

        if ($this->notice->target_audience === 'All') {
            $query->whereHas('roles', function ($q) {
                $q->whereIn('name', ['Parent', 'Teacher', 'Homeroom Teacher']);
            });
        } else {
            $roleMap = [
                'Parent'  => ['Parent'],
                'Teacher' => ['Teacher', 'Homeroom Teacher'],
                'Student' => ['Student'],
            ];
            $roles = $roleMap[$this->notice->target_audience] ?? [];
            if (!empty($roles)) {
                $query->whereHas('roles', function ($q) use ($roles) {
                    $q->whereIn('name', $roles);
                });
            }
        }

        // Process in chunks of 100 to avoid memory exhaustion
        $query->chunk(100, function ($users) {
            Notification::send($users, new NewNoticePublished($this->notice));
        });
    }
}
