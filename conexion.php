<?php
session_start();
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require('fpdf/fpdf.php');

// ---------- CONFIGURACIÓN BASES DE DATOS ----------

// Configuración nube
$db_config_cloud = [
    'host' => 'aws-1-us-east-2.pooler.supabase.com',
    'port' => '6543',
    'dbname' => 'postgres3',
    'user' => 'postgres.orzsdjjmyouhhxjfnemt',
    'pass' => 'Zv2sW23OhBVM5Tkz'
];

// Configuración local
$db_config_local = [
    'host' => '127.0.0.1', // Mejor usar IP en vez de localhost
    'port' => '5432',
    'dbname' => 'postgres',
    'user' => 'postgres',
    'pass' => '12345'
];

// ---------- FUNCIÓN PARA VERIFICAR CONEXIÓN TCP ----------
function isHostReachable($host, $port, $timeout = 2) {
    $fp = @fsockopen($host, $port, $errCode, $errStr, $timeout);
    if ($fp) {
        fclose($fp);
        return true;
    }
    return false;
}

// ---------- FUNCIÓN PARA OBTENER PDO CON FALLBACK ----------
function getPDO() {
    global $db_config_local, $db_config_cloud;

    // Lista de intentos: primero local, luego nube
    $attempts = [$db_config_local, $db_config_cloud];

    foreach ($attempts as $cfg) {
        if (!isHostReachable($cfg['host'], $cfg['port'], 3)) {
            error_log("⚠️ Host {$cfg['host']}:{$cfg['port']} no accesible, saltando...");
            continue;
        }

        try {
            $dsn = "pgsql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']}";
            $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            error_log("✅ Conexión exitosa a {$cfg['host']}:{$cfg['port']}");
            echo "Conexión establecida con éxito a {$cfg['host']}:{$cfg['port']}<br>";
            return $pdo;
        } catch (PDOException $e) {
            error_log("⚠️ Error PDO en {$cfg['host']}: {$e->getMessage()}");
        }
    }

    die("❌ Error: no se pudo conectar ni a la base local ni a la nube.");
}

// ---------- USO ----------
$pdo = getPDO();
?>
