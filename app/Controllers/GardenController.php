<?php

declare(strict_types=1);

namespace App\Controllers;

class GardenController extends BaseController
{
    public function dashboard(): void
    {
        $this->requireAuth();
        $uid = (int) $_SESSION['user']['id'];

        $plants = $this->db->prepare(
            'SELECT p.id, p.name, p.stage, p.planted_at, z.name AS zone_name, c.name AS catalog_name
             FROM plants p
             LEFT JOIN zones z ON z.id = p.zone_id
             LEFT JOIN plant_catalog c ON c.id = p.catalog_id
             WHERE p.user_id = :uid
             ORDER BY p.created_at DESC LIMIT 6'
        );
        $plants->execute(['uid' => $uid]);

        $zones = $this->db->prepare('SELECT id, name, zone_type FROM zones WHERE user_id = :uid ORDER BY name ASC');
        $zones->execute(['uid' => $uid]);

        $this->render('garden/dashboard', [
            'pageTitle' => 'Личный кабинет',
            'plants' => $plants->fetchAll(),
            'zones' => $zones->fetchAll(),
        ]);
    }

    public function plants(): void
    {
        $this->requireAuth();
        $uid = (int) $_SESSION['user']['id'];
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 8;
        $offset = ($page - 1) * $perPage;
        $q = trim((string) ($_GET['q'] ?? ''));
        $zoneFilter = (int) ($_GET['zone_id'] ?? 0);

        $where = ' WHERE p.user_id = :uid ';
        $params = ['uid' => $uid];
        if ($q !== '') {
            $where .= ' AND (p.name LIKE :q_name OR c.name LIKE :q_catalog) ';
            $params['q_name'] = '%' . $q . '%';
            $params['q_catalog'] = '%' . $q . '%';
        }
        if ($zoneFilter > 0) {
            $where .= ' AND p.zone_id = :zone_id ';
            $params['zone_id'] = $zoneFilter;
        }

        $countStmt = $this->db->prepare('SELECT COUNT(*) FROM plants p LEFT JOIN plant_catalog c ON c.id = p.catalog_id ' . $where);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare(
            'SELECT p.id, p.name, p.stage, p.target_soil_humidity, p.target_temperature, p.target_light_hours,
                    z.name AS zone_name, c.name AS catalog_name
             FROM plants p
             LEFT JOIN zones z ON z.id = p.zone_id
             LEFT JOIN plant_catalog c ON c.id = p.catalog_id
             ' . $where . '
             ORDER BY p.id DESC
             LIMIT :limit OFFSET :offset'
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v, is_int($v) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $zones = $this->db->prepare('SELECT id, name FROM zones WHERE user_id = :uid ORDER BY name');
        $zones->execute(['uid' => $uid]);
        $catalog = $this->db->query('SELECT id, name, category FROM plant_catalog ORDER BY name ASC');

        $this->render('garden/plants', [
            'pageTitle' => 'Растения',
            'plants' => $stmt->fetchAll(),
            'zones' => $zones->fetchAll(),
            'catalog' => $catalog->fetchAll(),
            'page' => $page,
            'pages' => max(1, (int) ceil($total / $perPage)),
            'filters' => ['q' => $q, 'zone_id' => $zoneFilter],
        ]);
    }

    public function addPlant(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid = (int) $_SESSION['user']['id'];
        $zoneMode = trim((string) ($_POST['zone_mode'] ?? 'existing'));
        $zoneId = (int) ($_POST['zone_id'] ?? 0);

        if ($zoneMode === 'new') {
            $newZoneName = trim((string) ($_POST['new_zone_name'] ?? ''));
            $newZoneType = trim((string) ($_POST['new_zone_type'] ?? 'комната'));
            $newZoneDescription = trim((string) ($_POST['new_zone_description'] ?? ''));

            if ($newZoneName === '') {
                $_SESSION['flash_error'] = 'Укажите название новой зоны.';
                $this->redirect('plants');
            }

            $insertZone = $this->db->prepare(
                'INSERT INTO zones (user_id, name, zone_type, description, created_at)
                 VALUES (:uid, :name, :zone_type, :description, NOW())'
            );
            $insertZone->execute([
                'uid' => $uid,
                'name' => $newZoneName,
                'zone_type' => $newZoneType !== '' ? $newZoneType : 'комната',
                'description' => $newZoneDescription,
            ]);
            $zoneId = (int) $this->db->lastInsertId();
            $this->logAction($uid, 'zone.create', 'Создана зона при добавлении растения');
        } else {
            $checkZone = $this->db->prepare('SELECT id FROM zones WHERE id = :id AND user_id = :uid LIMIT 1');
            $checkZone->execute([
                'id' => $zoneId,
                'uid' => $uid,
            ]);
            if (!$checkZone->fetch()) {
                $_SESSION['flash_error'] = 'Выберите корректную зону или создайте новую.';
                $this->redirect('plants');
            }
        }

        $stmt = $this->db->prepare(
            'INSERT INTO plants (user_id, zone_id, catalog_id, name, stage, planted_at, target_soil_humidity, target_temperature, target_light_hours, created_at)
             VALUES (:user_id, :zone_id, :catalog_id, :name, :stage, :planted_at, :target_soil_humidity, :target_temperature, :target_light_hours, NOW())'
        );
        $stmt->execute([
            'user_id' => $uid,
            'zone_id' => $zoneId,
            'catalog_id' => (int) ($_POST['catalog_id'] ?? 0),
            'name' => trim((string) ($_POST['name'] ?? '')),
            'stage' => trim((string) ($_POST['stage'] ?? 'рост')),
            'planted_at' => $_POST['planted_at'] ?: date('Y-m-d'),
            'target_soil_humidity' => (float) ($_POST['target_soil_humidity'] ?? 60),
            'target_temperature' => (float) ($_POST['target_temperature'] ?? 22),
            'target_light_hours' => (int) ($_POST['target_light_hours'] ?? 12),
        ]);

        $this->logAction($uid, 'plant.create', 'Создана карточка растения');
        $_SESSION['flash_success'] = 'Растение добавлено.';
        $this->redirect('plants');
    }

    public function zones(): void
    {
        $this->requireAuth();
        $uid = (int) $_SESSION['user']['id'];
        $stmt = $this->db->prepare('SELECT id, name, zone_type, description FROM zones WHERE user_id = :uid ORDER BY id DESC');
        $stmt->execute(['uid' => $uid]);
        $this->render('garden/zones', [
            'pageTitle' => 'Зоны выращивания',
            'zones' => $stmt->fetchAll(),
        ]);
    }

    public function status(): void
    {
        $this->requireAuth();
        $uid = (int) $_SESSION['user']['id'];

        $devices = $this->db->prepare('SELECT name, status, is_auto FROM devices WHERE user_id = :uid ORDER BY id DESC');
        $devices->execute(['uid' => $uid]);

        $latestSensor = $this->db->prepare(
            'SELECT soil_humidity, temperature, air_humidity, light_level, reading_time
             FROM sensor_readings WHERE user_id = :uid ORDER BY reading_time DESC LIMIT 1'
        );
        $latestSensor->execute(['uid' => $uid]);

        $this->render('garden/status', [
            'pageTitle' => 'Статус системы',
            'devices' => $devices->fetchAll(),
            'sensor' => $latestSensor->fetch(),
        ]);
    }

    public function addZone(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid = (int) $_SESSION['user']['id'];
        $stmt = $this->db->prepare('INSERT INTO zones (user_id, name, zone_type, description, created_at) VALUES (:uid, :name, :zone_type, :description, NOW())');
        $stmt->execute([
            'uid' => $uid,
            'name' => trim((string) ($_POST['name'] ?? '')),
            'zone_type' => trim((string) ($_POST['zone_type'] ?? 'комната')),
            'description' => trim((string) ($_POST['description'] ?? '')),
        ]);
        $this->logAction($uid, 'zone.create', 'Создана зона выращивания');
        $_SESSION['flash_success'] = 'Зона добавлена.';
        $this->redirect('zones');
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
