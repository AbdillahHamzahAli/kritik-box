<?php

namespace App\Middleware;

use App\Config\Config;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    public function handle(): ?object
    {
        $headers = getallheaders();
        $authHeader = $headers["Authorization"] ?? "";

        if (!preg_match("/Bearer\s(\S+)/", $authHeader, $matches)) {
            $this->sendUnauthorized("Token tidak ditemukan");
            return null;
        }

        $jwt = $matches[1];

        try {
            $decoded = JWT::decode(
                $jwt,
                new Key(Config::JWT_SECRET(), Config::JWT_ALGO()),
            );

            return $decoded->data;
        } catch (Exception $e) {
            $this->sendUnauthorized(
                "Token tidak valid atau kadaluarsa: " . $e->getMessage(),
            );
            return null;
        }
    }

    private function sendUnauthorized($message)
    {
        http_response_code(401);
        header("Content-Type: application/json");
        echo json_encode([
            "status" => "error",
            "message" => "Unauthorized access. " . $message,
        ]);
        exit();
    }
}
