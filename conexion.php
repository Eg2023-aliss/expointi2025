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
    'pass' => 'Zv2sW23OhBVM5Tkz',
    'sslmode' => 'require' // SSL para nube
];

// Configuración local
$db_config_local = [
    'host' => '', // Se detectará automáticamente
    'port' => '5432',
    'dbname' => 'postgres',
    'user' => 'postgres',
    'pass' => '12345',
    'sslmode' => 'disable' // Local normalmente no usa SSL
];

// ---------- FUNCIÓN PARA DETECTAR HOST LOCAL ----------

function detectLocalPostgresHost($ports = [5432]) {
    $localHosts = ['127.0.0.1', 'localhost'];
    foreach ($localHosts as $host) {
        foreach ($ports as $port) {
            $fp = @fsockopen($host, $port, $errCode, $errStr, 1);
            if ($fp) { fclose($fp); return $host; }
        }
    }

    // Prueba IP de red local (ajustar según tu red)
    $networkHost = '192.168.1.24';
    foreach ($ports as $port) {
        $fp = @fsockopen($networkHost, $port, $errCode, $errStr, 2);
        if ($fp) { fclose($fp); return $networkHost; }
    }

    return null; // No detectado
}

// Detectar host local automáticamente
$db_config_local['host'] = detectLocalPostgresHost();

// ---------- FUNCIÓN PARA OBTENER PDO CON FALLBACK Y SSL ----------

function getPDO() {
    global $db_config_local, $db_config_cloud;

    $attempts = [];

    if (!empty($db_config_local['host'])) {
        $attempts[] = $db_config_local;
    } else {
        error_log("⚠️ No se detectó host local PostgreSQL, se saltará al intento nube");
    }

    $attempts[] = $db_config_cloud;

    foreach ($attempts as $cfg) {
        // Verificar TCP
        $fp = @fsockopen($cfg['host'], $cfg['port'], $errCode, $errStr, 2);
        if (!$fp) {
            error_log("⚠️ Host {$cfg['host']}:{$cfg['port']} no accesible: $errStr ($errCode)");
            continue;
        }
        fclose($fp);

        // Intento de conexión PDO con SSLmode
        try {
            $dsn = "pgsql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};sslmode={$cfg['sslmode']}";
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
