<?php

namespace App\Config;

class Config
{
    public static function JWT_SECRET()
    {
        return $_ENV["JWT_SECRET"] ?? (getenv("JWT_SECRET") ?? "");
    }

    public static function JWT_ALGO()
    {
        return $_ENV["JWT_ALGO"] ?? (getenv("JWT_ALGO") ?? "HS256");
    }

    public static function MIDTRANS_SERVER_KEY()
    {
        return $_ENV["MIDTRANS_SERVER_KEY"] ??
            (getenv("MIDTRANS_SERVER_KEY") ?? "");
    }

    public static function MIDTRANS_CLIENT_KEY()
    {
        return $_ENV["MIDTRANS_CLIENT_KEY"] ??
            (getenv("MIDTRANS_CLIENT_KEY") ?? "");
    }

    public static function MIDTRANS_IS_PRODUCTION()
    {
        $val =
            $_ENV["MIDTRANS_IS_PRODUCTION"] ??
            (getenv("MIDTRANS_IS_PRODUCTION") ?? "false");
        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }
}
