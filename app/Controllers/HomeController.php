<?php

declare(strict_types=1);

namespace App\Controllers;

class HomeController extends BaseController
{
    public function index(): void
    {
        $stats = [
            'plants' => 0,
            'zones' => 0,
            'notifications' => 0,
        ];

        if (!empty($_SESSION['user']['id'])) {
            $uid = (int) $_SESSION['user']['id'];
            $q1 = $this->db->prepare('SELECT COUNT(*) FROM plants WHERE user_id = :uid');
            $q1->execute(['uid' => $uid]);
            $stats['plants'] = (int) $q1->fetchColumn();

            $q2 = $this->db->prepare('SELECT COUNT(*) FROM zones WHERE user_id = :uid');
            $q2->execute(['uid' => $uid]);
            $stats['zones'] = (int) $q2->fetchColumn();

            $q3 = $this->db->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0');
            $q3->execute(['uid' => $uid]);
            $stats['notifications'] = (int) $q3->fetchColumn();
        }

        $this->render('home/index', [
            'pageTitle' => 'Главная',
            'stats' => $stats,
        ]);
    }

    public function notFound(): void
    {
        $this->render('errors/404', [
            'pageTitle' => '404',
        ]);
    }
}
