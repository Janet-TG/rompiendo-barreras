<?php
session_start();
// sólo admin (rol numérico 1)
if (!isset($_SESSION['id_usr']) || ($_SESSION['rol'] ?? '') !== '1') {
  header('Location: ../index.php');
  exit();
}

include_once __DIR__ . '/../config/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: clientes_listar.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nombre'] ?? '');
    $mail = trim($_POST['email'] ?? '');
    $tel = trim($_POST['telefono'] ?? '');

    if ($nom === '') $error = 'El nombre es requerido.';
    elseif ($mail === '' || !filter_var($mail, FILTER_VALIDATE_EMAIL)) $error = 'Correo inválido.';
    elseif ($tel === '') $error = 'El teléfono es requerido.';
    
    if (!$error) {
        $sql = "UPDATE usuarios SET nom_usr=?, mail=?, telefono=? WHERE id_usr=? AND tip_usu=2";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sssi', $nom, $mail, $tel, $id);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: clientes_listar.php?msg=' . urlencode('Cliente actualizado'));
                exit();
            } else {
                $error = 'Error al actualizar.';
            }
        } else {
            $error = 'Error en la consulta.';
        }
    }
}

$row = null;
$stmt = mysqli_prepare($conn, "SELECT id_usr, nom_usr, mail, telefono FROM usuarios WHERE id_usr = ? AND tip_usu = 2 LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$row) {
    header('Location: clientes_listar.php');
    exit();
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar Cliente</title>
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
    .logout-btn { background: #dc3545; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-block; }
    .logout-btn:hover { background: #bb2d3b; color: white; }
    .section-title { font-size: 2rem; font-weight: 700; color: #333; margin: 2rem 0 0.5rem 0; }
    .section-subtitle { color: #999; font-size: 0.95rem; margin-bottom: 2rem; }
    .form-card { background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 5px solid #bc8fec; }
    .form-label { font-weight: 700; color: #333; margin-bottom: 0.7rem; font-size: 0.95rem; }
    .form-control, .form-select { border: 1.5px solid #e0e0e0; border-radius: 8px; padding: 0.75rem 1rem; transition: all 0.3s ease; }
    .form-control:focus, .form-select:focus { border-color: #bc8fec; box-shadow: 0 0 0 3px rgba(188, 143, 236, 0.1); }
    .btn-submit { background: linear-gradient(135deg, #bc8fec 0%, #a875d9 100%); color: white; border: none; padding: 0.9rem 2rem; border-radius: 8px; font-weight: 700; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(188, 143, 236, 0.3); }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(188, 143, 236, 0.4); }
    .btn-cancel { background: #6c757d; color: white; border: none; padding: 0.9rem 2rem; border-radius: 8px; font-weight: 700; text-decoration: none; display: inline-block; transition: all 0.3s ease; }
    .btn-cancel:hover { background: #5a6268; color: white; }
    .alert { border-radius: 8px; border: none; font-weight: 500; margin-bottom: 2rem; }
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
    <h1 class="section-title"><i class="fas fa-user-edit"></i> Editar Usuario</h1>
    <p class="section-subtitle">Actualiza los datos del usuario</p>

    <?php if ($error): ?>
      <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="form-card">
      <form id="form-cliente" method="post">
        <div class="mb-4">
          <label class="form-label"><i class="fas fa-user"></i> Nombre Completo</label>
          <input class="form-control" name="nombre" value="<?= htmlspecialchars($row['nom_usr'], ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="mb-4">
          <label class="form-label"><i class="fas fa-envelope"></i> Correo Electrónico</label>
          <input class="form-control" name="email" type="email" value="<?= htmlspecialchars($row['mail'], ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="mb-4">
          <label class="form-label"><i class="fas fa-phone"></i> Teléfono</label>
          <input class="form-control" name="telefono" value="<?= htmlspecialchars($row['telefono'], ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="d-grid gap-2">
          <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Guardar Cambios</button>
          <a href="clientes_listar.php" class="btn-cancel" style="text-align: center;"><i class="fas fa-times"></i> Cancelar</a>
        </div>
      </form>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/form-validation.js"></script>
</body>
</html>
