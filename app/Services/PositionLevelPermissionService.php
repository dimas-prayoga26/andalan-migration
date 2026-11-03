<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Position;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Menu permissions are assigned per position level; position_has_permissions stays the runtime source,
 * so a level is "all" only when every position in it holds the permission.
 */
class PositionLevelPermissionService
{
    public const STATE_ALL = 'all';

    public const STATE_PARTIAL = 'partial';

    public const STATE_NONE = 'none';

    public const STATES = [
        self::STATE_ALL,
        self::STATE_PARTIAL,
        self::STATE_NONE,
    ];

    /**
     * @return Collection<string, array{key: string, label: string, positions: Collection<int, Position>}>
     */
    public function levels(): Collection
    {
        $positionsByLevel = Position::query()
            ->whereNotSystemKey(Position::KEY_SUPER_ADMINISTRATOR)
            ->orderBy('name')
            ->get(['id', 'name', 'system_key', 'level'])
            ->groupBy('level');

        return collect(Position::LEVEL_LABELS)->map(fn (string $label, string $level): array => [
            'key' => $level,
            'label' => $label,
            'positions' => $positionsByLevel->get($level, collect())->values(),
        ]);
    }

    /**
     * @param  Collection<string, array{key: string, label: string, positions: Collection<int, Position>}>  $levels
     * @param  Collection<int, string>  $grantedPositionIds
     * @return array<string, array{state: string, granted_position_names: array<int, string>}>
     */
    public function levelStates(Collection $levels, Collection $grantedPositionIds): array
    {
        return $levels->map(function (array $level) use ($grantedPositionIds): array {
            $grantedPositions = $level['positions']
                ->filter(fn (Position $position): bool => $grantedPositionIds->contains((string) $position->id));

            $state = match (true) {
                $grantedPositions->isEmpty() => self::STATE_NONE,
                $grantedPositions->count() === $level['positions']->count() => self::STATE_ALL,
                default => self::STATE_PARTIAL,
            };

            return [
                'state' => $state,
                'granted_position_names' => $grantedPositions->pluck('name')->values()->all(),
            ];
        })->all();
    }

    /**
     * A missing or "partial" level keeps the positions that already hold the permission.
     *
     * @param  array<string, array<string, string>>  $submittedLevelStates  Permission uuid => level => state.
     */
    public function sync(array $submittedLevelStates): void
    {
        $levels = $this->levels();

        DB::transaction(function () use ($levels, $submittedLevelStates): void {
            $this->permissionsWithPositionIds()
                ->each(function (Permission $permission) use ($levels, $submittedLevelStates): void {
                    $grantedPositionIds = $this->positionIdsOf($permission);
                    $submittedStates = $submittedLevelStates[(string) $permission->uuid] ?? [];

                    $positionIds = $levels->flatMap(function (array $level) use ($grantedPositionIds, $submittedStates): Collection {
                        $levelPositionIds = $level['positions']->map(fn (Position $position): string => (string) $position->id);

                        return match ($submittedStates[$level['key']] ?? self::STATE_PARTIAL) {
                            self::STATE_ALL => $levelPositionIds,
                            self::STATE_NONE => collect(),
                            default => $levelPositionIds->intersect($grantedPositionIds),
                        };
                    });

                    $permission->positions()->sync($positionIds->unique()->values()->all());
                });
        });
    }

    /**
     * Gives a new position every permission its level holds in full, so it matches the Assign Permission page.
     */
    public function grantLevelPermissionsTo(Position $position): void
    {
        if ($position->system_key === Position::KEY_SUPER_ADMINISTRATOR) {
            return;
        }

        $position->permissions()->syncWithoutDetaching($this->levelWidePermissionIdsFor($position));
    }

    /**
     * A position moved to another level drops its old access and takes the new level's access.
     */
    public function replaceWithLevelPermissions(Position $position): void
    {
        if ($position->system_key === Position::KEY_SUPER_ADMINISTRATOR) {
            return;
        }

        $position->permissions()->sync($this->levelWidePermissionIdsFor($position));
    }

    /**
     * @return array<int, string>
     */
    private function levelWidePermissionIdsFor(Position $position): array
    {
        $levelPositionIds = $this->levels()
            ->get((string) $position->level, ['positions' => collect()])['positions']
            ->map(fn (Position $levelPosition): string => (string) $levelPosition->id)
            ->reject(fn (string $positionId): bool => $positionId === (string) $position->id)
            ->values();

        if ($levelPositionIds->isEmpty()) {
            return [];
        }

        return $this->permissionsWithPositionIds()
            ->filter(fn (Permission $permission): bool => $levelPositionIds->diff($this->positionIdsOf($permission))->isEmpty())
            ->pluck('uuid')
            ->all();
    }

    /**
     * @return Collection<int, Permission>
     */
    private function permissionsWithPositionIds(): Collection
    {
        return Permission::query()
            ->with(['positions' => fn ($query) => $query->select(['positions.id'])])
            ->get(['uuid']);
    }

    /**
     * @return Collection<int, string>
     */
    private function positionIdsOf(Permission $permission): Collection
    {
        return $permission->positions
            ->map(fn (Position $position): string => (string) $position->id)
            ->values();
    }
}
