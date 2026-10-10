<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends AdminController
{
    private function ensureCustomer(User $user): void
    {
        abort_unless($user->role === 'customer', 404);
    }

    public function index(Request $request)
    {
        $filters = Validator::make($request->only(['q', 'status']), [
            'q'      => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:active,locked'],
        ])->valid();

        $customers = User::where('role', 'customer')
            ->withCount('orders')
            ->withSum(['orders as spent' => fn ($q) => $q->where('status', 'completed')], 'total')
            ->when(trim($filters['q'] ?? ''), fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")))
            ->when(($filters['status'] ?? null) === 'active', fn ($q) => $q->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'locked', fn ($q) => $q->where('is_active', false))
            ->latest()
            ->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('customers', 'filters'));
    }

    public function show(User $user)
    {
        $this->ensureCustomer($user);

        $orders = $user->orders()->with('branch')->latest()->paginate(15);
        $spent  = (int) $user->orders()->where('status', 'completed')->sum('total');

        return view('admin.customers.show', ['customer' => $user, 'orders' => $orders, 'spent' => $spent]);
    }

    public function toggleLock(User $user)
    {
        $this->ensureCustomer($user);

        $user->is_active = !$user->is_active;
        $changes = AdminLog::diff($user);
        $user->save();
        $this->log($user->is_active ? 'customer.unlock' : 'customer.lock', $user, $changes);

        return back()->with('success', $user->is_active ? "Đã mở khoá {$user->name}." : "Đã khoá {$user->name}.");
    }
}
