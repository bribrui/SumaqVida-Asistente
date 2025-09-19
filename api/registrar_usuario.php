<?php
header('Content-Type: application/json');
require __DIR__ . '/db.php';

$nombre = $_POST['nombre'] ?? '';
$usuario = $_POST['usuario'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($nombre === '' || $usuario === '' || $email === '' || $password === '') {
  echo json_encode(['success' => false, 'error' => 'Faltan datos']);
  exit;
}

// Verifica si el usuario o email ya existen
$stmt = $mysqli->prepare("SELECT id FROM usuarios WHERE usuario = ? OR email = ? LIMIT 1");
$stmt->bind_param('ss', $usuario, $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
  echo json_encode(['success' => false, 'error' => 'Usuario o correo ya registrado']);
  $stmt->close();
  exit;
}
$stmt->close();

// Hashea la contraseña
$hash = password_hash($password, PASSWORD_DEFAULT);

// Inserta el usuario
$stmt = $mysqli->prepare("INSERT INTO usuarios (nombre, usuario, email, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param('ssss', $nombre, $usuario, $email, $hash);
if ($stmt->execute()) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'error' => 'Error al registrar usuario']);
}
$stmt->close();
?>