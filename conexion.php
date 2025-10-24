<?php
// Conexión a PostgreSQL en la nube (Render compatible)

// Tomamos los datos desde variables de entorno para mayor seguridad
$db_host = getenv('DB_HOST') ?: 'aws-1-us-east-2.pooler.supabase.com';
$db_port = getenv('DB_PORT') ?: '5432';
$db_name = getenv('DB_NAME') ?: 'postgres3';
$db_user = getenv('DB_USER') ?: 'postgres.orzsdjjmyouhhxjfnemt';
$db_pass = getenv('DB_PASS') ?: 'Zv2sW23OhBVM5Tkz';

try {
    $pdo = new PDO(
        "pgsql:host={$db_host};port={$db_port};dbname={$db_name}",
        $db_user,
        $db_pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión exitosa a la base de datos en la nube!";
} catch (PDOException $e) {
    echo "❌ Error: No se puede conectar a la base de datos en la nube. " . $e->getMessage();
}
