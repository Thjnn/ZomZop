<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Services\BranchPayroll;
use Illuminate\Database\Seeder;

class PayrollSeeder extends Seeder
{
    public function run(BranchPayroll $payroll): void
    {
        foreach (Branch::pluck('id') as $branchId) {
            $payroll->calculate($branchId, now()->month, now()->year);
        }
    }
}
