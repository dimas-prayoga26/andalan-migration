<?php

namespace Tests\Feature;

use App\Http\Controllers\ReportController;
use App\Models\AttendanceHoliday;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class ReportHolidayDatabaseTest extends TestCase
{
    public function test_report_uses_attendance_holidays_table_without_external_http_request(): void
    {
        try {
            Http::preventStrayRequests();

            Schema::create('attendances_holidays', function (Blueprint $table): void {
                $table->id();
                $table->date('date');
                $table->string('name');
                $table->unsignedTinyInteger('type')->default(1);
                $table->timestamps();
            });

            AttendanceHoliday::query()->create([
                'date' => '2026-05-27',
                'name' => 'Idul Adha',
                'type' => 1,
            ]);

            AttendanceHoliday::query()->create([
                'date' => '2026-05-28',
                'name' => 'Cuti Bersama Idul Adha',
                'type' => 2,
            ]);

            AttendanceHoliday::query()->create([
                'date' => '2026-06-01',
                'name' => 'Hari Lahir Pancasila',
                'type' => 1,
            ]);

            $buildHolidayMapByMonth = new ReflectionMethod(ReportController::class, 'buildHolidayMapByMonth');
            $holidayMap = $buildHolidayMapByMonth->invoke(app(ReportController::class), 2026, 5);

            $this->assertSame([
                '2026-05-27' => [
                    'name' => 'Idul Adha',
                    'is_national_holiday' => true,
                ],
                '2026-05-28' => [
                    'name' => 'Cuti Bersama Idul Adha',
                    'is_national_holiday' => false,
                ],
            ], $holidayMap);
            Http::assertNothingSent();
        } finally {
            Schema::dropIfExists('attendances_holidays');
        }
    }
}
