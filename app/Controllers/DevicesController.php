<?php

declare(strict_types=1);

namespace App\Controllers;

class DevicesController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        $uid = (int) $_SESSION['user']['id'];
        $stmt = $this->db->prepare('SELECT id, name, device_type, status, is_auto, zone_id FROM devices WHERE user_id = :uid ORDER BY id DESC');
        $stmt->execute(['uid' => $uid]);
        $this->render('devices/index', [
            'pageTitle' => 'Устройства',
            'devices' => $stmt->fetchAll(),
        ]);
    }

    public function toggle(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid = (int) $_SESSION['user']['id'];
        $id = (int) ($_POST['id'] ?? 0);

        $stmt = $this->db->prepare('SELECT status FROM devices WHERE id = :id AND user_id = :uid LIMIT 1');
        $stmt->execute(['id' => $id, 'uid' => $uid]);
        $row = $stmt->fetch();
        if (!$row) {
            $_SESSION['flash_error'] = 'Устройство не найдено.';
            $this->redirect('devices');
        }

        $next = $row['status'] === 'on' ? 'off' : 'on';
        $upd = $this->db->prepare('UPDATE devices SET status = :status, updated_at = NOW() WHERE id = :id AND user_id = :uid');
        $upd->execute([
            'status' => $next,
            'id' => $id,
            'uid' => $uid,
        ]);

        $log = $this->db->prepare('INSERT INTO system_logs (user_id, action, message, created_at) VALUES (:uid, :action, :message, NOW())');
        $log->execute([
            'uid' => $uid,
            'action' => 'device.toggle',
            'message' => 'Переключено устройство #' . $id . ' на ' . $next,
        ]);

        $this->redirect('devices');
    }
}
