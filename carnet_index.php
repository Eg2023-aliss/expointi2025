<?php
ob_start();
require('fpdf/fpdf.php');

// ==========================
// CONFIGURACIÓN DE BASE DE DATOS
// ==========================
$db_config = [
    'host' => 'aws-1-us-east-2.pooler.supabase.com', // Cloud DB
    'port' => '5432',
    'dbname' => 'postgres3',
    'user' => 'postgres.orzsdjjmyouhhxjfnemt',
    'pass' => 'Zv2sW23OhBVM5Tkz'
];

try {
    $dsn = "pgsql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['dbname']}";
    $pdo = new PDO($dsn, $db_config['user'], $db_config['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}

// ==========================
// OBTENER DATOS DEL EMPLEADO
// ==========================
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) die("ID de empleado no válido");

$stmt = $pdo->prepare("SELECT nombre, puesto, identificacion, foto FROM empleados WHERE id_empleado = ?");
$stmt->execute([$id]);
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empleado) die("Empleado no encontrado");

// ==========================
// CREAR PDF
// ==========================
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

// Foto ajustada a 35x45 mm
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

// ==========================
// FORZAR DESCARGA
// ==========================
ob_end_clean();
$filename = 'Carnet_' . preg_replace('/[^A-Za-z0-9]/', '_', $empleado['nombre']) . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.$filename.'"');
$pdf->Output('I', $filename);
exit;
?>
