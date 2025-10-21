<?php
// =======================
// Configuración de bases de datos
// =======================

// Base de datos local (solo para desarrollo local, Render no puede usar localhost)
$db_local = [
    'host' => 'localhost',
    'port' => '5432',
    'dbname' => 'postgres',
    'user' => 'postgres',
    'pass' => '12345'
];

// Base de datos remota (usada en Render)
$db_remota = [
    'host' => 'aws-1-us-east-2.pooler.supabase.com',
    'port' => '5432',
    'dbname' => 'postgres3',
    'user' => 'postgres.orzsdjjmyouhhxjfnemt',
    'pass' => 'Zv2sW23OhBVM5Tkz'
];

// =======================
// Función para crear conexión PDO
// =======================
function connectPDO($dbConfig) {
    try {
        $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
        $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("❌ Error de conexión a {$dbConfig['host']}: " . $e->getMessage());
    }
}

// =======================
// Detectar si estamos en Render o local
// =======================
if (getenv('RENDER') === 'true') {
    // Conexión en Render → solo remota
    $pdo_local = null; // no se usa
    $pdo_remota = connectPDO($db_remota);
    $pdo = $pdo_remota; // usar por defecto
} else {
    // Conexión local → se pueden usar ambas
    $pdo_local = connectPDO($db_local);
    $pdo_remota = connectPDO($db_remota);
    $pdo = $pdo_local; // usar por defecto
}

// =======================
// Obtener ID del empleado
// =======================
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    die("ID inválido");
}

// =======================
// Consulta ejemplo en la base remota (usada por defecto en Render)
// =======================
$stmt = $pdo->prepare("SELECT * FROM empleados WHERE id_empleado = ?");
$stmt->execute([$id]);
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empleado) {
    echo "Empleado no encontrado";
} else {
    echo "<h2>Empleado: " . htmlspecialchars($empleado['nombre_completo']) . "</h2>";
    // Mostrar foto del carnet si existe
    if (!empty($empleado['imagen'])) {
        echo "<img src='{$empleado['imagen']}' alt='Foto del carnet' width='100'>";
    }
}

// =======================
// Si quieres usar también la remota desde local
// =======================
if ($pdo_local !== null && $pdo_local !== $pdo) {
    $stmt2 = $pdo_remota->prepare("SELECT * FROM empleados WHERE id_empleado = ?");
    $stmt2->execute([$id]);
    $empleado_remoto = $stmt2->fetch(PDO::FETCH_ASSOC);

    if ($empleado_remoto) {
        echo "<p>Empleado en base remota: " . htmlspecialchars($empleado_remoto['nombre_completo']) . "</p>";
    }
}
