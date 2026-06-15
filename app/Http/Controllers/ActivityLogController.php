<?php

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_activity_logs'), 403);
        $query = ActivityLog::with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs    = $query->paginate(25)->withQueryString();
        $actions = ActivityAction::cases();
        $modules = ActivityLog::select('module')->distinct()->orderBy('module')->pluck('module');
        $users   = User::orderBy('name')->get();

        return view('activity-logs.index', compact('logs', 'actions', 'modules', 'users'));
    }
}
