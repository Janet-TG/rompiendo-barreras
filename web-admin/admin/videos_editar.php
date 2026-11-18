<?php
session_start();
if (!isset($_SESSION['id_usr']) || ($_SESSION['rol'] ?? '') !== '1') {
    header('Location: ../index.php');
  exit();
}

include_once __DIR__ . '/../config/db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
  header('Location: videos_list.php');
  exit();
}

$error = '';
$stmt = mysqli_prepare($conn, "SELECT * FROM videos WHERE id_video = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$video = mysqli_fetch_assoc($res);
if (!$video) {
  header('Location: videos_list.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $titulo = trim($_POST['titulo'] ?? '');
  $descripcion = trim($_POST['descripcion'] ?? '');
  $url_video = trim($_POST['url_video'] ?? '');

  // Subida opcional de archivo
  if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['video_file']['tmp_name'];
    $name = basename($_FILES['video_file']['name']);
    $targetDir = __DIR__ . '/../uploads/videos';
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    $targetPath = $targetDir . '/' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/','_', $name);
    if (move_uploaded_file($tmp, $targetPath)) {
      $url_video = 'uploads/videos/' . basename($targetPath);
      // intentar eliminar archivo anterior si estaba en uploads/videos
      if (strpos($video['url_video'], 'uploads/videos/') === 0) {
        $oldPath = __DIR__ . '/../' . $video['url_video'];
        if (is_file($oldPath)) @unlink($oldPath);
      }
    }
  }

  if ($titulo === '' || $url_video === '') {
    $error = 'Título y URL o archivo son obligatorios.';
  } else {
    $stmt = mysqli_prepare($conn, "UPDATE videos SET titulo = ?, descripcion = ?, url_video = ? WHERE id_video = ?");
    mysqli_stmt_bind_param($stmt, 'sssi', $titulo, $descripcion, $url_video, $id);
    if (mysqli_stmt_execute($stmt)) {
      header('Location: videos_list.php?msg=' . urlencode('Video actualizado'));
      exit();
    } else {
      $error = 'Error al actualizar: ' . mysqli_error($conn);
    }
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar Video - Admin</title>
  <link rel="stylesheet" href="../assets/css/bootstrap.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="p-4">
  <div class="container">
    <h2>Editar video</h2>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Título</label>
        <input name="titulo" class="form-control" required value="<?= htmlspecialchars($video['titulo']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" class="form-control"><?= htmlspecialchars($video['descripcion']) ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">URL del video (YouTube o ruta)</label>
        <input name="url_video" class="form-control" value="<?= htmlspecialchars($video['url_video']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">O subir nuevo archivo .mp4 (opcional)</label>
        <input type="file" name="video_file" accept="video/mp4" class="form-control">
      </div>
      <button class="btn btn-primary">Guardar cambios</button>
      <a href="videos_list.php" class="btn btn-link">Cancelar</a>
    </form>
  </div>
</body>
</html>
