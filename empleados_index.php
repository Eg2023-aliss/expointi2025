$isRender = getenv('RENDER') === 'true';

// Config base remota
$db_remota = [
    'host' => 'aws-1-us-east-2.pooler.supabase.com',
    'port' => '5432',
    'dbname' => 'postgres3',
    'user' => 'postgres.orzsdjjmyouhhxjfnemt',
    'pass' => 'Zv2sW23OhBVM5Tkz',
    'sslmode' => 'require'
];

// Función para conectar
function getPDO($cfg) {
    $dsn = "pgsql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};sslmode={$cfg['sslmode']}";
    try {
        return new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    } catch (Exception $e) {
        die("Error en la conexión: " . $e->getMessage());
    }
}

// Solo usar remota
$pdo = getPDO($db_remota);
