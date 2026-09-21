<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Brand
    |--------------------------------------------------------------------------
    |
    | Used whenever an applicant's brand_key is null or does not match a known
    | brand below.
    |
    */

    'default_brand' => 'rnb',

    /*
    |--------------------------------------------------------------------------
    | Brands
    |--------------------------------------------------------------------------
    |
    | Branding used for applicant status emails, mirrored from the
    | web_careers application's config/careers.php so the logo/colors sent
    | to an applicant match the career site they originally applied through.
    |
    */

    'brands' => [
        'rnb' => [
            'name' => 'RNB Management',
            'mailer' => 'rnb',
            'logo_url' => 'https://raw.githubusercontent.com/dimas-prayoga26/web_careers_company/main/public/images/Logo%20RNB.png',
            'website' => 'https://rnb.co.id/',
            'email' => 'hr@rnb.co.id',
            'primary_color' => '#b01020',
            'accent_color' => '#8c6d00',
            'header_background_color' => '#fff8e6',
            'header_text_color' => '#172033',
        ],
        'andalanku' => [
            'name' => 'AndalanKu',
            'mailer' => 'andalanku',
            'logo_url' => 'https://raw.githubusercontent.com/dimas-prayoga26/web_careers_company/main/public/images/Logo%20AndalanKu.png',
            'website' => 'https://andalanku.com/',
            'email' => 'recruitment@andalanku.com',
            'primary_color' => '#900000',
            'accent_color' => '#d02020',
            'header_background_color' => '#fff5f5',
            'header_text_color' => '#172033',
        ],
        'kma' => [
            'name' => 'KMA',
            'mailer' => 'kma',
            'logo_url' => 'https://raw.githubusercontent.com/dimas-prayoga26/web_careers_company/main/public/images/Logo%20KMA.png',
            'website' => 'https://karpetmerah.id/',
            'email' => 'hr@karpetmerah.id',
            'primary_color' => '#b01020',
            'accent_color' => '#e01020',
            'header_background_color' => '#fff5f5',
            'header_text_color' => '#172033',
        ],
        'niskala' => [
            'name' => 'Niskala',
            'mailer' => 'niskala',
            'logo_url' => 'https://raw.githubusercontent.com/dimas-prayoga26/web_careers_company/main/public/images/Logo%20Niskala.png',
            'website' => 'https://coffeeniskala.com/',
            'email' => 'hr@coffeeniskala.com',
            'primary_color' => '#6f6f58',
            'accent_color' => '#76765f',
            'header_background_color' => '#f7f6ef',
            'header_text_color' => '#172033',
        ],
        'rne' => [
            'name' => 'RNE',
            'mailer' => 'rne',
            'logo_url' => 'https://raw.githubusercontent.com/dimas-prayoga26/web_careers_company/main/public/images/Logo%20RNE.png',
            'website' => 'https://rne.co.id/',
            'email' => 'hr@rne.co.id',
            'primary_color' => '#b01020',
            'accent_color' => '#8c6d00',
            'header_background_color' => '#fff8e6',
            'header_text_color' => '#172033',
        ],
        'tms' => [
            'name' => 'TMS',
            'mailer' => 'tms',
            'logo_url' => 'https://raw.githubusercontent.com/dimas-prayoga26/web_careers_company/main/public/images/Logo%20TMS.png',
            'website' => 'https://tims.co.id/',
            'email' => 'hr@tims.co.id',
            'primary_color' => '#202090',
            'accent_color' => '#b35f00',
            'header_background_color' => '#f4f6ff',
            'header_text_color' => '#172033',
        ],
        'trah' => [
            'name' => 'Trah',
            'mailer' => 'trah',
            'logo_url' => 'https://raw.githubusercontent.com/dimas-prayoga26/web_careers_company/main/public/images/Logo%20Trah.png',
            'website' => 'https://trah.co.id/',
            'email' => 'hr@trah.co.id',
            'primary_color' => '#7a6035',
            'accent_color' => '#8a6d3e',
            'header_background_color' => '#f7f2e6',
            'header_text_color' => '#172033',
        ],
    ],
];
