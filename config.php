<?php

declare(strict_types=1);

return [
    'db' => [
        'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=lapor_purbalingga;charset=utf8mb4',
        'user' => 'root',
        'password' => '',
    ],
    'app' => [
        'base_url' => '/TemanBuluBurung',
        'upload_dir' => __DIR__ . '/storage/private',
        'max_uploads' => 5,
        'max_upload_bytes' => 5 * 1024 * 1024,
        'google_maps_api_key' => 'AIzaSyDXB-mBsa47T7_oj5nQybBUqjGYvlk8kVY',
    ],
];
