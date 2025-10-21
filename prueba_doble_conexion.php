<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================
// CONFIGURACIÓN DE BASES
// ==========================

$db_local = [
      'URL' => 'jdbc:postgresql://localhost:5432/postgres',
'port' => '5432',
'dbname' => 'postgres',
'user' => 'postgres',
'pass' => '12345'
];

$db_cloud = [
    'host' => 'aws-1-us-east-2.pooler.supabase.com',
'port' => '6543',
'dbname' => 'postgres',
'user' => 'postgres.orzsdjjmyouhhxjfnemt',
'pass' => 'Zv2sW23OhBVM5Tkz'
];

// ==========================
// FUNCIÓN DE CONEXIÓN
// ==========================
function getPDO($config) {
    $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
    return new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $pdo_local = getPDO($db_local);
        $pdo_cloud = getPDO($db_cloud);

        // Crear tabla usuarios si no existe
        $createSQL = "
        CREATE TABLE IF NOT EXISTS usuarios (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL,
            password VARCHAR(255) NOT NULL
            );
            ";
$pdo_local->exec($createSQL);
$pdo_cloud->exec($createSQL);

// Recibir datos del formulario
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$password = password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT);

// Verificar que el email exista en curriculum
$checkSQL = "SELECT id_empleado, nombre_completo FROM curriculum WHERE correo = :email";
$stmtCheck = $pdo_local->prepare($checkSQL);
$stmtCheck->execute([':email' => $email]);
$empleado = $stmtCheck->fetch(PDO::FETCH_ASSOC);

if (!$empleado) {
    $message = "❌ No puedes registrarte: el correo no corresponde a un empleado contratado.";
} else {
    // Query de inserción
    $insertSQL = "INSERT INTO usuarios (username, email, password) VALUES (:username, :email, :password)";

    // Insertar en Local
    $stmtLocal = $pdo_local->prepare($insertSQL);
    $stmtLocal->execute([
        ':username' => $username,
        ':email' => $email,
        ':password' => $password
    ]);

    // Insertar en Nube
    $stmtCloud = $pdo_cloud->prepare($insertSQL);
    $stmtCloud->execute([
        ':username' => $username,
        ':email' => $email,
        ':password' => $password
    ]);

    $message = "✅ Usuario registrado con éxito en ambas bases.";
}

    } catch (PDOException $e) {
        $message = "❌ Error: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<link rel="icon" href="logss.png">

<title>Registro de Usuarios</title>

<style>

body {
    font-family: Arial, sans-serif;
    background: #0b2a56;
    margin: 0;
    padding: 0;
    display: flex;
    height: 100vh;
    justify-content: center;
    align-items: center;
}
.container {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0px 6px 15px rgba(0,0,0,0.2);
    width: 350px;
}
h2 {
    text-align: center;
    color: #1e3c72;
    margin-bottom: 20px;
}
label {
    font-weight: bold;
    color: #333;
    display: block;
    margin-bottom: 6px;
}
input {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
}
button {
    width: 100%;
    padding: 12px;
    background: #1e3c72;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s ease;
}
button:hover {
    background: #2a5298;
    transform: scale(1.03);
}
.message {
    text-align: center;
    margin-top: 15px;
    font-weight: bold;
    color: green;
}
.error {
    color: red;
}
</style>
</head>
<body>
<div class="container">
<div class="navbar">
<a href="login.php">Salir</a>
</div>
    <h2>Registro de Usuarios</h2>
    <form method="POST" action="">
        <label for="username">Nombre de Usuario</label>
        <input type="text" id="username" name="username" required>

        <label for="email">Correo Electrónico</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Registrarse</button>
    </form>

    <?php if ($message): ?>
        <p class="message <?= strpos($message, 'Error') !== false ? 'error' : '' ?>">
            <?= $message ?>
        </p>
    <?php endif; ?>
</div>
</body>
</html>
