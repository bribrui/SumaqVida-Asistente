<?php
session_start();
header('Content-Type: application/json');
require __DIR__ . '/db.php';

if (!isset($_SESSION['usuario_id'])) {
  echo json_encode(['success' => false, 'error' => 'No autenticado']);
  exit;
}

$id = $_SESSION['usuario_id'];
$peso = $_POST['peso'] ?? null;
$talla = $_POST['talla'] ?? null;

if ($peso === null || $talla === null) {
  echo json_encode(['success' => false, 'error' => 'Faltan datos']);
  exit;
}

$stmt = $mysqli->prepare("UPDATE usuarios SET peso = ?, talla = ? WHERE id = ?");
$stmt->bind_param('ddi', $peso, $talla, $id);
if ($stmt->execute()) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'error' => 'Error al actualizar']);
}
$stmt->close();
?>
