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
        Schema::table('user_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('user_profiles', 'gender_id')) {
                $table->foreignId('gender_id')->nullable()->after('nickname')->constrained('meta_data_gender')->nullOnDelete();
            }

            if (! Schema::hasColumn('user_profiles', 'marital_status_id')) {
                $table->foreignId('marital_status_id')->nullable()->after('gender_id')->constrained('meta_data_marital_statuses')->nullOnDelete();
            }
        });

        if (Schema::hasColumn('user_profiles', 'gender')) {
            DB::table('user_profiles')
                ->join('meta_data_gender', 'user_profiles.gender', '=', 'meta_data_gender.name')
                ->update(['user_profiles.gender_id' => DB::raw('meta_data_gender.id')]);
        }

        if (Schema::hasColumn('user_profiles', 'marital_status')) {
            DB::table('user_profiles')
                ->join('meta_data_marital_statuses', 'user_profiles.marital_status', '=', 'meta_data_marital_statuses.name')
                ->update(['user_profiles.marital_status_id' => DB::raw('meta_data_marital_statuses.id')]);
        }

        Schema::table('user_profiles', function (Blueprint $table) {
            foreach (['gender', 'marital_status'] as $column) {
                if (Schema::hasColumn('user_profiles', $column)) {
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
        Schema::table('user_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('user_profiles', 'gender')) {
                $table->string('gender')->nullable()->after('nickname');
            }

            if (! Schema::hasColumn('user_profiles', 'marital_status')) {
                $table->string('marital_status')->nullable()->after('gender');
            }
        });

        if (Schema::hasColumn('user_profiles', 'gender_id')) {
            DB::table('user_profiles')
                ->join('meta_data_gender', 'user_profiles.gender_id', '=', 'meta_data_gender.id')
                ->update(['user_profiles.gender' => DB::raw('meta_data_gender.name')]);
        }

        if (Schema::hasColumn('user_profiles', 'marital_status_id')) {
            DB::table('user_profiles')
                ->join('meta_data_marital_statuses', 'user_profiles.marital_status_id', '=', 'meta_data_marital_statuses.id')
                ->update(['user_profiles.marital_status' => DB::raw('meta_data_marital_statuses.name')]);
        }

        Schema::table('user_profiles', function (Blueprint $table) {
            foreach (['gender_id', 'marital_status_id'] as $column) {
                if (Schema::hasColumn('user_profiles', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
        });
    }
};
