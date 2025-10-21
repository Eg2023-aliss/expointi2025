<?php
ob_start();
require('fpdf/fpdf.php');

// =======================================
// 🔹 CONFIGURACIÓN DE CONEXIÓN DUAL
// =======================================
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
    'user' => 'postgres',
    'pass' => '12345'
];

// Elegir base de datos según parámetro
$conexion = (isset($_GET['origen']) && $_GET['origen'] === 'cloud') ? $db_config_cloud : $db_config_local;

try {
    $dsn = "pgsql:host={$conexion['host']};port={$conexion['port']};dbname={$conexion['dbname']}";
    $pdo = new PDO($dsn, $conexion['user'], $conexion['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}

// =======================================
// 🔹 OBTENER DATOS DEL EMPLEADO
// =======================================
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) die("ID de empleado no válido");

$stmt = $pdo->prepare("SELECT nombre, puesto, identificacion, foto FROM empleados WHERE id_empleado = ?");
$stmt->execute([$id]);
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empleado) die("Empleado no encontrado en la base de datos");

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

// Foto del empleado ajustada a 35x45 mm
$fotoPath = 'uploads/' . $empleado['foto'];
if (!empty($empleado['foto']) && file_exists($fotoPath)) {
    $pdf->Image($fotoPath, 80, 90, 35, 45);
} else {
    $pdf->Rect(80, 90, 35, 45);
    $pdf->Text(92, 115, 'Sin foto');
}

$pdf->Ln(75);
$pdf->SetFont('Arial','I',10);
$pdf->Cell(0,10,utf8_decode('Emitido por Recursos Humanos'),0,1,'C');

// =======================================
// 🔹 FORZAR DESCARGA
// =======================================
ob_end_clean();
$filename = 'Carnet_' . preg_replace('/[^A-Za-z0-9]/', '_', $empleado['nombre']) . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.$filename.'"');
$pdf->Output('I', $filename);
exit;
?>
