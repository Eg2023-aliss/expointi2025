<?php
session_start();
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require('fpdf/fpdf.php');
// conexion.php — solo conexión, sin HTML ni consultas

function getPDO() {
    $configs = [
        [
            'host' => 'localhost',
            'port' => '5432',
            'dbname' => 'postgres',
            'user' => 'postgres',
            'pass' => 'tu_password_local'
        ],
        [
            'host' => 'aws-1-us-east-2.pooler.supabase.com',
            'port' => '6543',
            'dbname' => 'postgres',
            'user' => 'postgres.orzsdjjmyouhhxjfnemt',
            'pass' => 'Zv2sW23OhBVM5Tkz'
        ]
    ];

    foreach ($configs as $cfg) {
        try {
            $pdo = new PDO(
                "pgsql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']}",
                $cfg['user'],
                $cfg['pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            error_log("✅ Conectado a la base de datos: {$cfg['host']}");
            return $pdo;
        } catch (PDOException $e) {
            error_log("⚠️ Error al conectar con {$cfg['host']}: " . $e->getMessage());
            continue;
        }
    }

    die("❌ No se pudo conectar ni a la base local ni a la nube.");
}
?>
