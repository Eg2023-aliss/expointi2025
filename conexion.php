<?php
$host = '192.168.1.24';
$port = 5432;
$waitTimeoutInSeconds = 5;

if ($fp = @fsockopen($host, $port, $errCode, $errStr, $waitTimeoutInSeconds)) {
    echo "Conexión TCP exitosa a $host:$port";
    fclose($fp);
} else {
    echo "No se puede conectar a $host:$port. Error: $errStr ($errCode)";
}
?>
