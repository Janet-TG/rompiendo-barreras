<?php
session_start();
if (!isset($_SESSION['id_usr']) || ($_SESSION['rol'] ?? '') !== '1') {
    header('Location: ../index.php');
  exit();
}

include_once __DIR__ . '/../config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $titulo = trim($_POST['titulo'] ?? '');
  $descripcion = trim($_POST['descripcion'] ?? '');
  $url_video = trim($_POST['url_video'] ?? '');

  // Manejo de subida opcional
  if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['video_file']['tmp_name'];
    $name = basename($_FILES['video_file']['name']);
    $targetDir = __DIR__ . '/../uploads/videos';
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    $targetPath = $targetDir . '/' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/','_', $name);
    if (move_uploaded_file($tmp, $targetPath)) {
      // Guardar ruta relativa
      $url_video = 'uploads/videos/' . basename($targetPath);
    }
  }

  if ($titulo === '' || $url_video === '') {
    $error = 'Título y URL o archivo son obligatorios.';
  } else {
    $created_by = $_SESSION['id_usr'] ?? null;
    $stmt = mysqli_prepare($conn, "INSERT INTO videos (titulo, descripcion, url_video, created_by) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sssi', $titulo, $descripcion, $url_video, $created_by);
    if (mysqli_stmt_execute($stmt)) {
      header('Location: videos_list.php?msg=' . urlencode('Video creado'));
      exit();
    } else {
      $error = 'Error al guardar: ' . mysqli_error($conn);
    }
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Crear Video - Admin</title>
  <link rel="stylesheet" href="../assets/css/bootstrap.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="p-4">
  <div class="container">
    <h2>Nuevo video</h2>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Título</label>
        <input name="titulo" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" class="form-control"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">URL del video (YouTube o ruta)</label>
        <input name="url_video" class="form-control" placeholder="https://... o uploads/videos/archivo.mp4">
      </div>
      <div class="mb-3">
        <label class="form-label">O subir archivo .mp4 (opcional)</label>
        <input type="file" name="video_file" accept="video/mp4" class="form-control">
      </div>
      <button class="btn btn-primary">Guardar</button>
      <a href="videos_list.php" class="btn btn-link">Cancelar</a>
    </form>
  </div>
</body>
</html>
