<?php
session_start();


require_once __DIR__ . '/config/config.php'; 
require_once __DIR__ . '/utils/FileUtils.php';
require_once __DIR__ . '/utils/SecurityUtils.php';
require_once __DIR__ . '/services/FileService.php';
require_once __DIR__ . '/controllers/FileController.php';

use App\Controllers\FileController;

$controller = new FileController();
$controller->handleRequest();