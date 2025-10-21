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
// Función para crear conexión PDO
// =======================
function connectPDO($dbConfig) {
    try {
        $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
        // Añadir sslmode si existe
        if (!empty($dbConfig['sslmode'])) {
            $dsn .= ";sslmode={$dbConfig['sslmode']}";
        }
        $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("❌ Error de conexión a {$dbConfig['host']}: " . $e->getMessage());
    }
}

// =======================
// Detectar entorno
// =======================
$isRender = getenv('RENDER') === 'true';

if ($isRender) {
    // En Render solo usamos la remota (Supabase)
    $pdo_local = null;
    $pdo_remota = connectPDO($db_remota);
    $pdo = $pdo_remota; // conexión principal
} else {
    // En local puedes usar ambas
    $pdo_local = connectPDO($db_local);
    $pdo_remota = connectPDO($db_remota);
    $pdo = $pdo_local; // por defecto usar local
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
$stmt = $pdo->prepare("SELECT * FROM empleados WHERE id_empleado = ?");
$stmt->execute([$id]);
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

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
// Ejemplo de consulta remota desde local (opcional)
// =======================
if (!$isRender && $pdo_remota !== null && $pdo_remota !== $pdo) {
    $stmt2 = $pdo_remota->prepare("SELECT * FROM empleados WHERE id_empleado = ?");
    $stmt2->execute([$id]);
    $empleado_remoto = $stmt2->fetch(PDO::FETCH_ASSOC);

    if ($empleado_remoto) {
        echo "<p>Empleado en base remota: " . htmlspecialchars($empleado_remoto['nombre_completo']) . "</p>";
    }
}
