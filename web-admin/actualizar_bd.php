<?php
/**
 * Script de actualización de base de datos
 * Ejecutar una sola vez para aplicar los cambios
 * 
 * Instrucciones:
 * 1. Acceder a: http://localhost/Evaluacion3_analisis/actualizar_bd.php
 * 2. Hacer clic en "Ejecutar Migración"
 * 3. Eliminar este archivo después de ejecutarlo
 */

// Incluir la configuración de la base de datos
include_once __DIR__ . '/config/db.php';

$mensaje = '';
$error = '';
$ejecutado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ejecutar'])) {
    $ejecutado = true;
    
    // Iniciar transacción
    mysqli_begin_transaction($conn);
    
    try {
        // 1. Agregar columna estado a citas (compatible sin IF NOT EXISTS)
        $sqlCheck = "SELECT COUNT(*) AS c FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'citas' AND column_name = 'estado'";
        $resCheck = mysqli_query($conn, $sqlCheck);
        if (!$resCheck) {
            throw new Exception('Error al verificar columna estado: ' . mysqli_error($conn));
        }
        $rowCheck = mysqli_fetch_assoc($resCheck);
        if ((int)$rowCheck['c'] === 0) {
            $sql1 = "ALTER TABLE `citas` ADD COLUMN `estado` VARCHAR(20) DEFAULT 'pendiente' COMMENT 'pendiente, cancelada' AFTER `motivo`";
            if (!mysqli_query($conn, $sql1)) {
                throw new Exception("Error al agregar columna estado: " . mysqli_error($conn));
            }
        }
        
        // 2. Actualizar citas existentes
        $sql2 = "UPDATE `citas` SET `estado` = 'pendiente' WHERE `estado` IS NULL OR `estado` = ''";
        mysqli_query($conn, $sql2);
        
        // 3. Crear tabla productos
        $sql3 = "CREATE TABLE IF NOT EXISTS `productos` (
          `id_producto` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          `nombre` VARCHAR(255) NOT NULL,
          `descripcion` TEXT DEFAULT NULL,
          `precio` DECIMAL(10,2) NOT NULL,
          `imagen_url` VARCHAR(1000) DEFAULT NULL COMMENT 'URL externa o ruta relativa a la imagen del producto',
          `stock` INT DEFAULT 0,
          `categoria` VARCHAR(100) DEFAULT 'rehabilitacion',
          `created_by` INT UNSIGNED DEFAULT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          CONSTRAINT `fk_productos_usuario` FOREIGN KEY (`created_by`) REFERENCES `usuarios`(`id_usr`) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if (!mysqli_query($conn, $sql3)) {
            throw new Exception("Error al crear tabla productos: " . mysqli_error($conn));
        }
        
        // 4. Verificar si ya hay productos
        $check = mysqli_query($conn, "SELECT COUNT(*) as total FROM productos");
        $row = mysqli_fetch_assoc($check);
        
        if ($row['total'] == 0) {
            // Insertar productos de ejemplo
            $productos = [
                ['Guante de Rehabilitación Motriz', 'Guante especializado que ayuda con la movilidad de la mano mediante estimulación controlada', 1299.99, 'https://via.placeholder.com/300x200?text=Guante+Rehabilitacion', 10, 'mano'],
                ['Andadera Ergonómica Ajustable', 'Andadera con soporte ergonómico ajustable para mejorar la movilidad y balance', 2499.99, 'https://via.placeholder.com/300x200?text=Andadera', 5, 'movilidad'],
                ['Banda Elástica de Resistencia', 'Set de bandas elásticas de diferentes resistencias para terapia física', 299.99, 'https://via.placeholder.com/300x200?text=Bandas+Elasticas', 20, 'ejercicio'],
                ['Ortesis de Muñeca', 'Ortesis ajustable para soporte y rehabilitación de muñeca', 599.99, 'https://via.placeholder.com/300x200?text=Ortesis+Muneca', 15, 'mano']
            ];
            
            foreach ($productos as $prod) {
                $stmt = mysqli_prepare($conn, "INSERT INTO productos (nombre, descripcion, precio, imagen_url, stock, categoria, created_by) VALUES (?, ?, ?, ?, ?, ?, 1)");
                mysqli_stmt_bind_param($stmt, 'ssdsss', $prod[0], $prod[1], $prod[2], $prod[3], $prod[4], $prod[5]);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Error al insertar productos: " . mysqli_error($conn));
                }
                mysqli_stmt_close($stmt);
            }
        }
        
        // Confirmar transacción
        mysqli_commit($conn);
        $mensaje = '✅ Base de datos actualizada exitosamente! Se agregó el campo estado a citas, se creó la tabla productos y se insertaron ' . (($row['total'] == 0) ? '4 productos de ejemplo' : '0 productos (ya existían)') . '.';
        
    } catch (Exception $e) {
        // Revertir cambios en caso de error
        mysqli_rollback($conn);
        $error = '❌ Error: ' . $e->getMessage();
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Base de Datos - Rompiendo Barreras</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .update-card { background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 600px; width: 100%; padding: 3rem; }
        .update-icon { font-size: 4rem; color: #667eea; text-align: center; margin-bottom: 1.5rem; }
        h1 { font-weight: 700; color: #333; text-align: center; margin-bottom: 1rem; }
        .subtitle { color: #999; text-align: center; margin-bottom: 2rem; }
        .btn-ejecutar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 1rem 2rem; border-radius: 8px; font-weight: 700; width: 100%; font-size: 1.1rem; cursor: pointer; transition: all 0.3s ease; }
        .btn-ejecutar:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4); }
        .alert-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .alert-danger { background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%); color: white; border: none; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .info-box { background: #f8f9fa; border-left: 4px solid #667eea; padding: 1.5rem; border-radius: 8px; margin: 1.5rem 0; }
        .info-box h5 { font-weight: 700; color: #667eea; margin-bottom: 1rem; }
        .info-box ul { margin: 0; padding-left: 1.5rem; }
        .warning-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 1rem; border-radius: 8px; margin: 1rem 0; color: #856404; }
    </style>
</head>
<body>
    <div class="update-card">
        <div class="update-icon"><i class="fas fa-database"></i></div>
        <h1>Actualizar Base de Datos</h1>
        <p class="subtitle">Sistema Rompiendo Barreras v2.0</p>
        
        <?php if ($mensaje): ?>
            <div class="alert-success">
                <?= $mensaje ?>
            </div>
            <div class="warning-box">
                <strong><i class="fas fa-exclamation-triangle"></i> Importante:</strong> 
                Por seguridad, elimina este archivo (actualizar_bd.php) después de ejecutar la migración.
            </div>
            <a href="index.php" class="btn-ejecutar" style="text-decoration: none; display: block; text-align: center; margin-top: 1rem;">
                <i class="fas fa-home"></i> Ir al Inicio
            </a>
        <?php elseif ($error): ?>
            <div class="alert-danger">
                <?= $error ?>
            </div>
            <button onclick="location.reload()" class="btn-ejecutar">
                <i class="fas fa-redo"></i> Reintentar
            </button>
        <?php else: ?>
            <div class="info-box">
                <h5><i class="fas fa-info-circle"></i> Cambios que se aplicarán:</h5>
                <ul>
                    <li>✅ Agregar campo <strong>estado</strong> a la tabla <code>citas</code></li>
                    <li>✅ Crear tabla <strong>productos</strong></li>
                    <li>✅ Insertar 4 productos de ejemplo</li>
                    <li>✅ Actualizar todas las citas existentes al estado "pendiente"</li>
                </ul>
            </div>
            
            <div class="warning-box">
                <strong><i class="fas fa-shield-alt"></i> Seguro:</strong> 
                Este script no eliminará ni modificará tus datos existentes. Solo agrega nuevas funcionalidades.
            </div>
            
            <form method="POST">
                <button type="submit" name="ejecutar" value="1" class="btn-ejecutar">
                    <i class="fas fa-play"></i> Ejecutar Migración
                </button>
            </form>
        <?php endif; ?>
        
        <?php if (!$ejecutado): ?>
        <div style="text-align: center; margin-top: 2rem; color: #999; font-size: 0.9rem;">
            <p>
                <i class="fas fa-clock"></i> Tiempo estimado: menos de 1 segundo<br>
                <i class="fas fa-backup"></i> Se recomienda hacer respaldo antes de ejecutar
            </p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
