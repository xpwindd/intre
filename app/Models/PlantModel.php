<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class PlantModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function byUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM plants WHERE user_id = :uid ORDER BY id DESC');
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }
}
