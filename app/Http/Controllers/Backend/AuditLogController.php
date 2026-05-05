<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->filled('module'), fn ($query) => $query->where('module', $request->module))
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->action))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->user_id))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date_to))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('backend.audit.index', [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'modules' => AuditLog::select('module')->distinct()->orderBy('module')->pluck('module'),
            'actions' => AuditLog::select('action')->distinct()->orderBy('action')->pluck('action'),
        ]);
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');

        return view('backend.audit.show', compact('auditLog'));
    }
}
