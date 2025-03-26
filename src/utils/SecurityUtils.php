<?php
namespace App\Utils;

class SecurityUtils {
    public static function isWithinRoot($directory) {
        return $directory && strpos($directory, ROOT_DIR) === 0;
    }
}