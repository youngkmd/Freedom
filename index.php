<?php
session_start();
require __DIR__ . '/vendor/autoload.php';




use App\Controllers\FileController;
$controller = new FileController();
$controller->handleRequest();
