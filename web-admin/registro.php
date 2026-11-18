<?php
include_once __DIR__ . '/config/db.php';

$error_message = '';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    // por defecto registramos como cliente (2)
    $tip_usu = 2;

    if ($nombre === '' || $email === '' || $password === '' || $telefono === '') {
        $error_message = 'Completa todos los campos requeridos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Correo inválido.';
    } else {
        // verificar si ya existe el correo (procedimental)
        $checkSql = "SELECT id_usr FROM usuarios WHERE mail = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $checkSql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            if (mysqli_fetch_assoc($res)) {
                $error_message = 'Ya existe una cuenta con ese correo.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_message = 'Error en la verificación de usuarios.';
        }
    }

    if (!$error_message) {
        // hasheamos la contraseña
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insertSql = "INSERT INTO usuarios (nom_usr, mail, pass, telefono, tip_usu) VALUES (?, ?, ?, ?, ?)";
        $stmt2 = mysqli_prepare($conn, $insertSql);
        if ($stmt2) {
            mysqli_stmt_bind_param($stmt2, 'ssssi', $nombre, $email, $hash, $telefono, $tip_usu);
            if (mysqli_stmt_execute($stmt2)) {
                $mensaje = 'Registro completado correctamente. Puedes <a href="iniciar_sesion.php">iniciar sesión</a>.';
            } else {
                $error_message = 'Error al crear el usuario. Intenta de nuevo.';
            }
            mysqli_stmt_close($stmt2);
        } else {
            $error_message = 'Error en la consulta de inserción.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="card p-4" style="width: 100%; max-width: 480px;">
            <h2 class="card-title text-center mb-4">Registrarse</h2>
            <?php if ($mensaje): ?>
                <div class="alert alert-success" role="alert"><?= $mensaje ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert"><?= $error_message ?></div>
            <?php endif; ?>

            <form id="form-registro" action="registro.php" method="POST">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre completo</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="telefono" class="form-label">Número de teléfono</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="Ej: 555-1234" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn-nav-rojo">registrarse</button>
                </div>
            </form>

            <div class="mt-3 text-center">
                <small>¿Ya tienes cuenta? <a href="iniciar_sesion.php">iniciar sesion</a></small>
            </div>
        </div>
    </div>
    <script src="assets/js/form-validation.js"></script>
</body>
</html>
