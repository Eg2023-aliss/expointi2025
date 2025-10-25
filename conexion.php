<?php
// conexion.php — configuración y funciones de conexión a la base de datos

// ---------- CONFIGURACIÓN BASES DE DATOS ----------
$db_config_cloud = [
  'host' => 'aws-1-us-east-2.pooler.supabase.com',
  'port' => '5432',
  'dbname' => 'postgres3',
  'user' => 'postgres.orzsdjjmyouhhxjfnemt',
  'pass' => 'Zv2sW23OhBVM5Tkz'
];

$db_config_local = [
  'host' => 'localhost',
  'port' => '5432',
  'dbname' => 'postgres',
  'user' => 'postgres 2',
  'pass' => '12345'
];

// 🔸 Por defecto, intenta la nube
$db_config = $db_config_cloud;

// ---------- FUNCIONES ----------
function getPDO($cfg = null) {
  global $db_config;
  $use = $cfg ?? $db_config;
  $dsn = "pgsql:host={$use['host']};port={$use['port']};dbname={$use['dbname']}";
  try {
    $pdo = new PDO($dsn, $use['user'], $use['pass'], [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    return $pdo;
  } catch (PDOException $e) {
    error_log("❌ Error al conectar con {$use['host']}: " . $e->getMessage());
    throw $e;
  }
}

// 🔁 Ejecutar en ambas bases (local + nube)
function runBoth($callback) {
  global $db_config_local, $db_config_cloud;
  $results = [];
  try {
    $pdoL = getPDO($db_config_local);
    $pdoC = getPDO($db_config_cloud);
    $results[] = $callback($pdoL);
    $results[] = $callback($pdoC);
  } catch (Exception $e) {
    error_log("⚠️ Error runBoth: " . $e->getMessage());
  }
  return $results[0] ?? null;
}
?>
