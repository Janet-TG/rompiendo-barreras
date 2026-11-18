<?php
session_start();
if (!isset($_SESSION['id_usr']) || ($_SESSION['rol'] ?? '') !== '2') {
    header('Location: ../index.php');
    exit();
}
include_once __DIR__ . '/../config/db.php';
$usuario_sesion = $_SESSION['nom_usr'] ?? $_SESSION['usuario'] ?? 'Desconocido';
$email_sesion = $_SESSION['mail'] ?? 'No disponible';
$productos = [];
$stmt = mysqli_prepare($conn, "SELECT id_producto, nombre, descripcion, precio, imagen_url, stock, categoria, created_at FROM productos WHERE stock > 0 ORDER BY created_at DESC");
if ($stmt) {
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $productos[] = $row;
    }
    mysqli_stmt_close($stmt);
}
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
        .producto-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); height: 100%; display: flex; flex-direction: column; }
        .producto-card:hover { transform: translateY(-8px); box-shadow: 0 12px 30px rgba(0,0,0,0.15); }
        .producto-imagen { position: relative; overflow: hidden; background: linear-gradient(135deg, #bc8fec 0%, #afd8ed 100%); height: 220px; }
        .producto-imagen img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
        .producto-card:hover .producto-imagen img { transform: scale(1.08); }
        .producto-badge { position: absolute; top: 1rem; right: 1rem; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 0.4rem 0.8rem; border-radius: 4px; font-weight: 600; font-size: 0.85rem; }
        .producto-body { padding: 1.5rem; flex: 1; display: flex; flex-direction: column; }
        .producto-categoria { font-size: 0.8rem; color: #bc8fec; font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem; }
        .producto-nombre { font-weight: 700; color: #333; font-size: 1.1rem; margin-bottom: 0.8rem; line-height: 1.4; }
        .producto-descripcion { color: #777; font-size: 0.9rem; margin-bottom: 1rem; flex: 1; line-height: 1.5; }
        .producto-footer { border-top: 1px solid #f0f0f0; padding-top: 1rem; display: flex; justify-content: space-between; align-items: center; }
        .producto-precio { font-size: 1.4rem; font-weight: 700; color: #10b981; }
        .btn-comprar { background: linear-gradient(135deg, #bc8fec 0%, #a875d9 100%); color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-block; }
        .btn-comprar:hover { background: linear-gradient(135deg, #a875d9 0%, #9460c7 100%); color: white; transform: scale(1.05); }
        .empty-state { text-align: center; padding: 4rem 2rem; background: white; border-radius: 12px; border: 2px dashed #e0e0e0; }
        .empty-state-icon { font-size: 3rem; margin-bottom: 1rem; color: #bc8fec; }
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

    <div class="container" style="max-width: 1200px; padding: 2rem 0;">
        <div style="margin-bottom: 1.5rem;">
            <a href="index.php" style="color: #999; text-decoration: none; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem; transition: color 0.3s ease;">
                <i class="fas fa-arrow-left"></i> Volver al Panel
            </a>
        </div>

        <h1 class="section-title">Productos de Rehabilitación</h1>
        <p class="section-subtitle">Explora nuestro catálogo de productos especializados para discapacidad motriz</p>

        <?php if (empty($productos)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-box"></i></div>
                <h5 style="color: #888; font-weight: 600;">No hay productos disponibles</h5>
                <p style="color: #999; margin-bottom: 0;">Los productos de rehabilitación estarán disponibles pronto</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($productos as $producto): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="producto-card">
                        <div class="producto-imagen">
                            <?php if ($producto['imagen_url']): ?>
                                <img src="<?= htmlspecialchars($producto['imagen_url']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                            <?php else: ?>
                                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;"><i class="fas fa-image" style="font-size:3rem;color:white;opacity:0.5;"></i></div>
                            <?php endif; ?>
                            <span class="producto-badge"><?= $producto['stock'] ?> disponibles</span>
                        </div>
                        <div class="producto-body">
                            <div class="producto-categoria"><?= htmlspecialchars(ucfirst($producto['categoria'])) ?></div>
                            <h6 class="producto-nombre" title="<?= htmlspecialchars($producto['nombre']) ?>"><?= htmlspecialchars(mb_strimwidth($producto['nombre'],0,50,'...')) ?></h6>
                            <p class="producto-descripcion"><?= htmlspecialchars(mb_strimwidth($producto['descripcion'] ?? 'Sin descripción',0,100,'...')) ?></p>
                            <div class="producto-footer">
                                <div class="producto-precio">$<?= number_format($producto['precio'], 2) ?></div>
                                <button class="btn-comprar" onclick="mostrarDetalleProducto(<?= $producto['id_producto'] ?>)"><i class="fas fa-shopping-cart"></i> Ver</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Modal para detalle del producto -->
            <div class="modal fade" id="productoModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius: 12px; border: none;">
                  <div class="modal-header" style="border-bottom: 1px solid #f0f0f0;">
                    <h5 class="modal-title" id="productoModalLabel" style="font-weight: 700;"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                  </div>
                  <div class="modal-body" id="productoModalBody">
                    <div class="text-center">
                      <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    var productosData = <?= json_encode($productos) ?>;
    
    function mostrarDetalleProducto(idProducto) {
        var producto = productosData.find(p => p.id_producto == idProducto);
        if (!producto) return;
        
        var label = document.getElementById('productoModalLabel');
        var body = document.getElementById('productoModalBody');
        
        label.textContent = producto.nombre;
        
        var imagenHtml = producto.imagen_url 
            ? '<img src="' + producto.imagen_url + '" alt="' + producto.nombre + '" class="img-fluid mb-3" style="max-height:300px;border-radius:8px;">'
            : '<div style="height:200px;background:#f0f0f0;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-bottom:1rem;"><i class="fas fa-image" style="font-size:3rem;color:#ccc;"></i></div>';
        
        body.innerHTML = `
            ${imagenHtml}
            <div class="mb-3">
                <span class="badge" style="background:#bc8fec;font-size:0.9rem;">${producto.categoria.toUpperCase()}</span>
            </div>
            <p style="color:#666;line-height:1.6;margin-bottom:1.5rem;">${producto.descripcion || 'Sin descripción disponible'}</p>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div style="font-size:0.9rem;color:#999;">Precio</div>
                    <div style="font-size:2rem;font-weight:700;color:#10b981;">$${parseFloat(producto.precio).toFixed(2)}</div>
                </div>
                <div>
                    <div style="font-size:0.9rem;color:#999;">Disponibles</div>
                    <div style="font-size:1.5rem;font-weight:700;color:#333;">${producto.stock} unidades</div>
                </div>
            </div>
            <div class="alert alert-info" style="border-radius:8px;">
                <i class="fas fa-info-circle"></i> Para realizar tu compra, contacta con nuestro equipo de atención al cliente.
            </div>
        `;
        
        var modal = new bootstrap.Modal(document.getElementById('productoModal'));
        modal.show();
    }
    </script>
</body>
</html>
