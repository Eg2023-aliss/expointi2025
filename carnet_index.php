<?php
// =======================
// Configuración Supabase REST
// =======================
$SUPABASE_URL = 'https://tu-proyecto.supabase.co/rest/v1';
$SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'; // tu API key anon/public
$table = 'empleados';

// =======================
// Obtener ID del empleado
// =======================
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) die("ID inválido");

// =======================
// Hacer la consulta a Supabase vía REST
// =======================
$url = "$SUPABASE_URL/$table?id=eq.$id";
$options = [
    "http" => [
        "method" => "GET",
        "header" => "apikey: $SUPABASE_KEY\r\nAuthorization: Bearer $SUPABASE_KEY\r\nAccept: application/json\r\n"
    ]
];
$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response === false) {
    die("❌ Error al conectar con Supabase REST");
}

$data = json_decode($response, true);
if (empty($data)) {
    echo "Empleado no encontrado";
    exit;
}

$empleado = $data[0]; // Supabase devuelve un array

// =======================
// Mostrar datos del empleado
// =======================
echo "<h2>Empleado: " . htmlspecialchars($empleado['nombre_completo']) . "</h2>";

// Mostrar foto del carnet si existe (ajustada a 35x45 mm aprox 105x135 px)
if (!empty($empleado['imagen'])) {
    echo "<img src='" . htmlspecialchars($empleado['imagen']) . "' alt='Foto del carnet' width='105' height='135'>";
}
