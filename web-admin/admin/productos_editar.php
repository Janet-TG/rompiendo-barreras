<?php
session_start();
if (!isset($_SESSION['id_usr']) || ($_SESSION['rol'] ?? '') !== '1') {
    header('Location: ../index.php');
    exit();
}
include_once __DIR__ . '/../config/db.php';
$usuario_sesion = $_SESSION['nom_usr'] ?? $_SESSION['usuario'] ?? 'Desconocido';
$email_sesion = $_SESSION['mail'] ?? 'No disponible';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: productos_listar.php');
    exit();
}

$error_message = null;
$producto = null;

// Obtener datos actuales del producto
$stmt = mysqli_prepare($conn, "SELECT * FROM productos WHERE id_producto = ?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $producto = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
}

if (!$producto) {
    header('Location: productos_listar.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $imagen_url = trim($_POST['imagen_url'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);
    $categoria = trim($_POST['categoria'] ?? 'rehabilitacion');

    if ($nombre === '' || $precio === '') {
        $error_message = 'El nombre y el precio son obligatorios.';
    } elseif ($imagen_url !== '') {
        $urlValida = filter_var($imagen_url, FILTER_VALIDATE_URL);
        $scheme = strtolower((string)parse_url($imagen_url, PHP_URL_SCHEME));
        if (!$urlValida || !in_array($scheme, ['http', 'https'], true)) {
            $error_message = 'La URL de la imagen debe ser válida y comenzar con http:// o https://';
        }
    }

    if (!$error_message) {
        $sql = "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, imagen_url = ?, stock = ?, categoria = ? WHERE id_producto = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ssdsisi', $nombre, $descripcion, $precio, $imagen_url, $stock, $categoria, $id);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: productos_listar.php?msg=' . urlencode('Producto actualizado exitosamente'));
                exit();
            } else {
                $error_message = 'Error al actualizar el producto.';
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Producto - Admin</title>
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
                    <div class="panel-user-name"><?= htmlspecialchars($usuario_sesion) ?><span class="admin-badge">ADMIN</span></div>
                    <div class="panel-user-email"><?= htmlspecialchars($email_sesion) ?></div>
                </div>
                <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="container" style="max-width: 900px; padding: 2rem 0;">
        <div style="margin-bottom: 1.5rem;">
            <a href="productos_listar.php" style="color: #999; text-decoration: none; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-arrow-left"></i> Volver a Productos
            </a>
        </div>

        <h1 class="section-title">Editar Producto</h1>
        <p class="section-subtitle">Modifica la información del producto seleccionado</p>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form method="post">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Nombre del Producto *</label>
                        <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($producto['nombre']) ?>">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Precio *</label>
                        <input type="number" step="0.01" name="precio" class="form-control" required value="<?= htmlspecialchars($producto['precio']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-control" value="<?= htmlspecialchars($producto['stock']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Categoría</label>
                        <select name="categoria" class="form-select">
                            <option value="rehabilitacion" <?= $producto['categoria'] === 'rehabilitacion' ? 'selected' : '' ?>>Rehabilitación</option>
                            <option value="mano" <?= $producto['categoria'] === 'mano' ? 'selected' : '' ?>>Mano</option>
                            <option value="movilidad" <?= $producto['categoria'] === 'movilidad' ? 'selected' : '' ?>>Movilidad</option>
                            <option value="ejercicio" <?= $producto['categoria'] === 'ejercicio' ? 'selected' : '' ?>>Ejercicio</option>
                            <option value="soporte" <?= $producto['categoria'] === 'soporte' ? 'selected' : '' ?>>Soporte</option>
                            <option value="otros" <?= $producto['categoria'] === 'otros' ? 'selected' : '' ?>>Otros</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">URL de Imagen</label>
                        <input type="url" name="imagen_url" class="form-control" pattern="https?://.*" value="<?= htmlspecialchars($producto['imagen_url'] ?? '') ?>">
                        <small class="text-muted">Ingresa una URL real (http/https) de imagen</small>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn-submit" type="submit"><i class="fas fa-save"></i> Actualizar Producto</button>
                    <a href="productos_listar.php" class="btn-cancel"><i class="fas fa-times"></i> Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
