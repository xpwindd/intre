<?php

return [
    'app' => [
        'name' => 'Smart Garden',
        'base_url' => '',
        'env' => 'dev',
    ],
    'db' => [
        'host' => 'MySQL-8.0',
        'port' => '3306',
        'name' => 'smart_garden',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'from' => 'noreply@smartgarden.local',
        'from_name' => 'Smart Garden',
    ],
    'security' => [
        'session_name' => 'sg_session',
        'csrf_key' => '_csrf_token',
    ],
];
