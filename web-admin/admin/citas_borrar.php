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
    header('Location: citas_listar.php?msg=' . urlencode('ID de cita inválido'));
    exit();
}

$permitidaHistorial = false;
$sqlValidar = "SELECT estado, fecha FROM citas WHERE id_cita = ? LIMIT 1";
$stmtValidar = mysqli_prepare($conn, $sqlValidar);
if ($stmtValidar) {
    mysqli_stmt_bind_param($stmtValidar, 'i', $id);
    mysqli_stmt_execute($stmtValidar);
    $resValidar = mysqli_stmt_get_result($stmtValidar);
    $cita = mysqli_fetch_assoc($resValidar);
    mysqli_stmt_close($stmtValidar);

    if ($cita) {
        $estado = $cita['estado'] ?? 'pendiente';
        $fecha = $cita['fecha'] ?? null;
        $hoy = date('Y-m-d');
        if ($estado === 'cancelada' || ($fecha !== null && $fecha < $hoy)) {
            $permitidaHistorial = true;
        }
    }
}

if (!$permitidaHistorial) {
    header('Location: citas_listar.php?msg=' . urlencode('Solo puedes borrar citas del historial'));
    exit();
}

$sql = "DELETE FROM citas WHERE id_cita = ?";
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
header('Location: citas_listar.php?msg=' . urlencode('Cita eliminada del historial correctamente'));
exit();
