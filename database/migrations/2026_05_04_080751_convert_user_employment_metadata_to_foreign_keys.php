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
        Schema::table('user_employments', function (Blueprint $table) {
            if (! Schema::hasColumn('user_employments', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('user_id')->constrained('companies')->nullOnDelete();
            }

            if (! Schema::hasColumn('user_employments', 'position_id')) {
                $table->foreignId('position_id')->nullable()->after('company_id')->constrained('meta_data_positions')->nullOnDelete();
            }

            if (! Schema::hasColumn('user_employments', 'division_id')) {
                $table->foreignId('division_id')->nullable()->after('position_id')->constrained('meta_data_divisions')->nullOnDelete();
            }

            if (! Schema::hasColumn('user_employments', 'domicile_id')) {
                $table->foreignId('domicile_id')->nullable()->after('division_id')->constrained('meta_data_domicili')->nullOnDelete();
            }
        });

        if (Schema::hasColumn('user_employments', 'company')) {
            DB::table('user_employments')
                ->join('companies', 'user_employments.company', '=', 'companies.name')
                ->update(['user_employments.company_id' => DB::raw('companies.id')]);
        }

        if (Schema::hasColumn('user_employments', 'position')) {
            DB::table('user_employments')
                ->join('meta_data_positions', 'user_employments.position', '=', 'meta_data_positions.name')
                ->update(['user_employments.position_id' => DB::raw('meta_data_positions.id')]);
        }

        if (Schema::hasColumn('user_employments', 'department')) {
            DB::table('user_employments')
                ->join('meta_data_divisions', 'user_employments.department', '=', 'meta_data_divisions.name')
                ->update(['user_employments.division_id' => DB::raw('meta_data_divisions.id')]);
        }

        if (Schema::hasColumn('user_employments', 'domicile')) {
            DB::table('user_employments')
                ->join('meta_data_domicili', 'user_employments.domicile', '=', 'meta_data_domicili.name')
                ->update(['user_employments.domicile_id' => DB::raw('meta_data_domicili.id')]);
        }

        Schema::table('user_employments', function (Blueprint $table) {
            foreach (['company', 'position', 'department', 'domicile'] as $column) {
                if (Schema::hasColumn('user_employments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_employments', function (Blueprint $table) {
            if (! Schema::hasColumn('user_employments', 'company')) {
                $table->string('company')->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('user_employments', 'position')) {
                $table->string('position')->nullable()->after('company');
            }

            if (! Schema::hasColumn('user_employments', 'department')) {
                $table->string('department')->nullable()->after('position');
            }

            if (! Schema::hasColumn('user_employments', 'domicile')) {
                $table->string('domicile')->nullable()->after('department');
            }
        });

        if (Schema::hasColumn('user_employments', 'company_id')) {
            DB::table('user_employments')
                ->join('companies', 'user_employments.company_id', '=', 'companies.id')
                ->update(['user_employments.company' => DB::raw('companies.name')]);
        }

        if (Schema::hasColumn('user_employments', 'position_id')) {
            DB::table('user_employments')
                ->join('meta_data_positions', 'user_employments.position_id', '=', 'meta_data_positions.id')
                ->update(['user_employments.position' => DB::raw('meta_data_positions.name')]);
        }

        if (Schema::hasColumn('user_employments', 'division_id')) {
            DB::table('user_employments')
                ->join('meta_data_divisions', 'user_employments.division_id', '=', 'meta_data_divisions.id')
                ->update(['user_employments.department' => DB::raw('meta_data_divisions.name')]);
        }

        if (Schema::hasColumn('user_employments', 'domicile_id')) {
            DB::table('user_employments')
                ->join('meta_data_domicili', 'user_employments.domicile_id', '=', 'meta_data_domicili.id')
                ->update(['user_employments.domicile' => DB::raw('meta_data_domicili.name')]);
        }

        Schema::table('user_employments', function (Blueprint $table) {
            foreach (['company_id', 'position_id', 'division_id', 'domicile_id'] as $column) {
                if (Schema::hasColumn('user_employments', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
        });
    }
};
