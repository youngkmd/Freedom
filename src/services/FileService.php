<?php
namespace App\Services;

use App\Utils\FileUtils;

class FileService {
    public function downloadFile($directory, $file) {
        $filepath = $directory . '/' . basename($file);
        if (file_exists($filepath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($filepath));
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            exit;
        }
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative'>File not found!</div>";
    }

    public function editFile($directory, $file) {
        $filePath = $directory . '/' . basename($file);
        if (file_exists($filePath)) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                file_put_contents($filePath, $_POST['content']);
                echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4'>File saved successfully!</div>";
            }
            $content = htmlspecialchars(file_get_contents($filePath));
            include __DIR__ . '/../views/edit.php';
            exit;
        }
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative'>File not found!</div>";
    }

    public function uploadFiles($directory) {
        foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
            $fileName = basename($_FILES['files']['name'][$key]);
            $filePath = $directory . '/' . $fileName;
            move_uploaded_file($tmpName, $filePath) ?
                $this->successMessage("$fileName uploaded successfully.") :
                $this->errorMessage("Error uploading $fileName.");
        }
    }

    public function importFileFromUrl($directory, $fileUrl) {
        if (!filter_var($fileUrl, FILTER_VALIDATE_URL)) {
            $this->errorMessage("Invalid URL.");
            return;
        }
        $fileName = basename($fileUrl);
        $filePath = $directory . '/' . $fileName;
        $fileContent = $this->downloadUrl($fileUrl);
        file_put_contents($filePath, $fileContent) ?
            $this->successMessage("File imported successfully: $fileName") :
            $this->errorMessage("Failed to save the file.");
    }

    public function unzipFile($directory, $zipFile) {
        $zipPath = $directory . '/' . basename($zipFile);
        if (file_exists($zipPath)) {
            $zip = new \ZipArchive();
            $zip->open($zipPath) === TRUE && $zip->extractTo($directory) && $zip->close() ?
                $this->successMessage("Extracted successfully.") :
                $this->errorMessage("Failed to extract.");
        } else {
            $this->errorMessage("ZIP file not found.");
        }
    }

    public function createFolder($directory, $folderName) {
        $newFolder = $directory . '/' . basename($folderName);
        !file_exists($newFolder) && mkdir($newFolder) ?
            $this->successMessage("Folder created successfully.") :
            $this->errorMessage("Folder already exists.");
    }

    public function deleteItem($directory, $item) {
        $path = $directory . '/' . basename($item);
        is_dir($path) ? FileUtils::rrmdir($path) : unlink($path);
        $this->successMessage("Deleted successfully.");
    }

    public function renameItem($directory, $oldName) {
        $path = $directory . '/' . basename($oldName);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_name'])) {
            $newName = $directory . '/' . basename($_POST['new_name']);
            rename($path, $newName);
            $this->successMessage("Renamed successfully.");
        } else {
            include __DIR__ . '/../views/rename.php';
        }
    }

    public function handleClipboard($directory, $get) {
        if (isset($get['copy'])) {
            $_SESSION['clipboard'] = ['action' => 'copy', 'path' => $directory . '/' . basename($get['copy'])];
            echo "<div class='bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4'>Copied to clipboard.</div>";
        } elseif (isset($get['cut'])) {
            $_SESSION['clipboard'] = ['action' => 'cut', 'path' => $directory . '/' . basename($get['cut'])];
            echo "<div class='bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4'>Cut to clipboard.</div>";
        } elseif (isset($get['paste']) && isset($_SESSION['clipboard'])) {
            $dest = $directory . '/' . basename($_SESSION['clipboard']['path']);
            $_SESSION['clipboard']['action'] === 'copy' ?
                (copy($_SESSION['clipboard']['path'], $dest) && $this->successMessage("Pasted successfully (copy).")) :
                (rename($_SESSION['clipboard']['path'], $dest) && $this->successMessage("Pasted successfully (move)."));
            unset($_SESSION['clipboard']);
        }
    }

    public function listItems($directory) {
        return scandir($directory);
    }

    private function downloadUrl($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }

    private function successMessage($message) {
        echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4'>$message</div>";
    }

    private function errorMessage($message) {
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4'>$message</div>";
    }
}