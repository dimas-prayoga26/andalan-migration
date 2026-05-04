<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'company_id')
            && Schema::hasTable('user_employments')
            && Schema::hasColumn('user_employments', 'company_id')) {
            DB::table('user_employments')
                ->join('users', 'user_employments.user_id', '=', 'users.id')
                ->whereNull('user_employments.company_id')
                ->whereNotNull('users.company_id')
                ->update(['user_employments.company_id' => DB::raw('users.company_id')]);

            $now = now();

            $missingEmployments = DB::table('users')
                ->leftJoin('user_employments', 'users.id', '=', 'user_employments.user_id')
                ->whereNull('user_employments.user_id')
                ->whereNotNull('users.company_id')
                ->get(['users.id as user_id', 'users.company_id']);

            if ($missingEmployments->isNotEmpty()) {
                DB::table('user_employments')->insert(
                    $missingEmployments
                        ->map(fn (object $row): array => [
                            'user_id' => $row->user_id,
                            'company_id' => $row->company_id,
                            'status' => 'Active',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ])
                        ->all(),
                );
            }
        }

        if (Schema::hasColumn('users', 'company_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('company_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('users', 'company_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('company_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('companies')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasColumn('users', 'company_id')
            && Schema::hasTable('user_employments')
            && Schema::hasColumn('user_employments', 'company_id')) {
            DB::table('users')
                ->join('user_employments', 'users.id', '=', 'user_employments.user_id')
                ->whereNull('users.company_id')
                ->whereNotNull('user_employments.company_id')
                ->update(['users.company_id' => DB::raw('user_employments.company_id')]);
        }
    }
};
