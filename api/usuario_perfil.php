<?php
session_start();
header('Content-Type: application/json');
require __DIR__ . '/db.php';

if (!isset($_SESSION['usuario_id'])) {
  echo json_encode(['success' => false, 'error' => 'No autenticado']);
  exit;
}

$id = $_SESSION['usuario_id'];
$stmt = $mysqli->prepare("SELECT nombre, usuario, email, fecha_registro FROM usuarios WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
  echo json_encode(['success' => true] + $row);
} else {
  echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
}
$stmt->close();
?>
