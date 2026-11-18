<?php
session_start();
// sólo admin (rol numérico 1)
if (!isset($_SESSION['id_usr']) || ($_SESSION['rol'] ?? '') !== '1') {
  header('Location: ../index.php');
  exit();
}

include_once __DIR__ . '/../config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nom_usr = trim($_POST['nom_usr'] ?? '');
  $mail = trim($_POST['mail'] ?? '');
  $pass = $_POST['pass'] ?? '';
  $tel_usr = trim($_POST['tel_usr'] ?? '');

  if ($nom_usr === '' || $mail === '' || $pass === '' || $tel_usr === '') {
    $error = 'Nombre, correo, contraseña y teléfono son requeridos.';
  } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    $error = 'Correo inválido.';
  } elseif (strlen($pass) < 6) {
    $error = 'La contraseña debe tener al menos 6 caracteres.';
  } else {
    $stmt = mysqli_prepare($conn, "SELECT id_usr FROM usuarios WHERE mail = ? LIMIT 1");
    if ($stmt) {
      mysqli_stmt_bind_param($stmt, 's', $mail);
      mysqli_stmt_execute($stmt);
      $resu = mysqli_stmt_get_result($stmt);
      if (mysqli_fetch_assoc($resu)) {
        $error = 'Ya existe un usuario con ese correo.';
      }
      mysqli_stmt_close($stmt);
    } else {
      $error = 'Error al validar el correo.';
    }
  }

  if (!$error) {
    $hash = $pass;
    $stmt2 = mysqli_prepare($conn, "INSERT INTO usuarios (nom_usr, mail, pass, telefono, tip_usu) VALUES (?, ?, ?, ?, 2)");
    if ($stmt2) {
      mysqli_stmt_bind_param($stmt2, 'ssss', $nom_usr, $mail, $hash, $tel_usr);
      if (mysqli_stmt_execute($stmt2)) {
        mysqli_stmt_close($stmt2);
        header('Location: clientes_listar.php?msg=' . urlencode('Usuario creado'));
        exit();
      } else {
        $error = 'Error al crear el usuario.';
      }
      mysqli_stmt_close($stmt2);
    } else {
      $error = 'Error en la consulta de usuario.';
    }
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Crear Usuario</title>
  <link rel="stylesheet" href="../assets/css/bootstrap.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    body { font-family: 'Montserrat', sans-serif; background: #f5f5f5; }
    .panel-header { background: white; border-bottom: 1px solid #e0e0e0; padding: 1.5rem 0; }
    .panel-container { display: flex; gap: 2rem; align-items: center; }
    .panel-logo { display: flex; align-items: center; gap: 0.8rem; text-decoration: none; color: inherit; }
    .panel-logo img { width: 50px; }
    .panel-logo-text small { color: #999; display: block; font-size: 0.75rem; }
    .panel-logo-text strong { color: #333; font-weight: 700; }
    .admin-badge { background: #dc3545; color: white; padding: 0.3rem 0.7rem; border-radius: 4px; font-weight: 700; font-size: 0.75rem; margin-left: 0.5rem; }
    .panel-user-info { margin-left: auto; text-align: right; }
    .panel-user-name { font-weight: 700; color: #333; font-size: 0.95rem; }
    .panel-user-email { font-size: 0.85rem; color: #999; }
    .logout-btn { background: #dc3545; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-block; }
    .logout-btn:hover { background: #bb2d3b; color: white; }
    .section-title { font-size: 2rem; font-weight: 700; color: #333; margin: 2rem 0 0.5rem 0; }
    .section-subtitle { color: #999; font-size: 0.95rem; margin-bottom: 2rem; }
    .form-card { background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 5px solid #10b981; }
    .form-label { font-weight: 700; color: #333; margin-bottom: 0.7rem; font-size: 0.95rem; }
    .form-control, .form-select { border: 1.5px solid #e0e0e0; border-radius: 8px; padding: 0.75rem 1rem; transition: all 0.3s ease; }
    .form-control:focus, .form-select:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); }
    .btn-submit { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; padding: 0.9rem 2rem; border-radius: 8px; font-weight: 700; width: 100%; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4); }
    .btn-cancel { background: #6c757d; color: white; border: none; padding: 0.9rem 2rem; border-radius: 8px; font-weight: 700; text-decoration: none; display: inline-block; transition: all 0.3s ease; }
    .btn-cancel:hover { background: #5a6268; color: white; }
    .alert { border-radius: 8px; border: none; font-weight: 500; margin-bottom: 2rem; }
    .user-option-section { background: #f9fafb; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; }
    .option-radio { display: flex; align-items: center; gap: 0.8rem; padding: 0.8rem; cursor: pointer; border-radius: 6px; transition: all 0.3s ease; }
    .option-radio:hover { background: white; }
    .option-radio input { cursor: pointer; }
    .new-user-section { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); padding: 2rem; border-radius: 10px; border: 1px solid #bbf7d0; margin-bottom: 2rem; }
    .new-user-section h5 { color: #15803d; font-weight: 700; margin-bottom: 1rem; }
  </style>
</head>
<body>
  <div class="panel-header">
    <div class="container">
      <div class="panel-container">
        <a href="index.php" class="panel-logo">
          <img src="../assets/img/logo.png" alt="Logo">
          <div class="panel-logo-text"><small>Rompiendo</small><strong>Barreras</strong></div>
        </a>
        <div class="panel-user-info">
          <div class="panel-user-name"><?= htmlspecialchars($_SESSION['nom_usr'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?><span class="admin-badge">ADMIN</span></div>
        </div>
        <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
      </div>
    </div>
  </div>

  <div class="container" style="max-width: 800px; padding: 2rem 0;">
    <div style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
      <a href="clientes_listar.php" style="color: #999; text-decoration: none; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-arrow-left"></i> Volver a Usuarios
      </a>
      <a href="index.php" style="color: #999; text-decoration: none; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-home"></i> Panel Principal
      </a>
    </div>
    <h1 class="section-title"><i class="fas fa-user-plus"></i> Nuevo Usuario</h1>
    <p class="section-subtitle">Registra un nuevo usuario en el sistema</p>

    <?php if ($error): ?>
      <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="form-card">
      <form id="form-paciente" method="post">
        <div class="mb-3">
          <label class="form-label">Nombre de usuario</label>
          <input class="form-control" name="nom_usr" placeholder="Ej: juan.perez" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Correo electrónico</label>
          <input class="form-control" name="mail" type="email" placeholder="correo@ejemplo.com" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Contraseña</label>
          <input class="form-control" name="pass" type="password" placeholder="Mínimo 6 caracteres" required>
        </div>
        <div class="mb-0">
          <label class="form-label">Teléfono</label>
          <input class="form-control" name="tel_usr" placeholder="Teléfono del usuario" required>
        </div>

        <div class="d-grid gap-2 mt-4">
          <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Crear Usuario</button>
          <a href="clientes_listar.php" class="btn-cancel" style="text-align: center;"><i class="fas fa-times"></i> Cancelar</a>
        </div>
      </form>
    </div>
  </div>
  <script src="../assets/js/form-validation.js"></script>
</body>
</html>
