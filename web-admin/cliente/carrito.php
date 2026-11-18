<?php
session_start();
if (!isset($_SESSION['id_usr']) || ($_SESSION['rol'] ?? '') !== '2') {
    header('Location: ../index.php');
    exit();
}

include_once __DIR__ . '/../config/db.php';

$usuario_sesion = $_SESSION['nom_usr'] ?? $_SESSION['usuario'] ?? 'Desconocido';
$email_sesion = $_SESSION['mail'] ?? 'No disponible';
$id_usr = (int)$_SESSION['id_usr'];

if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
$carrito = $_SESSION['carrito'];

if (isset($_GET['action'])) {
    $action = trim((string)$_GET['action']);
    $id = (int)($_GET['id'] ?? 0);

    if ($action === 'remove' && $id > 0 && isset($carrito[$id])) {
        unset($carrito[$id]);
        $_SESSION['carrito'] = $carrito;
        header('Location: carrito.php?msg=' . urlencode('Producto eliminado del carrito'));
        exit();
    }

    if ($action === 'clear') {
        $_SESSION['carrito'] = [];
        header('Location: carrito.php?msg=' . urlencode('Carrito vaciado'));
        exit();
    }
}

$msg = isset($_GET['msg']) ? htmlspecialchars($_GET['msg'], ENT_QUOTES, 'UTF-8') : '';
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['accion'] ?? '') === 'comprar_carrito')) {
    $nombre_envio = trim($_POST['nombre_envio'] ?? '');
    $direccion_envio = trim($_POST['direccion_envio'] ?? '');
    $telefono_envio = trim($_POST['telefono_envio'] ?? '');
    $metodo_pago = trim($_POST['metodo_pago'] ?? '');

    $nombre_tarjeta = trim($_POST['nombre_tarjeta'] ?? '');
    $numero_tarjeta = trim($_POST['numero_tarjeta'] ?? '');
    $vencimiento_tarjeta = trim($_POST['vencimiento_tarjeta'] ?? '');
    $cvv_tarjeta = trim($_POST['cvv_tarjeta'] ?? '');
    $pago_confirmado = trim($_POST['pago_confirmado'] ?? '0');

    if (empty($carrito)) {
        $error_message = 'Tu carrito está vacío.';
    } elseif ($nombre_envio === '' || $direccion_envio === '' || $telefono_envio === '' || $metodo_pago === '') {
        $error_message = 'Completa todos los campos de envío.';
    } elseif ($nombre_tarjeta === '' || $numero_tarjeta === '' || $vencimiento_tarjeta === '' || $cvv_tarjeta === '') {
        $error_message = 'Completa todos los campos del pago con tarjeta.';
    } elseif ($pago_confirmado !== '1') {
        $error_message = 'Debes realizar el pago antes de confirmar la compra.';
    } elseif ($metodo_pago !== 'tarjeta') {
        $error_message = 'Método de pago inválido.';
    } else {
        $insertSql = "INSERT INTO compras (id_usr, id_producto, nombre_envio, direccion_envio, telefono_envio, metodo_pago, fecha_compra) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmtInsert = mysqli_prepare($conn, $insertSql);
        if (!$stmtInsert) {
            $error_message = 'Error al preparar la compra.';
        } else {
            $compraOk = true;
            foreach ($carrito as $id_producto => $cantidad) {
                $id_producto = (int)$id_producto;
                $cantidad = (int)$cantidad;
                if ($id_producto <= 0 || $cantidad <= 0) {
                    continue;
                }

                $stmtCheck = mysqli_prepare($conn, "SELECT id_producto FROM productos WHERE id_producto = ? LIMIT 1");
                $existe = false;
                if ($stmtCheck) {
                    mysqli_stmt_bind_param($stmtCheck, 'i', $id_producto);
                    mysqli_stmt_execute($stmtCheck);
                    $resCheck = mysqli_stmt_get_result($stmtCheck);
                    $existe = (bool)mysqli_fetch_assoc($resCheck);
                    mysqli_stmt_close($stmtCheck);
                }
                if (!$existe) {
                    continue;
                }

                for ($i = 0; $i < $cantidad; $i++) {
                    mysqli_stmt_bind_param($stmtInsert, 'iissss', $id_usr, $id_producto, $nombre_envio, $direccion_envio, $telefono_envio, $metodo_pago);
                    if (!mysqli_stmt_execute($stmtInsert)) {
                        $compraOk = false;
                        break 2;
                    }
                }
            }
            mysqli_stmt_close($stmtInsert);

            if ($compraOk) {
                $_SESSION['carrito'] = [];
                header('Location: carrito.php?msg=' . urlencode('Compra del carrito realizada exitosamente'));
                exit();
            }
            $error_message = 'No se pudo completar la compra del carrito.';
        }
    }
}

$carrito = $_SESSION['carrito'];
$productos = [];
$total = 0.0;

if (!empty($carrito)) {
    $ids = array_keys($carrito);
    $ids = array_map('intval', $ids);
    $ids = array_filter($ids, function ($value) {
        return $value > 0;
    });

    if (!empty($ids)) {
        $idsSql = implode(',', $ids);
        $sql = "SELECT id_producto, nombre, descripcion, precio, imagen_url FROM productos WHERE id_producto IN ($idsSql)";
        $res = mysqli_query($conn, $sql);
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $idProducto = (int)$row['id_producto'];
                $cantidad = (int)($carrito[$idProducto] ?? 0);
                if ($cantidad <= 0) {
                    continue;
                }
                $subtotal = ((float)$row['precio']) * $cantidad;
                $row['cantidad'] = $cantidad;
                $row['subtotal'] = $subtotal;
                $total += $subtotal;
                $productos[] = $row;
            }
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi Carrito</title>
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
        .panel-user-info { margin-left: auto; text-align: right; }
        .panel-user-name { font-weight: 700; color: #333; font-size: 0.95rem; }
        .panel-user-email { font-size: 0.85rem; color: #999; }
        .logout-btn { background: #dc3545; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; font-weight: 600; text-decoration: none; display: inline-block; }
        .logout-btn:hover { background: #bb2d3b; color: white; }
        .section-title { font-size: 2rem; font-weight: 700; color: #333; margin: 2rem 0 0.5rem 0; }
        .section-subtitle { color: #999; font-size: 0.95rem; margin-bottom: 2rem; }
        .item-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 1rem; margin-bottom: 1rem; }
        .item-img { width: 90px; height: 90px; object-fit: cover; border-radius: 8px; }
        .btn-remove { background: #6c757d; color: white; border: none; border-radius: 6px; padding: 0.45rem 0.7rem; text-decoration: none; }
        .btn-remove:hover { color: white; background: #5a6268; }
        .checkout-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 1.3rem; }
        .btn-pay { background: #dc3545; color: white; border: none; border-radius: 8px; padding: 0.7rem 1rem; font-weight: 700; width: 100%; }
        .btn-pay:hover { background: #bb2d3b; }
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
                    <div class="panel-user-name"><?= htmlspecialchars($usuario_sesion, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="panel-user-email"><?= htmlspecialchars($email_sesion, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="container" style="max-width: 1100px; padding: 2rem 0;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <a href="productos.php" style="color:#999; text-decoration:none;"><i class="fas fa-arrow-left"></i> Seguir comprando</a>
            <?php if (!empty($productos)): ?>
                <a href="carrito.php?action=clear" style="color:#dc3545; text-decoration:none;" onclick="return confirm('¿Vaciar carrito?');"><i class="fas fa-trash"></i> Vaciar carrito</a>
            <?php endif; ?>
        </div>

        <h1 class="section-title"><i class="fas fa-shopping-cart"></i> Mi Carrito</h1>
        <p class="section-subtitle">Revisa tus productos agregados y realiza tu compra desde aquí</p>

        <?php if ($msg): ?>
            <div class="alert alert-success"><?= $msg ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if (empty($productos)): ?>
            <div class="checkout-card">
                <p style="margin:0; color:#666;">Tu carrito está vacío.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <div class="col-lg-7">
                    <?php foreach ($productos as $p): ?>
                        <div class="item-card">
                            <div class="d-flex gap-3 align-items-center">
                                <?php if (!empty($p['imagen_url'])): ?>
                                    <img src="<?= htmlspecialchars($p['imagen_url'], ENT_QUOTES, 'UTF-8') ?>" class="item-img" alt="<?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                                <?php else: ?>
                                    <div class="item-img" style="background:#e0e0e0;"></div>
                                <?php endif; ?>
                                <div style="flex:1;">
                                    <div style="font-weight:700; color:#333;"><?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div style="color:#666; font-size:0.92rem;">Cantidad: <?= (int)$p['cantidad'] ?></div>
                                    <div style="color:#059669; font-weight:700;">Subtotal: $<?= number_format((float)$p['subtotal'], 2) ?></div>
                                </div>
                                <a class="btn-remove" href="carrito.php?action=remove&id=<?= (int)$p['id_producto'] ?>"><i class="fas fa-times"></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="col-lg-5">
                    <div class="checkout-card">
                        <h5 style="font-weight:700; margin-bottom:1rem;">Finalizar compra</h5>
                        <div style="margin-bottom:1rem;"><strong>Total:</strong> $<?= number_format($total, 2) ?></div>
                        <form id="form-carrito-compra" method="post">
                            <input type="hidden" name="accion" value="comprar_carrito">
                            <input type="hidden" name="metodo_pago" value="tarjeta">
                            <input type="hidden" name="pago_confirmado" id="pago_confirmado_carrito" value="0">

                            <div class="mb-2">
                                <label class="form-label">Nombre completo</label>
                                <input type="text" class="form-control" name="nombre_envio" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Dirección de envío</label>
                                <input type="text" class="form-control" name="direccion_envio" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Teléfono</label>
                                <input type="text" class="form-control" name="telefono_envio" required>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Nombre del titular</label>
                                <input type="text" class="form-control" id="nombre_tarjeta_carrito" name="nombre_tarjeta" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Número de tarjeta</label>
                                <input type="text" class="form-control" id="numero_tarjeta_carrito" name="numero_tarjeta" maxlength="19" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Fecha de vencimiento</label>
                                <input type="month" class="form-control" id="vencimiento_tarjeta_carrito" name="vencimiento_tarjeta" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">CVV</label>
                                <input type="password" class="form-control" id="cvv_tarjeta_carrito" name="cvv_tarjeta" maxlength="4" required>
                            </div>

                            <button type="button" class="btn-pay" id="btn-simular-pago-carrito"><i class="fas fa-credit-card"></i> Realizar pago</button>
                            <p id="estado-pago-carrito" style="margin-top:0.6rem; font-size:0.9rem; color:#666;">Completa la tarjeta para simular el pago.</p>
                            <button type="submit" class="btn-pay" id="btn-confirmar-carrito" style="display:none;"><i class="fas fa-check"></i> Confirmar compra del carrito</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var btnSimular = document.getElementById('btn-simular-pago-carrito');
        var btnConfirmar = document.getElementById('btn-confirmar-carrito');
        var pagoConfirmado = document.getElementById('pago_confirmado_carrito');
        var estadoPago = document.getElementById('estado-pago-carrito');
        var form = document.getElementById('form-carrito-compra');

        if (btnSimular) {
            btnSimular.addEventListener('click', function () {
                var nombre = (document.getElementById('nombre_tarjeta_carrito').value || '').trim();
                var numero = (document.getElementById('numero_tarjeta_carrito').value || '').trim();
                var vencimiento = (document.getElementById('vencimiento_tarjeta_carrito').value || '').trim();
                var cvv = (document.getElementById('cvv_tarjeta_carrito').value || '').trim();

                if (!nombre || !numero || !vencimiento || !cvv) {
                    pagoConfirmado.value = '0';
                    btnConfirmar.style.display = 'none';
                    estadoPago.textContent = 'Faltan datos del pago.';
                    alert('Completa todos los campos del pago.');
                    return;
                }

                pagoConfirmado.value = '1';
                estadoPago.textContent = 'Pago realizado con éxito.';
                alert('Pago realizado con éxito.');
                btnConfirmar.style.display = 'block';
            });
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                if (pagoConfirmado.value !== '1') {
                    e.preventDefault();
                    alert('Primero debes realizar el pago.');
                }
            });
        }
    });
    </script>
</body>
</html>
