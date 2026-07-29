<?php
declare(strict_types=1);

header('Content-Type: application/json');
require __DIR__ . '/db.php';

$nombre = trim((string)($_POST['nombre'] ?? ''));
$usuario = trim((string)($_POST['usuario'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if ($nombre === '' || $usuario === '' || $email === '' || $password === '') {
  echo json_encode(['success' => false, 'error' => 'Faltan datos obligatorios.']);
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['success' => false, 'error' => 'El correo electrónico no es válido.']);
  exit;
}

if (strlen($password) < 6) {
  echo json_encode(['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres.']);
  exit;
}

$usuario = strtolower($usuario);
$email = strtolower($email);

$stmt = $mysqli->prepare("SELECT id FROM usuarios WHERE LOWER(usuario) = LOWER(?) OR LOWER(email) = LOWER(?) LIMIT 1");
$stmt->bind_param('ss', $usuario, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
  echo json_encode(['success' => false, 'error' => 'Usuario o correo ya registrado.']);
  $stmt->close();
  exit;
}
$stmt->close();

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $mysqli->prepare("INSERT INTO usuarios (nombre, usuario, email, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param('ssss', $nombre, $usuario, $email, $hash);

if ($stmt->execute()) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'error' => 'No se pudo registrar el usuario.']);
}

$stmt->close();
?>