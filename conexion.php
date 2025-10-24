<?php
$host ='localhost';
$port = 5432;
$timeout = 5;

$start = microtime(true);
if ($fp = @fsockopen($host, $port, $errCode, $errStr, $timeout)) {
    echo "✅ PHP puede conectarse a $host:$port";
    fclose($fp);
} else {
    echo "❌ PHP NO puede conectarse a $host:$port. Error: $errStr ($errCode)";
}
echo "\nTiempo: " . (microtime(true) - $start) . "s";
?>
