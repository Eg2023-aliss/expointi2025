<?php
$hosts = ['127.0.0.1', '192.168.1.24', 'aws-1-us-east-2.pooler.supabase.com'];
$port = 5432;

foreach ($hosts as $host) {
    $fp = @fsockopen($host, $port, $errCode, $errStr, 2);
    if ($fp) {
        echo "✅ PHP puede conectarse a $host:$port<br>";
        fclose($fp);
    } else {
        echo "❌ PHP NO puede conectarse a $host:$port. Error: $errStr ($errCode)<br>";
    }
}
