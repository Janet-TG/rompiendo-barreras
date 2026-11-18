<?php
session_start();
if (!isset($_SESSION['id_usr']) || ($_SESSION['rol'] ?? '') !== '1') {
    header('Location: ../index.php');
    exit();
}

include_once __DIR__ . '/../config/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: productos_listar.php');
    exit();
}

$sql = "DELETE FROM productos WHERE id_producto = ?";
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

header('Location: productos_listar.php?msg=' . urlencode('Producto eliminado correctamente'));
exit();
