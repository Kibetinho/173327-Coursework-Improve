<?php

class Mailer
{
    public static function send(string $to, string $subject, string $message): void
    {
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=utf-8';
        $headers[] = 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>';
        @mail($to, $subject, $message, implode("\r\n", $headers));
    }
}


