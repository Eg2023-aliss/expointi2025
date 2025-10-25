<?php
session_start();
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('fpdf/fpdf.php');
require_once 'conexion.php';

// 🔐 Verificar sesión

// ---------- OBTENER DATOS ----------
try {
  $pdo = getPDO();
  $stmt = $pdo->query("SELECT id, nombre_completo, area, cargo FROM empleados ORDER BY id ASC");
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("❌ Error al obtener empleados: " . $e->getMessage());
}

// ---------- FUNCIONES PDF ----------
function generarCarnet($empleado) {
  $pdf = new FPDF('P', 'mm', [85, 54]);
  $pdf->AddPage();
  $pdf->Image('logo.png', 5, 5, 20);
  $pdf->SetFont('Arial', 'B', 12);
  $pdf->Cell(0, 10, 'Carnet de Empleado', 0, 1, 'C');
  $pdf->SetFont('Arial', '', 10);
  $pdf->Ln(5);
  $pdf->Cell(0, 6, 'Nombre: ' . $empleado['nombre_completo'], 0, 1);
  $pdf->Cell(0, 6, 'Área: ' . $empleado['area'], 0, 1);
  $pdf->Cell(0, 6, 'Cargo: ' . $empleado['cargo'], 0, 1);
  $pdf->Ln(5);
  $pdf->Output('I', 'carnet_' . $empleado['id'] . '.pdf');
}

function generarCurriculum($empleado) {
  $pdf = new FPDF();
  $pdf->AddPage();
  $pdf->Image('logo.png', 10, 8, 25);
  $pdf->SetFont('Arial', 'B', 16);
  $pdf->Cell(0, 15, 'Curriculum Vitae', 0, 1, 'C');
  $pdf->SetFont('Arial', '', 12);
  $pdf->Ln(10);
  $pdf->Cell(0, 8, 'Nombre: ' . $empleado['nombre_completo'], 0, 1);
  $pdf->Cell(0, 8, 'Área: ' . $empleado['area'], 0, 1);
  $pdf->Cell(0, 8, 'Cargo: ' . $empleado['cargo'], 0, 1);
  $pdf->Ln(10);
  $pdf->MultiCell(0, 8, "Experiencia laboral y formación académica.\n(Se pueden añadir más campos desde la base de datos.)");
  $pdf->Output('I', 'curriculum_' . $empleado['id'] . '.pdf');
}

function generarAsistencia($empleado) {
  $pdf = new FPDF();
  $pdf->AddPage();
  $pdf->Image('logo.png', 10, 8, 25);
  $pdf->SetFont('Arial', 'B', 14);
  $pdf->Cell(0, 15, 'Registro de Asistencia', 0, 1, 'C');
  $pdf->SetFont('Arial', '', 12);
  $pdf->Ln(5);
  $pdf->Cell(0, 8, 'Empleado: ' . $empleado['nombre_completo'], 0, 1);
  $pdf->Cell(0, 8, 'Área: ' . $empleado['area'], 0, 1);
  $pdf->Cell(0, 8, 'Cargo: ' . $empleado['cargo'], 0, 1);
  $pdf->Ln(10);
  $pdf->MultiCell(0, 8, "Asistencias registradas del mes actual.\n(Si deseas mostrar datos reales, conecta con la tabla asistencia.)");
  $pdf->Output('I', 'asistencia_' . $empleado['id'] . '.pdf');
}

// ---------- MANEJADOR DE DESCARGAS ----------
if (isset($_GET['accion']) && isset($_GET['id'])) {
  $id = intval($_GET['id']);
  $stmt = $pdo->prepare("SELECT * FROM empleados WHERE id = ?");
  $stmt->execute([$id]);
  $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$empleado) die("Empleado no encontrado.");

  switch ($_GET['accion']) {
    case 'carnet': generarCarnet($empleado); break;
    case 'curriculum': generarCurriculum($empleado); break;
    case 'asistencia': generarAsistencia($empleado); break;
  }
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>📋 Lista de Empleados</title>
<link rel="icon" href="logo.png">
<style>
  body {
    font-family: Arial, sans-serif;
    background: #f4f6f8;
    margin: 0;
  }
  header {
    background: #003366;
    color: white;
    text-align: center;
    padding: 15px;
    font-size: 1.5em;
  }
  table {
    width: 90%;
    margin: 30px auto;
    border-collapse: collapse;
    background: white;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    border-radius: 8px;
    overflow: hidden;
  }
  th, td {
    padding: 12px;
    text-align: left;
  }
  th {
    background: #004080;
    color: white;
  }
  tr:nth-child(even) {
    background: #f2f2f2;
  }
  button {
    background: #0066cc;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
  }
  button:hover {
    background: #004d99;
  }
  footer {
    text-align: center;
    padding: 15px;
    background: #003366;
    color: white;
    position: fixed;
    bottom: 0;
    width: 100%;
  }
</style>
</head>
<body>

<header>📋 Lista de Empleados</header>

<table>
  <tr>
    <th>ID</th>
    <th>Nombre Completo</th>
    <th>Área</th>
    <th>Cargo</th>
    <th>Acciones</th>
  </tr>
  <?php foreach ($rows as $e): ?>
  <tr>
    <td><?= htmlspecialchars($e['id']) ?></td>
    <td><?= htmlspecialchars($e['nombre_completo']) ?></td>
    <td><?= htmlspecialchars($e['area']) ?></td>
    <td><?= htmlspecialchars($e['cargo']) ?></td>
    <td>
      <button onclick="window.location='?accion=carnet&id=<?= $e['id'] ?>'">🎫 Carnet</button>
      <button onclick="window.location='?accion=curriculum&id=<?= $e['id'] ?>'">📄 Curriculum</button>
      <button onclick="window.location='?accion=asistencia&id=<?= $e['id'] ?>'">🗓️ Asistencia</button>
    </td>
  </tr>
  <?php endforeach; ?>
</table>

<footer>© 2025 ExpoInti - Render & Supabase</footer>
</body>
</html>
