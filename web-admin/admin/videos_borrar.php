<?php
session_start();
if (!isset($_SESSION['id_usr']) || ($_SESSION['rol'] ?? '') !== '1') {
    header('Location: ../index.php');
  exit();
}

include_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: videos_list.php');
  exit();
}

$id = intval($_POST['id'] ?? 0);
$csrf = $_POST['csrf_token'] ?? '';
if (!$id || empty($csrf) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
  header('Location: videos_list.php?msg=' . urlencode('Operación inválida'));
  exit();
}

// obtener registro para borrar archivo si aplica
$stmt = mysqli_prepare($conn, "SELECT url_video FROM videos WHERE id_video = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);

if ($row) {
  // si es un archivo local en uploads/videos, eliminarlo
  if (strpos($row['url_video'], 'uploads/videos/') === 0) {
    $path = __DIR__ . '/../' . $row['url_video'];
    if (is_file($path)) @unlink($path);
  }

  $stmt = mysqli_prepare($conn, "DELETE FROM videos WHERE id_video = ?");
  mysqli_stmt_bind_param($stmt, 'i', $id);
  mysqli_stmt_execute($stmt);
}

header('Location: videos_list.php?msg=' . urlencode('Video eliminado'));
exit();
