<?php

namespace Database\Factories;

use App\Models\Permission;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->numerify('Position ####'),
            'status' => 'active',
            'is_protected' => false,
        ];
    }

    /**
     * Grant the position the given permissions, creating any that do not exist yet.
     *
     * @param  list<string>  $permissionNames
     */
    public function withPermissions(array $permissionNames): static
    {
        return $this->afterCreating(function (Position $position) use ($permissionNames): void {
            $permissionIds = collect($permissionNames)
                ->map(fn (string $permissionName): string => Permission::query()
                    ->firstOrCreate(['name' => $permissionName, 'guard_name' => 'web'])
                    ->uuid)
                ->all();

            $position->permissions()->syncWithoutDetaching($permissionIds);
        });
    }
}
