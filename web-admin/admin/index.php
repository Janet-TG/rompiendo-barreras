<?php
session_start();

if (!isset($_SESSION['id_usr']) || ($_SESSION['rol'] ?? '') !== '1') {
    header("Location: ../index.php");
    exit();
}

$usuario_sesion = $_SESSION['nom_usr'] ?? $_SESSION['usuario'] ?? 'Desconocido';
$email_sesion = $_SESSION['mail'] ?? 'No disponible';

$mostrar_alert = false;
if (isset($_SESSION['login_success']) && $_SESSION['login_success'] === true) {
    $mostrar_alert = true;
    unset($_SESSION['login_success']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Rompiendo Barreras</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: #f5f5f5;
        }
        .panel-header {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 1.5rem 0;
        }
        .panel-container {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        .panel-logo {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            text-decoration: none;
            color: inherit;
        }
        .panel-logo img {
            width: 50px;
        }
        .panel-logo-text small {
            color: #999;
            display: block;
            font-size: 0.75rem;
        }
        .panel-logo-text strong {
            color: #333;
            font-weight: 700;
        }
        .panel-user-info {
            margin-left: auto;
            text-align: right;
        }
        .panel-user-name {
            font-weight: 700;
            color: #333;
            font-size: 0.95rem;
        }
        .panel-user-email {
            font-size: 0.85rem;
            color: #999;
        }
        .admin-badge {
            display: inline-block;
            background: linear-gradient(135deg, #dc3545 0%, #ff6b6b 100%);
            color: white;
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            margin-left: 0.5rem;
        }
        .panel-hero {
            background: linear-gradient(135deg, #dc3545 0%, #ff6b6b 100%);
            padding: 4rem 0;
            color: white;
            margin: 2rem 0;
            border-radius: 12px;
        }
        .panel-hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 0 1rem 0;
        }
        .panel-hero p {
            font-size: 1.1rem;
            margin: 0;
            opacity: 0.95;
        }
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }
        .service-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-top: 5px solid #dc3545;
        }
        .service-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        .service-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #dc3545 0%, #ff6b6b 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            margin-bottom: 1rem;
        }
        .service-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.8rem;
        }
        .service-desc {
            font-size: 0.95rem;
            color: #666;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .service-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #dc3545;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .service-link:hover {
            color: #ff6b6b;
            gap: 0.8rem;
        }
        .panel-footer {
            text-align: center;
            padding: 3rem 0;
            color: #999;
            font-size: 0.9rem;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .logout-btn:hover {
            background: #bb2d3b;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="panel-header">
        <div class="container">
            <div class="panel-container">
                <a href="../index.php" class="panel-logo">
                    <img src="../assets/img/logo.png" alt="Logo">
                    <div class="panel-logo-text">
                        <small>Rompiendo</small>
                        <strong>Barreras</strong>
                    </div>
                </a>
                <div class="panel-user-info">
                    <div class="panel-user-name">
                        <?= htmlspecialchars($usuario_sesion) ?>
                        <span class="admin-badge">ADMIN</span>
                    </div>
                    <div class="panel-user-email"><?= htmlspecialchars($email_sesion) ?></div>
                </div>
                <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container" style="max-width: 1200px; padding: 2rem 0;">
        <div class="panel-hero">
            <h1>Bienvenido al Panel de Administración</h1>
            <p>Gestiona productos, usuarios y citas del sistema</p>
        </div>

        <div class="services-grid">
            <!-- Gestionar Productos -->
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="service-title">Gestionar Productos</div>
                <div class="service-desc">
                    Administra productos de rehabilitación para discapacidad motriz disponibles para compra.
                </div>
                <a href="productos_listar.php" class="service-link">
                    Acceder
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <!-- Gestionar Usuarios -->
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="service-title">Gestionar Usuarios</div>
                <div class="service-desc">
                    Administra la información de los usuarios registrados en el sistema.
                </div>
                <a href="clientes_listar.php" class="service-link">
                    Acceder
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <!-- Ver Citas -->
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="service-title">Ver Citas</div>
                <div class="service-desc">
                    Visualiza, administra y controla todas las citas agendadas por los clientes.
                </div>
                <a href="citas_listar.php" class="service-link">
                    Acceder
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="panel-footer">
        <p>© 2025 Rompiendo Barreras. Todos los derechos reservados.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if ($mostrar_alert): ?>
        document.addEventListener('DOMContentLoaded', function(){
            const alertDiv = document.createElement('div');
            alertDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); font-weight: 600; z-index: 9999; animation: slideInRight 0.4s ease;';
            alertDiv.innerHTML = '<i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i> Bienvenido, ingresaste correctamente';
            const style = document.createElement('style');
            style.textContent = '@keyframes slideInRight { from { transform: translateX(400px); opacity: 0; } to { transform: translateX(0); opacity: 1; } } @keyframes slideOutRight { from { transform: translateX(0); opacity: 1; } to { transform: translateX(400px); opacity: 0; } }';
            document.head.appendChild(style);
            document.body.appendChild(alertDiv);
            setTimeout(function(){
                alertDiv.style.animation = 'slideOutRight 0.4s ease';
                setTimeout(function(){
                    alertDiv.remove();
                }, 400);
            }, 3000);
        });
        <?php endif; ?>
    </script>
</body>
</html>
