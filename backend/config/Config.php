<?php

class Config
{
    public static function JWT_SECRET()
    {
        return $_ENV["JWT_SECRET"];
    }

    public static function JWT_ALGO()
    {
        return $_ENV["JWT_ALGO"];
    }

    public static function MIDTRANS_SERVER_KEY()
    {
        return $_ENV["MIDTRANS_SERVER_KEY"];
    }

    public static function MIDTRANS_CLIENT_KEY()
    {
        return $_ENV["MIDTRANS_CLIENT_KEY"];
    }

    public static function MIDTRANS_IS_PRODUCTION()
    {
        return $_ENV["MIDTRANS_IS_PRODUCTION"] === "true";
    }
}
