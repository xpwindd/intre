<?php

declare(strict_types=1);

namespace App\Controllers;

class LegalController extends BaseController
{
    public function privacy(): void
    {
        $this->render('legal/privacy', [
            'pageTitle' => 'Политика конфиденциальности',
            'metaDescription' => 'Как Smart Garden обрабатывает и защищает персональные данные пользователей.',
        ]);
    }
}
