<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminLog;
use Illuminate\Http\Request;

class LogController extends AdminController
{
    public function index(Request $request)
    {
        $action = preg_replace('/[^a-z_.]/', '', strtolower((string) $request->query('action')));
        $date   = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->query('date')) ? $request->query('date') : null;

        $logs = AdminLog::with('user')
            ->when($action, fn ($q) => $q->where('action', 'like', "{$action}%"))
            ->when($date, fn ($q) => $q->whereDate('created_at', $date))
            ->latest('id')
            ->paginate(30)->withQueryString();

        return view('admin.logs.index', compact('logs', 'action', 'date'));
    }
}
