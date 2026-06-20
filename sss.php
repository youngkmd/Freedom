<?php
// =============================================
// رفع الملفات مع واجهة مستخدم
// =============================================

$temp_dir = '/tmp/';
$target_dir = '/';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploaded_file'])) {
    $file = $_FILES['uploaded_file'];
    $filename = basename($file['name']);
    
    // 1. رفع الملف إلى المجلد المؤقت
    $temp_path = $temp_dir . $filename;
    if (move_uploaded_file($file['tmp_name'], $temp_path)) {
        
        // 2. محاولة نسخ الملف إلى المجلد الرئيسي
        $target_path = $target_dir . $filename;
        if (copy($temp_path, $target_path)) {
            $message = "✅ تم الرفع بنجاح إلى: " . $target_path;
            $message_type = 'success';
            unlink($temp_path); // حذف الملف المؤقت
        } else {
            $message = "❌ فشل النسخ إلى المجلد الرئيسي. حاول استخدام الأمر التالي عبر SSH:<br>
                        <code>sudo cp $temp_path $target_path</code>";
            $message_type = 'error';
        }
    } else {
        $message = "❌ فشل الرفع إلى المجلد المؤقت";
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رفع الملفات - تجاوز 403</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
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
        h1 { color: #333; }
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
        <h1>📤 رفع ملف إلى المجلد الرئيسي (/)</h1>
        
        <?php if ($message): ?>
            <div class="message <?= $message_type ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="info">
            <strong>⚠️ تحذير:</strong> هذا السكربت يحاول رفع الملفات إلى المجلد الرئيسي.<br>
            <strong>المجلد المؤقت:</strong> <code><?= $temp_dir ?></code><br>
            <strong>المجلد المستهدف:</strong> <code><?= $target_dir ?></code>
        </div>

        <form method="post" enctype="multipart/form-data">
            <input type="file" name="uploaded_file" required>
            <button type="submit">🚀 رفع الملف</button>
        </form>

        <hr>
        <p style="color: #999; font-size: 13px; margin-top: 20px;">
            <strong>ملاحظة:</strong> قد تحتاج إلى صلاحيات إضافية للكتابة في المجلد الرئيسي.
        </p>
    </div>
</body>
</html>
