<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php'; // Assuming Composer autoloading
require_once __DIR__ . '/../config/config.php';

use App\Controllers\FileController;

$controller = new FileController();
$controller->handleRequest();