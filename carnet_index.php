<?php
// =======================
// Configuración de bases de datos
// =======================

// Base de datos local
$db_local = [
     'host' => 'localhost',
'port' => '5432',
'dbname' => 'postgres',
'user' => 'postgres',
'pass' => '12345'
];

// Base de datos remota
$db_remota = [
   'host' => 'aws-1-us-east-2.pooler.supabase.com',
'port' => '5432',
'dbname' => 'postgres3',
'user' => 'postgres.orzsdjjmyouhhxjfnemt',
'pass' => 'Zv2sW23OhBVM5Tkz'
];


// =======================
// Conexión a base de datos local
// =======================
try {
    $dsn_local = "pgsql:host={$db_local['host']};port={$db_local['port']};dbname={$db_local['dbname']}";
    $pdo_local = new PDO($dsn_local, $db_local['user'], $db_local['password']);
    $pdo_local->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión a la base local: " . $e->getMessage());
}

// =======================
// Conexión a base de datos remota
// =======================
try {
    $dsn_remota = "pgsql:host={$db_remota['host']};port={$db_remota['port']};dbname={$db_remota['dbname']}";
    $pdo_remota = new PDO($dsn_remota, $db_remota['user'], $db_remota['password']);
    $pdo_remota->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión a la base remota: " . $e->getMessage());
}

// =======================
// Ejemplo de consulta en la base local
// =======================
$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $pdo_local->prepare("SELECT * FROM empleados WHERE id_empleado = ?");
    $stmt->execute([$id]);
    $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$empleado) {
        echo "Empleado no encontrado en la base local";
    } else {
        echo "Empleado local: " . $empleado['nombre_completo'];
    }
}

// =======================
// Ejemplo de consulta en la base remota
// =======================
if ($id > 0) {
    $stmt2 = $pdo_remota->prepare("SELECT * FROM empleados WHERE id_empleado = ?");
    $stmt2->execute([$id]);
    $empleado_remoto = $stmt2->fetch(PDO::FETCH_ASSOC);

    if (!$empleado_remoto) {
        echo "Empleado no encontrado en la base remota";
    } else {
        echo "Empleado remoto: " . $empleado_remoto['nombre_completo'];
    }
}
