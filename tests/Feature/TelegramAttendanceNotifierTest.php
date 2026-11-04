<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Role;
use App\Models\User;
use App\Services\Attendance\AttendanceMutationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramAttendanceNotifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_telegram_notifier_models_and_config_are_removed(): void
    {
        $this->assertFileDoesNotExist(app_path('Http/Controllers/Support/TelegramAttendanceNotifier.php'));
        $this->assertFileDoesNotExist(app_path('Models/TelegramUser.php'));
        $this->assertFileDoesNotExist(app_path('Models/TelegramUserLog.php'));
        $this->assertNull(config('services.telegram'));

        foreach (['app', 'config', 'routes', 'resources'] as $directory) {
            foreach (File::allFiles(base_path($directory)) as $file) {
                $this->assertStringNotContainsStringIgnoringCase('telegram', $file->getContents(), $file->getRelativePathname());
            }
        }
    }

    public function test_clock_in_does_not_send_telegram_notification(): void
    {
        Http::fake();

        $user = User::factory()->activeEmployee(['view-attendance'])->create();
        $user->assignRole(Role::query()->firstOrCreate([
            'name' => 'staff',
            'guard_name' => 'web',
        ]));

        $result = app(AttendanceMutationService::class)->store([], '127.0.0.1', 'PHPUnit', $user, $user->id);

        $this->assertTrue($result['payload']['success'] ?? false);
        $this->assertTrue(Attendance::query()->where('employee_id', $user->employee->id)->exists());
        Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'api.telegram.org'));
    }
}
