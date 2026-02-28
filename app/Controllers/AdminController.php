<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;

class AdminController extends BaseController
{
    public function index(): void
    {
        $this->requireAdmin();

        $userQuery = trim((string) ($_GET['q'] ?? ''));
        $userRole = trim((string) ($_GET['role'] ?? ''));
        $deviceStatus = trim((string) ($_GET['device_status'] ?? ''));
        $logQuery = trim((string) ($_GET['log_q'] ?? ''));

        $stats = $this->buildStats();
        $roles = $this->db->query('SELECT id, name, slug FROM roles ORDER BY id ASC')->fetchAll();
        $users = $this->loadUsers($userQuery, $userRole);
        $devices = $this->loadDevices($deviceStatus);
        $notifications = $this->loadNotifications();
        $logs = $this->loadLogs($logQuery);

        $this->render('admin/index', [
            'pageTitle' => 'Админ-панель',
            'stats' => $stats,
            'roles' => $roles,
            'users' => $users,
            'devices' => $devices,
            'notifications' => $notifications,
            'logs' => $logs,
            'filters' => [
                'q' => $userQuery,
                'role' => $userRole,
                'device_status' => $deviceStatus,
                'log_q' => $logQuery,
            ],
        ]);
    }

    public function updateUserRole(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $targetUserId = (int) ($_POST['user_id'] ?? 0);
        $roleId = (int) ($_POST['role_id'] ?? 0);

        if ($targetUserId < 1 || $roleId < 1) {
            $_SESSION['flash_error'] = 'Некорректные параметры смены роли.';
            $this->redirect('admin');
        }

        if ($targetUserId === (int) Auth::id()) {
            $_SESSION['flash_error'] = 'Нельзя изменить роль текущего администратора.';
            $this->redirect('admin');
        }

        $roleCheck = $this->db->prepare('SELECT id, slug FROM roles WHERE id = :id LIMIT 1');
        $roleCheck->execute(['id' => $roleId]);
        $role = $roleCheck->fetch();
        if (!$role) {
            $_SESSION['flash_error'] = 'Роль не найдена.';
            $this->redirect('admin');
        }

        $userCheck = $this->db->prepare('SELECT id, email FROM users WHERE id = :id LIMIT 1');
        $userCheck->execute(['id' => $targetUserId]);
        $user = $userCheck->fetch();
        if (!$user) {
            $_SESSION['flash_error'] = 'Пользователь не найден.';
            $this->redirect('admin');
        }

        $update = $this->db->prepare('UPDATE users SET role_id = :role_id WHERE id = :id');
        $update->execute([
            'role_id' => $roleId,
            'id' => $targetUserId,
        ]);

        $this->logAdmin('admin.user.role', sprintf('Изменена роль пользователя #%d (%s) на %s', $targetUserId, $user['email'], $role['slug']));
        $_SESSION['flash_success'] = 'Роль пользователя обновлена.';
        $this->redirect('admin');
    }

    public function toggleDevice(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $deviceId = (int) ($_POST['device_id'] ?? 0);
        if ($deviceId < 1) {
            $_SESSION['flash_error'] = 'Устройство не выбрано.';
            $this->redirect('admin');
        }

        $check = $this->db->prepare('SELECT id, user_id, status, name FROM devices WHERE id = :id LIMIT 1');
        $check->execute(['id' => $deviceId]);
        $device = $check->fetch();
        if (!$device) {
            $_SESSION['flash_error'] = 'Устройство не найдено.';
            $this->redirect('admin');
        }

        $nextStatus = $device['status'] === 'on' ? 'off' : 'on';
        $update = $this->db->prepare('UPDATE devices SET status = :status, updated_at = NOW() WHERE id = :id');
        $update->execute([
            'status' => $nextStatus,
            'id' => $deviceId,
        ]);

        $this->logAdmin('admin.device.toggle', sprintf('Переключено устройство #%d (%s) на %s', $deviceId, $device['name'], $nextStatus));
        $_SESSION['flash_success'] = 'Статус устройства обновлен.';
        $this->redirect('admin');
    }

    public function markNotificationRead(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $notificationId = (int) ($_POST['notification_id'] ?? 0);
        if ($notificationId < 1) {
            $_SESSION['flash_error'] = 'Уведомление не выбрано.';
            $this->redirect('admin');
        }

        $update = $this->db->prepare('UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = :id');
        $update->execute(['id' => $notificationId]);

        $this->logAdmin('admin.notification.read', sprintf('Уведомление #%d отмечено как прочитанное', $notificationId));
        $_SESSION['flash_success'] = 'Уведомление отмечено как прочитанное.';
        $this->redirect('admin');
    }

    private function buildStats(): array
    {
        return [
            'users' => (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'plants' => (int) $this->db->query('SELECT COUNT(*) FROM plants')->fetchColumn(),
            'devices' => (int) $this->db->query('SELECT COUNT(*) FROM devices')->fetchColumn(),
            'unread_notifications' => (int) $this->db->query('SELECT COUNT(*) FROM notifications WHERE is_read = 0')->fetchColumn(),
        ];
    }

    private function loadUsers(string $query, string $role): array
    {
        $where = ' WHERE 1=1 ';
        $params = [];

        if ($query !== '') {
            $where .= ' AND (u.name LIKE :q_name OR u.email LIKE :q_email) ';
            $params['q_name'] = '%' . $query . '%';
            $params['q_email'] = '%' . $query . '%';
        }
        if (in_array($role, ['admin', 'user'], true)) {
            $where .= ' AND r.slug = :role ';
            $params['role'] = $role;
        }

        $sql = 'SELECT u.id, u.name, u.email, u.created_at, r.id AS role_id, r.slug AS role_slug,
                       (SELECT COUNT(*) FROM plants p WHERE p.user_id = u.id) AS plants_count,
                       (SELECT COUNT(*) FROM devices d WHERE d.user_id = u.id) AS devices_count
                FROM users u
                JOIN roles r ON r.id = u.role_id
                ' . $where . '
                ORDER BY u.id DESC
                LIMIT 120';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, \PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function loadDevices(string $status): array
    {
        $where = ' WHERE 1=1 ';
        $params = [];
        if (in_array($status, ['on', 'off'], true)) {
            $where .= ' AND d.status = :status ';
            $params['status'] = $status;
        }

        $sql = 'SELECT d.id, d.name, d.device_type, d.status, d.is_auto, d.updated_at,
                       u.name AS user_name, u.email AS user_email, z.name AS zone_name
                FROM devices d
                JOIN users u ON u.id = d.user_id
                LEFT JOIN zones z ON z.id = d.zone_id
                ' . $where . '
                ORDER BY d.id DESC
                LIMIT 120';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, \PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function loadNotifications(): array
    {
        $stmt = $this->db->prepare(
            'SELECT n.id, n.title, n.message, n.severity, n.is_read, n.created_at, u.email AS user_email
             FROM notifications n
             JOIN users u ON u.id = n.user_id
             ORDER BY n.id DESC
             LIMIT 80'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function loadLogs(string $query): array
    {
        $where = ' WHERE 1=1 ';
        $params = [];
        if ($query !== '') {
            $where .= ' AND (l.action LIKE :q_action OR l.message LIKE :q_message OR u.email LIKE :q_email) ';
            $params['q_action'] = '%' . $query . '%';
            $params['q_message'] = '%' . $query . '%';
            $params['q_email'] = '%' . $query . '%';
        }

        $sql = 'SELECT l.id, l.user_id, l.action, l.message, l.created_at, u.email AS user_email
                FROM system_logs l
                LEFT JOIN users u ON u.id = l.user_id
                ' . $where . '
                ORDER BY l.id DESC
                LIMIT 160';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, \PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function logAdmin(string $action, string $message): void
    {
        $stmt = $this->db->prepare('INSERT INTO system_logs (user_id, action, message, created_at) VALUES (:user_id, :action, :message, NOW())');
        $stmt->execute([
            'user_id' => (int) Auth::id(),
            'action' => $action,
            'message' => $message,
        ]);
    }
}
