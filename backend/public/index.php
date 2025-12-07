<?php

require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../src/routes/api.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$db = new Database();
