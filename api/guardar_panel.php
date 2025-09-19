<?php
session_start();
header('Content-Type: application/json');
require __DIR__ . '/db.php';

if (!isset($_SESSION['usuario_id'])) {
  echo json_encode(['success' => false, 'error' => 'No autenticado']);
  exit;
}

$id_usuario = $_SESSION['usuario_id'];
$glucosa = $_POST['valor'] ?? null;
$insulina = $_POST['insulina'] ?? null;
$actividad = $_POST['actividad'] ?? '';
$fecha = date('Y-m-d H:i:s');

if ($glucosa === null || $insulina === null || $actividad === '') {
  echo json_encode(['success' => false, 'error' => 'Faltan datos']);
  exit;
}

$stmt = $mysqli->prepare("INSERT INTO registro_panel (id_usuario, fecha, glucosa, insulina, actividad) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param('isdss', $id_usuario, $fecha, $glucosa, $insulina, $actividad);
if ($stmt->execute()) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'error' => 'Error al guardar']);
}
$stmt->close();
?>