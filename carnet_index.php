<?php
// =======================
// Configuración de bases de datos
// =======================

// Base de datos local (solo para desarrollo en tu PC)
$db_local = [
    'host' => 'localhost',
    'port' => '5432',
    'dbname' => 'postgres',
    'user' => 'postgres',
    'pass' => '12345'
];

// Base de datos remota (Supabase)
$db_remota = [
    'host' => 'aws-1-us-east-2.pooler.supabase.com',
    'port' => '5432',
    'dbname' => 'postgres3',
    'user' => 'postgres.orzsdjjmyouhhxjfnemt',
    'pass' => 'Zv2sW23OhBVM5Tkz',
    'sslmode' => 'require'
];

// =======================
// Detectar entorno
// =======================
$isRender = getenv('RENDER') === 'true';

if ($isRender) {
    // Conexión remota con pg_connect (Supabase)
    $conn_string = "host={$db_remota['host']} port={$db_remota['port']} dbname={$db_remota['dbname']} user={$db_remota['user']} password={$db_remota['pass']} sslmode=require";
    $dbconn = pg_connect($conn_string);
    if (!$dbconn) {
        die("❌ No se pudo conectar a Supabase");
    }
} else {
    // Conexión local con pg_connect
    $conn_string_local = "host={$db_local['host']} port={$db_local['port']} dbname={$db_local['dbname']} user={$db_local['user']} password={$db_local['pass']}";
    $dbconn = pg_connect($conn_string_local);
    if (!$dbconn) {
        die("❌ No se pudo conectar a la base local");
    }
}

// =======================
// Obtener ID del empleado
// =======================
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    die("ID inválido");
}

// =======================
// Consulta principal
// =======================
$result = pg_query_params($dbconn, "SELECT * FROM empleados WHERE id_empleado = $1", [$id]);
if (!$result) {
    die("❌ Error en la consulta");
}

$empleado = pg_fetch_assoc($result);

if (!$empleado) {
    echo "Empleado no encontrado";
} else {
    echo "<h2>Empleado: " . htmlspecialchars($empleado['nombre_completo']) . "</h2>";

    // Mostrar foto del carnet si existe
    if (!empty($empleado['imagen'])) {
        echo "<img src='" . htmlspecialchars($empleado['imagen']) . "' alt='Foto del carnet' width='100'>";
    }
}

// =======================
// Cerrar conexión
// =======================
pg_close($dbconn);
