<?php
return [
    'domain'            => env('DOMAIN', 'localhost'),
    'app_version'       => env('APP_VERSION', 'latest'),
    'default_page_size' => 10,
    'use_users_nicks'   => env('USE_USERS_NICKS', true),
    'ml'                => env('MULTI_LANGUAGE_ENABLED', false),
    'seo'               => ['desc_length' => 160],
    'upload'            => [
        'disk'                    => env('UPLOAD_DISK', 'uploads'),
        'allowed_file_extensions' => [
            'image'    => ['png', 'jpg', 'jpeg', 'tif'],
            'document' => ['pdf', 'odt', 'ods', 'doc', 'docx', 'xls', 'xlsx', 'txt'],
            'video'    => ['mp4'],
            'music'    => ['mp3']
        ]
    ]
];
