<?php
namespace App\Utils;

class FileUtils {
    public static function formatFileSize($bytes) {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        elseif ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        elseif ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        elseif ($bytes > 1) return $bytes . ' bytes';
        elseif ($bytes == 1) return '1 byte';
        else return '0 bytes';
    }

    public static function getLastModifiedDate($path) {
        return date("Y-m-d H:i:s", filemtime($path));
    }

    public static function rrmdir($dir) {
        foreach (glob($dir . '/*') as $file) {
            is_dir($file) ? self::rrmdir($file) : unlink($file);
        }
        rmdir($dir);
    }

    public static function getFileIcon($file) {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        switch ($extension) {
            case 'jpg': case 'jpeg': case 'png': case 'gif': case 'webp':
                return 'fa-image text-yellow-400';
            case 'mp4': case 'avi': case 'mov': case 'mkv': case 'webm':
                return 'fa-video text-red-400';
            case 'mp3': case 'wav': case 'ogg': case 'flac': case 'm4a':
                return 'fa-music text-purple-400';
            case 'zip': case 'rar': case 'tar': case 'gz': case '7z':
                return 'fa-file-archive text-yellow-600';
            case 'pdf':
                return 'fa-file-pdf text-red-500';
            case 'doc': case 'docx':
                return 'fa-file-word text-blue-500';
            case 'xls': case 'xlsx':
                return 'fa-file-excel text-green-500';
            case 'ppt': case 'pptx':
                return 'fa-file-powerpoint text-orange-500';
            case 'txt': case 'log': case 'md':
                return 'fa-file-alt text-gray-400';
            case 'php': case 'js': case 'html': case 'css': case 'json':
                return 'fa-file-code text-blue-400';
            default:
                return 'fa-file text-gray-400';
        }
    }

    public static function createBreadcrumb($dir, $rootDir) {
        $parts = explode(DIRECTORY_SEPARATOR, $dir);
        $breadcrumb = '<div class="flex items-center space-x-2 text-sm">';
        $currentPath = '';
        foreach ($parts as $part) {
            if (empty($part)) continue;
            $currentPath .= $part . DIRECTORY_SEPARATOR;
            $isActive = rtrim($currentPath, DIRECTORY_SEPARATOR) === $dir;
            $breadcrumb .= '<a href="?dir=' . urlencode(rtrim($currentPath, DIRECTORY_SEPARATOR)) . '" class="' . ($isActive ? 'text-blue-600 font-medium' : 'text-gray-600 hover:text-blue-500') . '">' . htmlspecialchars($part) . '</a>';
            $breadcrumb .= '<span class="text-gray-400">/</span>';
        }
        $breadcrumb .= '</div>';
        return $breadcrumb;
    }
}