<?php
// =============================================================
// CONFIGURACIÓN DE BASES DE DATOS
// =============================================================
$isRender = getenv('RENDER') === 'true';

// Base local (para desarrollo)
$db_local = [
    'host' => 'localhost',
    'port' => '5432',
    'dbname' => 'postgres',
    'user' => 'postgres',
    'pass' => '12345',  // tu contraseña real
    'sslmode' => 'disable'
];

// Base remota (Supabase o Render PostgreSQL)
$db_remota = [
    'host' => 'aws-1-us-east-2.pooler.supabase.com',
    'port' => '5432',
    'dbname' => 'postgres3',
    'user' => 'postgres.orzsdjjmyouhhxjfnemt',
    'pass' => 'Zv2sW23OhBVM5Tkz', // tu contraseña real
    'sslmode' => 'require'
];

// =============================================================
// FUNCIÓN PARA CONECTAR A UNA BASE DE DATOS
// =============================================================
function getPDO($cfg) {
    $dsn = "pgsql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};sslmode={$cfg['sslmode']}";
    try {
        return new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    } catch (Exception $e) {
        error_log("Error de conexión a {$cfg['host']}: " . $e->getMessage());
        return null;
    }
}

// =============================================================
// ESTABLECER CONEXIONES SEGÚN EL ENTORNO
// =============================================================
$pdo_local = null;
$pdo_remota = null;

if ($isRender) {
    // Render → solo base remota
    $pdo_remota = getPDO($db_remota);
} else {
    // Local → usar ambas
    $pdo_local = getPDO($db_local);
    $pdo_remota = getPDO($db_remota);
}

// =============================================================
// OBTENER EMPLEADOS
// =============================================================
$empleados = [];

// Intentar obtener desde local primero
if ($pdo_local) {
    try {
        $stmt = $pdo_local->query("SELECT * FROM empleados ORDER BY nombre_completo");
        $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error query local: " . $e->getMessage());
    }
}

// Si no hay empleados locales, usar remota
if (empty($empleados) && $pdo_remota) {
    try {
        $stmt = $pdo_remota->query("SELECT * FROM empleados ORDER BY nombre_completo");
        $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error query remota: " . $e->getMessage());
    }
}

// =============================================================
// MOSTRAR EMPLEADOS EN HTML
// =============================================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empleados</title>
    <style>
        table { border-collapse: collapse; width: 80%; margin: 20px auto; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Lista de Empleados</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Completo</th>
                <th>Puesto</th>
                <th>Correo</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($empleados)): ?>
                <?php foreach ($empleados as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['id']) ?></td>
                        <td><?= htmlspecialchars($e['nombre_completo']) ?></td>
                        <td><?= htmlspecialchars($e['puesto'] ?? '') ?></td>
                        <td><?= htmlspecialchars($e['correo'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align:center;">No hay empleados disponibles</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
