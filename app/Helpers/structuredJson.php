<?php
function structuredJson($data = null, string $status = "successful", string $message = "", int $code = 200, string $headers = "")
{
    return [
        [
            "status" => $status,
            "message" => $message,
            "data" => $data
        ],
        $code,
        ["Content-Type=application/json;charset=utf-8", $headers],
        JSON_UNESCAPED_UNICODE
    ];
}
