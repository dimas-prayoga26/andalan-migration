<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class MetaDataLeaveCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $companies = Company::query()
                ->select(['id', 'name'])
                ->get();

            foreach ($companies as $company) {
                DB::table('meta_data_leave_companies')->updateOrInsert(
                    ['company_id' => $company->id],
                    [
                        'name' => $company->name,
                        'annual_quota' => 12,
                        'montly_leave_limit' => 1,
                        'is_active' => true,
                    ]
                );
            }
        } catch (Throwable $throwable) {
            throw new RuntimeException('MetaDataLeaveCompanySeeder gagal dijalankan.', 0, $throwable);
        }
    }
}
