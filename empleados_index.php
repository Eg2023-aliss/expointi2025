<?php
session_start();
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require('fpdf/fpdf.php');

// 🔐 Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// =============================================================
// CONFIGURACIÓN DE BASES DE DATOS
// =============================================================
$isRender = getenv('RENDER') === 'true';

// Base local
$db_local = [
    'host' => 'localhost',
    'port' => '5432',
    'dbname' => 'postgres',
    'user' => 'postgres',
    'pass' => '12345',
    'sslmode' => 'disable'
];

// Base remota (Supabase / Render PostgreSQL)
$db_remota = [
    'host' => 'aws-1-us-east-2.pooler.supabase.com',
    'port' => '5432',
    'dbname' => 'postgres3',
    'user' => 'postgres.orzsdjjmyouhhxjfnemt',
    'pass' => 'Zv2sW23OhBVM5Tkz',
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
        error_log("Error de conexión a {$cfg['host']}: ".$e->getMessage());
        return null;
    }
}

// =============================================================
// ESTABLECER CONEXIONES SEGÚN EL ENTORNO
// =============================================================
$pdo_local = $pdo_remota = null;

if ($isRender) {
    $pdo_remota = getPDO($db_remota);
} else {
    $pdo_local = getPDO($db_local);
    $pdo_remota = getPDO($db_remota);
}

// =============================================================
// OBTENER EMPLEADOS DESDE LA BD DISPONIBLE
// =============================================================
$empleados = [];

if ($pdo_local) {
    try {
        $stmt = $pdo_local->query("SELECT * FROM empleados ORDER BY nombre_completo");
        $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error al consultar empleados local: ".$e->getMessage());
    }
} elseif ($pdo_remota) {
    try {
        $stmt = $pdo_remota->query("SELECT * FROM empleados ORDER BY nombre_completo");
        $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error al consultar empleados remota: ".$e->getMessage());
    }
} else {
    die("❌ No hay conexión a ninguna base de datos.");
}

// =============================================================
// FUNCIONES AUXILIARES
// =============================================================
function getAreaByPuesto($puesto) {
    $puesto_lower = strtolower($puesto);
    if (stripos($puesto_lower, 'director') !== false || stripos($puesto_lower, 'general') !== false) return 'Dirección y Administración General';
    if (stripos($puesto_lower, 'finanzas') !== false || stripos($puesto_lower, 'conta') !== false) return 'Finanzas y Administración';
    if (stripos($puesto_lower, 'rrhh') !== false || stripos($puesto_lower, 'recursos') !== false) return 'Recursos Humanos';
    if (stripos($puesto_lower, 'ventas') !== false || stripos($puesto_lower, 'comercial') !== false) return 'Ventas y Atención Comercial';
    if (stripos($puesto_lower, 'redes') !== false || stripos($puesto_lower, 'infraestructura') !== false) return 'Infraestructura y Redes';
    return 'Otros';
}

// Agrupar empleados por área
$areas = [
    'Dirección y Administración General',
    'Finanzas y Administración',
    'Recursos Humanos',
    'Ventas y Atención Comercial',
    'Infraestructura y Redes',
    'Otros'
];

$empleados_por_area = [];
foreach ($areas as $a) $empleados_por_area[$a] = [];
foreach ($empleados as $row) {
    $area = getAreaByPuesto($row['puesto']);
    $empleados_por_area[$area][] = [
        'id' => $row['id'],
        'nombre' => $row['nombre_completo'],
        'correo' => $row['correo'],
        'puesto' => $row['puesto'],
        'fecha' => $row['fecha_contratacion']
    ];
}

// =============================================================
// TODO EL RESTO DEL CÓDIGO: PDF de carnet, curriculum, asistencia...
// Puedes reutilizar exactamente lo que ya tenías desde aquí
// =============================================================

?>
