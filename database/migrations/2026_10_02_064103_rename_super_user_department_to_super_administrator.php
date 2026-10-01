<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SOURCE_NAMES = ['Super User', 'Superuser', 'Super Usesr'];

    private const TARGET_NAME = 'Super Administrator';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        $this->normalizeDepartments($now, self::SOURCE_NAMES, self::TARGET_NAME);
        $this->renameMetadataRows('meta_data_divisions', self::SOURCE_NAMES, self::TARGET_NAME, $now);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $now = now();

        $this->normalizeDepartments($now, [self::TARGET_NAME], 'Super User');
        $this->renameMetadataRows('meta_data_divisions', [self::TARGET_NAME], 'Super User', $now);
    }

    /**
     * @param  array<int, string>  $sourceNames
     */
    private function normalizeDepartments(mixed $now, array $sourceNames, string $targetName): void
    {
        if (! Schema::hasTable('departments')) {
            return;
        }

        $targetId = DB::table('departments')
            ->where('name', $targetName)
            ->value('id');

        $sourceRows = DB::table('departments')
            ->whereIn('name', $sourceNames)
            ->get(['id', 'name']);

        if ((! is_string($targetId) || trim($targetId) === '') && $sourceRows->isNotEmpty()) {
            $targetRow = $sourceRows->first();
            $targetId = (string) $targetRow->id;

            DB::table('departments')
                ->where('id', $targetId)
                ->update([
                    'name' => $targetName,
                    'updated_at' => $now,
                ]);

            $sourceRows = $sourceRows
                ->reject(static fn (object $sourceRow): bool => (string) $sourceRow->id === $targetId)
                ->values();
        }

        if (! is_string($targetId) || trim($targetId) === '') {
            return;
        }

        $this->moveDepartmentReferences($sourceRows, $targetId, $now);
    }

    /**
     * @param  Collection<int, object{id: mixed, name: mixed}>  $sourceRows
     */
    private function moveDepartmentReferences(Collection $sourceRows, string $targetId, mixed $now): void
    {
        $sourceRows->each(function (object $sourceRow) use ($targetId, $now): void {
            $sourceId = (string) $sourceRow->id;

            if ($sourceId === $targetId) {
                return;
            }

            if (Schema::hasTable('employee_deployments')) {
                DB::table('employee_deployments')
                    ->where('current_department_id', $sourceId)
                    ->update([
                        'current_department_id' => $targetId,
                        'updated_at' => $now,
                    ]);
            }

            if (Schema::hasTable('project_departments')) {
                DB::table('project_departments')
                    ->where('department_id', $sourceId)
                    ->get(['id', 'project_id'])
                    ->each(function (object $projectDepartment) use ($targetId, $now): void {
                        $targetExists = DB::table('project_departments')
                            ->where('project_id', $projectDepartment->project_id)
                            ->where('department_id', $targetId)
                            ->exists();

                        if ($targetExists) {
                            DB::table('project_departments')
                                ->where('id', $projectDepartment->id)
                                ->delete();

                            return;
                        }

                        DB::table('project_departments')
                            ->where('id', $projectDepartment->id)
                            ->update([
                                'department_id' => $targetId,
                                'updated_at' => $now,
                            ]);
                    });
            }

            DB::table('departments')
                ->where('id', $sourceId)
                ->delete();
        });
    }

    /**
     * @param  array<int, string>  $sourceNames
     */
    private function renameMetadataRows(string $table, array $sourceNames, string $targetName, mixed $now): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        DB::table($table)
            ->whereIn('name', $sourceNames)
            ->update([
                'name' => $targetName,
                'updated_at' => $now,
            ]);
    }
};
