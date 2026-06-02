<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\TelegramUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VerifyTelegramUsernameTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_telegram_username_skips_telegram_api_when_employee_already_linked(): void
    {
        config(['services.telegram.bot_token' => 'test-bot-token']);

        [$user, $employee] = $this->createAuthenticatedUserWithEmployee('staff_sync_skip');

        TelegramUser::query()->create([
            'employee_id' => $employee->id,
            'chat_id' => '123456',
            'first_name' => 'Staff',
            'last_name' => 'Sync',
            'username' => 'staff_sync_skip',
            'language_code' => 'id',
        ]);

        Http::fake();

        $response = $this
            ->actingAs($user)
            ->postJson(route('attendance.verify-telegram-username'));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Verifikasi Telegram berhasil.',
            ]);

        Http::assertNothingSent();
    }

    public function test_verify_telegram_username_creates_telegram_user_when_match_found(): void
    {
        config(['services.telegram.bot_token' => 'test-bot-token']);

        [$user, $employee] = $this->createAuthenticatedUserWithEmployee('staff_sync_create');

        Http::fake([
            'https://api.telegram.org/*' => Http::response([
                'ok' => true,
                'result' => [
                    [
                        'update_id' => 1,
                        'message' => [
                            'from' => [
                                'id' => 1001,
                                'first_name' => 'Staff',
                                'last_name' => 'Create',
                                'username' => 'staff_sync_create',
                                'language_code' => 'id',
                            ],
                            'chat' => [
                                'id' => 1001,
                                'type' => 'private',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this
            ->actingAs($user)
            ->postJson(route('attendance.verify-telegram-username'));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Verifikasi Telegram berhasil.',
            ]);

        $this->assertDatabaseHas('telegram_users', [
            'employee_id' => $employee->id,
            'chat_id' => '1001',
            'username' => 'staff_sync_create',
        ]);
    }

    public function test_verify_telegram_username_returns_error_when_telegram_api_fails(): void
    {
        config(['services.telegram.bot_token' => 'test-bot-token']);

        [$user] = $this->createAuthenticatedUserWithEmployee('staff_sync_error');

        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => false], 500),
        ]);

        $response = $this
            ->actingAs($user)
            ->postJson(route('attendance.verify-telegram-username'));

        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Gagal mengambil data update Telegram.',
            ]);
    }

    /**
     * @return array{0: User, 1: Employee}
     */
    private function createAuthenticatedUserWithEmployee(string $username): array
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

        return [$user, $employee];
    }
}
