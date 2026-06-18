<?php
// PHP File Manager - Modern UI Edition
$current_dir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
$msg = '';
$msg_type = '';
$uploaded_file_url = '';
$edit_content = '';
$edit_file = '';

// Security: Prevent directory traversal
$current_dir = realpath($current_dir) ?: getcwd();

// Get the web accessible path
$doc_root = $_SERVER['DOCUMENT_ROOT'];
$web_path = str_replace($doc_root, '', $current_dir);
$web_path = ltrim($web_path, '/');

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $filename = basename($file['name']);
    $target = $current_dir . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        $msg = "File uploaded successfully!";
        $msg_type = 'success';
        $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $web_url = $protocol . $host . '/' . $web_path . '/' . $filename;
        $web_url = str_replace('//', '/', $web_url);
    } else {
        $msg = "Upload failed";
        $msg_type = 'error';
    }
}

// Handle rename
if (isset($_POST['rename_old']) && isset($_POST['rename_new'])) {
    $old_name = $current_dir . '/' . basename($_POST['rename_old']);
    $new_name = $current_dir . '/' . basename($_POST['rename_new']);
    if (file_exists($old_name) && !file_exists($new_name)) {
        if (rename($old_name, $new_name)) {
            $msg = "File renamed successfully!";
            $msg_type = 'success';
        } else {
            $msg = "Rename failed";
            $msg_type = 'error';
        }
    } else {
        $msg = "File not found or name already exists";
        $msg_type = 'error';
    }
}

// Handle deletion
if (isset($_GET['delete'])) {
    $file_to_delete = $current_dir . '/' . basename($_GET['delete']);
    if (file_exists($file_to_delete) && is_file($file_to_delete)) {
        if (unlink($file_to_delete)) {
            $msg = "File deleted successfully!";
            $msg_type = 'success';
        } else {
            $msg = "Delete failed";
            $msg_type = 'error';
        }
    }
}

// Handle edit
if (isset($_GET['edit'])) {
    $edit_file = basename($_GET['edit']);
    $file_path = $current_dir . '/' . $edit_file;
    if (file_exists($file_path) && is_file($file_path)) {
        $edit_content = file_get_contents($file_path);
    }
}

// Handle save
if (isset($_POST['save_content']) && isset($_POST['filename'])) {
    $filename = basename($_POST['filename']);
    $file_path = $current_dir . '/' . $filename;
    if (file_exists($file_path) && is_file($file_path)) {
        if (file_put_contents($file_path, $_POST['content'])) {
            $msg = "Changes saved successfully!";
            $msg_type = 'success';
            $edit_content = '';
            $edit_file = '';
        } else {
            $msg = "Save failed";
            $msg_type = 'error';
        }
    }
}

// Handle file opening
if (isset($_GET['open'])) {
    $file_to_open = $current_dir . '/' . basename($_GET['open']);
    if (file_exists($file_to_open) && is_file($file_to_open)) {
        header('Content-Type: ' . mime_content_type($file_to_open));
        header('Content-Disposition: inline; filename="' . basename($file_to_open) . '"');
        readfile($file_to_open);
        exit;
    }
}

// Get folders and files
$items = scandir($current_dir);
$folders = $files = [];
foreach ($items as $item) {
    if ($item !== '.' && $item !== '..') {
        $path = $current_dir . '/' . $item;
        is_dir($path) ? $folders[] = $item : $files[] = $item;
    }
}

$parent_dir = dirname($current_dir);

// Get file size helper
function format_size($bytes) {
    if ($bytes == 0) return '0 B';
    $k = 1024;
    $sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

// Get file icon
function get_file_icon($filename) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $icons = [
        'php' => '🐘', 'html' => '🌐', 'css' => '🎨', 'js' => '⚡',
        'json' => '📋', 'xml' => '📄', 'txt' => '📝', 'md' => '📖',
        'jpg' => '🖼️', 'jpeg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️',
        'svg' => '🖼️', 'pdf' => '📕', 'doc' => '📘', 'docx' => '📘',
        'xls' => '📊', 'xlsx' => '📊', 'zip' => '📦', 'rar' => '📦',
        'tar' => '📦', 'gz' => '📦', 'mp3' => '🎵', 'mp4' => '🎬',
        'avi' => '🎬', 'mkv' => '🎬', 'exe' => '⚙️'
    ];
    return $icons[$ext] ?? '📄';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ===== CSS Variables ===== */
        :root {
            --bg-primary: #0d1117;
            --bg-secondary: #161b22;
            --bg-tertiary: #1c2333;
            --bg-hover: #252d3f;
            --bg-card: #1a1f2e;
            --border-color: #30363d;
            --border-hover: #58a6ff;
            --text-primary: #f0f6fc;
            --text-secondary: #8b949e;
            --text-muted: #484f58;
            --accent-blue: #58a6ff;
            --accent-green: #3fb950;
            --accent-red: #f85149;
            --accent-yellow: #d29922;
            --accent-purple: #bc8cff;
            --shadow: 0 8px 32px rgba(0,0,0,0.4);
            --radius: 12px;
            --radius-sm: 8px;
            --transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ===== Reset & Base ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            padding: 24px;
            line-height: 1.6;
        }

        /* ===== Scrollbar ===== */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bg-secondary);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-muted);
        }

        /* ===== Container ===== */
        .app-container {
            max-width: 1280px;
            margin: 0 auto;
        }

        /* ===== Header ===== */
        .app-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            padding: 20px 28px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            margin-bottom: 24px;
        }

        .app-header .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 22px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .app-header .logo span {
            color: var(--accent-blue);
        }

        .app-header .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        /* ===== Buttons ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: none;
            border-radius: var(--radius-sm);
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid transparent;
        }

        .btn:hover {
            transform: translateY(-1px);
            background: var(--bg-hover);
        }

        .btn-primary {
            background: var(--accent-blue);
            color: #0d1117;
        }
        .btn-primary:hover {
            background: #79c0ff;
        }

        .btn-success {
            background: var(--accent-green);
            color: #0d1117;
        }
        .btn-success:hover {
            background: #56d36e;
        }

        .btn-danger {
            background: var(--accent-red);
            color: #0d1117;
        }
        .btn-danger:hover {
            background: #ff6b6b;
        }

        .btn-outline {
            background: transparent;
            border-color: var(--border-color);
        }
        .btn-outline:hover {
            border-color: var(--accent-blue);
            background: var(--bg-tertiary);
        }

        .btn-sm {
            padding: 6px 14px;
            font-size: 12px;
        }
        .btn-lg {
            padding: 14px 28px;
            font-size: 16px;
        }

        .btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none !important;
        }

        /* ===== Path Bar ===== */
        .path-bar {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 14px 20px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            margin-bottom: 24px;
        }

        .path-bar .location {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            min-width: 200px;
            color: var(--text-secondary);
            font-size: 13px;
            overflow: hidden;
        }

        .path-bar .location .path-text {
            color: var(--text-primary);
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .path-bar .breadcrumb {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
            font-size: 13px;
        }

        .path-bar .breadcrumb a {
            color: var(--accent-blue);
            text-decoration: none;
            padding: 4px 10px;
            border-radius: 4px;
            transition: var(--transition);
        }

        .path-bar .breadcrumb a:hover {
            background: var(--bg-tertiary);
        }

        .path-bar .breadcrumb .sep {
            color: var(--text-muted);
        }

        /* ===== Stats ===== */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }

        .stat-card {
            padding: 16px 20px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            text-align: center;
        }

        .stat-card .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .stat-card .stat-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ===== Card ===== */
        .card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 24px;
            margin-bottom: 24px;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 16px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ===== Upload Area ===== */
        .upload-area {
            border: 2px dashed var(--border-color);
            border-radius: var(--radius);
            padding: 40px;
            text-align: center;
            transition: var(--transition);
            cursor: pointer;
            background: var(--bg-tertiary);
        }

        .upload-area:hover {
            border-color: var(--accent-blue);
            background: var(--bg-hover);
        }

        .upload-area .upload-icon {
            font-size: 48px;
            margin-bottom: 12px;
        }

        .upload-area .upload-text {
            font-size: 16px;
            color: var(--text-secondary);
        }

        .upload-area input[type="file"] {
            display: none;
        }

        /* ===== Dropdown ===== */
        .folder-dropdown {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            cursor: pointer;
            transition: var(--transition);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%238b949e' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
        }

        .folder-dropdown:hover {
            border-color: var(--accent-blue);
        }

        .folder-dropdown:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.2);
        }

        /* ===== File List ===== */
        .file-list {
            list-style: none;
            padding: 0;
        }

        .file-list .file-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: var(--bg-tertiary);
            border: 1px solid transparent;
            border-radius: var(--radius-sm);
            margin-bottom: 6px;
            transition: var(--transition);
        }

        .file-list .file-item:hover {
            background: var(--bg-hover);
            border-color: var(--border-color);
        }

        .file-list .file-item .file-icon {
            font-size: 24px;
            flex-shrink: 0;
        }

        .file-list .file-item .file-info {
            flex: 1;
            min-width: 120px;
        }

        .file-list .file-item .file-info .file-name {
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .file-list .file-item .file-info .file-name:hover {
            color: var(--accent-blue);
        }

        .file-list .file-item .file-info .file-meta {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .file-list .file-item .file-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            align-items: center;
        }

        .file-list .file-item .file-actions .rename-form {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .file-list .file-item .file-actions .rename-form input {
            padding: 6px 12px;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            color: var(--text-primary);
            font-size: 13px;
            width: 140px;
            font-family: 'Inter', sans-serif;
        }

        .file-list .file-item .file-actions .rename-form input:focus {
            outline: none;
            border-color: var(--accent-blue);
        }

        .file-list .file-item .file-actions .action-link {
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            color: var(--text-secondary);
            background: transparent;
            border: 1px solid transparent;
        }

        .file-list .file-item .file-actions .action-link:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
        }

        .file-list .file-item .file-actions .action-link.edit {
            color: var(--accent-blue);
        }
        .file-list .file-item .file-actions .action-link.edit:hover {
            background: rgba(88, 166, 255, 0.15);
        }

        .file-list .file-item .file-actions .action-link.delete {
            color: var(--accent-red);
        }
        .file-list .file-item .file-actions .action-link.delete:hover {
            background: rgba(248, 81, 73, 0.15);
        }

        /* ===== Edit Area ===== */
        .edit-area textarea {
            width: 100%;
            min-height: 350px;
            padding: 16px;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.8;
            resize: vertical;
        }

        .edit-area textarea:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.15);
        }

        .edit-area .edit-actions {
            display: flex;
            gap: 12px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        /* ===== Empty State ===== */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-secondary);
        }

        .empty-state .empty-icon {
            font-size: 48px;
            margin-bottom: 12px;
        }

        /* ===== Footer ===== */
        .app-footer {
            text-align: center;
            padding: 20px;
            color: var(--text-muted);
            font-size: 13px;
            border-top: 1px solid var(--border-color);
            margin-top: 24px;
        }

        /* ===== Notification ===== */
        .notification {
            position: fixed;
            top: 24px;
            right: 24px;
            padding: 16px 24px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow);
            z-index: 10000;
            max-width: 420px;
            transform: translateX(120%);
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            backdrop-filter: blur(12px);
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification .notif-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .notification .notif-icon {
            font-size: 24px;
        }

        .notification .notif-message {
            flex: 1;
            font-size: 14px;
            font-weight: 500;
        }

        .notification .notif-close {
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 18px;
            padding: 0 4px;
            transition: var(--transition);
        }

        .notification .notif-close:hover {
            color: var(--text-primary);
        }

        .notification .notif-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: var(--bg-tertiary);
            border-radius: 0 0 var(--radius-sm) var(--radius-sm);
            overflow: hidden;
        }

        .notification .notif-progress .bar {
            height: 100%;
            width: 100%;
            background: linear-gradient(90deg, var(--accent-blue), var(--accent-purple));
            transition: width 0.1s linear;
        }

        .notification.success { border-left: 4px solid var(--accent-green); }
        .notification.error { border-left: 4px solid var(--accent-red); }
        .notification.info { border-left: 4px solid var(--accent-blue); }

        /* ===== Modal ===== */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(8px);
            z-index: 10001;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 32px;
            max-width: 440px;
            width: 100%;
            box-shadow: var(--shadow);
            animation: modalIn 0.3s ease;
        }

        @keyframes modalIn {
            from {
                transform: scale(0.95) translateY(-20px);
                opacity: 0;
            }
            to {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }

        .modal-box h3 {
            font-size: 20px;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .modal-box p {
            color: var(--text-secondary);
            font-size: 15px;
            margin-bottom: 24px;
        }

        .modal-box .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        /* ===== Responsive ===== */
        @media (max-width: 768px) {
            body {
                padding: 12px;
            }
            .app-header {
                padding: 16px 20px;
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }
            .app-header .header-actions {
                justify-content: center;
            }
            .path-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .file-list .file-item {
                flex-wrap: wrap;
            }
            .file-list .file-item .file-actions {
                width: 100%;
                justify-content: flex-start;
            }
            .file-list .file-item .file-actions .rename-form {
                flex-wrap: wrap;
            }
            .file-list .file-item .file-actions .rename-form input {
                width: 100%;
            }
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
            .notification {
                top: 12px;
                right: 12px;
                left: 12px;
                max-width: none;
            }
        }

        @media (max-width: 480px) {
            .stats-row {
                grid-template-columns: 1fr;
            }
            .app-header .logo {
                font-size: 18px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<div class="app-container">

    <!-- ===== HEADER ===== -->
    <header class="app-header">
        <div class="logo">
            <span>📁</span> File<span>Manager</span>
        </div>
        <div class="header-actions">
            <a href="?dir=<?=urlencode($parent_dir)?>" class="btn btn-outline btn-sm" id="backBtn">
                ⬅ Back
            </a>
            <a href="?dir=<?=urlencode(getcwd())?>" class="btn btn-outline btn-sm">
                🏠 Home
            </a>
            <a href="?dir=<?=urlencode($parent_dir)?>" class="btn btn-outline btn-sm">
                📂 Parent
            </a>
            <button class="btn btn-primary btn-sm" onclick="document.getElementById('fileInput').click()">
                📤 Upload
            </button>
        </div>
    </header>

    <!-- ===== PATH BAR ===== -->
    <div class="path-bar">
        <div class="location">
            📍 <span class="path-text"><?=htmlspecialchars($current_dir)?></span>
        </div>
        <div class="breadcrumb">
            <?php
            $parts = explode('/', str_replace('\\', '/', $current_dir));
            $build = '';
            $first = true;
            foreach ($parts as $part) {
                if (empty($part)) continue;
                $build .= '/' . $part;
                if ($first) {
                    echo '<a href="?dir=' . urlencode($build) . '">' . htmlspecialchars($part) . '</a>';
                    $first = false;
                } else {
                    echo '<span class="sep">›</span>';
                    echo '<a href="?dir=' . urlencode($build) . '">' . htmlspecialchars($part) . '</a>';
                }
            }
            ?>
        </div>
    </div>

    <!-- ===== STATS ===== -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-value"><?=count($folders)?></div>
            <div class="stat-label">📂 Folders</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?=count($files)?></div>
            <div class="stat-label">📄 Files</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">
                <?php
                $total = 0;
                foreach ($files as $f) {
                    $total += filesize($current_dir . '/' . $f);
                }
                echo format_size($total);
                ?>
            </div>
            <div class="stat-label">💾 Total Size</div>
        </div>
    </div>

    <!-- ===== UPLOAD AREA ===== -->
    <div class="card">
        <div class="upload-area" onclick="document.getElementById('fileInput').click()">
            <div class="upload-icon">📤</div>
            <div class="upload-text">
                <strong>Drop files here</strong> or click to browse
            </div>
            <div style="font-size:13px; color:var(--text-muted); margin-top:6px;">
                Max file size: <?=ini_get('upload_max_filesize')?>
            </div>
        </div>
        <form method="post" enctype="multipart/form-data" style="display:none;">
            <input type="file" name="file" id="fileInput" onchange="this.form.submit()">
        </form>
    </div>

    <!-- ===== EDIT AREA ===== -->
    <?php if ($edit_file && isset($_GET['edit'])): ?>
    <div class="card edit-area">
        <div class="card-header">
            <div class="card-title">✏️ Editing: <?=htmlspecialchars($edit_file)?></div>
        </div>
        <form method="post">
            <input type="hidden" name="filename" value="<?=htmlspecialchars($edit_file)?>">
            <textarea name="content"><?=htmlspecialchars($edit_content)?></textarea>
            <div class="edit-actions">
                <button type="submit" name="save_content" class="btn btn-success">💾 Save</button>
                <a href="?dir=<?=urlencode($current_dir)?>" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- ===== FOLDERS DROPDOWN ===== -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📂 Folders</div>
            <span style="font-size:13px; color:var(--text-secondary);"><?=count($folders)?> folders</span>
        </div>
        <select class="folder-dropdown" onchange="navigate(this.value)">
            <option value="..">⬆ Parent Folder</option>
            <?php foreach ($folders as $f): ?>
                <option value="<?=htmlspecialchars($f)?>">📁 <?=htmlspecialchars($f)?></option>
            <?php endforeach; ?>
            <?php if (empty($folders)): ?>
                <option value="" disabled>No folders available</option>
            <?php endif; ?>
        </select>
    </div>

    <!-- ===== FILES LIST ===== -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📄 Files</div>
            <span style="font-size:13px; color:var(--text-secondary);"><?=count($files)?> files</span>
        </div>

        <?php if (empty($files)): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <p>No files in this folder</p>
            </div>
        <?php else: ?>
            <ul class="file-list">
                <?php foreach ($files as $f):
                    $file_path = $current_dir . '/' . $f;
                    $file_size = format_size(filesize($file_path));
                    $file_icon = get_file_icon($f);
                    $is_editable = in_array(pathinfo($f, PATHINFO_EXTENSION), ['txt', 'php', 'html', 'css', 'js', 'json', 'xml', 'csv', 'md']);
                ?>
                <li class="file-item">
                    <span class="file-icon"><?=$file_icon?></span>
                    <div class="file-info">
                        <div class="file-name" onclick="openFile('<?=htmlspecialchars($f)?>')">
                            <?=htmlspecialchars($f)?>
                        </div>
                        <div class="file-meta"><?=$file_size?></div>
                    </div>
                    <div class="file-actions">
                        <?php if ($is_editable): ?>
                            <a href="?dir=<?=urlencode($current_dir)?>&edit=<?=urlencode($f)?>" class="action-link edit">✏️ Edit</a>
                        <?php endif; ?>

                        <form class="rename-form" method="post" onsubmit="event.preventDefault(); confirmRename(this);">
                            <input type="hidden" name="rename_old" value="<?=htmlspecialchars($f)?>">
                            <input type="text" name="rename_new" placeholder="New name" required>
                            <button type="submit" class="btn btn-sm btn-outline">Rename</button>
                        </form>

                        <a onclick="confirmDelete('<?=urlencode($f)?>')" class="action-link delete">🗑️</a>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- ===== FOOTER ===== -->
    <footer class="app-footer">
        File Manager Pro &bull; Built with <span style="color:var(--accent-red);">❤</span> &bull; PHP <?=phpversion()?>
    </footer>
</div>

<!-- ===== NOTIFICATION ===== -->
<div id="notification" class="notification">
    <div class="notif-content">
        <span class="notif-icon" id="notifIcon">✅</span>
        <span class="notif-message" id="notifMessage">Notification</span>
        <button class="notif-close" onclick="hideNotification()">✕</button>
    </div>
    <div class="notif-progress"><div class="bar" id="notifProgress"></div></div>
</div>

<!-- ===== MODAL ===== -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal-box">
        <h3>⚠️ Confirm Action</h3>
        <p id="confirmMessage">Are you sure?</p>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal()">Cancel</button>
            <button class="btn btn-danger" id="confirmBtn">Confirm</button>
        </div>
    </div>
</div>

<script>
// ============================================================
// NOTIFICATION
// ============================================================
let notifTimeout = null;
let notifProgress = null;

function showNotification(message, type = 'info', duration = 4000) {
    const el = document.getElementById('notification');
    const icon = document.getElementById('notifIcon');
    const msg = document.getElementById('notifMessage');
    const prog = document.getElementById('notifProgress');

    if (notifTimeout) {
        clearTimeout(notifTimeout);
        clearInterval(notifProgress);
    }

    el.className = 'notification ' + type;
    icon.textContent = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
    msg.textContent = message;
    el.classList.add('show');

    let w = 100;
    prog.style.width = '100%';
    notifProgress = setInterval(() => {
        w -= 100 / (duration / 100);
        if (w <= 0) {
            clearInterval(notifProgress);
            prog.style.width = '0%';
        } else {
            prog.style.width = w + '%';
        }
    }, 100);

    notifTimeout = setTimeout(hideNotification, duration);
}

function hideNotification() {
    document.getElementById('notification').classList.remove('show');
    if (notifTimeout) {
        clearTimeout(notifTimeout);
        clearInterval(notifProgress);
    }
}

// ============================================================
// MODAL
// ============================================================
let confirmCallback = null;
let confirmData = null;

function showModal(message, callback, data = null) {
    document.getElementById('confirmMessage').textContent = message;
    document.getElementById('confirmModal').classList.add('active');
    confirmCallback = callback;
    confirmData = data;
}

function closeModal() {
    document.getElementById('confirmModal').classList.remove('active');
    confirmCallback = null;
    confirmData = null;
}

function confirmAction() {
    if (confirmCallback) confirmCallback(confirmData);
    closeModal();
}

// ============================================================
// CONFIRMATION HELPERS
// ============================================================
function confirmDelete(filename) {
    showModal('Delete this file?', (data) => {
        window.location.href = '?dir=<?=urlencode($current_dir)?>&delete=' + data;
    }, filename);
}

function confirmRename(form) {
    const old = form.querySelector('input[name="rename_old"]').value;
    const neu = form.querySelector('input[name="rename_new"]').value.trim();
    if (!neu) {
        showNotification('Please enter a new name', 'error');
        return;
    }
    showModal('Rename "' + old + '" to "' + neu + '"?', () => form.submit());
}

// ============================================================
// NAVIGATION
// ============================================================
function navigate(folder) {
    if (folder === '..') {
        window.location.href = '?dir=<?=urlencode($current_dir)?>/..';
    } else if (folder) {
        window.location.href = '?dir=<?=urlencode($current_dir)?>/' + encodeURIComponent(folder);
    }
}

function openFile(file) {
    window.location.href = '?dir=<?=urlencode($current_dir)?>&open=' + encodeURIComponent(file);
}

function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('URL copied!', 'success');
        }).catch(() => fallbackCopy(text));
    } else {
        fallbackCopy(text);
    }
}

function fallbackCopy(text) {
    const ta = document.createElement('textarea');
    ta.value = text;
    document.body.appendChild(ta);
    ta.select();
    try {
        document.execCommand('copy');
        showNotification('URL copied!', 'success');
    } catch {
        showNotification('Copy manually', 'error');
    }
    document.body.removeChild(ta);
}

// ============================================================
// HISTORY (Back button)
// ============================================================
let navHistory = JSON.parse(sessionStorage.getItem('navHistory') || '[]');
const currentDir = '<?=addslashes($current_dir)?>';

if (!navHistory.length || navHistory[navHistory.length - 1] !== currentDir) {
    navHistory.push(currentDir);
    if (navHistory.length > 50) navHistory.shift();
    sessionStorage.setItem('navHistory', JSON.stringify(navHistory));
}

document.getElementById('backBtn').addEventListener('click', function(e) {
    e.preventDefault();
    if (navHistory.length > 1) {
        navHistory.pop();
        const prev = navHistory[navHistory.length - 1];
        sessionStorage.setItem('navHistory', JSON.stringify(navHistory));
        window.location.href = '?dir=' + encodeURIComponent(prev);
    } else {
        showNotification('No previous folder', 'info');
    }
});

// ============================================================
// AUTO SHOW PHP MESSAGES
// ============================================================
<?php if ($msg): ?>
document.addEventListener('DOMContentLoaded', () => {
    showNotification('<?=addslashes($msg)?>', '<?=$msg_type ?: 'info'?>');
});
<?php endif; ?>

// ============================================================
// EVENT LISTENERS
// ============================================================
document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeModal(); hideNotification(); }
    if (e.key === 'Enter' && document.getElementById('confirmModal').classList.contains('active')) {
        confirmAction();
    }
});

document.getElementById('confirmBtn').addEventListener('click', confirmAction);
</script>
</body>
</html>
