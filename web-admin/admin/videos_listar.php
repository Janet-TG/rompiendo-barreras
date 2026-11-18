<?php
session_start();
if (!isset($_SESSION['id_usr']) || ($_SESSION['rol'] ?? '') !== '1') {
    header('Location: ../index.php');
    exit();
}
include_once __DIR__ . '/../config/db.php';
$usuario_sesion = $_SESSION['nom_usr'] ?? $_SESSION['usuario'] ?? 'Desconocido';
$email_sesion = $_SESSION['mail'] ?? 'No disponible';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$sql = "SELECT v.*, u.nom_usr FROM videos v LEFT JOIN usuarios u ON v.created_by = u.id_usr ORDER BY v.created_at DESC";
$res = mysqli_query($conn, $sql);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestionar Videos - Admin</title>
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
        .admin-badge { background: #dc3545; color: white; padding: 0.3rem 0.7rem; border-radius: 4px; font-weight: 700; font-size: 0.75rem; margin-left: 0.5rem; }
        .panel-user-info { margin-left: auto; text-align: right; }
        .panel-user-name { font-weight: 700; color: #333; font-size: 0.95rem; }
        .panel-user-email { font-size: 0.85rem; color: #999; }
        .logout-btn { background: #dc3545; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-block; }
        .logout-btn:hover { background: #bb2d3b; color: white; }
        .section-title { font-size: 2rem; font-weight: 700; color: #333; margin: 2rem 0 0.5rem 0; }
        .section-subtitle { color: #999; font-size: 0.95rem; margin-bottom: 2rem; }
        .header-controls { display: flex; gap: 1.5rem; align-items: center; margin-bottom: 2rem; }
        .btn-new { background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%); color: white; border: none; padding: 0.8rem 1.5rem; border-radius: 6px; font-weight: 600; font-size: 0.95rem; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-block; }
        .btn-new:hover { color: white; transform: translateY(-2px); box-shadow: 0 6px 18px rgba(220, 53, 69, 0.35); }
        .table-container { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); overflow: hidden; }
        .table { margin: 0; }
        .table thead { background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%); color: white; }
        .table th { font-weight: 700; font-size: 0.95rem; border: none; padding: 1rem; }
        .table tbody tr { border-bottom: 1px solid #f0f0f0; transition: all 0.3s ease; }
        .table tbody tr:hover { background: rgba(220, 53, 69, 0.03); }
        .table td { padding: 1rem; color: #555; }
        .video-id { font-weight: 700; color: #dc3545; }
        .video-title { font-weight: 600; color: #333; }
        .badge-yt { background: #ff6b6b; color: white; padding: 0.3rem 0.6rem; border-radius: 4px; font-weight: 600; font-size: 0.85rem; }
        .badge-mp4 { background: #bc8fec; color: white; padding: 0.3rem 0.6rem; border-radius: 4px; font-weight: 600; font-size: 0.85rem; }
        .btn-action { padding: 0.5rem 0.8rem; border-radius: 6px; font-weight: 600; font-size: 0.85rem; text-decoration: none; display: inline-block; margin-right: 0.4rem; transition: all 0.3s ease; border: none; cursor: pointer; }
        .btn-edit { background: #bc8fec; color: white; }
        .btn-edit:hover { background: #a875d9; color: white; }
        .btn-delete { background: #ff6b6b; color: white; }
        .btn-delete:hover { background: #ff5252; color: white; }
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
                    <div class="panel-user-name"><?= htmlspecialchars($usuario_sesion) ?><span class="admin-badge">ADMIN</span></div>
                    <div class="panel-user-email"><?= htmlspecialchars($email_sesion) ?></div>
                </div>
                <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="container" style="max-width: 1200px; padding: 2rem 0;">
        <h1 class="section-title"><i class="fas fa-film"></i> Gestionar Videos</h1>
        <p class="section-subtitle">Crea, edita y elimina los videos de ejercicios disponibles para los clientes</p>

        <div class="header-controls">
            <a href="videos_create.php" class="btn-new"><i class="fas fa-plus"></i> Nuevo Video</a>
            <a href="index.php" style="color: #999; text-decoration: none; font-size: 0.95rem;"><i class="fas fa-arrow-left"></i> Volver al Panel</a>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th>Tipo</th>
                        <th>Creado por</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($res)): ?>
                    <tr>
                        <td class="video-id">#<?= $row['id_video'] ?></td>
                        <td class="video-title"><?= htmlspecialchars(mb_strimwidth($row['titulo'],0,40,'...')) ?></td>
                        <td><?= htmlspecialchars(mb_strimwidth($row['descripcion'],0,50,'...')) ?></td>
                        <td>
                            <?php if (stripos($row['url_video'], 'youtube.com') !== false || stripos($row['url_video'],'youtu.be') !== false): ?>
                                <span class="badge-yt"><i class="fab fa-youtube"></i> YouTube</span>
                            <?php else: ?>
                                <span class="badge-mp4"><i class="fas fa-video"></i> MP4</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['nom_usr'] ?? '—') ?></td>
                        <td style="font-size: 0.9rem; color: #999;"><?= htmlspecialchars($row['created_at']) ?></td>
                        <td>
                            <a href="videos_edit.php?id=<?= $row['id_video'] ?>" class="btn-action btn-edit"><i class="fas fa-pencil"></i> Editar</a>
                            <form method="post" action="videos_delete.php" style="display:inline-block; margin:0;">
                                <input type="hidden" name="id" value="<?= $row['id_video'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <button type="submit" class="btn-action btn-delete" onclick="return confirm('¿Eliminar este video?');"><i class="fas fa-trash"></i> Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
