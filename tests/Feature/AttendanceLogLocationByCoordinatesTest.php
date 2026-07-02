<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AttendanceLogLocationByCoordinatesTest extends TestCase
{
    public function test_attendance_log_location_is_saved_from_plus_code_with_coordinate_fallback(): void
    {
        $attendanceMutationService = File::get(app_path('Services/Attendance/AttendanceMutationService.php'));

        $this->assertStringContainsString("'location' => \$this->formatAttendanceLogLocation(\$locationMetadata['plus_code_compound_code'] ?? null, \$latitude, \$longitude),", $attendanceMutationService);
        $this->assertStringContainsString("'location' => \$trackingContext['location'],", $attendanceMutationService);
        $this->assertStringContainsString("\$plusCodeCompoundCode = isset(\$plusCode['compound_code']) && is_string(\$plusCode['compound_code'])", $attendanceMutationService);
        $this->assertStringContainsString("'plus_code_compound_code' => \$plusCodeCompoundCode,", $attendanceMutationService);
        $this->assertStringContainsString("'language' => 'en',", $attendanceMutationService);
        $this->assertStringContainsString('$this->parsePlusCodeCompoundCode($plusCodeCompoundCode)', $attendanceMutationService);
        $this->assertStringContainsString('private function mergeAddressComponents(array $addressComponents, array $plusCodeAddressComponents): array', $attendanceMutationService);
        $this->assertStringContainsString('private function parsePlusCodeCompoundCode(?string $compoundCode): array', $attendanceMutationService);
        $this->assertStringContainsString("preg_replace('/^[A-Z0-9]{4,}\\+[A-Z0-9]{2,}\\s*/i", $attendanceMutationService);
        $this->assertStringContainsString("\$resolvedComponents['address_village'] ??= \$locationPart;", $attendanceMutationService);
        $this->assertStringContainsString("\$resolvedComponents['address_regency'] ??= \$locationPart;", $attendanceMutationService);
        $this->assertStringContainsString("\$resolvedComponents['address_province'] ??= \$locationPart;", $attendanceMutationService);
        $this->assertStringContainsString('private function extractPostalCode(string $locationPart): ?string', $attendanceMutationService);
        $this->assertStringContainsString('private function formatAttendanceLogLocation(mixed $plusCodeCompoundCode, float $latitude, float $longitude): ?string', $attendanceMutationService);
        $this->assertStringContainsString('return trim($plusCodeCompoundCode);', $attendanceMutationService);
        $this->assertStringContainsString('return $this->formatCoordinateMapUrl($latitude, $longitude);', $attendanceMutationService);
        $this->assertStringContainsString('private function formatCoordinateMapUrl(float $latitude, float $longitude): ?string', $attendanceMutationService);
        $this->assertStringContainsString("return 'https://www.google.com/maps?q='.\$coordinateLabel;", $attendanceMutationService);
        $this->assertStringNotContainsString("'location' => isset(\$locationMetadata['formatted_address'])", $attendanceMutationService);
    }
}
