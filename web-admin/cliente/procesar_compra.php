<?php
session_start();
if (!isset($_SESSION['id_usr']) || ($_SESSION['rol'] ?? '') !== '2') {
    header('Location: ../index.php');
    exit();
}
include_once __DIR__ . '/../config/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = (int)($_POST['id_producto'] ?? 0);
    $nombre_envio = trim($_POST['nombre_envio'] ?? '');
    $direccion_envio = trim($_POST['direccion_envio'] ?? '');
    $telefono_envio = trim($_POST['telefono_envio'] ?? '');
    $metodo_pago = trim($_POST['metodo_pago'] ?? '');
    $nombre_tarjeta = trim($_POST['nombre_tarjeta'] ?? '');
    $numero_tarjeta = trim($_POST['numero_tarjeta'] ?? '');
    $vencimiento_tarjeta = trim($_POST['vencimiento_tarjeta'] ?? '');
    $cvv_tarjeta = trim($_POST['cvv_tarjeta'] ?? '');
    $pago_confirmado = trim($_POST['pago_confirmado'] ?? '0');
    $id_usr = (int)$_SESSION['id_usr'];
    if (!$id_producto || !$nombre_envio || !$direccion_envio || !$telefono_envio || !$metodo_pago) {
        $error = 'Completa todos los campos de compra.';
    } elseif ($nombre_tarjeta === '' || $numero_tarjeta === '' || $vencimiento_tarjeta === '' || $cvv_tarjeta === '') {
        $error = 'Completa todos los campos del pago con tarjeta.';
    } elseif ($pago_confirmado !== '1') {
        $error = 'Debes realizar el pago antes de confirmar la compra.';
    } elseif ($metodo_pago !== 'tarjeta') {
        $error = 'Método de pago inválido.';
    } else {
        $sql = "INSERT INTO compras (id_usr, id_producto, nombre_envio, direccion_envio, telefono_envio, metodo_pago, fecha_compra) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'iissss', $id_usr, $id_producto, $nombre_envio, $direccion_envio, $telefono_envio, $metodo_pago);
            if (mysqli_stmt_execute($stmt)) {
                header('Location: productos.php?msg=¡Compra realizada exitosamente!');
                exit();
            } else {
                $error = 'Error al procesar la compra.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = 'Error en la consulta.';
        }
    }
} else {
    $error = 'Acceso inválido.';
}
if (isset($error)) {
    header('Location: productos.php?msg=' . urlencode($error));
    exit();
}
