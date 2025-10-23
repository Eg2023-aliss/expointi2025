<?php
// ---------- CONFIGURACIÓN BASES DE DATOS ----------
$db_config_cloud = [
    'host' => 'aws-1-us-east-2.pooler.supabase.com',
    'port' => '6543',
    'dbname' => 'postgres3',
    'user' => 'postgres.orzsdjjmyouhhxjfnemt',
    'pass' => 'Zv2sW23OhBVM5Tkz'
];

$db_config_local = [
    'host' => '127.0.0.1',  // IPv4 explícita para Docker
    'port' => '5432',
    'dbname' => 'postgres',
    'user' => 'postgres',
    'pass' => '12345'
];

// ---------- FUNCIONES ----------

// Función para verificar si el puerto local está activo
function isLocalPostgresActive($host = '127.0.0.1', $port = 5432, $timeout = 1) {
    $connection = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if ($connection) {
        fclose($connection);
        return true;
    }
    return false;
}

// Función para obtener PDO con detección automática
function getPDOAuto() {
    global $db_config_cloud, $db_config_local;

    // Detectar si PostgreSQL local está activo
    if (isLocalPostgresActive($db_config_local['host'], $db_config_local['port'])) {
        $primary = $db_config_local;
        $secondary = $db_config_cloud;
        error_log("ℹ️ PostgreSQL local detectado, intentando conexión local primero.");
    } else {
        $primary = $db_config_cloud;
        $secondary = $db_config_local;
        error_log("ℹ️ PostgreSQL local no disponible, usando conexión a la nube primero.");
    }

    // Intentar conexión primaria
    try {
        $dsn = "pgsql:host={$primary['host']};port={$primary['port']};dbname={$primary['dbname']}";
        $pdo = new PDO($dsn, $primary['user'], $primary['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        error_log("✅ Conexión exitosa a {$primary['host']}:{$primary['port']}");
        return $pdo;
    } catch (Exception $e) {
        error_log("⚠️ Error conexión primaria ({$primary['host']}): " . $e->getMessage());

        // Intentar conexión secundaria
        try {
            $dsn2 = "pgsql:host={$secondary['host']};port={$secondary['port']};dbname={$secondary['dbname']}";
            $pdo2 = new PDO($dsn2, $secondary['user'], $secondary['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            error_log("✅ Conexión secundaria usada: {$secondary['host']}:{$secondary['port']}");
            return $pdo2;
        } catch (Exception $ex) {
            error_log("❌ Falla también la conexión secundaria: " . $ex->getMessage());
            die("Error: no se pudo conectar ni a la nube ni al servidor local.");
        }
    }
}

// ---------- USO ----------
$pdo = getPDOAuto();
echo "Conexión establecida con éxito!";
?>
