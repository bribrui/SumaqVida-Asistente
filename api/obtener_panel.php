<?php
session_start();
header('Content-Type: application/json');
require __DIR__ . '/db.php';

if (!isset($_SESSION['usuario_id'])) {
  echo json_encode(['success' => false, 'error' => 'No autenticado']);
  exit;
}

$id_usuario = $_SESSION['usuario_id'];
$stmt = $mysqli->prepare("SELECT fecha, glucosa, insulina, actividad FROM registro_panel WHERE id_usuario = ? ORDER BY fecha DESC LIMIT 14");
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$registros = [];
while ($row = $result->fetch_assoc()) {
  $registros[] = $row;
}
$stmt->close();
echo json_encode(['success' => true, 'registros' => $registros]);
?>