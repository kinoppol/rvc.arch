<?php
/**
 * Copy this file to config.php and adjust for your environment.
 * config.php is gitignored (it holds credentials).
 */
return [
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'rvc_arch',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name'             => 'ระบบคลังงานวิจัย',
        'org'              => 'วิทยาลัยอาชีวศึกษาร้อยเอ็ด',
        'upload_dir'       => __DIR__ . '/../storage/uploads',
        'max_upload_bytes' => 20 * 1024 * 1024, // 20 MB per PDF
    ],
];
