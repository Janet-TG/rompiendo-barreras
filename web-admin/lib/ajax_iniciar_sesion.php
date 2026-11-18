<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

// debug log (temporal)
$logFile = __DIR__ . '/../logs/ajax_auth.log';
if (!is_dir(dirname($logFile))) @mkdir(dirname($logFile), 0755, true);
function llog($msg) {
    global $logFile;
    @file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
}
llog("AJAX login request from " . ($_SERVER['REMOTE_ADDR'] ?? 'CLI'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    llog('invalid method: ' . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$mail = trim($_POST['mail'] ?? '');
$pass = $_POST['pass'] ?? '';
if ($mail === '' || $pass === '') {
    llog("missing fields: mail='" . $mail . "'");
    echo json_encode(['success' => false, 'error' => 'Completa todos los campos']);
    exit;
}

$sql = "SELECT id_usr, nom_usr, mail, pass, tip_usu FROM usuarios WHERE mail = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    llog("db prepare failed: " . mysqli_error($conn));
    echo json_encode(['success' => false, 'error' => 'Error interno (prep)']);
    exit;
}
mysqli_stmt_bind_param($stmt, 's', $mail);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$user) {
    llog("user not found for mail='" . $mail . "'");
    echo json_encode(['success' => false, 'error' => 'Correo o contraseña incorrectos']);
    exit;
}

// Comparación directa sin hash
if ($pass !== $user['pass']) {
    llog("password mismatch for user id=" . ($user['id_usr'] ?? 'n/a'));
    echo json_encode(['success' => false, 'error' => 'Correo o contraseña incorrectos']);
    exit;
}

// Set session
$_SESSION['id_usr'] = $user['id_usr'];
$_SESSION['nom_usr'] = $user['nom_usr'];
$_SESSION['mail'] = $user['mail'];
$_SESSION['tip_usu'] = $user['tip_usu'];
// compatibility keys used elsewhere
$_SESSION['login_success'] = true;
$_SESSION['rol'] = (string)$user['tip_usu'];

llog("login OK for id=" . $user['id_usr'] . " mail=" . $user['mail'] . " tip_usu=" . $user['tip_usu']);

// Decide redirect
$basePath = str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/')));
if ($basePath === '' || $basePath === '.') {
    $basePath = '';
}
if ($user['tip_usu'] == 1) {
    $redirect = $basePath . '/admin/';
} else {
    $redirect = $basePath . '/cliente/';
}

llog("responding success, redirect=" . $redirect);
echo json_encode(['success' => true, 'redirect' => $redirect, 'tip_usu' => $user['tip_usu']]);
exit;

