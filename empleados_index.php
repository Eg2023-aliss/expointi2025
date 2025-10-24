<?php
session_start();
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('fpdf/fpdf.php');
require_once 'conexion.php';

// 🔐 Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
  header("Location: login.php");
  exit;
}

// 🔗 Obtener conexión activa (usa la nube por defecto)
try {
  $pdo = getPDO();
  $stmt = $pdo->query("SELECT id, nombre_completo, area, cargo FROM empleados ORDER BY id ASC");
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("❌ Error al obtener empleados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>📋 Lista de Empleados</title>
<link rel="icon" href="logo.png">
<style>
  body {
    font-family: Arial, sans-serif;
    margin: 0;
    background: #f4f6f8;
    color: #333;
  }
  header {
    background: #003366;
    color: white;
    padding: 15px;
    text-align: center;
    font-size: 1.5em;
  }
  table {
    width: 90%;
    margin: 30px auto;
    border-collapse: collapse;
    background: white;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    border-radius: 8px;
    overflow: hidden;
  }
  th, td {
    padding: 12px;
    text-align: left;
  }
  th {
    background: #004080;
    color: white;
  }
  tr:nth-child(even) {
    background: #f2f2f2;
  }
  button {
    background: #0066cc;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s;
  }
  button:hover {
    background: #004d99;
  }
  footer {
    text-align: center;
    padding: 15px;
    background: #003366;
    color: white;
    position: fixed;
    bottom: 0;
    width: 100%;
  }
</style>
</head>
<body>

<header>📋 Lista de Empleados</header>

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
    <td>
      <button onclick="downloadCarnet(<?= $e['id'] ?>)">🎫 Carnet</button>
    </td>
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
