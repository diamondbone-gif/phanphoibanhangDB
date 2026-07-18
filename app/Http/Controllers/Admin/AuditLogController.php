<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'event' => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:120'],
            'ip' => ['nullable', 'ip'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $logs = AuditLog::query()
            ->when($filters['event'] ?? null, fn ($query, $event) => $query->where('event', $event))
            ->when($filters['subject'] ?? null, fn ($query, $subject) => $query->where('auditable_type', 'like', '%'.addcslashes($subject, '%_\\').'%'))
            ->when($filters['ip'] ?? null, fn ($query, $ip) => $query->where('ip_address', $ip))
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('created_at', '<=', $to))
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        $events = AuditLog::query()->select('event')->distinct()->orderBy('event')->pluck('event');

        return view('admin.auth.audit-logs.index', compact('logs', 'events'));
    }
}
