<?php

namespace Database\Seeders;

use App\Models\AttendanceHoliday;
use Illuminate\Database\Seeder;

class AttendanceHolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AttendanceHoliday::query()
            ->whereYear('date', 2026)
            ->delete();

        $holidayRows = [
            ['date' => '2026-01-01', 'name' => 'Tahun Baru 2026 Masehi', 'type' => 1],
            ['date' => '2026-01-16', 'name' => 'Isra Mikraj Nabi Muhammad S.A.W.', 'type' => 1],
            ['date' => '2026-02-17', 'name' => 'Tahun Baru Imlek 2577 Kongzili', 'type' => 1],
            ['date' => '2026-03-19', 'name' => 'Hari Suci Nyepi (Tahun Baru Saka 1948)', 'type' => 1],
            ['date' => '2026-03-21', 'name' => 'Idul Fitri 1447 Hijriah', 'type' => 1],
            ['date' => '2026-03-22', 'name' => 'Idul Fitri 1447 Hijriah', 'type' => 1],
            ['date' => '2026-04-03', 'name' => 'Wafat Yesus Kristus', 'type' => 1],
            ['date' => '2026-04-05', 'name' => 'Kebangkitan Yesus Kristus (Paskah)', 'type' => 1],
            ['date' => '2026-05-01', 'name' => 'Hari Buruh Internasional', 'type' => 1],
            ['date' => '2026-05-14', 'name' => 'Kenaikan Yesus Kristus', 'type' => 1],
            ['date' => '2026-05-27', 'name' => 'Idul Adha 1447 Hijriah', 'type' => 1],
            ['date' => '2026-05-31', 'name' => 'Hari Raya Waisak 2570 BE', 'type' => 1],
            ['date' => '2026-06-01', 'name' => 'Hari Lahir Pancasila', 'type' => 1],
            ['date' => '2026-06-16', 'name' => '1 Muharam Tahun Baru Islam 1448 Hijriah', 'type' => 1],
            ['date' => '2026-08-17', 'name' => 'Proklamasi Kemerdekaan', 'type' => 1],
            ['date' => '2026-08-25', 'name' => 'Maulid Nabi Muhammad S.A.W.', 'type' => 1],
            ['date' => '2026-12-25', 'name' => 'Kelahiran Yesus Kristus', 'type' => 1],
            ['date' => '2026-02-16', 'name' => 'Tahun Baru Imlek 2577 Kongzili', 'type' => 2],
            ['date' => '2026-03-18', 'name' => 'Hari Suci Nyepi (Tahun Baru Saka 1948)', 'type' => 2],
            ['date' => '2026-03-20', 'name' => 'Idul Fitri 1447 Hijriah', 'type' => 2],
            ['date' => '2026-03-23', 'name' => 'Idul Fitri 1447 Hijriah', 'type' => 2],
            ['date' => '2026-03-24', 'name' => 'Idul Fitri 1447 Hijriah', 'type' => 2],
            ['date' => '2026-05-15', 'name' => 'Kenaikan Yesus Kristus', 'type' => 2],
            ['date' => '2026-05-28', 'name' => 'Idul Adha 1447 Hijriah', 'type' => 2],
            ['date' => '2026-12-24', 'name' => 'Kelahiran Yesus Kristus', 'type' => 2],
        ];

        foreach ($holidayRows as $holidayRow) {
            $attendanceHoliday = new AttendanceHoliday;
            $attendanceHoliday->date = $holidayRow['date'];
            $attendanceHoliday->name = $holidayRow['name'];
            $attendanceHoliday->type = (int) $holidayRow['type'];
            $attendanceHoliday->save();
        }
    }
}
