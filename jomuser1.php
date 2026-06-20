<?php

$upload_dir = __DIR__ . '/uploads/';

// إنشاء المجلد مع صلاحيات مناسبة
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// محاولة تغيير المالك (قد لا تعمل إذا كنت مستخدماً عادياً)
// @chown($upload_dir, 'www-data');

// الآن استخدم هذا المسار
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploaded_file'])) {
    $target = $upload_dir . basename($_FILES['uploaded_file']['name']);
    if (move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $target)) {
        $web_url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/uploads/' . basename($_FILES['uploaded_file']['name']);
        echo "✅ تم الرفع إلى: <a href='$web_url'>$web_url</a>";
    }
}
?>
