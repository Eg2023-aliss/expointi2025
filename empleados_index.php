<?php
require_once 'conexion.php';  // ✅ Importar conexión

$pdo = getPDO();              // ✅ Obtener la conexión
$stmt = $pdo->query("SELECT * FROM empleados ORDER BY nombre_completo");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>📋 Lista de Empleados</title>
<link rel="icon" href="logo.png">
<!-- (Tu CSS y diseño aquí) -->
</head>
<body>
<header>
  <h1>📋 Lista de Empleados</h1>
</header>

<table>
  <tr>
    <th>ID</th>
    <th>Nombre Completo</th>
    <th>Área</th>
    <th>Cargo</th>
    <th>Acción</th>
  </tr>
  <?php foreach ($rows as $e): ?>
  <tr>
    <td><?= htmlspecialchars($e['id']) ?></td>
    <td><?= htmlspecialchars($e['nombre_completo']) ?></td>
    <td><?= htmlspecialchars($e['area']) ?></td>
    <td><?= htmlspecialchars($e['cargo']) ?></td>
    <td><button onclick="downloadCarnet(<?= $e['id'] ?>)">🎫 Carnet</button></td>
  </tr>
  <?php endforeach; ?>
</table>

<footer>© 2025 ExpoInti - Render & Supabase</footer>

<script>
function downloadCarnet(id) {
  window.location.href = `descargar_carnet.php?id=${id}`;
}
</script>
</body>
</html>
