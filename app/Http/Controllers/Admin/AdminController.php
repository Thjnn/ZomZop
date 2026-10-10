<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use Illuminate\Database\Eloquent\Model;

abstract class AdminController extends Controller
{
    protected function log(string $action, ?Model $subject = null, array $changes = []): void
    {
        AdminLog::record($action, $subject, $changes);
    }
}
