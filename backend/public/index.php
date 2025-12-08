<?php
// Autoload Composer (Tombol ON untuk semua magic di atas)
require_once __DIR__ . "/../vendor/autoload.php";

// Load Environment Variables (Jika di localhost)
if (file_exists(__DIR__ . "/../.env")) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
    $dotenv->load();
}

// Load Routing
require_once __DIR__ . "/../src/Routes/api.php";
