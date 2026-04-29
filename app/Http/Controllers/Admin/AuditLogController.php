<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $logs = AuditLog::with('user')
            ->when($search, function($query) use ($search) {
                $query->where('event', 'like', "%{$search}%")
                    ->orWhere('auditable_type', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('admin.audit-logs.index', compact('logs', 'search'));
    }
}
