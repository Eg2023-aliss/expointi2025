<?php
// Configuración de la base de datos en la nube
$db_config = [
    'host' => 'aws-1-us-east-2.pooler.supabase.com', // host de tu base de datos en la nube
    'port' => '6543',                                // puerto correcto
    'dbname' => 'postgres3',                         // nombre de la base de datos
    'user' => 'postgres.orzsdjjmyouhhxjfnemt',      // usuario
    'pass' => 'Zv2sW23OhBVM5Tkz'                    // contraseña
];

try {
    $pdo = new PDO(
        "pgsql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['dbname']}",
        $db_config['user'],
        $db_config['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión exitosa a la base de datos en la nube!";
} catch (PDOException $e) {
    echo "❌ Error: No se puede conectar a la base de datos en la nube. " . $e->getMessage();
}
?>
