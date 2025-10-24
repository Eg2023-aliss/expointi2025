<?php
session_start();
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require('fpdf/fpdf.php');

// Configuración de la nube (Supabase)
$db_config_cloud = [
    'host' => 'aws-1-us-east-2.pooler.supabase.com',
    'port' => '5432',
    'dbname' => 'postgres3',
    'user' => 'postgres.orzsdjjmyouhhxjfnemt',
    'pass' => 'Zv2sW23OhBVM5Tkz',
    'sslmode' => 'require' // SSL obligatorio para Supabase
];

// Función para verificar conexión TCP
function isHostReachable($host, $port, $timeout = 2) {
    $fp = @fsockopen($host, $port, $errCode, $errStr, $timeout);
    if ($fp) {
        fclose($fp);
        return true;
    }
    return false;
}

// Función para obtener PDO usando solo la nube
function getPDO() {
    global $db_config_cloud;

    if (!isHostReachable($db_config_cloud['host'], $db_config_cloud['port'])) {
        die("❌ Error: No se puede conectar a la base de datos en la nube.");
    }

    try {
    $pdo = new PDO(
        "pgsql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['dbname']}",
        $db_config['user'],
        $db_config['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexión exitosa a la base de datos en la nube!";
} catch (PDOException $e) {
    echo "❌ Error: No se puede conectar a la base de datos en la nube. " . $e->getMessage();
}

}

// Uso
$pdo = getPDO();
