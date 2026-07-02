<?php

namespace Tests\Unit;

use App\Services\Leave\AnnualLeaveBalanceService;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class AnnualLeaveBalanceServiceTest extends TestCase
{
    public function test_accrual_starts_after_a_completed_month_and_stops_at_personal_quota(): void
    {
        $service = new AnnualLeaveBalanceService;

        $this->assertSame(0, $service->calculateEarnedQuotaForPersonalQuota(
            '2020-01-01',
            2026,
            Carbon::parse('2026-01-31 23:59:59', 'Asia/Jakarta'),
            4
        ));
        $this->assertSame(1, $service->calculateEarnedQuotaForPersonalQuota(
            '2020-01-01',
            2026,
            Carbon::parse('2026-02-01 00:10:00', 'Asia/Jakarta'),
            4
        ));
        $this->assertSame(4, $service->calculateEarnedQuotaForPersonalQuota(
            '2020-01-01',
            2026,
            Carbon::parse('2026-05-01 00:10:00', 'Asia/Jakarta'),
            4
        ));
        $this->assertSame(4, $service->calculateEarnedQuotaForPersonalQuota(
            '2020-01-01',
            2026,
            Carbon::parse('2026-06-01 00:10:00', 'Asia/Jakarta'),
            4
        ));
    }

    public function test_balance_accrual_is_independent_from_request_eligibility(): void
    {
        $service = new AnnualLeaveBalanceService;
        $asOf = Carbon::parse('2026-07-01 09:00:00', 'Asia/Jakarta');

        $this->assertFalse($service->isEligible('2025-07-02', $asOf));
        $this->assertTrue($service->isEligible('2025-07-01', $asOf));
        $this->assertSame(3, $service->calculateEarnedQuotaForPersonalQuota(
            '2026-04-20',
            2026,
            $asOf,
            4
        ));
    }

    public function test_mid_year_join_accrues_from_join_month_until_personal_quota(): void
    {
        $service = new AnnualLeaveBalanceService;

        $this->assertSame(0, $service->calculateEarnedQuotaForPersonalQuota(
            '2026-04-20',
            2026,
            Carbon::parse('2026-04-30 23:59:59', 'Asia/Jakarta'),
            4
        ));
        $this->assertSame(1, $service->calculateEarnedQuotaForPersonalQuota(
            '2026-04-20',
            2026,
            Carbon::parse('2026-05-01 00:10:00', 'Asia/Jakarta'),
            4
        ));
        $this->assertSame(3, $service->calculateEarnedQuotaForPersonalQuota(
            '2026-04-20',
            2026,
            Carbon::parse('2026-07-01 00:10:00', 'Asia/Jakarta'),
            4
        ));
        $this->assertSame(4, $service->calculateEarnedQuotaForPersonalQuota(
            '2026-04-20',
            2026,
            Carbon::parse('2026-08-01 00:10:00', 'Asia/Jakarta'),
            4
        ));
    }
}
