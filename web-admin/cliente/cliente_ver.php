<?php
session_start();
if (!isset($_SESSION['id_usr']) || ($_SESSION['rol'] ?? '') !== '2') {
    header('Location: ../index.php');
    exit();
}
include_once __DIR__ . '/../config/db.php';
$usuario_sesion = $_SESSION['nom_usr'] ?? $_SESSION['usuario'] ?? 'Desconocido';
$email_sesion = $_SESSION['mail'] ?? 'No disponible';
$id_usr = $_SESSION['id_usr'];
$cliente = [];
$stmt = mysqli_prepare($conn, "SELECT nom_usr, mail, telefono, dir_usr FROM usuarios WHERE id_usr = ? LIMIT 1");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $id_usr);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $cliente = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
}
if (!$cliente) {
    $cliente = ['nom_usr' => 'N/A', 'mail' => 'N/A', 'telefono' => 'N/A'];
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi Ficha Médica</title>
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
        .ficha-card { background: white; border-radius: 12px; padding: 1.8rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); transition: all 0.3s ease; border-left: 5px solid #bc8fec; margin-bottom: 1.5rem; }
        .ficha-card:hover { box-shadow: 0 8px 25px rgba(0,0,0,0.12); }
        .ficha-label { color: #999; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; }
        .ficha-value { color: #333; font-size: 1.1rem; font-weight: 600; line-height: 1.6; }
        .ficha-icon { font-size: 1.5rem; margin-right: 1rem; color: #bc8fec; }
        .ficha-row { display: flex; align-items: flex-start; }
        .grid-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
        @media (max-width: 768px) { .grid-2col { grid-template-columns: 1fr; } }
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

    <div class="container" style="max-width: 900px; padding: 2rem 0;">
        <h1 class="section-title">Mi Ficha Médica</h1>
        <p class="section-subtitle">Información de tu registro de usuario</p>

        <div class="grid-2col">
            <div class="ficha-card">
                <div class="ficha-label"><i class="fas fa-user"></i> Nombre</div>
                <div class="ficha-value"><?= htmlspecialchars($cliente['nom_usr'] ?? 'N/A') ?></div>
            </div>

            <div class="ficha-card">
                <div class="ficha-label"><i class="fas fa-envelope"></i> Correo</div>
                <div class="ficha-value"><?= htmlspecialchars($cliente['mail'] ?? 'N/A') ?></div>
            </div>
        </div>

        <div class="grid-2col">
            <div class="ficha-card">
                <div class="ficha-label"><i class="fas fa-phone"></i> Teléfono</div>
                <div class="ficha-value\"><?= htmlspecialchars($cliente['telefono'] ?? 'N/A') ?></div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #e0e0e0;">
            <p style="color: #999; font-size: 0.95rem; margin-bottom: 1rem;">Para actualizar tu información de usuario, contacta con nuestro equipo administrativo</p>
            <a href="index.php" class="btn btn-primary" style="background: #bc8fec; border: none; padding: 0.7rem 1.5rem; font-weight: 600; border-radius: 6px; transition: all 0.3s ease;">
                <i class="fas fa-arrow-left"></i> Volver al Panel
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
