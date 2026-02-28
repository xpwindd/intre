<?php

declare(strict_types=1);

namespace App\Controllers;

class ApiController extends BaseController
{
    public function sensors(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json; charset=utf-8');
        $uid = (int) $_SESSION['user']['id'];
        $stmt = $this->db->prepare(
            'SELECT reading_time, soil_humidity, temperature, air_humidity, light_level
             FROM sensor_readings
             WHERE user_id = :uid
             ORDER BY reading_time DESC
             LIMIT 20'
        );
        $stmt->execute(['uid' => $uid]);
        echo json_encode(['ok' => true, 'data' => array_reverse($stmt->fetchAll())], JSON_UNESCAPED_UNICODE);
    }

    public function toggleDevice(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        header('Content-Type: application/json; charset=utf-8');
        $uid = (int) $_SESSION['user']['id'];
        $id = (int) ($_POST['id'] ?? 0);

        $stmt = $this->db->prepare('SELECT status FROM devices WHERE id = :id AND user_id = :uid LIMIT 1');
        $stmt->execute(['id' => $id, 'uid' => $uid]);
        $row = $stmt->fetch();
        if (!$row) {
            echo json_encode(['ok' => false, 'message' => 'Устройство не найдено'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $newStatus = $row['status'] === 'on' ? 'off' : 'on';
        $upd = $this->db->prepare('UPDATE devices SET status = :status, updated_at = NOW() WHERE id = :id AND user_id = :uid');
        $upd->execute(['status' => $newStatus, 'id' => $id, 'uid' => $uid]);
        echo json_encode(['ok' => true, 'status' => $newStatus], JSON_UNESCAPED_UNICODE);
    }

    public function addDiary(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        header('Content-Type: application/json; charset=utf-8');
        $uid = (int) $_SESSION['user']['id'];
        $plantId = (int) ($_POST['plant_id'] ?? 0);
        $note = trim((string) ($_POST['note'] ?? ''));
        if ($plantId < 1 || $note === '') {
            echo json_encode(['ok' => false, 'message' => 'Неверные данные'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $ins = $this->db->prepare(
            'INSERT INTO growth_diary (user_id, plant_id, entry_date, note, height_cm, condition_text, created_at)
             VALUES (:uid, :plant_id, :entry_date, :note, :height_cm, :condition_text, NOW())'
        );
        $ins->execute([
            'uid' => $uid,
            'plant_id' => $plantId,
            'entry_date' => date('Y-m-d'),
            'note' => $note,
            'height_cm' => (float) ($_POST['height_cm'] ?? 0),
            'condition_text' => trim((string) ($_POST['condition_text'] ?? '')),
        ]);

        echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
    }

    public function markNotification(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        header('Content-Type: application/json; charset=utf-8');
        $uid = (int) $_SESSION['user']['id'];
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $this->db->prepare('UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = :id AND user_id = :uid');
        $stmt->execute(['id' => $id, 'uid' => $uid]);
        echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
    }
}
