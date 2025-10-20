<?php
session_start();
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require('fpdf/fpdf_index.php');

// 🔐 Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
  $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
  $baseUrl .= "://".$_SERVER['HTTP_HOST'];
  header("Location: $baseUrl/login.php");
  exit;
}


// ---------- CONFIGURACIÓN BASES DE DATOS ----------
$db_config_cloud = [
  'host' => 'aws-1-us-east-2.pooler.supabase.com',
'port' => '5432',
'dbname' => 'postgres3',
'user' => 'postgres.orzsdjjmyouhhxjfnemt',
'pass' => 'Zv2sW23OhBVM5Tkz'
];

$db_config_local = [
  'URL' => 'jdbc:postgresql://localhost:5432/postgres',
'port' => '5432',
'dbname' => 'postgres',
'user' => 'postgres',
'pass' => '12345'
];

$db_config = $db_config_local;

// ---------- FUNCIONES ----------
function getPDO($cfg = null) {
  // Si se pasa $cfg, úsalo; si no, usa $db_config global
  global $db_config;
  $use = $cfg ?? $db_config;
  $dsn = "pgsql:host={$use['host']};port={$use['port']};dbname={$use['dbname']}";
  return new PDO($dsn, $use['user'], $use['pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
  ]);
}

function runBoth($callback) {
  global $db_config_local, $db_config_cloud;
  $results = [];
  try {
    $pdoL = getPDO($db_config_local);
    $pdoC = getPDO($db_config_cloud);
    $results[] = $callback($pdoL);
    $results[] = $callback($pdoC);
  } catch (Exception $e) {
    error_log("Error runBoth: " . $e->getMessage());
  }
  return $results[0] ?? null;
}

// Helper: obtiene curriculum probando local y luego cloud si no existe local
function fetchCurriculumById($id_empleado) {
  global $db_config_local, $db_config_cloud;
  $fields = "id_empleado, nombre, puesto, experiencia, educacion, habilidades, correo";
  // Intenta en local
  try {
    $pdo = getPDO($db_config_local);
    $stmt = $pdo->prepare("SELECT $fields FROM curriculum WHERE id_empleado = :id LIMIT 1");
    $stmt->execute(['id' => $id_empleado]);
    $cv = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($cv) return $cv;
  } catch (Exception $e) {
    // no cortar, intentar en cloud
    error_log("fetchCurriculum local error: ".$e->getMessage());
  }
  // Intenta en cloud
  try {
    $pdo = getPDO($db_config_cloud);
    $stmt = $pdo->prepare("SELECT $fields FROM curriculum WHERE id_empleado = :id LIMIT 1");
    $stmt->execute(['id' => $id_empleado]);
    $cv = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($cv) return $cv;
  } catch (Exception $e) {
    error_log("fetchCurriculum cloud error: ".$e->getMessage());
  }
  return false;
}

// ---------- DATOS EMPLEADOS ----------
$empleados = [
  'Dirección y Administración General' => [
    ['id'=>3,'nombre'=>'Patricia Vásquez León','correo'=>'patricia.vasquez@empresa.com','puesto'=>'Directora General (CEO)','fecha'=>'2017-11-01'],
    ['id'=>2,'nombre'=>'Fernando Morales Díaz','correo'=>'fernando.morales@empresa.com','puesto'=>'Director de Tecnología (CTO)','fecha'=>'2018-02-20'],
    ['id'=>1,'nombre'=>'Carlos Hernández López','correo'=>'carlos.hernandez@empresa.com','puesto'=>'Director de Operaciones (COO)','fecha'=>'2020-03-15'],
  ],
'Finanzas y Administración' => [
  ['id'=>4,'nombre'=>'Ana María Torres','correo'=>'ana.torres@empresa.com','puesto'=>'Gerente de Finanzas','fecha'=>'2019-06-01'],
['id'=>5,'nombre'=>'Ricardo Gómez','correo'=>'ricardo.gomez@empresa.com','puesto'=>'Analista Financiero','fecha'=>'2021-07-22'],
['id'=>6,'nombre'=>'Silvia Ramírez','correo'=>'silvia.ramirez@empresa.com','puesto'=>'Auxiliar Contable','fecha'=>'2022-08-25'],
['id'=>7,'nombre'=>'Karen López','correo'=>'karen.lopez@empresa.com','puesto'=>'Contadora Junior','fecha'=>'2023-03-11'],
['id'=>8,'nombre'=>'David Herrera','correo'=>'david.herrera@empresa.com','puesto'=>'Pasante','fecha'=>'2023-07-20']
],
'Recursos Humanos' => [
  ['id'=>9,'nombre'=>'José Martínez','correo'=>'jose.martinez@empresa.com','puesto'=>'Gerente RRHH','fecha'=>'2021-01-10'],
['id'=>10,'nombre'=>'Daniel Pérez','correo'=>'daniel.perez@empresa.com','puesto'=>'Reclutador','fecha'=>'2021-09-30'],
['id'=>11,'nombre'=>'Gabriela Cruz','correo'=>'gabriela.cruz@empresa.com','puesto'=>'Auxiliar RRHH','fecha'=>'2022-10-05'],
['id'=>12,'nombre'=>'Laura Torres','correo'=>'laura.torres@empresa.com','puesto'=>'Psicóloga','fecha'=>'2023-01-17'],
['id'=>13,'nombre'=>'Mario Sánchez','correo'=>'mario.sanchez@empresa.com','puesto'=>'Asistente RRHH','fecha'=>'2023-05-29']
],
'Ventas y Atención Comercial' => [
  ['id'=>14,'nombre'=>'Luis Castillo','correo'=>'luis.castillo@empresa.com','puesto'=>'Gerente Comercial','fecha'=>'2021-04-12'],
['id'=>15,'nombre'=>'Marta López','correo'=>'marta.lopez@empresa.com','puesto'=>'Coordinadora Ventas','fecha'=>'2022-05-18'],
['id'=>16,'nombre'=>'Andrea Fuentes','correo'=>'andrea.fuentes@empresa.com','puesto'=>'Ejecutiva Ventas','fecha'=>'2023-02-10'],
['id'=>17,'nombre'=>'Óscar Molina','correo'=>'oscar.molina@empresa.com','puesto'=>'Asesor Comercial','fecha'=>'2023-06-18']
],
'Infraestructura y Redes' => [
  ['id'=>18,'nombre'=>'Jorge Ramírez','correo'=>'jorge.ramirez@empresa.com','puesto'=>'Gerente Redes','fecha'=>'2016-09-15']
]
];

// ---------- DESCARGAR CARNET ----------
if (($_GET['action'] ?? '') === 'carnet') {
  $id = intval($_GET['id'] ?? 0);
  if ($id <= 0) die("ID inválido");

  $empleado = null;
  foreach ($empleados as $area) {
    foreach ($area as $e) {
      if ($e['id'] === $id) $empleado = $e;
    }
  }
  if (!$empleado) die("Empleado no encontrado");

  // Crear PDF tamaño carnet (8.5 x 5.5 cm)
  $pdf = new FPDF('L', 'cm', [8.5, 5.5]);
  $pdf->AddPage();

  // 🔹 Fondo completo del carnet
  $fondo = __DIR__ . '/carnets.jpg';
  if (file_exists($fondo)) {
    $pdf->Image($fondo, 0, 0, 8.6, 5.5);
  } else {
    $pdf->SetFillColor(7,79,159);
    $pdf->Rect(0, 0, 8.5, 5.5, 'F');
  }

  // 🔹 Foto del empleado ajustada a 35x45 mm (3.5x4.5 cm)
  $fotoPaths = [
    __DIR__ . '/fts/' . $empleado['id'] . '.jpg',
    __DIR__ . '/uploads/' . $empleado['id'] . '.png'
  ];
  $foto = null;
  foreach ($fotoPaths as $p) if (file_exists($p)) $foto = $p;

  if ($foto) {
    $x = 0.5;
    $y = 0.7;
    $ancho = 3.5;
    $alto = 4.5;

    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect($x, $y, $ancho, $alto, 'F');

    $pdf->Image($foto, $x, $y, $ancho, $alto, '', '', true);
  } else {
    $pdf->SetDrawColor(180,180,180);
    $pdf->Rect(0.5, 0.7, 3.5, 4.5);
    $pdf->SetFont('Arial','I',7);
    $pdf->SetXY(0.5,2.7);
    $pdf->Cell(3.5,0.4,'Sin foto',0,0,'C');
  }

  // 🔹 Datos del empleado (sobre el fondo)
  $pdf->SetTextColor(0,0,0);
  $pdf->SetFont('Arial','B',8);
  $pdf->SetXY(4.2, 1.2);
  $pdf->MultiCell(4, 0.4, iconv('UTF-8','ISO-8859-1',$empleado['nombre']), 0, 'L');

  $pdf->SetFont('Arial','',7);
  $pdf->SetXY(4.2, 2.2);
  $pdf->Cell(4, 0.4, 'Puesto: '.iconv('UTF-8','ISO-8859-1',$empleado['puesto']), 0, 1);

  $pdf->SetXY(4.2, 2.8);
  $pdf->Cell(4, 0.4, 'Correo: '.iconv('UTF-8','ISO-8859-1',$empleado['correo']), 0, 1);

  // 🔹 Pie inferior
  $pdf->SetFont('Arial','I',6.5);
  $pdf->SetTextColor(255,255,255);
  $pdf->SetXY(0, 5.0);
  $pdf->Cell(8.5, 0.4, iconv('UTF-8','ISO-8859-1','Empresa XYZ | RRHH'), 0, 0, 'C');

  if (ob_get_length()) ob_end_clean();
  header('Content-Type: application/pdf');
  $pdf->Output('I', 'carnet_'.$empleado['id'].'.pdf');
  exit;
}

// ---------- DESCARGAR CURRICULUM ----------
if (($_GET['action'] ?? '') === 'curriculum') {
  $id = intval($_GET['id'] ?? 0);
  if ($id <= 0) die("ID inválido");

  // Conexión PDO
  $pdo = getPDO();

  // Buscar curriculum en la tabla
  $stmt = $pdo->prepare("SELECT * FROM curriculum WHERE id_empleado = ?");
  $stmt->execute([$id]);
  $cv = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$cv) {
    die("No se encontró curriculum para este empleado");
  }

  // Crear PDF de currículum
  $pdf = new FPDF();
  $pdf->AddPage();

  // Foto
  // Foto
  $foto = null;
  if (!empty($cv['imagen']) && file_exists($cv['imagen'])) {
    $foto = $cv['imagen'];
  }
  if ($foto) {
    $pdf->Image($foto, 10, 10, 30, 40);
    $pdf->SetXY(45, 12);
  } else {
    $pdf->SetXY(10, 12);
  }

  // Título
  $pdf->SetFont('Arial','B',16);
  $pdf->Cell(0, 8, utf8_decode('Currículum Vitae'), 0, 1, 'C');
  $pdf->Ln(2);

  // Datos personales básicos
  $pdf->SetFont('Arial','B',12);
  if ($foto) {
    $pdf->SetXY(45, 22);
    $pdf->Cell(0, 6, utf8_decode($cv['nombre_completo'] ?? ''), 0, 1);
    $pdf->SetFont('Arial','',11);
    $pdf->SetXY(45, 28);
    $pdf->Cell(0, 6, utf8_decode($cv['profesion'] ?? ''), 0, 1);
    $pdf->SetXY(45, 34);
    $pdf->Cell(0, 6, utf8_decode('Correo: '.$cv['correo']), 0, 1);
    $pdf->SetXY(45, 40);
    $pdf->Cell(0, 6, utf8_decode('Tel: '.$cv['telefono']), 0, 1);
  } else {
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0, 8, utf8_decode($cv['nombre_completo'] ?? ''), 0, 1, 'C');
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(0, 6, utf8_decode($cv['profesion'] ?? ''), 0, 1, 'C');
    $pdf->Cell(0, 6, utf8_decode('Correo: '.$cv['correo']), 0, 1, 'C');
    $pdf->Cell(0, 6, utf8_decode('Tel: '.$cv['telefono']), 0, 1, 'C');
  }

  $pdf->Ln(4);

  // Secciones principales
  $sections = [
    'Resumen Profesional' => $cv['resumen_profesional'] ?? '',
    'Experiencia' => $cv['experiencia'] ?? '',
    'Educación' => $cv['educacion'] ?? '',
    'Habilidades' => $cv['habilidades'] ?? '',
    'Idiomas' => $cv['idiomas'] ?? '',
    'Certificaciones' => $cv['certificaciones'] ?? '',
    'Cursos' => $cv['cursos'] ?? ''
  ];

  foreach ($sections as $title => $content) {
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0, 7, utf8_decode($title), 0, 1);
    $pdf->SetFont('Arial','',11);
    $pdf->MultiCell(0, 6, utf8_decode($content ?: 'No especificado'));
    $pdf->Ln(2);
  }

  // Datos adicionales
  $pdf->SetFont('Arial','B',12);
  $pdf->Cell(0, 7, utf8_decode('Datos Adicionales'), 0, 1);
  $pdf->SetFont('Arial','',11);
  $pdf->MultiCell(0,6,
                  utf8_decode(
                    'DUI: '.$cv['dui']."\n".
                    'Fecha de nacimiento: '.$cv['fecha_nacimiento']."\n".
                    'Sexo: '.($cv['sexo'] ? 'Masculino' : 'Femenino')."\n".
                    'Dirección: '.$cv['direccion']."\n".
                    'LinkedIn: '.$cv['linkedin']."\n".
                    'Salario pretendido: $'.$cv['salario_pretendido']
                  )
  );

  $pdf->Ln(6);
  $pdf->SetFont('Times','I',10);
  $pdf->Cell(0, 6, utf8_decode('Documento generado automáticamente por el sistema AX.'), 0, 1, 'C');

  if (ob_get_length()) ob_end_clean();
  header('Content-Type: application/pdf');
  header('Content-Disposition: attachment; filename="curriculum_'.$id.'.pdf"');
  $pdf->Output('I');
  exit;
}


// ---------- LISTAR EMPLEADOS ----------
if (($_GET['action'] ?? '') === 'list') {
  header('Content-Type: application/json');
  $area = $_GET['area'] ?? '';
  echo json_encode($empleados[$area] ?? []);
  exit;
}

// ---------- VER ASISTENCIA (AJAX) ----------
if (($_GET['action'] ?? '') === 'ver_asistencia') {
  $id = intval($_GET['id']);
  $pdo = getPDO();
  $stmt = $pdo->prepare("SELECT id, fecha, estado, observacion FROM asistencia WHERE id_empleado=:id ORDER BY fecha");
  $stmt->execute(['id'=>$id]);
  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

// ---------- EDITAR ASISTENCIA ----------
if (($_GET['action'] ?? '') === 'editar_asistencia' && $_SERVER['REQUEST_METHOD']==='POST') {
  $body = json_decode(file_get_contents('php://input'), true);
  $id = intval($body['id']);
  $estado = strtoupper(trim($body['estado']));
  $obs = $body['observacion'] ?? '';
  $pdo = getPDO();
  $stmt = $pdo->prepare("UPDATE asistencia SET estado=:e, observacion=:o WHERE id=:id");
  $stmt->execute(['e'=>$estado,'o'=>$obs,'id'=>$id]);
  echo json_encode(['status'=>'ok'], JSON_UNESCAPED_UNICODE);
  exit;
}

// ---------- DESCARGAR ASISTENCIA ----------
if (($_GET['action'] ?? '') === 'descargar_asistencia') {
  $id = intval($_GET['id'] ?? 0);
  $mes = $_GET['mes'] ?? date('m');
  $anio = $_GET['anio'] ?? date('Y');
  $pdo = getPDO();

  $stmt = $pdo->prepare("SELECT fecha, estado, observacion FROM asistencia WHERE id_empleado=? AND EXTRACT(MONTH FROM fecha)=? AND EXTRACT(YEAR FROM fecha)=?");
  $stmt->execute([$id, $mes, $anio]);
  $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $faltas = array_filter($datos, fn($d) => strtoupper($d['estado']) === 'FALTA');
  $descuento = count($faltas) > 0 ? 'Sí (5%)' : 'No';

  $pdf = new FPDF();
  $pdf->AddPage();

  if (file_exists('logss.png')) {
    $pdf->Image('logss.png', 20, 10, 20);
    $pdf->Ln(20);
  } else {
    $pdf->Ln(15);
  }

  $pdf->SetFont('Arial', 'B', 14);
  $pdf->Cell(0, 10, utf8_decode("Asistencia empleado (#$id)"), 0, 1, 'C');
  $pdf->Ln(5);

  $pdf->SetDrawColor(74, 144, 226);
  $pdf->SetLineWidth(0.8);
  $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
  $pdf->Ln(4);
  $pdf->SetFont('Arial','B',10);
  $pdf->Cell(0, 8, utf8_decode("Faltas injustificadas: " . count($faltas)), 0, 1);
  $pdf->Cell(0, 8, utf8_decode("Descuento aplicado: " . $descuento), 0, 1);
  $pdf->Ln(6);

  $pdf->SetFont('Arial', 'B', 10);
  $pdf->SetFillColor(74, 144, 226);
  $pdf->SetTextColor(255,255,255);
  $pdf->Cell(50, 9, utf8_decode("Fecha"), 1, 0, 'C', true);
  $pdf->Cell(50, 9, utf8_decode("Estado"), 1, 0, 'C', true);
  $pdf->Cell(90, 9, utf8_decode("Observación"), 1, 1, 'C', true);

  $pdf->SetFont('Arial','',10);
  $pdf->SetTextColor(0,0,0);
  $fill = false;
  foreach ($datos as $d) {
    $pdf->SetFillColor(242,248,255);
    $pdf->Cell(50, 8, utf8_decode($d['fecha']), 1, 0, 'L', $fill);
    $pdf->Cell(50, 8, utf8_decode($d['estado']), 1, 0, 'L', $fill);
    $pdf->Cell(90, 8, utf8_decode($d['observacion']), 1, 1, 'L', $fill);
    $fill = !$fill;
  }

  $pdf->Ln(6);
  $pdf->SetFont('Times', 'I', 10);
  $pdf->Cell(0, 6, utf8_decode("Reporte generado automáticamente por el sistema AX."), 0, 1, 'C');

  if (ob_get_length()) ob_end_clean();
  header('Content-Type: application/pdf');
  header('Content-Disposition: attachment; filename="asistencia_'.$id.'.pdf"');
  $pdf->Output('I');
  exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Áreas de la Empresa</title>
<link rel="icon" href="logss.png">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: "Poppins", sans-serif; }

body {
  background: linear-gradient(135deg, #002b5c, #004080, #007bff);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  color: #fff;
  overflow-x: hidden;
  position: relative;
}

/* Ondas decorativas del fondo */
body::before, body::after {
  content: "";
  position: absolute;
  width: 700px;
  height: 700px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.05);
  animation: girar 25s linear infinite;
  pointer-events: none;
  z-index: 5;
}
body::before { top: -250px; left: -250px; }
body::after { bottom: -250px; right: -250px; }
@keyframes girar { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

/* Navbar */
.navbar { position: fixed; top: 20px; left: 40px; display: flex; gap: 15px; z-index: 30; }
.navbar a {
  text-decoration: none;
  background: rgba(255,255,255,0.15);
  color: white;
  padding: 10px 20px;
  border-radius: 30px;
  font-weight: 500;
  transition: 0.3s;
  backdrop-filter: blur(10px);
}
.navbar a:hover { background: rgba(255,255,255,0.35); transform: scale(1.05); }

/* Logo */
.logo { position: fixed; top: 20px; right: 35px; width: 70px; opacity: 0.9; z-index: 30; }

/* Título */
header { text-align: center; margin-top: 120px; margin-bottom: 30px; text-shadow: 2px 2px 8px rgba(0,0,0,0.4); }
header h1 { font-size: 2.8rem; letter-spacing: 2px; }

/* Botones de áreas */
.area-container { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-bottom: 40px; }
.area-btn {
  background: white;
  color: #003366;
  padding: 15px 25px;
  border: none;
  border-radius: 30px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(0,0,0,0.3);
  transition: all 0.3s;
  position: relative;
  z-index: 40;
}
.area-btn:hover { background: #003366; color: white; transform: translateY(-4px); }

/* Tabla de empleados */
#empleadosArea {
width: 90%;
background: rgba(255,255,255,0.1);
border-radius: 10px;
padding: 20px;
backdrop-filter: blur(8px);
box-shadow: 0 4px 15px rgba(0,0,0,0.3);
margin-bottom: 40px;
position: relative;
z-index: 20;
}
#empleadosArea h2 { text-align: center; margin-bottom: 15px; }
table { width: 100%; border-collapse: collapse; color: #fff; }
table th, table td { padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.2); text-align: left; }
table th { background: rgba(255,255,255,0.2); color: #fff; }
table tr:hover { background: rgba(255,255,255,0.1); }

/* Botones generales */
.btn {
  background: #ffffff;
  color: #003366;
  border: none;
  padding: 8px 15px;
  border-radius: 25px;
  cursor: pointer;
  font-weight: 500;
  transition: 0.3s;
  position: relative;
  z-index: 40;
}
.btn:hover { background: #003366; color: white; }

/* Modal calendario */
#calendarModal {
display: none;
position: fixed;
top: 0; left: 0;
width: 100%; height: 100%;
background: rgba(0,0,0,0.8);
justify-content: center;
align-items: center;
z-index: 2000;
}

#calendarBox {
background: linear-gradient(135deg, #ffffff, #f0f4ff);
color: #002b5c;
padding: 25px;
border-radius: 15px;
width: 90%;
max-width: 900px;
box-shadow: 0 6px 20px rgba(0,0,0,0.4);
}

/* Botones dentro del modal */
#calendarBox .btn {
background: #004080;
color: white;
border: none;
padding: 8px 15px;
border-radius: 25px;
cursor: pointer;
font-weight: 500;
transition: 0.3s;
}
#calendarBox .btn:hover { background: #007bff; transform: scale(1.05); }

/* Selector de mes y año */
#calendarBox select {
padding: 6px 10px;
border-radius: 8px;
border: 1px solid #004080;
background: white;
color: #003366;
font-weight: 500;
margin: 0 5px;
}

/* Botón de descarga */
#btnDescargar {
background: #007bff;
color: white;
border: none;
padding: 10px 20px;
border-radius: 30px;
cursor: pointer;
transition: 0.3s;
font-weight: 600;
margin-bottom: 10px;
}
#btnDescargar:hover { background: #0056b3; }

/* Estilo calendario FullCalendar */
#calendar {
background: white;
border-radius: 10px;
padding: 10px;
margin-top: 15px;
box-shadow: 0 2px 8px rgba(0,0,0,0.2);
height: 350px; /* tamaño más compacto */
}

/* Cabecera (mes y controles) */
.fc-toolbar-title { color: #002b5c !important; font-weight: 700; text-transform: capitalize; }
.fc-button {
  background: #004080 !important;
  border: none !important;
  color: white !important;
  border-radius: 8px !important;
  transition: 0.3s;
}
.fc-button:hover { background: #007bff !important; }

/* Celdas de días */
.fc-daygrid-day { background: #f9fbff; border: 1px solid #e1e8ff; }
.fc-day-today { background: #e1edff !important; border: 2px solid #004080 !important; }
.fc-daygrid-day-number { color: #003366; font-weight: 600; }
.fc-event {
  border: none !important;
  color: white !important;
  border-radius: 5px;
  padding: 2px 4px;
  font-size: 0.8em;
}
/* Colores de eventos */
.fc-event[style*="background-color: #2ecc71"] { background-color: #2ecc71 !important; }
.fc-event[style*="background-color: #e74c3c"] { background-color: #e74c3c !important; }

</style>
</head>
<body>

<!-- Menú superior -->
<div class="navbar">
<a href="login.php">Login</a>
<a href="empleados_index.php">Empleados</a>
<a href="formulario.php">Registrar Encargado</a>
<a href="login.php">Salir</a>
</div>

<!-- Logo -->
<img src="logss.png" alt="Logo AX" class="logo">

<!-- Contenido principal -->
<header><h1>ÁREAS DE LA EMPRESA</h1></header>

<div class="area-container">
<button class="area-btn" onclick="mostrarArea('Dirección y Administración General')">1️⃣ Dirección y Administración General</button>
<button class="area-btn" onclick="mostrarArea('Finanzas y Administración')">2️⃣ Finanzas y Administración</button>
<button class="area-btn" onclick="mostrarArea('Recursos Humanos')">3️⃣ Recursos Humanos</button>
<button class="area-btn" onclick="mostrarArea('Ventas y Atención Comercial')">4️⃣ Ventas y Atención Comercial</button>
<button class="area-btn" onclick="mostrarArea('Infraestructura y Redes')">5️⃣ Infraestructura y Redes</button>
</div>

<div id="empleadosArea" style="display:none;">
<h2 id="tituloArea"></h2>
<table>
<thead>
<tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Puesto</th><th>Fecha</th><th>Opciones</th></tr>
</thead>
<tbody id="tbodyArea"></tbody>
</table>
</div>

<!-- Modal calendario -->
<div id="calendarModal">
<div id="calendarBox">
<button class="btn" onclick="cerrarCalendario()">❌ Cerrar</button>
<div style="margin:10px 0;">
<label>Mes:</label>
<select id="selectMes"></select>
<label>Año:</label>
<select id="selectAnio"></select>
</div>
<button id="btnDescargar">⬇️ Descargar Asistencia</button>
<div id="calendar"></div>
</div>
</div>

<script>
let calendar, empleadoActual = 0;

async function mostrarArea(area){
  if(!area) return;
  document.getElementById('empleadosArea').style.display='block';
  document.getElementById('tituloArea').textContent='Empleados del área: '+area;
  try {
    const res=await fetch('?action=list&area='+encodeURIComponent(area));
    const data=await res.json();
    if(!Array.isArray(data)) {
      document.getElementById('tbodyArea').innerHTML = '<tr><td colspan="6">No hay datos</td></tr>';
      return;
    }
    document.getElementById('tbodyArea').innerHTML=data.map(e=>`
    <tr>
    <td>${e.id}</td>
    <td>${e.nombre}</td>
    <td>${e.correo}</td>
    <td>${e.puesto}</td>
    <td>${e.fecha}</td>
    <td>
    <button class="btn" onclick="downloadCarnet(${e.id})">Carnet</button>
    <button class="btn" onclick="curriculum(${e.id})">Curriculum</button>
    <button class="btn" onclick="verAsistencia(${e.id})">📅 Ver Asistencia</button>
    </td>
    </tr>`).join('');
  } catch(err) {
    console.error(err);
    document.getElementById('tbodyArea').innerHTML = '<tr><td colspan="6">Error cargando empleados</td></tr>';
  }
}

function downloadCarnet(id){ if(!id) return; window.location='?action=carnet&id='+id; }

// función solicitada por tu HTML: descarga el CV
function curriculum(id){
  if(!id) return;
  window.location='?action=curriculum&id='+id;
}

function inicializarSelectores(){
  const selectMes=document.getElementById('selectMes'),
  selectAnio=document.getElementById('selectAnio');
  const meses=["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
  selectMes.innerHTML=meses.map((m,i)=>`<option value="${i+1}">${m}</option>`).join('');
  const anioAct=new Date().getFullYear();
  selectAnio.innerHTML=Array.from({length:6},(_,i)=>anioAct-3+i).map(a=>`<option value="${a}">${a}</option>`).join('');
  selectMes.value=new Date().getMonth()+1;
  selectAnio.value=anioAct;
}

async function verAsistencia(id){
  if(!id) return;
  empleadoActual=id;
  document.getElementById('calendarModal').style.display='flex';
  inicializarSelectores();
  try {
    const res=await fetch('?action=ver_asistencia&id='+id);
    const eventos=await res.json();
    const eventosCal=eventos.map(e=>({title:e.estado,start:e.fecha,color:(String(e.estado).toUpperCase()==='FALTA'?'#e74c3c':'#2ecc71')}));
    if(calendar) calendar.destroy();
    calendar=new FullCalendar.Calendar(document.getElementById('calendar'),{
      initialView:'dayGridMonth',
      height:350, // tamaño reducido
      locale:'es',
      events:eventosCal,
      editable:true,
      dateClick:async info=>{
        const estado=prompt("Ingrese estado (ASISTIÓ / FALTA / INCAPACIDAD):","ASISTIÓ");
        if(!estado) return;
        await fetch('?action=editar_asistencia',{
          method:'POST',
          headers:{'Content-Type':'application/json'},
          body:JSON.stringify({id,estado,observacion:'Modificado manualmente'})
        });
        alert('Actualizado');
        verAsistencia(id);
      }
    });
    calendar.render();
  } catch(err) {
    console.error(err);
    alert('Error cargando asistencia');
  }
}

function cerrarCalendario(){ document.getElementById('calendarModal').style.display='none'; }

document.getElementById('btnDescargar').onclick=()=>{
  const m=document.getElementById('selectMes').value;
  const a=document.getElementById('selectAnio').value;
  if(!empleadoActual) { alert('Seleccione primero un empleado'); return; }
  window.location=`?action=descargar_asistencia&id=${empleadoActual}&mes=${m}&anio=${a}`;
};
</script>

</body>
</html>
