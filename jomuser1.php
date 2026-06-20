<?php
// =============================================
// ملف: upload_and_extract.php
// رفع ملف ZIP وفك ضغطه تلقائياً
// =============================================

// إعدادات
$upload_dir = __DIR__ . '/uploads/';
$extract_dir = __DIR__ . '/extracted/';

// إنشاء المجلدات إذا لم تكن موجودة
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}
if (!is_dir($extract_dir)) {
    mkdir($extract_dir, 0755, true);
}

$message = '';
$message_type = '';

// معالجة الرفع والفك
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['zip_file'])) {
    $file = $_FILES['zip_file'];
    $filename = basename($file['name']);
    
    // التحقق من أن الملف هو ZIP
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if ($ext !== 'zip') {
        $message = "❌ الملف ليس بصيغة ZIP";
        $message_type = 'error';
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $message = "❌ خطأ في الرفع: " . $file['error'];
        $message_type = 'error';
    } else {
        // 1. رفع الملف
        $zip_path = $upload_dir . $filename;
        if (move_uploaded_file($file['tmp_name'], $zip_path)) {
            
            // 2. فك ضغط الملف
            $zip = new ZipArchive();
            if ($zip->open($zip_path) === TRUE) {
                // استخراج الملفات
                if ($zip->extractTo($extract_dir)) {
                    $message = "✅ تم رفع وفك ضغط الملف بنجاح!";
                    $message_type = 'success';
                    
                    // عرض الملفات المستخرجة
                    $extracted_files = [];
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $extracted_files[] = $zip->getNameIndex($i);
                    }
                    $zip->close();
                    
                    // حذف ملف ZIP بعد الفك (اختياري)
                    // unlink($zip_path);
                    
                } else {
                    $message = "❌ فشل فك ضغط الملف";
                    $message_type = 'error';
                }
            } else {
                $message = "❌ الملف ليس ZIP صالح";
                $message_type = 'error';
            }
        } else {
            $message = "❌ فشل رفع الملف";
            $message_type = 'error';
        }
    }
}

// عرض الملفات المستخرجة
$extracted_files_list = [];
if (is_dir($extract_dir)) {
    $extracted_files_list = array_diff(scandir($extract_dir), ['.', '..']);
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رفع وفك ضغط ZIP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h1 { color: #333; margin-bottom: 20px; }
        .message {
            padding: 12px;
            margin: 15px 0;
            border-radius: 5px;
            border: 1px solid;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #2196F3;
        }
        input[type="file"] {
            display: block;
            margin: 20px 0;
            padding: 10px;
            width: 100%;
            border: 2px dashed #ccc;
            border-radius: 5px;
            background: #fafafa;
        }
        button {
            background: #28a745;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover { background: #218838; }
        .file-list {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .file-list ul {
            list-style: none;
            padding: 0;
        }
        .file-list li {
            padding: 8px 12px;
            margin: 4px 0;
            background: white;
            border-radius: 4px;
            border: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .file-list a {
            color: #007bff;
            text-decoration: none;
        }
        .file-list a:hover { text-decoration: underline; }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📦 رفع وفك ضغط ZIP</h1>
        
        <?php if ($message): ?>
            <div class="message <?= $message_type ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="info">
            <strong>⚠️ معلومات:</strong><br>
            📁 مجلد الرفع: <code><?= $upload_dir ?></code><br>
            📂 مجلد الفك: <code><?= $extract_dir ?></code>
        </div>

        <form method="post" enctype="multipart/form-data">
            <input type="file" name="zip_file" accept=".zip" required>
            <button type="submit">🚀 رفع وفك الضغط</button>
        </form>

        <?php if (!empty($extracted_files_list)): ?>
            <div class="file-list">
                <h3>📄 الملفات المستخرجة:</h3>
                <ul>
                    <?php foreach ($extracted_files_list as $file): ?>
                        <li>
                            <span><?= htmlspecialchars($file) ?></span>
                            <a href="/extracted/<?= urlencode($file) ?>" target="_blank">🔗 فتح</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
