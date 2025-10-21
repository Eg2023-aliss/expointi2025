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


<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Registrar Encargado</title>
<link rel="icon" href="logss.png">
<style>
body {
    background: linear-gradient(135deg, #004080, #0073e6);
    color: #fff;
    font-family: Poppins, sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}
form {
    background: rgba(255,255,255,0.1);
    padding: 25px 40px;
    border-radius: 15px;
    box-shadow: 0 0 15px rgba(0,0,0,0.3);
    backdrop-filter: blur(10px);
    width: 750px;
}
h2 { text-align: center; margin-bottom: 25px; }
label { display: block; margin-top: 10px; font-weight: 500; }
input, textarea, select {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border-radius: 8px;
    border: none;
    outline: none;
}
textarea { resize: vertical; height: 60px; }
button {
    margin-top: 20px;
    width: 100%;
    padding: 12px;
    background: #007bff;
    border: none;
    border-radius: 25px;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
}
button:hover { background: #0056b3; }
a { color: #fff; text-decoration: none; font-size: 14px; display: block; text-align: center; margin-top: 10px; }
</style>
</head>
<body>

<form method="POST" enctype="multipart/form-data">
<h2>Registro de Encargado</h2>

<label>Fotografía</label>
<input type="file" name="imagen" accept="image/*">

<label>Nombre Completo</label>
<input type="text" name="nombre_completo" required>

<label>Fecha de Nacimiento</label>
<input type="date" name="fecha_nacimiento" required>

<label>DUI</label>
<input type="text" name="dui" maxlength="10" required>

<label>Sexo</label>
<select name="sexo" required>
<option value="true">Masculino</option>
<option value="false">Femenino</option>
</select>

<label>Profesión</label>
<input type="text" name="profesion" required>

<label>Teléfono</label>
<input type="text" name="telefono" maxlength="8" required>

<label>Correo</label>
<input type="email" name="correo" required>

<label>Dirección</label>
<input type="text" name="direccion" required>

<label>LinkedIn</label>
<input type="text" name="linkedin">

<label>Resumen Profesional</label>
<textarea name="resumen_profesional"></textarea>

<label>Experiencia Laboral</label>
<textarea name="experiencia"></textarea>

<label>Educación</label>
<textarea name="educacion"></textarea>

<label>Habilidades</label>
<textarea name="habilidades"></textarea>

<label>Idiomas</label>
<textarea name="idiomas"></textarea>

<label>Certificaciones</label>
<textarea name="certificaciones"></textarea>

<label>Cursos</label>
<textarea name="cursos"></textarea>

<label>Salario Pretendido</label>
<input type="number" step="0.01" name="salario_pretendido">

<button type="submit">Registrar Encargado</button>
<a href="empleados_index.php">⬅️ Volver</a>
</form>
</body>
</html>
