<?php

declare(strict_types=1);

namespace App\Helpers;

class Mailer
{
    public static function send(array $config, string $to, string $subject, string $body): bool
    {
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/plain; charset=UTF-8';
        $headers[] = 'From: ' . $config['mail']['from_name'] . ' <' . $config['mail']['from'] . '>';
        return mail($to, $subject, $body, implode("\r\n", $headers));
    }
}
