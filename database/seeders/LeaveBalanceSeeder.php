<?php

namespace Database\Seeders;

use App\Services\Leave\AnnualLeaveBalanceService;
use Illuminate\Database\Seeder;
use RuntimeException;
use Throwable;

class LeaveBalanceSeeder extends Seeder
{
    public function __construct(private readonly AnnualLeaveBalanceService $annualLeaveBalanceService) {}

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $this->annualLeaveBalanceService->syncAll(now('Asia/Jakarta'));
        } catch (Throwable $throwable) {
            throw new RuntimeException('LeaveBalanceSeeder gagal dijalankan.', 0, $throwable);
        }
    }
}
