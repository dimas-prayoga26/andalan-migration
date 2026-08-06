<?php

namespace Database\Seeders;

use App\Models\EmployeeDeployment;
use App\Models\OfficeLocation;
use App\Models\Position;
use App\Models\RulesOfAttendace;
use Illuminate\Database\Seeder;
use RuntimeException;
use Throwable;

class RulesOfAttendacesSeeder extends Seeder
{
    /**
     * @var array<string, array{address: string, latitude: float, longitude: float}>
     */
    private const OFFICE_LOCATIONS = [
        'Jakarta' => [
            'address' => '4, Jl. Bhineka Blok Bhineka No.26, RT.4/RW.2, Cipedak, Kec. Jagakarsa, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12630',
            'latitude' => -6.3636699,
            'longitude' => 106.8016359,
        ],
        'Yogyakarta' => [
            'address' => 'Bulurejo, RT.04/RW.02, Gantalan, Minomartani, Kec. Ngaglik, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55581',
            'latitude' => -7.7299965,
            'longitude' => 110.4040011,
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $yogyakartaOfficeLocationId = null;

            foreach (self::OFFICE_LOCATIONS as $name => $locationData) {
                $officeLocation = OfficeLocation::query()->updateOrCreate(
                    ['name' => $name],
                    $locationData + ['is_active' => true],
                );

                RulesOfAttendace::query()->updateOrCreate(
                    [
                        'office_location_id' => $officeLocation->id,
                        'attendance_type' => RulesOfAttendace::TYPE_FIXED,
                    ],
                    [
                        'ip_range' => '182.8',
                        'radius' => 75,
                        'office_start_time' => '08:00:00',
                        'office_end_time' => '17:00:00',
                        'attendance_type' => RulesOfAttendace::TYPE_FIXED,
                        'is_active' => true,
                    ],
                );

                if ($name === 'Yogyakarta') {
                    $yogyakartaOfficeLocationId = $officeLocation->id;
                }
            }

            if (is_string($yogyakartaOfficeLocationId)) {
                EmployeeDeployment::query()
                    ->whereNull('current_office_location_id')
                    ->update([
                        'current_office_location_id' => $yogyakartaOfficeLocationId,
                        'workplace' => 'Yogyakarta',
                    ]);
            }

            $jakartaOfficeLocation = OfficeLocation::query()->where('name', 'Jakarta')->first(['id']);
            $driverPosition = Position::query()->where('name', 'Driver')->first(['id']);
            if ($jakartaOfficeLocation instanceof OfficeLocation && $driverPosition instanceof Position) {
                $driverAttendanceRule = RulesOfAttendace::query()->updateOrCreate(
                    [
                        'office_location_id' => $jakartaOfficeLocation->id,
                        'attendance_type' => RulesOfAttendace::TYPE_FLEXIBLE,
                    ],
                    [
                        'ip_range' => '182.8',
                        'radius' => 75,
                        'office_start_time' => '00:00:00',
                        'office_end_time' => '23:59:00',
                        'office_reset_time' => '00:00:00',
                        'attendance_type' => RulesOfAttendace::TYPE_FLEXIBLE,
                        'is_active' => true,
                    ],
                );
                $driverAttendanceRule->positions()->syncWithoutDetaching([$driverPosition->id]);
            }
        } catch (Throwable $throwable) {
            throw new RuntimeException('RulesOfAttendacesSeeder gagal dijalankan.', 0, $throwable);
        }
    }
}
