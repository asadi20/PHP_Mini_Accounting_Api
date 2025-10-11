<?php

namespace Core; 

class Response
{
    public static function json(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    public static function text(string $text, int $statusCode = 200): void
    {
        header('Content-Type: text/plain');
        http_response_code($statusCode);
        echo $text;
        exit;
    }
}
