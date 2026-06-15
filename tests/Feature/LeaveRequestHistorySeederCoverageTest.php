<?php

namespace Tests\Feature;

use Database\Seeders\LeaveRequestHistorySeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use ReflectionMethod;
use Tests\TestCase;

class LeaveRequestHistorySeederCoverageTest extends TestCase
{
    public function test_leave_request_history_seeder_creates_one_pending_request_for_each_leave_type(): void
    {
        $seeder = File::get(database_path('seeders/LeaveRequestHistorySeeder.php'));

        foreach ([
            "->whereIn(DB::raw('LOWER(code)'), ['annual', 'sick', 'special', 'unpaid'])",
            "'reason' => '[Seeder] RNB special leave - '.\$specialLeaveSubTypeName,",
            "'reason' => '[Seeder] RNB annual leave pending',",
            "'reason' => '[Seeder] RNB sick leave pending',",
            "'reason' => '[Seeder] RNB unpaid leave pending',",
            "'status' => 'pending',",
            "'approved_by' => null,",
            "'approved_at' => null,",
            "'special_leave_sub_type_id' => \$specialLeaveSubTypeId,",
            "'special_leave_sub_type_name' => \$specialLeaveSubTypeName,",
            '$seedWorkingDates = $this->nextSeedWorkingDates($baseDay, 4, $this->seedBlockedDateValues());',
            'if (! $cursorDate->isWeekend() && ! array_key_exists($cursorDate->toDateString(), $blockedDateSet)) {',
            "Schema::hasTable('attendances_holidays')",
            "->orWhere('reason', 'like', '[Seeder] RNB dummy leave request%')",
            "->orWhere('reason', 'like', '[Seeder] RNB % leave approved')",
            "Schema::hasColumn('leave_requests', 'handover_notes')",
        ] as $expectedSeederFragment) {
            $this->assertStringContainsString($expectedSeederFragment, $seeder);
        }

        $this->assertStringNotContainsString("'status' => 'approved',", $seeder);
        $this->assertStringNotContainsString("'status' => 'rejected',", $seeder);
        $this->assertStringNotContainsString("'title' => 'Approved',", $seeder);
        $this->assertStringNotContainsString("'to_status' => 'approved',", $seeder);
        $this->assertSame(3, substr_count($seeder, "'event_type' => 'supervisor_review',"));
        $this->assertSame(2, substr_count($seeder, "'to_status' => 'complete',"));
        $this->assertSame(1, substr_count($seeder, "'event_type' => 'hr_verification',"));
        $this->assertStringNotContainsString('[Seeder] RNB dummy leave request rejected', $seeder);
        $this->assertStringNotContainsString('[Seeder] RNB dummy leave request supervisor review only', $seeder);
        $this->assertStringNotContainsString('$baseDay->copy()->addDays(6)->toDateString()', $seeder);
    }

    public function test_leave_request_history_seeder_date_picker_skips_weekends_and_holidays(): void
    {
        $method = new ReflectionMethod(LeaveRequestHistorySeeder::class, 'nextSeedWorkingDates');
        $method->setAccessible(true);

        $dates = $method->invoke(
            new LeaveRequestHistorySeeder,
            Carbon::parse('2026-06-12', 'Asia/Jakarta'),
            4,
            ['2026-06-16']
        );

        $this->assertSame(
            ['2026-06-12', '2026-06-15', '2026-06-17', '2026-06-18'],
            array_map(
                static fn (Carbon $date): string => $date->toDateString(),
                $dates
            )
        );
    }
}
