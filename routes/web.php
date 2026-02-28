<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\ApiController;
use App\Controllers\AuthController;
use App\Controllers\AutomationController;
use App\Controllers\DevicesController;
use App\Controllers\DiaryController;
use App\Controllers\GardenController;
use App\Controllers\HomeController;
use App\Controllers\LegalController;
use App\Controllers\NotificationsController;
use App\Controllers\ProfileController;
use App\Controllers\SensorsController;

return [
    ['method' => 'GET', 'path' => 'home', 'handler' => [HomeController::class, 'index']],
    ['method' => 'GET', 'path' => 'privacy', 'handler' => [LegalController::class, 'privacy']],
    ['method' => 'GET', 'path' => 'login', 'handler' => [AuthController::class, 'showLogin']],
    ['method' => 'POST', 'path' => 'login', 'handler' => [AuthController::class, 'login']],
    ['method' => 'GET', 'path' => 'register', 'handler' => [AuthController::class, 'showRegister']],
    ['method' => 'POST', 'path' => 'register', 'handler' => [AuthController::class, 'register']],
    ['method' => 'GET', 'path' => 'logout', 'handler' => [AuthController::class, 'logout']],
    ['method' => 'GET', 'path' => 'forgot', 'handler' => [AuthController::class, 'showForgot']],
    ['method' => 'POST', 'path' => 'forgot', 'handler' => [AuthController::class, 'forgot']],
    ['method' => 'GET', 'path' => 'reset', 'handler' => [AuthController::class, 'showReset']],
    ['method' => 'POST', 'path' => 'reset', 'handler' => [AuthController::class, 'reset']],

    ['method' => 'GET', 'path' => 'dashboard', 'handler' => [GardenController::class, 'dashboard']],
    ['method' => 'GET', 'path' => 'profile', 'handler' => [ProfileController::class, 'show']],
    ['method' => 'POST', 'path' => 'profile', 'handler' => [ProfileController::class, 'update']],
    ['method' => 'GET', 'path' => 'plants', 'handler' => [GardenController::class, 'plants']],
    ['method' => 'POST', 'path' => 'plants/add', 'handler' => [GardenController::class, 'addPlant']],
    ['method' => 'GET', 'path' => 'zones', 'handler' => [GardenController::class, 'zones']],
    ['method' => 'POST', 'path' => 'zones/add', 'handler' => [GardenController::class, 'addZone']],
    ['method' => 'GET', 'path' => 'status', 'handler' => [GardenController::class, 'status']],
    ['method' => 'GET', 'path' => 'diary', 'handler' => [DiaryController::class, 'index']],
    ['method' => 'POST', 'path' => 'diary/add', 'handler' => [DiaryController::class, 'add']],
    ['method' => 'GET', 'path' => 'devices', 'handler' => [DevicesController::class, 'index']],
    ['method' => 'POST', 'path' => 'devices/toggle', 'handler' => [DevicesController::class, 'toggle']],
    ['method' => 'GET', 'path' => 'sensors', 'handler' => [SensorsController::class, 'index']],
    ['method' => 'POST', 'path' => 'sensors/add', 'handler' => [SensorsController::class, 'add']],
    ['method' => 'GET', 'path' => 'automation', 'handler' => [AutomationController::class, 'index']],
    ['method' => 'POST', 'path' => 'automation/add', 'handler' => [AutomationController::class, 'add']],
    ['method' => 'GET', 'path' => 'notifications', 'handler' => [NotificationsController::class, 'index']],
    ['method' => 'POST', 'path' => 'notifications/read', 'handler' => [NotificationsController::class, 'markRead']],
    ['method' => 'GET', 'path' => 'admin', 'handler' => [AdminController::class, 'index']],
    ['method' => 'POST', 'path' => 'admin/users/role', 'handler' => [AdminController::class, 'updateUserRole']],
    ['method' => 'POST', 'path' => 'admin/devices/toggle', 'handler' => [AdminController::class, 'toggleDevice']],
    ['method' => 'POST', 'path' => 'admin/notifications/read', 'handler' => [AdminController::class, 'markNotificationRead']],

    ['method' => 'GET', 'path' => 'api/sensors', 'handler' => [ApiController::class, 'sensors']],
    ['method' => 'POST', 'path' => 'api/devices/toggle', 'handler' => [ApiController::class, 'toggleDevice']],
    ['method' => 'POST', 'path' => 'api/diary/add', 'handler' => [ApiController::class, 'addDiary']],
    ['method' => 'POST', 'path' => 'api/notifications/read', 'handler' => [ApiController::class, 'markNotification']],
];
