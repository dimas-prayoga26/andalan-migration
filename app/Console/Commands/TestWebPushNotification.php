<?php

namespace App\Console\Commands;

use App\Models\DeviceSubscription;
use App\Models\Employee;
use App\Models\User;
use App\Services\WebPushNotificationService;
use App\Support\Branding\HostBrandingResolver;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

#[Signature('webpush:test {recipient : User ID, employee ID, username, email, employee code, or employee name} {--latest} {--host=} {--url=} {--title=Meeting Baru: Test Push} {--body=Jadwal meeting test berhasil dikirim dari SIAP.}')]
#[Description('Send a test web push notification to a user or employee device subscription')]
class TestWebPushNotification extends Command
{
    public function handle(WebPushNotificationService $webPushNotificationService): int
    {
        $recipient = trim((string) $this->argument('recipient'));
        $user = $this->findUser($recipient);

        if (! $user instanceof User) {
            $this->error('Recipient not found.');

            return self::FAILURE;
        }

        $user->loadMissing('employee.profile');
        $employee = $user->employee;
        $subscriptions = $this->subscriptionsFor($user, $employee);

        $this->line('User ID: '.$user->id);
        $this->line('Username: '.($user->username ?: '-'));
        $this->line('Employee ID: '.($employee?->id ?: '-'));
        $this->line('Employee Name: '.($employee?->profile?->name ?: '-'));
        $this->line('VAPID public key: '.(config('services.webpush.vapid_public_key') ? 'loaded' : 'missing'));
        $this->line('VAPID private key: '.(config('services.webpush.vapid_private_key') ? 'loaded' : 'missing'));
        $this->line('Subscriptions found: '.$subscriptions->count());

        if ($subscriptions->isEmpty()) {
            $this->warn('No device subscription found for this user/employee.');

            return self::SUCCESS;
        }

        if ((bool) $this->option('latest')) {
            $subscriptions = $subscriptions
                ->sortByDesc('last_subscribed_at')
                ->take(1)
                ->values();
        }

        $host = trim((string) $this->option('host'));
        $brand = app(HostBrandingResolver::class)->resolve($host !== '' ? $host : null);
        $iconUrl = (string) ($brand['pwa_icon_192_url'] ?? $brand['logo_url'] ?? asset('images/images.png'));
        $this->line('Icon URL: '.$iconUrl);

        $this->table(
            ['ID', 'Browser', 'Platform', 'IP', 'Last Subscribed', 'Last Used', 'User Agent'],
            $subscriptions
                ->map(fn (DeviceSubscription $subscription): array => [
                    $subscription->id,
                    $subscription->browser ?: '-',
                    $subscription->platform ?: '-',
                    $subscription->ip_address ?: '-',
                    $subscription->last_subscribed_at?->timezone('Asia/Jakarta')->format('d M Y H:i:s') ?: '-',
                    $subscription->last_used_at?->timezone('Asia/Jakarta')->format('d M Y H:i:s') ?: '-',
                    mb_strimwidth((string) $subscription->user_agent, 0, 90, '...'),
                ])
                ->all()
        );

        $result = $webPushNotificationService->sendToSubscriptions($subscriptions, [
            'title' => (string) $this->option('title'),
            'body' => (string) $this->option('body'),
            'url' => (string) ($this->option('url') ?: route('zoom-meeting.index')),
            'icon' => $iconUrl,
            'tag' => 'test-push-'.now()->timestamp,
        ]);

        $this->table(['Metric', 'Value'], collect($result)
            ->map(fn (mixed $value, string $key): array => [$key, $value === null ? '-' : (string) $value])
            ->values()
            ->all());

        return self::SUCCESS;
    }

    private function findUser(string $recipient): ?User
    {
        $user = User::query()
            ->where('id', $recipient)
            ->orWhere('username', $recipient)
            ->orWhere('email', $recipient)
            ->first();

        if ($user instanceof User) {
            return $user;
        }

        $employee = Employee::query()
            ->with('user')
            ->where('id', $recipient)
            ->orWhere('employee_code', $recipient)
            ->orWhereHas('profile', function ($query) use ($recipient): void {
                $query->where('name', 'like', '%'.$recipient.'%');
            })
            ->first();

        return $employee?->user;
    }

    /**
     * @return Collection<int, DeviceSubscription>
     */
    private function subscriptionsFor(User $user, ?Employee $employee): Collection
    {
        return DeviceSubscription::query()
            ->where(function ($query) use ($user, $employee): void {
                $query->where('user_id', $user->id);

                if ($employee instanceof Employee) {
                    $query->orWhere('employee_id', $employee->id);
                }
            })
            ->get()
            ->unique('endpoint_hash')
            ->values();
    }
}
