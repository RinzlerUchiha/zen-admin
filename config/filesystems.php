<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'root' => env('AWS_FOLDER', '')
        ],

        /*
        | Applicant documents uploaded through zen-applicants. Read-only from
        | here, and private: files are only ever streamed after authorization.
        |
        | Must point at the same place zen-applicants writes to. On a
        | development machine that is zen-applicants' own storage folder; in
        | production it is the company bucket under zenhub/.
        */
        'applicant_documents' => env('APPLICANT_DOCUMENTS_DRIVER', 'local') === 's3'
            ? [
                'driver' => 's3',
                'key' => env('APPLICANT_DOCUMENTS_AWS_ACCESS_KEY_ID', env('AWS_ACCESS_KEY_ID')),
                'secret' => env('APPLICANT_DOCUMENTS_AWS_SECRET_ACCESS_KEY', env('AWS_SECRET_ACCESS_KEY')),
                'region' => env('APPLICANT_DOCUMENTS_AWS_REGION', 'ap-southeast-1'),
                'bucket' => env('APPLICANT_DOCUMENTS_AWS_BUCKET', 'e-classtngcacademy'),
                'root' => env('APPLICANT_DOCUMENTS_AWS_ROOT', 'zenhub'),
                // S3 keys always use "/", whatever OS this runs on.
                'directory_separator' => '/',
                'throw' => true,
            ]
            : [
                'driver' => 'local',
                'root' => env('APPLICANT_DOCUMENTS_LOCAL_ROOT', base_path('../zen-applicants/storage/app/private')),
                'throw' => true,
            ],

        'custom_s3' => [
            'driver' => 'local',
            'root' => env('CUSTOM_FILE_PATH', storage_path('app/private')), // Specify the full path to the desired location
            'visibility' => 'private', // Set visibility (public or private)
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
