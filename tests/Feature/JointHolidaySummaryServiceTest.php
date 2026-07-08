<?php

namespace Tests\Feature;

use App\Models\AttendanceHoliday;
use App\Services\Leave\JointHolidaySummaryService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class JointHolidaySummaryServiceTest extends TestCase
{
    public function test_joint_holiday_label_counts_actual_passed_days_from_system_date(): void
    {
        $jointHolidays = collect([
            $this->holiday('2026-01-02', 'Cuti Bersama Tahun Baru'),
            $this->holiday('2026-07-08', 'Cuti Bersama Tengah Tahun'),
            $this->holiday('2026-12-24', 'Cuti Bersama Natal'),
        ]);

        Carbon::setTestNow(Carbon::parse('2026-07-08 10:00:00', 'Asia/Jakarta'));

        try {
            $summary = app(JointHolidaySummaryService::class)->summarize($jointHolidays, now('Asia/Jakarta'));

            $this->assertSame('2 / 3 Days', $summary['label']);
            $this->assertSame([
                'Cuti Bersama Tahun Baru (02 Jan)',
                'Cuti Bersama Tengah Tahun (08 Jul)',
                'Cuti Bersama Natal (24 Dec)',
            ], $summary['items']);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_joint_holiday_label_counts_zero_before_first_joint_holiday(): void
    {
        $summary = app(JointHolidaySummaryService::class)->summarize(
            collect([$this->holiday('2026-01-02', 'Cuti Bersama Tahun Baru')]),
            Carbon::parse('2026-01-01 23:59:00', 'Asia/Jakarta')
        );

        $this->assertSame('0 / 1 Day', $summary['label']);
    }

    private function holiday(string $date, string $name): AttendanceHoliday
    {
        return new AttendanceHoliday([
            'date' => $date,
            'name' => $name,
            'type' => 2,
        ]);
    }
}
