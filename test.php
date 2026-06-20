<?php


$temp_dir = '/tmp/';  // مجلد قابل للكتابة عادة
$target_dir = '/';    // المجلد النهائي (الرئيسي)

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploaded_file'])) {
    $file = $_FILES['uploaded_file'];
    $filename = basename($file['name']);
    
    $temp_path = $temp_dir . $filename;
    if (move_uploaded_file($file['tmp_name'], $temp_path)) {
        

        $target_path = $target_dir . $filename;
        if (copy($temp_path, $target_path)) {
            echo "✅  " . $target_path;
            unlink($temp_path);
        } else {
            echo "❌ SSH:<br>";
            echo "sudo cp $temp_path $target_path";
        }
    } else {
        echo "❌ ";
    }
}
?>
