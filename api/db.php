<?php
declare(strict_types=1);

header('Content-Type: application/json');

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'sumaqvida';
$charset = 'utf8mb4';

if (!extension_loaded('mysqli')) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'La extensión MySQLi no está disponible en PHP.']);
  exit;
}

$mysqli = @new mysqli($host, $user, $pass, $dbname);

if ($mysqli->connect_errno) {
  $fallback = @new mysqli($host, $user, $pass);
  if ($fallback && !$fallback->connect_error) {
    $fallback->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $fallback->close();
    $mysqli = @new mysqli($host, $user, $pass, $dbname);
  }
}

if ($mysqli->connect_errno) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'No se pudo conectar a la base de datos.']);
  exit;
}

$mysqli->set_charset($charset);

$mysqli->query("CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  peso DECIMAL(5,2) DEFAULT NULL,
  talla DECIMAL(5,2) DEFAULT NULL,
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
?>