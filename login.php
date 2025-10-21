<?php
session_start();
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 🔹 Configuración de conexiones
$db_config_cloud = [
    'host' => 'aws-1-us-east-2.pooler.supabase.com',
    'port' => '5432',
    'dbname' => 'postgres',
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

// 🔹 Función para conectarse a PostgreSQL (local o cloud)
function getPDO($cfg) {
    $ssl = '';
    if (str_contains($cfg['host'], 'supabase.com')) {
        $ssl = ';sslmode=require';
    }
    $dsn = "pgsql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']}{$ssl}";
    return new PDO($dsn, $cfg['user'], $cfg['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
}

// 🔹 Función para ejecutar en ambas BD
function runBoth($callback) {
    global $db_config_local, $db_config_cloud;
    try {
        $pdoLocal = getPDO($db_config_local);
        $pdoCloud = getPDO($db_config_cloud);
        $callback($pdoLocal);
        $callback($pdoCloud);
    } catch (Exception $e) {
        echo "<script>alert('⚠️ Error al registrar: ".$e->getMessage()."');</script>";
        exit;
    }
}

// 🟩 Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = $_POST['nombre_completo'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $dui = $_POST['dui'];
    $sexo = $_POST['sexo'] === 'true';
    $profesion = $_POST['profesion'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $direccion = $_POST['direccion'];
    $linkedin = $_POST['linkedin'];
    $resumen_profesional = $_POST['resumen_profesional'];
    $experiencia = $_POST['experiencia'];
    $educacion = $_POST['educacion'];
    $habilidades = $_POST['habilidades'];
    $idiomas = $_POST['idiomas'];
    $certificaciones = $_POST['certificaciones'];
    $cursos = $_POST['cursos'];
    $salario = $_POST['salario_pretendido'];
    $puesto = $_POST['profesion'];
    $fecha_contratacion = date('Y-m-d');

    // 🔸 Imagen (si se sube)
    $imagen = null;
    if (!empty($_FILES['imagen']['tmp_name']) && is_uploaded_file($_FILES['imagen']['tmp_name'])) {
        $imagenData = file_get_contents($_FILES['imagen']['tmp_name']);
        $imagen = base64_encode($imagenData);
    }

    // 🔹 Generar ID único global para empleado (compatible con BIGINT)
    $id_empleado_global = hexdec(substr(uniqid(), 0, 12));

    // 🔹 Insertar en ambas BD
    runBoth(function($pdo) use (
        $id_empleado_global, $nombre, $fecha_nacimiento, $dui, $sexo, $profesion,
        $telefono, $correo, $direccion, $linkedin, $resumen_profesional,
        $experiencia, $educacion, $habilidades, $idiomas, $certificaciones,
        $cursos, $salario, $puesto, $fecha_contratacion, $imagen
    ) {
        $pdo->beginTransaction();
        try {
            // Insertar en empleados usando ID único
            $stmt1 = $pdo->prepare("INSERT INTO empleados (id, nombre_completo, dui, nit, correo, puesto, fecha_contratacion, telefono)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt1->execute([$id_empleado_global, $nombre, $dui, uniqid('NIT'), $correo, $puesto, $fecha_contratacion, $telefono]);

            // Insertar en curriculum
            $stmt2 = $pdo->prepare("INSERT INTO curriculum (
                id_empleado, imagen, nombre_completo, fecha_nacimiento, dui, sexo, profesion,
                telefono, correo, direccion, linkedin, resumen_profesional, experiencia,
                educacion, habilidades, idiomas, certificaciones, cursos, salario_pretendido
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

            $stmt2->execute([
                $id_empleado_global, $imagen, $nombre, $fecha_nacimiento, $dui, $sexo, $profesion,
                $telefono, $correo, $direccion, $linkedin, $resumen_profesional,
                $experiencia, $educacion, $habilidades, $idiomas, $certificaciones,
                $cursos, $salario
            ]);

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    });

    echo "<script>alert('✅ Encargado registrado correctamente'); window.location='empleados_index.php';</script>";
    exit;
}
?>


<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Login - Industrias AX</title>
<link rel="icon" href="logss.png">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<style>
body {
  margin: 0;
  padding: 0;
  font-family: 'Segoe UI', sans-serif;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(to right, #0e1e36, #1b3d66, #2a5b9e);
  color: #fff;
}

/* ---------- CONTENEDOR GENERAL ---------- */
.container {
  display: flex;
  width: 850px;
  max-width: 95%;
  background: #162842;
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 8px 25px rgba(0,0,0,0.6);
}

/* ---------- SECCIÓN IZQUIERDA ---------- */
.left {
  flex: 1;
  background: #1e3a64;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 30px;
  color: #cfd8e3;
}

.left img {
  width: 120px; /* 🔹 Tamaño del logo ajustado */
  height: auto;
  margin-bottom: 20px;
  border-radius: 10px;
}

.left p {
  margin: 5px 0;
  font-size: 14px;
}

/* ---------- SECCIÓN DERECHA ---------- */
.right {
  flex: 1;
  background: #0f2039;
  padding: 50px;
  color: #fff;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.right h2 {
  text-align: center;
  margin-bottom: 30px;
  font-weight: 700;
  color: #6fb8ff;
  letter-spacing: 1px;
}

/* ---------- FORMULARIO ---------- */
form input {
  width: 100%;
  padding: 12px;
  margin: 12px 0;
  border: none;
  background: #1b2e4d;
  color: #e0e6f0;
  border-radius: 30px;
  font-size: 14px;
  box-sizing: border-box;
  transition: all 0.3s ease;
}

form input:focus {
  outline: none;
  box-shadow: 0 0 10px #6fb8ff;
}

form button {
  width: 100%;
  padding: 12px;
  margin-top: 15px;
  background: #3178c6;
  border: none;
  border-radius: 30px;
  color: #fff;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.3s ease;
}

form button:hover {
  background: #2a5b9e;
  transform: scale(1.03);
}

/* ---------- MENSAJE DE ERROR ---------- */
.error {
  background: #d9534f;
  padding: 10px;
  border-radius: 8px;
  margin-bottom: 15px;
  text-align: center;
  color: #fff;
  font-weight: bold;
}
</style>
</head>
<body>
<div class="container">
<div class="left">
<link rel="icon" href="logss.png">
<img src="logss.png" alt="Logotipo Industrias AX">
<p><b>Teléfono:</b> +503 73653477</p>
<p><b>Email:</b> industriasax@gmail.com</p>
<p><b>Dirección:</b> Av. Jayaque #10C, Col. Jardines de La Libertad</p>
</div>


<div class="right">
<h2>Acceder al Sistema</h2>
<?php if ($error): ?>
<div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<form method="post">
<input type="text" name="user" placeholder="Nombre de usuario o correo" required>
<input type="password" name="password" placeholder="Contraseña" required>
<button type="submit">Iniciar sesión</button>
<button type="button" onclick="window.location.href='http://localhost/prueba_doble_conexion.php'">
Registrar nuevo encargado
</button>
</form>
</div>
</div>
</body>
</html>
