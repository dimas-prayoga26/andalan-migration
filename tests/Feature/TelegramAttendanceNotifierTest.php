<?php

namespace Tests\Feature;

use App\Http\Controllers\Support\TelegramAttendanceNotifier;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\TelegramUser;
use App\Models\TelegramUserLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramAttendanceNotifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifier_writes_success_log_for_clock_in(): void
    {
        config(['services.telegram.bot_token' => 'test-bot-token']);

        [$user, $employee] = $this->createUserEmployeeAndTelegramLink('notif_success');
        $attendance = Attendance::query()->create([
            'employee_id' => $employee->id,
            'date' => now('Asia/Jakarta')->toDateString(),
            'clock_in' => '08:00:00',
            'status' => 'Hadir',
        ]);

        Http::fake([
            'https://api.telegram.org/*' => Http::response([
                'ok' => true,
                'result' => [
                    'message_id' => 11,
                ],
            ], 200),
        ]);

        app(TelegramAttendanceNotifier::class)->notifyCheckIn($user, $attendance);

        $this->assertDatabaseHas('telegram_users_logs', [
            'telegram_user_id' => $employee->telegramUser?->id,
            'attendance_id' => $attendance->id,
            'notification_type' => 'clock_in',
            'is_success' => 1,
            'http_status_code' => 200,
            'error_message' => null,
        ]);

        $createdLog = TelegramUserLog::query()->first();
        $this->assertNotNull($createdLog);
        $this->assertIsArray($createdLog->response_payload);
        $this->assertSame(true, $createdLog->response_payload['ok'] ?? null);
    }

    public function test_notifier_writes_failure_log_for_clock_out_when_telegram_api_error(): void
    {
        config(['services.telegram.bot_token' => 'test-bot-token']);

        [$user, $employee] = $this->createUserEmployeeAndTelegramLink('notif_failure');
        $attendance = Attendance::query()->create([
            'employee_id' => $employee->id,
            'date' => now('Asia/Jakarta')->toDateString(),
            'clock_in' => '08:00:00',
            'clock_out' => '17:00:00',
            'status' => 'Pulang',
        ]);

        Http::fake([
            'https://api.telegram.org/*' => Http::response([
                'ok' => false,
                'description' => 'Bad Request: chat not found',
            ], 400),
        ]);

        app(TelegramAttendanceNotifier::class)->notifyCheckOut($user, $attendance);

        $createdLog = TelegramUserLog::query()->first();
        $this->assertNotNull($createdLog);
        $this->assertFalse((bool) $createdLog->is_success);
        $this->assertSame(400, $createdLog->http_status_code);
        $this->assertIsArray($createdLog->response_payload);
        $this->assertNotNull($createdLog->error_message);
    }

    /**
     * @return array{0: User, 1: Employee}
     */
    private function createUserEmployeeAndTelegramLink(string $username): array
    {
        $user = User::query()->create([
            'username' => $username,
            'email' => $username.'@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $employee = Employee::query()->create([
            'user_id' => $user->id,
            'status' => 'Active',
        ]);

        TelegramUser::query()->create([
            'employee_id' => $employee->id,
            'chat_id' => '999111222',
            'first_name' => 'Notif',
            'last_name' => 'Staff',
            'username' => $username,
            'language_code' => 'id',
        ]);

        $employee->load('telegramUser');

        return [$user, $employee];
    }
}
