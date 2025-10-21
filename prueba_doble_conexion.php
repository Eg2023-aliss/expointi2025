<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

// Configuración de BD
$db_local = [
    'host'=>'localhost','port'=>'5432','dbname'=>'postgres','user'=>'postgres','pass'=>'12345'
];
$db_cloud = [
    'host'=>'aws-1-us-east-2.pooler.supabase.com','port'=>'5432','dbname'=>'postgres',
    'user'=>'postgres.orzsdjjmyouhhxjfnemt','pass'=>'Zv2sW23OhBVM5Tkz'
];

function getPDO($cfg){
    $ssl = str_contains($cfg['host'],'supabase.com') ? ';sslmode=require' : '';
    $dsn = "pgsql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']}{$ssl}";
    return new PDO($dsn, $cfg['user'], $cfg['pass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
}

function runBoth($callback){
    global $db_local, $db_cloud;
    $callback(getPDO($db_local));
    $callback(getPDO($db_cloud));
}

$message = "";
if($_SERVER['REQUEST_METHOD']==='POST'){
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'],PASSWORD_BCRYPT);

    try{
        runBoth(function($pdo) use($username,$email,$password,&$message){
            // Crear tabla usuarios si no existe
            $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
                id SERIAL PRIMARY KEY,
                username VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL,
                password VARCHAR(255) NOT NULL
            )");

            // Verificar correo en curriculum
            $stmtCheck = $pdo->prepare("SELECT id_empleado FROM curriculum WHERE correo=:email");
            $stmtCheck->execute([':email'=>$email]);
            $empleado = $stmtCheck->fetch();

            if(!$empleado){
                $message = "❌ No puedes registrarte: el correo no corresponde a un empleado contratado.";
                return;
            }

            $stmt = $pdo->prepare("INSERT INTO usuarios (username,email,password) VALUES (:username,:email,:password)");
            $stmt->execute([':username'=>$username, ':email'=>$email, ':password'=>$password]);
            $message = "✅ Usuario registrado en ambas bases";
        });
    }catch(PDOException $e){
        $message = "❌ Error: ".$e->getMessage();
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
