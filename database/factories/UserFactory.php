<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'is_active' => true,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Give the user an active employee and deployment so it passes User::canLogin(),
     * holding a position that grants the given permissions.
     *
     * @param  list<string>  $permissionNames
     */
    public function activeEmployee(array $permissionNames = []): static
    {
        return $this->afterCreating(function (User $user) use ($permissionNames): void {
            $this->createActiveDeploymentFor(
                $user,
                Position::factory()->withPermissions($permissionNames)->create()
            );
        });
    }

    /**
     * Give the user an active employee holding the Super Administrator position.
     */
    public function superAdministrator(): static
    {
        return $this->afterCreating(function (User $user): void {
            $this->createActiveDeploymentFor(
                $user,
                Position::query()->firstOrCreate(
                    ['system_key' => Position::KEY_SUPER_ADMINISTRATOR],
                    ['name' => 'Super Administrator', 'status' => 'active', 'is_protected' => true]
                )
            );
        });
    }

    private function createActiveDeploymentFor(User $user, Position $position): void
    {
        EmployeeDeployment::factory()
            ->for(Employee::factory()->for($user))
            ->for($position)
            ->create();

        $user->unsetRelation('employee');
    }
}
