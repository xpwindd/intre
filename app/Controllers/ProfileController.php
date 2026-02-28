<?php

declare(strict_types=1);

namespace App\Controllers;

class ProfileController extends BaseController
{
    public function show(): void
    {
        $this->requireAuth();
        $uid = (int) $_SESSION['user']['id'];
        $stmt = $this->db->prepare('SELECT id, name, email, created_at FROM users WHERE id = :id');
        $stmt->execute(['id' => $uid]);
        $user = $stmt->fetch();
        $this->render('profile/show', [
            'pageTitle' => 'Профиль',
            'user' => $user,
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid = (int) $_SESSION['user']['id'];
        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            $_SESSION['flash_error'] = 'Имя не может быть пустым.';
            $this->redirect('profile');
        }
        $stmt = $this->db->prepare('UPDATE users SET name = :name WHERE id = :id');
        $stmt->execute([
            'name' => $name,
            'id' => $uid,
        ]);
        $_SESSION['user']['name'] = $name;
        $_SESSION['flash_success'] = 'Профиль обновлен.';
        $this->redirect('profile');
    }
}
