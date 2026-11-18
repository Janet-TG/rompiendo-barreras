<?php
session_start();
if (!isset($_SESSION['id_usr']) || ($_SESSION['rol'] ?? '') !== '2') {
    header('Location: ../index.php');
    exit();
}
include_once __DIR__ . '/../config/db.php';
$carrito = $_SESSION['carrito'] ?? [];

if (isset($_GET['add_cart'])) {
    $idAgregar = (int)$_GET['add_cart'];
    if ($idAgregar > 0) {
        $stmtCheck = mysqli_prepare($conn, "SELECT id_producto FROM productos WHERE id_producto = ? LIMIT 1");
        if ($stmtCheck) {
            mysqli_stmt_bind_param($stmtCheck, 'i', $idAgregar);
            mysqli_stmt_execute($stmtCheck);
            $resCheck = mysqli_stmt_get_result($stmtCheck);
            $existeProducto = mysqli_fetch_assoc($resCheck);
            mysqli_stmt_close($stmtCheck);
            if ($existeProducto) {
                if (!isset($carrito[$idAgregar])) {
                    $carrito[$idAgregar] = 0;
                }
                $carrito[$idAgregar]++;
                $_SESSION['carrito'] = $carrito;
                header('Location: productos.php?msg=' . urlencode('Producto agregado al carrito'));
                exit();
            }
        }
    }
    header('Location: productos.php?msg=' . urlencode('No se pudo agregar el producto al carrito'));
    exit();
}

$carritoCount = array_sum($carrito);
$usuario_sesion = $_SESSION['nom_usr'] ?? $_SESSION['usuario'] ?? 'Desconocido';
$email_sesion = $_SESSION['mail'] ?? 'No disponible';
$msg = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '';
$sql = "SELECT * FROM productos ORDER BY created_at DESC";
$res = mysqli_query($conn, $sql);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Productos de Rehabilitación</title>
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
        .logout-btn { background: #dc3545; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-block; }
        .logout-btn:hover { background: #bb2d3b; color: white; }
        .section-title { font-size: 2rem; font-weight: 700; color: #333; margin: 2rem 0 0.5rem 0; }
        .section-subtitle { color: #999; font-size: 0.95rem; margin-bottom: 2rem; }
        .productos-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem; margin-top: 2rem; }
        .producto-card { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); overflow: hidden; padding: 1.5rem; display: flex; flex-direction: column; align-items: center; }
        .producto-img { width: 180px; height: 180px; object-fit: cover; border-radius: 10px; margin-bottom: 1rem; }
        .producto-nombre { font-weight: 700; color: #333; font-size: 1.2rem; margin-bottom: 0.5rem; }
        .producto-precio { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 0.4rem 0.8rem; border-radius: 4px; font-weight: 600; font-size: 1rem; margin-bottom: 0.7rem; }
        .producto-desc { color: #555; font-size: 0.95rem; margin-bottom: 1rem; text-align: center; }
        .btn-comprar, .btn-carrito { background: #dc3545; color: white; border: none; padding: 0.7rem 1.2rem; border-radius: 6px; font-weight: 600; font-size: 1rem; cursor: pointer; margin: 0.3rem; transition: all 0.3s ease; }
        .btn-comprar:hover, .btn-carrito:hover { background: #bb2d3b; }
        .btn-carrito-top { background: #0d6efd; color: white; border: none; padding: 0.6rem 1rem; border-radius: 8px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; }
        .btn-carrito-top:hover { color: white; background: #0a58ca; }
        .carrito-badge { background: #ffc107; color: #333; border-radius: 999px; padding: 0.1rem 0.45rem; font-size: 0.8rem; font-weight: 700; }
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
                    <div class="panel-user-name"><?= htmlspecialchars($usuario_sesion) ?></div>
                    <div class="panel-user-email"><?= htmlspecialchars($email_sesion) ?></div>
                </div>
                <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
    </div>
    <div class="container" style="max-width: 1400px; padding: 2rem 0;">
        <div style="margin-bottom: 1.5rem;">
            <a href="index.php" style="color: #999; text-decoration: none; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem; transition: color 0.3s ease;">
                <i class="fas fa-arrow-left"></i> Volver al Panel
            </a>
        </div>
        <div style="display:flex; justify-content:flex-end; margin-bottom:1rem;">
            <a href="carrito.php" class="btn-carrito-top"><i class="fas fa-shopping-cart"></i> Carrito <span class="carrito-badge"><?= (int)$carritoCount ?></span></a>
        </div>
        <h1 class="section-title"><i class="fas fa-shopping-bag"></i> Productos de Rehabilitación</h1>
        <p class="section-subtitle">Compra productos para tu rehabilitación de manera fácil y segura</p>
        <?php if ($msg): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> <?= $msg ?>
            </div>
        <?php endif; ?>
        <div class="productos-grid">
            <?php while ($row = mysqli_fetch_assoc($res)): ?>
                <div class="producto-card">
                    <?php if (!empty($row['imagen_url'])): ?>
                        <img src="<?= htmlspecialchars($row['imagen_url'], ENT_QUOTES, 'UTF-8') ?>" class="producto-img" alt="<?= htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8') ?>" onerror="this.onerror=null;this.src='../assets/img/logo.png';">
                    <?php else: ?>
                        <div style="width:180px;height:180px;background:#e0e0e0;border-radius:10px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-image" style="font-size:3rem;color:#999;"></i></div>
                    <?php endif; ?>
                    <div class="producto-nombre"><?= htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="producto-precio">$<?= number_format($row['precio'], 2) ?></div>
                    <div class="producto-desc"><?= htmlspecialchars($row['descripcion'], ENT_QUOTES, 'UTF-8') ?></div>
                    <button class="btn-carrito" onclick="agregarCarrito(<?= $row['id_producto'] ?>)"><i class="fas fa-cart-plus"></i> Agregar al carrito</button>
                    <button class="btn-comprar" onclick="mostrarFormularioCompra(<?= $row['id_producto'] ?>)"><i class="fas fa-credit-card"></i> Comprar ahora</button>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <div id="formulario-compra-modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:white; border-radius:12px; padding:2rem; max-width:400px; margin:auto; position:relative;">
            <button onclick="cerrarFormularioCompra()" style="position:absolute; top:1rem; right:1rem; background:none; border:none; font-size:1.5rem; color:#dc3545; cursor:pointer;"><i class="fas fa-times"></i></button>
            <h2 style="font-size:1.3rem; font-weight:700; margin-bottom:1rem; color:#333;">Datos para el pago y envío</h2>
            <form id="form-compra" method="post" action="procesar_compra.php">
                <input type="hidden" name="id_producto" id="id_producto_compra" value="">
                <input type="hidden" name="pago_confirmado" id="pago_confirmado_compra" value="0">
                <div class="mb-3">
                    <label for="nombre_envio" class="form-label">Nombre completo</label>
                    <input type="text" class="form-control" name="nombre_envio" required>
                </div>
                <div class="mb-3">
                    <label for="direccion_envio" class="form-label">Dirección de envío</label>
                    <input type="text" class="form-control" name="direccion_envio" required>
                </div>
                <div class="mb-3">
                    <label for="telefono_envio" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" name="telefono_envio" required>
                </div>
                <div class="mb-3">
                    <label for="metodo_pago" class="form-label">Método de pago</label>
                    <input type="text" class="form-control" name="metodo_pago" value="tarjeta" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nombre del titular</label>
                    <input type="text" class="form-control" name="nombre_tarjeta" id="nombre_tarjeta_compra" placeholder="Nombre completo" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Número de tarjeta</label>
                    <input type="text" class="form-control" name="numero_tarjeta" id="numero_tarjeta_compra" maxlength="19" placeholder="0000 0000 0000 0000" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha de vencimiento</label>
                    <input type="month" class="form-control" name="vencimiento_tarjeta" id="vencimiento_tarjeta_compra" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">CVV</label>
                    <input type="password" class="form-control" name="cvv_tarjeta" id="cvv_tarjeta_compra" maxlength="4" placeholder="***" required>
                </div>
                <button type="button" class="btn-comprar" style="width:100%;" id="btn-simular-pago"><i class="fas fa-credit-card"></i> Realizar pago</button>
                <p id="estado-pago-compra" style="margin-top:0.8rem; color:#666; font-size:0.9rem;">Completa los datos de tarjeta para simular el pago.</p>
                <button type="submit" class="btn-comprar" style="width:100%; display:none;" id="btn-confirmar-compra"><i class="fas fa-check"></i> Confirmar compra</button>
            </form>
        </div>
    </div>
    <script>
        function agregarCarrito(id) {
            window.location.href = 'productos.php?add_cart=' + encodeURIComponent(id);
        }
        function mostrarFormularioCompra(id) {
            document.getElementById('id_producto_compra').value = id;
            document.getElementById('pago_confirmado_compra').value = '0';
            document.getElementById('estado-pago-compra').textContent = 'Completa los datos de tarjeta para simular el pago.';
            document.getElementById('btn-confirmar-compra').style.display = 'none';
            document.getElementById('formulario-compra-modal').style.display = 'flex';
        }
        function cerrarFormularioCompra() {
            document.getElementById('formulario-compra-modal').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function () {
            var btnSimularPago = document.getElementById('btn-simular-pago');
            var btnConfirmarCompra = document.getElementById('btn-confirmar-compra');
            var pagoConfirmadoInput = document.getElementById('pago_confirmado_compra');
            var estadoPago = document.getElementById('estado-pago-compra');
            var formCompra = document.getElementById('form-compra');

            if (btnSimularPago) {
                btnSimularPago.addEventListener('click', function () {
                    var nombre = (document.getElementById('nombre_tarjeta_compra').value || '').trim();
                    var numero = (document.getElementById('numero_tarjeta_compra').value || '').trim();
                    var vencimiento = (document.getElementById('vencimiento_tarjeta_compra').value || '').trim();
                    var cvv = (document.getElementById('cvv_tarjeta_compra').value || '').trim();

                    if (!nombre || !numero || !vencimiento || !cvv) {
                        pagoConfirmadoInput.value = '0';
                        btnConfirmarCompra.style.display = 'none';
                        estadoPago.textContent = 'Faltan datos de tarjeta.';
                        alert('Completa todos los campos del pago.');
                        return;
                    }

                    pagoConfirmadoInput.value = '1';
                    estadoPago.textContent = 'Pago realizado con éxito.';
                    alert('Pago realizado con éxito.');
                    btnConfirmarCompra.style.display = 'block';
                });
            }

            if (formCompra) {
                formCompra.addEventListener('submit', function (e) {
                    if (pagoConfirmadoInput.value !== '1') {
                        e.preventDefault();
                        alert('Primero debes realizar el pago con tarjeta.');
                    }
                });
            }
        });
    </script>
</body>
</html>
