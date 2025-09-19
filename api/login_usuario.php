<?php
session_start();
header('Content-Type: application/json');
require __DIR__ . '/db.php';

$usuario = $_POST['usuario'] ?? '';
$password = $_POST['password'] ?? '';

if ($usuario === '' || $password === '') {
  echo json_encode(['success' => false, 'error' => 'Faltan datos']);
  exit;
}

// Busca por usuario o correo
$stmt = $mysqli->prepare("SELECT id, nombre, usuario, email, peso, talla, password FROM usuarios WHERE usuario = ? OR email = ? LIMIT 1");
$stmt->bind_param('ss', $usuario, $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
  // Verifica la contraseña (usa password_verify si está hasheada)
  if (password_verify($password, $row['password'])) {
    $_SESSION['usuario_id'] = $row['id'];
    $_SESSION['usuario_nombre'] = $row['nombre'];
    $_SESSION['usuario_usuario'] = $row['usuario'];
    $_SESSION['usuario_email'] = $row['email'];
    $_SESSION['usuario_peso'] = $row['peso'];
    $_SESSION['usuario_talla'] = $row['talla'];
    echo json_encode([
      'success' => true,
      'nombre' => $row['nombre'],
      'usuario' => $row['usuario'],
      'email' => $row['email'],
      'peso' => $row['peso'],
      'talla' => $row['talla']
    ]);
  } else {
    echo json_encode(['success' => false, 'error' => 'Contraseña incorrecta']);
  }
} else {
  echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
}
$stmt->close();
?>
