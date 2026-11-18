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

// Verificar que el usuario sea un cliente (tip_usu=2)
$sqlInfo = "SELECT id_usr FROM usuarios WHERE id_usr = ? AND tip_usu = 2 LIMIT 1";
$stmtInfo = mysqli_prepare($conn, $sqlInfo);
if (!$stmtInfo) {
    header('Location: clientes_listar.php?msg=' . urlencode('Error al eliminar cliente'));
    exit();
}
mysqli_stmt_bind_param($stmtInfo, 'i', $id);
mysqli_stmt_execute($stmtInfo);
$res = mysqli_stmt_get_result($stmtInfo);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmtInfo);

if (!$row) {
    header('Location: clientes_listar.php?msg=' . urlencode('Cliente no encontrado'));
    exit();
}

// Eliminar el usuario (las claves foráneas eliminarán registros relacionados por cascada)
$sqlDel = "DELETE FROM usuarios WHERE id_usr = ? AND tip_usu = 2";
$stmtDel = mysqli_prepare($conn, $sqlDel);
if (!$stmtDel) {
    header('Location: clientes_listar.php?msg=' . urlencode('Error al eliminar'));
    exit();
}
mysqli_stmt_bind_param($stmtDel, 'i', $id);
if (mysqli_stmt_execute($stmtDel)) {
    mysqli_stmt_close($stmtDel);
    header('Location: clientes_listar.php?msg=' . urlencode('Cliente eliminado'));
    exit();
} else {
    mysqli_stmt_close($stmtDel);
    header('Location: clientes_listar.php?msg=' . urlencode('Error al eliminar cliente'));
    exit();
}

