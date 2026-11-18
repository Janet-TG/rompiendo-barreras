<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php?error=Acceso inválido");
    exit;
}

$mail = trim($_POST['mail'] ?? '');
$pass = $_POST['pass'] ?? '';

if ($mail === '' || $pass === '') {
    header("Location: ../login.php?error=Completa todos los campos");
    exit;
}

// Buscar usuario
$sql = "SELECT id_usr, nom_usr, mail, pass, tip_usu FROM usuarios WHERE mail = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    header("Location: ../login.php?error=Error interno");
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $mail);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Usuario no encontrado
if (!$user) {
    header("Location: ../login.php?error=Correo o contraseña incorrectos");
    exit;
}

// Validar contraseña
if (!password_verify($pass, $user['pass'])) {
    header("Location: ../login.php?error=Correo o contraseña incorrectos");
    exit;
}

// Crear sesión
$_SESSION['id_usr']   = $user['id_usr'];
$_SESSION['nom_usr']  = $user['nom_usr'];
$_SESSION['mail']     = $user['mail'];
$_SESSION['tip_usu']  = $user['tip_usu'];

// Redirección según rol
if ($user['tip_usu'] == 1) {
    // ADMIN
    header("Location: ../admin/");
    exit;
} else {
    // CLIENTE
    header("Location: ../cliente/");
    exit;
}
