<?php

$defaultCareersPublicPaths = implode(PATH_SEPARATOR, [
    base_path('../careers/public'),
    base_path('../web_careers/public'),
]);

return [
    /*
    |--------------------------------------------------------------------------
    | Applicant Upload Locations
    |--------------------------------------------------------------------------
    |
    | The careers application stores uploaded applicant files in its public
    | directory. The SIAP application only stores the filenames in the database,
    | so it needs these paths to decide whether a file belongs to the new
    | careers app or the legacy careers folder.
    |
    */

    'careers_public_paths' => array_values(array_filter(array_map(
        static fn (string $path): string => rtrim($path, DIRECTORY_SEPARATOR),
        explode(PATH_SEPARATOR, (string) env('CAREERS_PUBLIC_PATHS', $defaultCareersPublicPaths)),
    ))),

    'photo_base_url' => rtrim((string) env('CAREERS_PHOTO_BASE_URL', 'https://careers.rnb.co.id/files/photo'), '/').'/',

    'cv_base_url' => rtrim((string) env('CAREERS_CV_BASE_URL', 'https://careers.rnb.co.id/files/cv'), '/').'/',

    'legacy_photo_base_url' => rtrim((string) env('LEGACY_CAREERS_PHOTO_BASE_URL', 'https://rnbmanagement.com/domain-rnbmanagementcom/subdomain/careers/files/photo'), '/').'/',

    'legacy_cv_base_url' => rtrim((string) env('LEGACY_CAREERS_CV_BASE_URL', 'https://rnbmanagement.com/domain-rnbmanagementcom/subdomain/careers/files/cv'), '/').'/',
];
