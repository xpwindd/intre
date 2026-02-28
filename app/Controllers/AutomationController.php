<?php

declare(strict_types=1);

namespace App\Controllers;

class AutomationController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        $uid = (int) $_SESSION['user']['id'];
        $stmt = $this->db->prepare('SELECT id, name, schedule_type, execute_time, is_active FROM schedules WHERE user_id = :uid ORDER BY id DESC');
        $stmt->execute(['uid' => $uid]);
        $this->render('automation/index', [
            'pageTitle' => 'Расписания',
            'schedules' => $stmt->fetchAll(),
        ]);
    }

    public function add(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid = (int) $_SESSION['user']['id'];
        $stmt = $this->db->prepare(
            'INSERT INTO schedules (user_id, name, schedule_type, execute_time, is_active, created_at)
             VALUES (:uid, :name, :schedule_type, :execute_time, :is_active, NOW())'
        );
        $stmt->execute([
            'uid' => $uid,
            'name' => trim((string) ($_POST['name'] ?? 'Новое расписание')),
            'schedule_type' => trim((string) ($_POST['schedule_type'] ?? 'watering')),
            'execute_time' => trim((string) ($_POST['execute_time'] ?? '08:00')),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);
        $_SESSION['flash_success'] = 'Расписание сохранено.';
        $this->redirect('automation');
    }
}
