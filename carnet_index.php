<?php
ob_start();
require('fpdf/fpdf.php');

// =======================================
// 🔹 CONFIGURACIÓN DE CONEXIÓN DUAL
// =======================================


// ---------- CONFIGURACIÓN BASES DE DATOS ----------
$db_config_cloud = [
  'host' => 'aws-1-us-east-2.pooler.supabase.com',
'port' => '5432',
'dbname' => 'postgres3',
'user' => 'postgres.orzsdjjmyouhhxjfnemt',
'pass' => 'Zv2sW23OhBVM5Tkz'
];

$db_config_local = [
     'url' => 'jdbc:postgresql://localhost:5432/postgres',
'port' => '5432',
'dbname' => 'postgres',
'user' => 'postgres',
'pass' => '12345'
];

$db_config = $db_config_local;
// Conexión dinámica según origen
try {
    $conexion = (isset($_GET['origen']) && $_GET['origen'] === 'cloud') ? $db_cloud : $db_local;

    $dsn = "pgsql:url={$conexion['url']};port={$conexion['port']};dbname={$conexion['dbname']}";
    $pdo = new PDO($dsn, $conexion['user'], $conexion['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}

// =======================================
// 🔹 OBTENER DATOS DEL EMPLEADO
// =======================================
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("ID de empleado no válido");
}

$stmt = $pdo->prepare("SELECT nombre, puesto, identificacion, foto FROM empleados WHERE id_empleado = ?");
$stmt->execute([$id]);
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empleado) {
    die("Empleado no encontrado en la base de datos");
}

// =======================================
// 🔹 CREAR PDF
// =======================================
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',16);
        $this->Cell(0,10,utf8_decode('CARNET DE EMPLEADO'),0,1,'C');
        $this->Ln(5);
    }
}

$pdf = new PDF();
$pdf->AddPage();

$pdf->Image('logo.png', 10, 10, 30);
$pdf->Ln(25);

$pdf->SetFont('Arial','',12);
$pdf->Cell(0,10,utf8_decode("Nombre: " . $empleado['nombre']),0,1);
$pdf->Cell(0,10,utf8_decode("Puesto: " . $empleado['puesto']),0,1);
$pdf->Cell(0,10,utf8_decode("Identificación: " . $empleado['identificacion']),0,1);
$pdf->Ln(10);

// Foto del empleado
if (!empty($empleado['foto']) && file_exists('uploads/'.$empleado['foto'])) {
    $pdf->Image('uploads/'.$empleado['foto'], 80, 90, 50, 50);
} else {
    $pdf->Rect(80, 90, 50, 50);
    $pdf->Text(92, 115, 'Sin foto');
}

$pdf->Ln(75);
$pdf->SetFont('Arial','I',10);
$pdf->Cell(0,10,utf8_decode('Emitido por Recursos Humanos'),0,1,'C');

// =======================================
// 🔹 FORZAR DESCARGA
// =======================================
ob_end_clean();
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Carnet_' . $empleado['nombre'] . '.pdf"');
$pdf->Output('I', 'Carnet_' . $empleado['nombre'] . '.pdf');
exit;
?>
