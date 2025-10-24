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
        $dsn = "pgsql:host={$db_config_cloud['host']};port={$db_config_cloud['port']};dbname={$db_config_cloud['dbname']};sslmode={$db_config_cloud['sslmode']}";
        $pdo = new PDO($dsn, $db_config_cloud['user'], $db_config_cloud['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "✅ Conexión establecida con éxito a la nube ({$db_config_cloud['host']})<br>";
        return $pdo;
    } catch (PDOException $e) {
        die("❌ Error PDO: " . $e->getMessage());
    }
}

// Uso
$pdo = getPDO();
