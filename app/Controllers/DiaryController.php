<?php

declare(strict_types=1);

namespace App\Controllers;

class DiaryController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        $uid = (int) $_SESSION['user']['id'];
        $plantFilter = (int) ($_GET['plant_id'] ?? 0);
        $dateFrom = trim((string) ($_GET['date_from'] ?? ''));
        $dateTo = trim((string) ($_GET['date_to'] ?? ''));

        $where = ' WHERE d.user_id = :uid ';
        $params = ['uid' => $uid];
        if ($plantFilter > 0) {
            $where .= ' AND d.plant_id = :plant_id ';
            $params['plant_id'] = $plantFilter;
        }
        if ($dateFrom !== '') {
            $where .= ' AND d.entry_date >= :date_from ';
            $params['date_from'] = $dateFrom;
        }
        if ($dateTo !== '') {
            $where .= ' AND d.entry_date <= :date_to ';
            $params['date_to'] = $dateTo;
        }

        $stmt = $this->db->prepare(
            'SELECT d.id, d.plant_id, d.entry_date, d.note, d.height_cm, d.condition_text, d.photo_path, p.name AS plant_name
             FROM growth_diary d
             JOIN plants p ON p.id = d.plant_id
             ' . $where . '
             ORDER BY d.entry_date DESC, d.id DESC'
        );
        $stmt->execute($params);

        $plants = $this->db->prepare('SELECT id, name FROM plants WHERE user_id = :uid ORDER BY name');
        $plants->execute(['uid' => $uid]);

        $this->render('diary/index', [
            'pageTitle' => 'Дневник роста',
            'entries' => $stmt->fetchAll(),
            'plants' => $plants->fetchAll(),
            'filters' => ['plant_id' => $plantFilter, 'date_from' => $dateFrom, 'date_to' => $dateTo],
        ]);
    }

    public function add(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid = (int) $_SESSION['user']['id'];
        $plantId = (int) ($_POST['plant_id'] ?? 0);
        $note = trim((string) ($_POST['note'] ?? ''));

        $check = $this->db->prepare('SELECT id FROM plants WHERE id = :id AND user_id = :uid');
        $check->execute(['id' => $plantId, 'uid' => $uid]);
        if (!$check->fetch()) {
            $_SESSION['flash_error'] = 'Выберите корректное растение для записи.';
            $this->redirect('diary');
        }

        if ($note === '') {
            $_SESSION['flash_error'] = 'Добавьте текст заметки.';
            $this->redirect('diary');
        }

        $photoPath = null;
        if (!empty($_FILES['photo']['tmp_name']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
            $ext = strtolower((string) pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $name = 'diary_' . $uid . '_' . time() . '.' . $ext;
                $full = dirname(__DIR__, 2) . '/public/uploads/' . $name;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $full)) {
                    $photoPath = '/uploads/' . $name;
                }
            }
        }

        $stmt = $this->db->prepare(
            'INSERT INTO growth_diary (user_id, plant_id, entry_date, note, height_cm, condition_text, photo_path, created_at)
             VALUES (:uid, :plant_id, :entry_date, :note, :height_cm, :condition_text, :photo_path, NOW())'
        );
        $stmt->execute([
            'uid' => $uid,
            'plant_id' => $plantId,
            'entry_date' => $_POST['entry_date'] ?: date('Y-m-d'),
            'note' => $note,
            'height_cm' => (float) ($_POST['height_cm'] ?? 0),
            'condition_text' => trim((string) ($_POST['condition_text'] ?? '')),
            'photo_path' => $photoPath,
        ]);

        $care = $this->db->prepare(
            'INSERT INTO care_events (user_id, plant_id, event_type, event_date, note, created_at)
             VALUES (:uid, :plant_id, :event_type, :event_date, :note, NOW())'
        );
        $care->execute([
            'uid' => $uid,
            'plant_id' => $plantId,
            'event_type' => trim((string) ($_POST['care_event_type'] ?? 'осмотр')),
            'event_date' => $_POST['entry_date'] ?: date('Y-m-d'),
            'note' => trim((string) ($_POST['care_note'] ?? '')),
        ]);

        $_SESSION['flash_success'] = 'Запись дневника сохранена.';
        $this->redirect('diary');
    }
}
