<?php
namespace App\Controllers;
require_once __DIR__ . '/../config/config.php'; 
use App\Services\FileService;
use App\Utils\SecurityUtils;

class FileController {
    private $fileService;

    public function __construct() {
        $this->fileService = new FileService();
    }

    public function handleRequest() {
        $directory = isset($_GET['dir']) ? $_GET['dir'] : ROOT_DIR;
        $directory = realpath($directory);

        // Security check
        if (!SecurityUtils::isWithinRoot($directory)) {
            die("<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative'>Access denied.</div>");
        }

        // Handle actions
        if (isset($_GET['download'])) {
            $this->fileService->downloadFile($directory, $_GET['download']);
        } elseif (isset($_GET['edit'])) {
            $this->fileService->editFile($directory, $_GET['edit']);
        } elseif (isset($_GET['unzip'])) {
            $this->fileService->unzipFile($directory, $_GET['unzip']);
        } elseif (isset($_GET['delete'])) {
            $this->fileService->deleteItem($directory, $_GET['delete']);
        } elseif (isset($_GET['rename'])) {
            $this->fileService->renameItem($directory, $_GET['rename']);
        } elseif (isset($_GET['copy']) || isset($_GET['cut']) || isset($_GET['paste'])) {
            $this->fileService->handleClipboard($directory, $_GET);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_FILES['files'])) {
                $this->fileService->uploadFiles($directory);
            } elseif (isset($_POST['file_url'])) {
                $this->fileService->importFileFromUrl($directory, $_POST['file_url']);
            } elseif (isset($_POST['new_folder'])) {
                $this->fileService->createFolder($directory, $_POST['new_folder']);
            }
        }

        // Render the file manager view
        $this->renderIndex($directory);
    }

    private function renderIndex($directory) {
        $items = $this->fileService->listItems($directory);
        include __DIR__ . '/../views/index.php';
    }
}
