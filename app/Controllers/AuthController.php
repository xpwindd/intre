<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Mailer;

class AuthController extends BaseController
{
    public function showLogin(): void
    {
        $this->render('auth/login', ['pageTitle' => 'Вход']);
    }

    public function login(): void
    {
        $this->verifyCsrf();
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $stmt = $this->db->prepare(
            'SELECT u.id, u.email, u.name, u.password_hash, r.slug AS role_slug
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['flash_error'] = 'Неверный email или пароль.';
            $this->redirect('login');
        }

        Auth::login($user);
        $this->redirect('dashboard');
    }

    public function showRegister(): void
    {
        $this->render('auth/register', ['pageTitle' => 'Регистрация']);
    }

    public function register(): void
    {
        $this->verifyCsrf();
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($password) < 6) {
            $_SESSION['flash_error'] = 'Проверьте введенные данные.';
            $this->redirect('register');
        }

        $exists = $this->db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $exists->execute(['email' => $email]);
        if ($exists->fetch()) {
            $_SESSION['flash_error'] = 'Пользователь с таким email уже существует.';
            $this->redirect('register');
        }

        $role = $this->db->prepare('SELECT id FROM roles WHERE slug = :slug LIMIT 1');
        $role->execute(['slug' => 'user']);
        $roleId = (int) $role->fetchColumn();

        $stmt = $this->db->prepare(
            'INSERT INTO users (role_id, name, email, password_hash, created_at) VALUES (:role_id, :name, :email, :password_hash, NOW())'
        );
        $stmt->execute([
            'role_id' => $roleId,
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        $userId = (int) $this->db->lastInsertId();
        $this->bootstrapUserDevices($userId);

        Mailer::send($this->config, $email, 'Регистрация в Smart Garden', "Здравствуйте, {$name}! Ваш аккаунт успешно создан.");
        $_SESSION['flash_success'] = 'Регистрация завершена. Теперь войдите в систему.';
        $this->redirect('login');
    }

    private function bootstrapUserDevices(int $userId): void
    {
        $preset = [
            ['name' => 'Насос полива (старт)', 'type' => 'pump', 'status' => 'off', 'is_auto' => 1],
            ['name' => 'LED лампа (старт)', 'type' => 'lamp', 'status' => 'off', 'is_auto' => 1],
            ['name' => 'Вентилятор (старт)', 'type' => 'fan', 'status' => 'off', 'is_auto' => 0],
        ];

        $insert = $this->db->prepare(
            'INSERT INTO devices (user_id, zone_id, name, device_type, status, is_auto, created_at, updated_at)
             VALUES (:user_id, NULL, :name, :device_type, :status, :is_auto, NOW(), NOW())'
        );
        foreach ($preset as $device) {
            $insert->execute([
                'user_id' => $userId,
                'name' => $device['name'],
                'device_type' => $device['type'],
                'status' => $device['status'],
                'is_auto' => $device['is_auto'],
            ]);
        }

        $log = $this->db->prepare(
            'INSERT INTO system_logs (user_id, action, message, created_at) VALUES (:uid, :action, :message, NOW())'
        );
        $log->execute([
            'uid' => $userId,
            'action' => 'devices.bootstrap',
            'message' => 'Автоматически добавлены стартовые устройства.',
        ]);
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('home');
    }

    public function showForgot(): void
    {
        $this->render('auth/forgot', ['pageTitle' => 'Восстановление пароля']);
    }

    public function forgot(): void
    {
        $this->verifyCsrf();
        $email = trim((string) ($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Некорректный email.';
            $this->redirect('forgot');
        }

        $stmt = $this->db->prepare('SELECT id, name FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if ($user) {
            $token = bin2hex(random_bytes(24));
            $ins = $this->db->prepare('INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (:user_id, :token, DATE_ADD(NOW(), INTERVAL 2 HOUR), NOW())');
            $ins->execute([
                'user_id' => (int) $user['id'],
                'token' => $token,
            ]);
            $link = (isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : '') . '/?route=reset&token=' . urlencode($token);
            Mailer::send($this->config, $email, 'Сброс пароля Smart Garden', "Для сброса пароля перейдите по ссылке:\n{$link}");
        }

        $_SESSION['flash_success'] = 'Если аккаунт найден, ссылка для сброса отправлена.';
        $this->redirect('forgot');
    }

    public function showReset(): void
    {
        $token = trim((string) ($_GET['token'] ?? ''));
        $this->render('auth/reset', [
            'pageTitle' => 'Новый пароль',
            'token' => $token,
        ]);
    }

    public function reset(): void
    {
        $this->verifyCsrf();
        $token = trim((string) ($_POST['token'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if (mb_strlen($password) < 6 || $token === '') {
            $_SESSION['flash_error'] = 'Некорректные данные формы.';
            $this->redirect('reset&token=' . urlencode($token));
        }

        $stmt = $this->db->prepare('SELECT user_id FROM password_resets WHERE token = :token AND used_at IS NULL AND expires_at > NOW() LIMIT 1');
        $stmt->execute(['token' => $token]);
        $row = $stmt->fetch();
        if (!$row) {
            $_SESSION['flash_error'] = 'Токен недействителен или просрочен.';
            $this->redirect('forgot');
        }

        $this->db->beginTransaction();
        try {
            $upd = $this->db->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
            $upd->execute([
                'hash' => password_hash($password, PASSWORD_DEFAULT),
                'id' => (int) $row['user_id'],
            ]);
            $mark = $this->db->prepare('UPDATE password_resets SET used_at = NOW() WHERE token = :token');
            $mark->execute(['token' => $token]);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        $_SESSION['flash_success'] = 'Пароль обновлен.';
        $this->redirect('login');
    }
}
