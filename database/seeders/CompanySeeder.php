<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [
                'name' => 'AndalanKu',
                'legal_name' => 'PT AndalanKu Indonesia',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'country' => 'Indonesia',
                'industry' => 'Technology Services',
                'primary_color' => '#0B2A97',
                'secondary_color' => '#6A10C5',
            ],
            [
                'name' => 'KMA',
                'legal_name' => 'PT KMA Indonesia',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'country' => 'Indonesia',
                'industry' => 'Business Consulting',
                'primary_color' => '#1F7AE0',
                'secondary_color' => '#0F4C81',
            ],
            [
                'name' => 'Niskala',
                'legal_name' => 'PT Niskala Indonesia',
                'city' => 'Bandung',
                'province' => 'Jawa Barat',
                'country' => 'Indonesia',
                'industry' => 'Creative Services',
                'primary_color' => '#2D6A4F',
                'secondary_color' => '#95D5B2',
            ],
            [
                'name' => 'RNB',
                'legal_name' => 'PT RNB Indonesia',
                'city' => 'Surabaya',
                'province' => 'Jawa Timur',
                'country' => 'Indonesia',
                'industry' => 'Retail and Distribution',
                'primary_color' => '#1D3557',
                'secondary_color' => '#457B9D',
            ],
            [
                'name' => 'RNE',
                'legal_name' => 'PT RNE Indonesia',
                'city' => 'Semarang',
                'province' => 'Jawa Tengah',
                'country' => 'Indonesia',
                'industry' => 'Logistics',
                'primary_color' => '#E76F51',
                'secondary_color' => '#F4A261',
            ],
            [
                'name' => 'TMS',
                'legal_name' => 'PT TMS Indonesia',
                'city' => 'Yogyakarta',
                'province' => 'DI Yogyakarta',
                'country' => 'Indonesia',
                'industry' => 'Manufacturing',
                'primary_color' => '#264653',
                'secondary_color' => '#2A9D8F',
            ],
            [
                'name' => 'Trah',
                'legal_name' => 'PT Trah Indonesia',
                'city' => 'Malang',
                'province' => 'Jawa Timur',
                'country' => 'Indonesia',
                'industry' => 'Property Services',
                'primary_color' => '#8D0801',
                'secondary_color' => '#BF0603',
            ],
        ];

        foreach ($companies as $companyData) {
            Company::query()->updateOrCreate(
                ['name' => $companyData['name']],
                $companyData,
            );
        }
    }
}
