<?php

namespace Core; 

class Response
{
    /**
     * Convert output to json format
     * @param array|object $data
     * @param int $statusCode
     * @return never
     */
    public static function json(array|object $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    /**
     * convert output to text format
     * @param string $text
     * @param int $statusCode
     * @return never
     */
    public static function text(string $text, int $statusCode = 200): void
    {
        header('Content-Type: text/plain');
        http_response_code($statusCode);
        echo $text;
        exit;
    }
}
