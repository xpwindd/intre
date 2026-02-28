<?php

declare(strict_types=1);

namespace App\Controllers;

class SensorsController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        $uid = (int) $_SESSION['user']['id'];

        $zoneId = (int) ($_GET['zone_id'] ?? 0);
        $dateFrom = trim((string) ($_GET['date_from'] ?? ''));
        $dateTo = trim((string) ($_GET['date_to'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = ' WHERE s.user_id = :uid ';
        $params = ['uid' => $uid];

        if ($zoneId > 0) {
            $where .= ' AND s.zone_id = :zone_id ';
            $params['zone_id'] = $zoneId;
        }
        if ($dateFrom !== '') {
            $where .= ' AND s.reading_time >= :date_from ';
            $params['date_from'] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo !== '') {
            $where .= ' AND s.reading_time <= :date_to ';
            $params['date_to'] = $dateTo . ' 23:59:59';
        }

        $count = $this->db->prepare('SELECT COUNT(*) FROM sensor_readings s ' . $where);
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        $stmt = $this->db->prepare(
            'SELECT s.id, s.zone_id, s.reading_time, s.soil_humidity, s.temperature, s.air_humidity, s.light_level, z.name AS zone_name
             FROM sensor_readings s
             LEFT JOIN zones z ON z.id = s.zone_id
             ' . $where . '
             ORDER BY s.reading_time DESC
             LIMIT :limit OFFSET :offset'
        );

        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v, is_int($v) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $readings = $stmt->fetchAll();

        $zonesStmt = $this->db->prepare('SELECT id, name FROM zones WHERE user_id = :uid ORDER BY name ASC');
        $zonesStmt->execute(['uid' => $uid]);
        $zones = $zonesStmt->fetchAll();

        $latestStmt = $this->db->prepare(
            'SELECT s.soil_humidity, s.temperature, s.air_humidity, s.light_level, s.reading_time, z.name AS zone_name
             FROM sensor_readings s
             LEFT JOIN zones z ON z.id = s.zone_id
             WHERE s.user_id = :uid
             ORDER BY s.reading_time DESC
             LIMIT 1'
        );
        $latestStmt->execute(['uid' => $uid]);
        $latest = $latestStmt->fetch();

        $trendStmt = $this->db->prepare(
            'SELECT reading_time, soil_humidity, temperature, air_humidity, light_level
             FROM sensor_readings
             WHERE user_id = :uid
             ORDER BY reading_time DESC
             LIMIT 24'
        );
        $trendStmt->execute(['uid' => $uid]);
        $trend = array_reverse($trendStmt->fetchAll());

        $this->render('sensors/index', [
            'pageTitle' => 'Раздел датчиков',
            'readings' => $readings,
            'zones' => $zones,
            'latest' => $latest ?: null,
            'trend' => $trend,
            'statuses' => $this->buildStatuses($latest ?: []),
            'page' => $page,
            'pages' => max(1, (int) ceil($total / $perPage)),
            'filters' => [
                'zone_id' => $zoneId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function add(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $uid = (int) $_SESSION['user']['id'];
        $zoneId = (int) ($_POST['zone_id'] ?? 0);
        $soil = (float) ($_POST['soil_humidity'] ?? 0);
        $temp = (float) ($_POST['temperature'] ?? 0);
        $air = (float) ($_POST['air_humidity'] ?? 0);
        $light = (float) ($_POST['light_level'] ?? 0);

        $zoneCheck = $this->db->prepare('SELECT id FROM zones WHERE id = :id AND user_id = :uid LIMIT 1');
        $zoneCheck->execute([
            'id' => $zoneId,
            'uid' => $uid,
        ]);
        if (!$zoneCheck->fetch()) {
            $_SESSION['flash_error'] = 'Выберите корректную зону.';
            $this->redirect('sensors');
        }

        if ($soil < 0 || $soil > 100 || $air < 0 || $air > 100 || $temp < -20 || $temp > 70 || $light < 0 || $light > 200000) {
            $_SESSION['flash_error'] = 'Проверьте диапазоны показаний.';
            $this->redirect('sensors');
        }

        $insert = $this->db->prepare(
            'INSERT INTO sensor_readings (user_id, zone_id, soil_humidity, temperature, air_humidity, light_level, reading_time)
             VALUES (:uid, :zone_id, :soil, :temp, :air, :light, NOW())'
        );
        $insert->execute([
            'uid' => $uid,
            'zone_id' => $zoneId,
            'soil' => $soil,
            'temp' => $temp,
            'air' => $air,
            'light' => $light,
        ]);

        $this->createAlerts($uid, $soil, $temp, $air);
        $this->logAction($uid, 'sensor.create', 'Добавлено новое показание датчиков');

        $_SESSION['flash_success'] = 'Показания датчиков сохранены.';
        $this->redirect('sensors');
    }

    private function buildStatuses(array $latest): array
    {
        if (empty($latest)) {
            return [];
        }

        return [
            'soil' => $this->statusByRange((float) $latest['soil_humidity'], 45, 75),
            'temperature' => $this->statusByRange((float) $latest['temperature'], 18, 30),
            'air' => $this->statusByRange((float) $latest['air_humidity'], 35, 70),
            'light' => $this->statusByRange((float) $latest['light_level'], 2500, 11000),
        ];
    }

    private function statusByRange(float $value, float $min, float $max): string
    {
        if ($value < $min) {
            return 'low';
        }
        if ($value > $max) {
            return 'high';
        }
        return 'ok';
    }

    private function createAlerts(int $uid, float $soil, float $temp, float $air): void
    {
        $alerts = [];
        if ($soil < 40) {
            $alerts[] = ['Низкая влажность почвы', 'Влажность почвы опустилась ниже 40%.', 'high'];
        }
        if ($temp > 32) {
            $alerts[] = ['Высокая температура', 'Температура превысила 32°C.', 'high'];
        }
        if ($air < 30) {
            $alerts[] = ['Сухой воздух', 'Влажность воздуха ниже 30%.', 'medium'];
        }

        if ($alerts === []) {
            return;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO notifications (user_id, title, message, severity, is_read, created_at)
             VALUES (:uid, :title, :message, :severity, 0, NOW())'
        );
        foreach ($alerts as $a) {
            $stmt->execute([
                'uid' => $uid,
                'title' => $a[0],
                'message' => $a[1],
                'severity' => $a[2],
            ]);
        }
    }

    private function logAction(int $uid, string $action, string $message): void
    {
        $log = $this->db->prepare('INSERT INTO system_logs (user_id, action, message, created_at) VALUES (:uid, :action, :message, NOW())');
        $log->execute([
            'uid' => $uid,
            'action' => $action,
            'message' => $message,
        ]);
    }
}
