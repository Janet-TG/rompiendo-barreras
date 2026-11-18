<?php
session_start();
if (!isset($_SESSION['id_usr']) || ($_SESSION['rol'] ?? '') !== '1') {
    header('Location: ../index.php');
    exit();
}

include_once __DIR__ . '/../config/db.php';

$usuario_sesion = $_SESSION['nom_usr'] ?? $_SESSION['usuario'] ?? 'Desconocido';
$email_sesion = $_SESSION['mail'] ?? 'No disponible';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: citas_listar.php?msg=' . urlencode('ID de cita inválido'));
    exit();
}

$error_message = null;

$cita = null;
$stmtCita = mysqli_prepare($conn, "SELECT id_cita, id_usr, fecha, hora, motivo, estado FROM citas WHERE id_cita = ? LIMIT 1");
if ($stmtCita) {
    mysqli_stmt_bind_param($stmtCita, 'i', $id);
    mysqli_stmt_execute($stmtCita);
    $resCita = mysqli_stmt_get_result($stmtCita);
    $cita = mysqli_fetch_assoc($resCita);
    mysqli_stmt_close($stmtCita);
}

if (!$cita) {
    header('Location: citas_listar.php?msg=' . urlencode('La cita no existe'));
    exit();
}

$estadoCita = $cita['estado'] ?? 'pendiente';
if ($estadoCita !== 'pendiente') {
    header('Location: citas_listar.php?msg=' . urlencode('Solo puedes editar citas pendientes'));
    exit();
}

$medicos = [];
$sqlMedicos = "SELECT id_medico, nombre, especialidad FROM medicos WHERE activo = 1 AND LOWER(especialidad) LIKE 'fisioterapeuta%' ORDER BY nombre ASC";
$resMedicos = mysqli_query($conn, $sqlMedicos);
if ($resMedicos) {
    while ($m = mysqli_fetch_assoc($resMedicos)) {
        $medicos[] = $m;
    }
}

$motivoTexto = trim((string)($cita['motivo'] ?? ''));
$medicoNombreActual = '';
if (preg_match('/^(?:Fisioterapeuta|Médico):\s*(.*?)\s*\|\s*Motivo:\s*(.*)$/u', $motivoTexto, $match)) {
    $medicoNombreActual = trim((string)$match[1]);
    $motivoTexto = trim((string)$match[2]);
}

$fecha = (string)($cita['fecha'] ?? '');
$hora = substr((string)($cita['hora'] ?? ''), 0, 5);
$motivo = $motivoTexto;
$id_medico = 0;

if (!empty($medicos)) {
    foreach ($medicos as $m) {
        if (($m['nombre'] ?? '') === $medicoNombreActual) {
            $id_medico = (int)$m['id_medico'];
            break;
        }
    }
    if ($id_medico === 0) {
        $id_medico = (int)$medicos[0]['id_medico'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = trim($_POST['fecha'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $motivo = trim($_POST['motivo'] ?? '');
    $id_medico = (int)($_POST['id_medico'] ?? 0);

    if ($fecha === '' || $hora === '' || $motivo === '' || $id_medico <= 0) {
        $error_message = 'Completa fecha, hora, médico y motivo.';
    } else {
        $fechaHoraCita = DateTime::createFromFormat('Y-m-d H:i', $fecha . ' ' . substr($hora, 0, 5));
        $ahora = new DateTime();
        if (!$fechaHoraCita || $fechaHoraCita < $ahora) {
            $error_message = 'No puedes asignar una cita en fecha u hora pasada.';
        }
    }

    if (!$error_message) {
        $medicoSeleccionado = null;
        $stmtMedico = mysqli_prepare($conn, "SELECT nombre, especialidad FROM medicos WHERE id_medico = ? AND activo = 1 LIMIT 1");
        if ($stmtMedico) {
            mysqli_stmt_bind_param($stmtMedico, 'i', $id_medico);
            mysqli_stmt_execute($stmtMedico);
            $resMedico = mysqli_stmt_get_result($stmtMedico);
            $medicoSeleccionado = mysqli_fetch_assoc($resMedico);
            mysqli_stmt_close($stmtMedico);
        }

        if (!$medicoSeleccionado || stripos((string)$medicoSeleccionado['especialidad'], 'fisioterapeuta') !== 0) {
            $error_message = 'El médico seleccionado no es válido.';
        } else {
            $motivoCompleto = 'Fisioterapeuta: ' . $medicoSeleccionado['nombre'] . ' | Motivo: ' . $motivo;
            $sqlUpdate = "UPDATE citas SET fecha = ?, hora = ?, motivo = ? WHERE id_cita = ? AND estado = 'pendiente'";
            $stmtUpdate = mysqli_prepare($conn, $sqlUpdate);
            if ($stmtUpdate) {
                mysqli_stmt_bind_param($stmtUpdate, 'sssi', $fecha, $hora, $motivoCompleto, $id);
                if (mysqli_stmt_execute($stmtUpdate)) {
                    mysqli_stmt_close($stmtUpdate);
                    header('Location: citas_listar.php?msg=' . urlencode('Cita actualizada correctamente'));
                    exit();
                }
                mysqli_stmt_close($stmtUpdate);
                $error_message = 'No se pudo actualizar la cita.';
            } else {
                $error_message = 'Error en la consulta de actualización.';
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
    <title>Editar Cita - Admin</title>
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
        .form-card { background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 5px solid #bc8fec; }
        .form-label { font-weight: 700; color: #333; margin-bottom: 0.7rem; font-size: 0.95rem; }
        .form-control, .form-select { border: 1.5px solid #e0e0e0; border-radius: 8px; padding: 0.75rem 1rem; transition: all 0.3s ease; }
        .form-control:focus, .form-select:focus { border-color: #bc8fec; box-shadow: 0 0 0 3px rgba(188, 143, 236, 0.1); }
        .btn-submit { background: linear-gradient(135deg, #bc8fec 0%, #a875d9 100%); color: white; border: none; padding: 0.9rem 2rem; border-radius: 8px; font-weight: 700; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(188, 143, 236, 0.3); }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(188, 143, 236, 0.4); }
        .btn-cancel { background: #6c757d; color: white; border: none; padding: 0.9rem 2rem; border-radius: 8px; font-weight: 700; text-decoration: none; display: inline-block; transition: all 0.3s ease; }
        .btn-cancel:hover { background: #5a6268; color: white; }
        .alert { border-radius: 8px; border: none; font-weight: 500; margin-bottom: 2rem; }
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
                    <div class="panel-user-name"><?= htmlspecialchars($usuario_sesion, ENT_QUOTES, 'UTF-8') ?><span class="admin-badge">ADMIN</span></div>
                    <div class="panel-user-email"><?= htmlspecialchars($email_sesion, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="container" style="max-width: 900px; padding: 2rem 0;">
        <div style="margin-bottom: 1.5rem;">
            <a href="citas_listar.php" style="color: #999; text-decoration: none; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-arrow-left"></i> Volver a Citas
            </a>
        </div>

        <h1 class="section-title">Editar Cita</h1>
        <p class="section-subtitle">Modifica día, hora y fisioterapeuta de la cita</p>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form method="post">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Día</label>
                        <input type="date" id="fechaInput" name="fecha" class="form-control" required min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hora</label>
                        <input type="time" id="horaInput" name="hora" class="form-control" required value="<?= htmlspecialchars($hora, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fisioterapeuta</label>
                        <select name="id_medico" class="form-select" required>
                            <option value="">Selecciona un fisioterapeuta</option>
                            <?php foreach ($medicos as $m): ?>
                                <option value="<?= (int)$m['id_medico'] ?>" <?= (int)$id_medico === (int)$m['id_medico'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(($m['nombre'] ?? '') . ' - ' . ($m['especialidad'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Motivo</label>
                        <textarea name="motivo" class="form-control" rows="3" required><?= htmlspecialchars($motivo, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn-submit" type="submit"><i class="fas fa-save"></i> Guardar cambios</button>
                    <a href="citas_listar.php" class="btn-cancel"><i class="fas fa-times"></i> Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.querySelector('form[method="post"]');
        var fechaInput = document.getElementById('fechaInput');
        var horaInput = document.getElementById('horaInput');
        if (!form || !fechaInput || !horaInput) return;

        form.addEventListener('submit', function (e) {
            var fecha = fechaInput.value;
            var hora = horaInput.value;
            if (!fecha || !hora) return;

            var fechaHora = new Date(fecha + 'T' + hora);
            var ahora = new Date();
            if (fechaHora.getTime() < ahora.getTime()) {
                e.preventDefault();
                alert('No puedes asignar una cita en fecha u hora pasada.');
            }
        });
    });
    </script>
</body>
</html>
