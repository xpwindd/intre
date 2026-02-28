<?php

declare(strict_types=1);

namespace App\Controllers;

class NotificationsController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        $uid = (int) $_SESSION['user']['id'];
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        $severity = trim((string) ($_GET['severity'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));

        $where = ' WHERE user_id = :uid ';
        $params = ['uid' => $uid];
        if (in_array($severity, ['low', 'medium', 'high'], true)) {
            $where .= ' AND severity = :severity ';
            $params['severity'] = $severity;
        }
        if ($status === 'read') {
            $where .= ' AND is_read = 1 ';
        } elseif ($status === 'unread') {
            $where .= ' AND is_read = 0 ';
        }

        $count = $this->db->prepare('SELECT COUNT(*) FROM notifications ' . $where);
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        $stmt = $this->db->prepare(
            'SELECT id, title, message, severity, is_read, created_at
             FROM notifications
             ' . $where . '
             ORDER BY id DESC
             LIMIT :limit OFFSET :offset'
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v, is_int($v) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $this->render('notifications/index', [
            'pageTitle' => 'Уведомления',
            'notifications' => $stmt->fetchAll(),
            'page' => $page,
            'pages' => max(1, (int) ceil($total / $perPage)),
            'filters' => ['severity' => $severity, 'status' => $status],
        ]);
    }

    public function markRead(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid = (int) $_SESSION['user']['id'];
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $this->db->prepare('UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = :id AND user_id = :uid');
        $stmt->execute(['id' => $id, 'uid' => $uid]);
        $this->redirect('notifications');
    }
}
