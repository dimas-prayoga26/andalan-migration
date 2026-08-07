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
        if (! Schema::hasColumn('applicants', 'gender_id')) {
            Schema::table('applicants', function (Blueprint $table): void {
                $table->foreignId('gender_id')
                    ->nullable()
                    ->after('phone')
                    ->constrained('meta_data_gender', 'id')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('applicants', 'marital_status_id')) {
            Schema::table('applicants', function (Blueprint $table): void {
                $table->foreignId('marital_status_id')
                    ->nullable()
                    ->after('gender_id')
                    ->constrained('meta_data_marital_statuses', 'id')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasColumn('applicants', 'gender')) {
            foreach ($this->genderIdsByLegacyValue() as $legacyValue => $genderId) {
                DB::table('applicants')
                    ->where('gender', (string) $legacyValue)
                    ->update(['gender_id' => $genderId]);
            }
        }

        if (Schema::hasColumn('applicants', 'marital_status')) {
            foreach ($this->maritalStatusIdsByLegacyValue() as $legacyValue => $maritalStatusId) {
                DB::table('applicants')
                    ->where('marital_status', (string) $legacyValue)
                    ->update(['marital_status_id' => $maritalStatusId]);
            }
        }

        if (Schema::hasColumn('applicants', 'gender')) {
            Schema::table('applicants', function (Blueprint $table): void {
                $table->dropColumn('gender');
            });
        }

        if (Schema::hasColumn('applicants', 'marital_status')) {
            Schema::table('applicants', function (Blueprint $table): void {
                $table->dropColumn('marital_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('applicants', 'gender')) {
            Schema::table('applicants', function (Blueprint $table): void {
                $table->string('gender')->nullable()->after('phone');
            });
        }

        if (! Schema::hasColumn('applicants', 'marital_status')) {
            Schema::table('applicants', function (Blueprint $table): void {
                $table->string('marital_status')->nullable()->after('gender');
            });
        }

        foreach ($this->genderIdsByLegacyValue() as $legacyValue => $genderId) {
            DB::table('applicants')
                ->where('gender_id', $genderId)
                ->update(['gender' => (string) $legacyValue]);
        }

        foreach ($this->maritalStatusIdsByLegacyValue() as $legacyValue => $maritalStatusId) {
            DB::table('applicants')
                ->where('marital_status_id', $maritalStatusId)
                ->update(['marital_status' => (string) $legacyValue]);
        }

        if (Schema::hasColumn('applicants', 'gender_id')) {
            Schema::table('applicants', function (Blueprint $table): void {
                $table->dropForeign(['gender_id']);
                $table->dropColumn('gender_id');
            });
        }

        if (Schema::hasColumn('applicants', 'marital_status_id')) {
            Schema::table('applicants', function (Blueprint $table): void {
                $table->dropForeign(['marital_status_id']);
                $table->dropColumn('marital_status_id');
            });
        }
    }

    /**
     * @return array<int, int>
     */
    private function genderIdsByLegacyValue(): array
    {
        return $this->metadataIdsByLegacyValue('meta_data_gender', [
            1 => 'Male',
            2 => 'Female',
        ]);
    }

    /**
     * @return array<int, int>
     */
    private function maritalStatusIdsByLegacyValue(): array
    {
        return $this->metadataIdsByLegacyValue('meta_data_marital_statuses', [
            1 => 'Single',
            2 => 'Married',
            3 => 'Divorced',
            4 => 'Widowed',
        ]);
    }

    /**
     * @param  array<int, string>  $namesByLegacyValue
     * @return array<int, int>
     */
    private function metadataIdsByLegacyValue(string $table, array $namesByLegacyValue): array
    {
        $idsByLegacyValue = [];

        foreach ($namesByLegacyValue as $legacyValue => $name) {
            $metadataId = DB::table($table)->where('id', $legacyValue)->value('id')
                ?? DB::table($table)->where('name', $name)->value('id');

            if ($metadataId !== null) {
                $idsByLegacyValue[$legacyValue] = (int) $metadataId;
            }
        }

        return $idsByLegacyValue;
    }
};
