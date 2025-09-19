<?php
header('Content-Type: application/json');
$mysqli = new mysqli('localhost', 'root', '', 'sumaqvida');
if ($mysqli->connect_errno) {
  http_response_code(500);
  echo json_encode(['error' => 'Error de conexión a la base de datos']);
  exit;
}
?>