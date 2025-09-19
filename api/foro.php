<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/db.php';

$action = $_GET['action'] ?? '';

if ($action === 'add_post') {
  // Mejor usar sentencias preparadas para evitar inyección SQL
  $stmt = $mysqli->prepare("INSERT INTO foro_posts (user, content, media_type, media_url, hashtags, receta_url) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param('ssssss', $_POST['user'], $_POST['content'], $_POST['media_type'], $_POST['media_url'], $_POST['hashtags'], $_POST['receta_url']);
  if ($stmt->execute()) {
    echo json_encode(['success'=>true]);
  } else {
    echo json_encode(['success'=>false, 'error'=>$stmt->error]);
  }
  $stmt->close();
  exit;
}

if ($action === 'get_posts') {
  $res = $mysqli->query("SELECT * FROM foro_posts ORDER BY date DESC");
  $posts = [];
  while ($row = $res->fetch_assoc()) {
    $post_id = $row['id'];
    // Comentarios
    $cres = $mysqli->query("SELECT user, text FROM foro_comments WHERE post_id=$post_id ORDER BY id ASC");
    $comments = [];
    while ($crow = $cres->fetch_assoc()) $comments[] = $crow;
    // Reacciones
    $rres = $mysqli->query("SELECT reaction, COUNT(*) as total FROM foro_reactions WHERE post_id=$post_id GROUP BY reaction");
    $reactions = [];
    while ($rrow = $rres->fetch_assoc()) $reactions[$rrow['reaction']] = $rrow['total'];
    // Estrellas
    $sres = $mysqli->query("SELECT AVG(stars) as avg FROM foro_stars WHERE post_id=$post_id");
    $stars = ($srow = $sres->fetch_assoc()) && $srow['avg'] !== null ? round($srow['avg'],2) : 0;
    $row['comments'] = $comments;
    $row['reactions'] = $reactions;
    $row['stars'] = $stars;
    $posts[] = $row;
  }
  echo json_encode($posts);
  exit;
}

// Endpoint para agregar comentario
if ($action === 'add_comment') {
  $stmt = $mysqli->prepare("INSERT INTO foro_comments (post_id, user, text) VALUES (?, ?, ?)");
  $stmt->bind_param('iss', $_POST['post_id'], $_POST['user'], $_POST['text']);
  if ($stmt->execute()) {
    echo json_encode(['success'=>true]);
  } else {
    echo json_encode(['success'=>false, 'error'=>$stmt->error]);
  }
  $stmt->close();
  exit;
}

// Endpoint para agregar reacción
if ($action === 'add_reaction') {
  $stmt = $mysqli->prepare("INSERT INTO foro_reactions (post_id, user, reaction) VALUES (?, ?, ?)");
  $stmt->bind_param('isi', $_POST['post_id'], $_POST['user'], $_POST['reaction']);
  if ($stmt->execute()) {
    echo json_encode(['success'=>true]);
  } else {
    echo json_encode(['success'=>false, 'error'=>$stmt->error]);
  }
  $stmt->close();
  exit;
}

// Endpoint para agregar estrellas
if ($action === 'add_star') {
  $stmt = $mysqli->prepare("INSERT INTO foro_stars (post_id, user, stars) VALUES (?, ?, ?)");
  $stmt->bind_param('isi', $_POST['post_id'], $_POST['user'], $_POST['stars']);
  if ($stmt->execute()) {
    echo json_encode(['success'=>true]);
  } else {
    echo json_encode(['success'=>false, 'error'=>$stmt->error]);
  }
  $stmt->close();
  exit;
}

// Puedes agregar aquí más endpoints si es necesario
?>