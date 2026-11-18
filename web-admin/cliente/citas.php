<?php
session_start();
if (!isset($_SESSION['id_usr']) || ($_SESSION['rol'] ?? '') !== '2') {
    header('Location: ../index.php');
    exit();
}   
include_once __DIR__ . '/../config/db.php';
$usuario_sesion = $_SESSION['nom_usr'] ?? $_SESSION['usuario'] ?? 'Desconocido';
$email_sesion = $_SESSION['mail'] ?? 'No disponible';
$mensaje = null;
$error_message = null;
$id_usr = (int)$_SESSION['id_usr'];
$fecha = '';
$hora = '';
$motivo = '';
$medico = '';
$id_medico = 0;

$medicos = [];
$sqlMedicos = "SELECT id_medico, nombre, especialidad, horarios_disponibles FROM medicos WHERE activo = 1 AND LOWER(especialidad) LIKE 'fisioterapeuta%' ORDER BY nombre ASC";
$resMedicos = mysqli_query($conn, $sqlMedicos);
if ($resMedicos) {
    while ($m = mysqli_fetch_assoc($resMedicos)) {
        $medicos[] = $m;
    }
}

if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    $accion = isset($_POST['accion']) ? trim($_POST['accion']) : 'agendar';

    if ($accion === 'cancelar_cita') {
        $id_cita_cancelar = isset($_POST['id_cita']) ? (int)$_POST['id_cita'] : 0;
        if ($id_cita_cancelar <= 0) {
            header('Location: citas.php?cancel_error=1');
            exit();
        }

        $sqlCancelar = "UPDATE citas SET estado = 'cancelada' WHERE id_cita = ? AND id_usr = ? AND estado = 'pendiente' AND fecha >= CURDATE()";
        $stmtCancelar = mysqli_prepare($conn, $sqlCancelar);
        if ($stmtCancelar) {
            mysqli_stmt_bind_param($stmtCancelar, 'ii', $id_cita_cancelar, $id_usr);
            mysqli_stmt_execute($stmtCancelar);
            $filasAfectadas = mysqli_stmt_affected_rows($stmtCancelar);
            mysqli_stmt_close($stmtCancelar);

            if ($filasAfectadas > 0) {
                header('Location: citas.php?cancel=1');
                exit();
            }
        }

        header('Location: citas.php?cancel_error=1');
        exit();
    }

    $fecha = isset($_POST['fecha']) ? trim($_POST['fecha']) : '';
    $hora = isset($_POST['hora']) ? trim($_POST['hora']) : '';
    $motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : '';
    $medico = isset($_POST['medico']) ? trim($_POST['medico']) : '';
    $id_medico = isset($_POST['id_medico']) ? (int)$_POST['id_medico'] : 0;
    $nombre_tarjeta = isset($_POST['nombre_tarjeta']) ? trim($_POST['nombre_tarjeta']) : '';
    $numero_tarjeta = isset($_POST['numero_tarjeta']) ? trim($_POST['numero_tarjeta']) : '';
    $vencimiento_tarjeta = isset($_POST['vencimiento_tarjeta']) ? trim($_POST['vencimiento_tarjeta']) : '';
    $cvv_tarjeta = isset($_POST['cvv_tarjeta']) ? trim($_POST['cvv_tarjeta']) : '';
    $pago_confirmado = isset($_POST['pago_confirmado']) ? trim($_POST['pago_confirmado']) : '0';

    if ($fecha === '' || $hora === '' || $motivo === '' || $medico === '' || $id_medico <= 0) {
        $error_message = 'Completa todos los campos requeridos.';
    } elseif ($nombre_tarjeta === '' || $numero_tarjeta === '' || $vencimiento_tarjeta === '' || $cvv_tarjeta === '') {
        $error_message = 'Completa todos los campos del pago para continuar.';
    } elseif ($pago_confirmado !== '1') {
        $error_message = 'Debes realizar el pago antes de confirmar la cita.';
    } else {
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
            $error_message = 'El médico seleccionado no es válido. Elige un fisioterapeuta.';
        } else {
            $nombreMedicoSeguro = $medicoSeleccionado['nombre'];
            $motivoCompleto = 'Fisioterapeuta: ' . $nombreMedicoSeguro . ' | Motivo: ' . $motivo;
            $insertSql = "INSERT INTO citas (id_usr, fecha, hora, motivo) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insertSql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'isss', $id_usr, $fecha, $hora, $motivoCompleto);
                if (mysqli_stmt_execute($stmt)) {
                    header('Location: citas.php?success=1');
                    exit();
                } else {
                    $error_message = 'Error al agendar la cita. Intenta de nuevo.';
                }
                mysqli_stmt_close($stmt);
            } else {
                $error_message = 'Error en la consulta de inserción.';
            }
        }
    }
}

if (isset($_GET['success']) && $_GET['success'] == '1') {
    $mensaje = 'Cita agendada correctamente.';
}
if (isset($_GET['cancel']) && $_GET['cancel'] == '1') {
    $mensaje = 'Cita cancelada correctamente.';
}
if (isset($_GET['cancel_error']) && $_GET['cancel_error'] == '1') {
    $error_message = 'No se pudo cancelar la cita. Verifica que siga pendiente.';
}

$citas = [];
$stmt2 = mysqli_prepare($conn, "SELECT id_cita, fecha, hora, motivo, estado, created_at FROM citas WHERE id_usr = ? ORDER BY fecha DESC, hora DESC");
if ($stmt2) {
        mysqli_stmt_bind_param($stmt2, 'i', $id_usr);
        mysqli_stmt_execute($stmt2);
        $res2 = mysqli_stmt_get_result($stmt2);
        while ($r = mysqli_fetch_assoc($res2)) $citas[] = $r;
        mysqli_stmt_close($stmt2);
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agendar Cita</title>
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
        .form-card { background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 3rem; border-left: 5px solid #bc8fec; }
        .form-label { font-weight: 700; color: #333; margin-bottom: 0.7rem; font-size: 0.95rem; }
        .form-control { border: 1.5px solid #e0e0e0; border-radius: 8px; padding: 0.75rem 1rem; transition: all 0.3s ease; }
            .estado-acabada { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: #fff; }
        .form-control:focus { border-color: #bc8fec; box-shadow: 0 0 0 3px rgba(188, 143, 236, 0.1); }
        .btn-submit { background: linear-gradient(135deg, #bc8fec 0%, #a875d9 100%); color: white; border: none; padding: 0.9rem 2rem; border-radius: 8px; font-weight: 700; width: 100%; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(188, 143, 236, 0.3); }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(188, 143, 236, 0.4); }
        .cita-card { background: white; padding: 1.5rem; border-radius: 10px; border-left: 5px solid #afd8ed; box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 1rem; transition: all 0.3s ease; }
        .cita-card.cancelada { border-left-color: #6c757d; opacity: 0.7; }
        .cita-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.12); transform: translateY(-2px); }
        .estado-badge { padding: 0.4rem 0.8rem; border-radius: 4px; font-weight: 600; font-size: 0.85rem; display: inline-block; margin-left: 1rem; }
        .estado-pendiente { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; }
        .estado-cancelada { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; }
        .alert { border-radius: 8px; border: none; font-weight: 500; margin-bottom: 2rem; }
        .empty-state { text-align: center; padding: 3rem; background: white; border-radius: 10px; border: 2px dashed #e0e0e0; }
        .empty-state-icon { font-size: 3rem; margin-bottom: 1rem; color: #bc8fec; }
        .wizard-section { background: #fafafa; border: 1px solid #ececec; border-radius: 10px; padding: 1rem; margin-top: 1rem; }
        .section-head { font-weight: 700; color: #333; margin-bottom: 0.8rem; font-size: 1rem; }
        .doctor-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.8rem; }
        .doctor-option { border: 1.5px solid #e0e0e0; border-radius: 8px; padding: 0.8rem; cursor: pointer; transition: all 0.2s ease; }
        .doctor-option.active { border-color: #bc8fec; background: #f7efff; }
        .doctor-name { font-weight: 700; color: #333; }
        .doctor-spec { font-size: 0.85rem; color: #777; }
        .slot-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.6rem; }
        .slot-btn { border: 1px solid #d5d5d5; background: #fff; border-radius: 7px; padding: 0.55rem 0.4rem; font-weight: 600; color: #333; }
        .slot-btn.active { background: #bc8fec; color: #fff; border-color: #bc8fec; }
        .detail-box { border: 1px dashed #d9c2f5; border-radius: 8px; padding: 0.9rem; background: #fff; }
        .detail-item { margin-bottom: 0.35rem; color: #555; }
        .detail-item strong { color: #333; }
        .hidden-step { display: none; }
        .mini-btn { background: #bc8fec; color: white; border: none; border-radius: 8px; padding: 0.7rem 1rem; font-weight: 700; }
        .mini-btn:disabled { background: #b7b7b7; }
        .mini-btn-secondary { background: #e9ecef; color: #333; border: 1px solid #d3d7dc; border-radius: 8px; padding: 0.7rem 1rem; font-weight: 700; margin-right: 0.5rem; }
        .status-text { margin-top: 0.7rem; font-size: 0.9rem; color: #666; }
        @media (max-width: 768px) {
            .doctor-grid { grid-template-columns: 1fr; }
            .slot-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
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
        <div style="margin-bottom: 1.5rem;">
            <a href="index.php" style="color: #999; text-decoration: none; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem; transition: color 0.3s ease;">
                <i class="fas fa-arrow-left"></i> Volver al Panel
            </a>
        </div>

        <h1 class="section-title">Agendar Cita</h1>
        <p class="section-subtitle">Reserva tus sesiones de rehabilitación seleccionando fecha, hora y motivo</p>

        <?php if ($mensaje): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form method="post" id="citaForm">
                <input type="hidden" name="accion" value="agendar">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Fecha de la cita</label>
                        <input type="date" id="fechaInput" name="fecha" class="form-control" required value="<?= htmlspecialchars($fecha) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Motivo</label>
                        <input type="text" id="motivoInput" name="motivo" class="form-control" required placeholder="Ej: Dolor de espalda" value="<?= htmlspecialchars($motivo) ?>">
                    </div>
                </div>

                <div class="wizard-section" id="pasoMedico">
                    <div class="section-head">Seleccionar médico</div>
                    <input type="hidden" name="medico" id="medicoInput" value="<?= htmlspecialchars($medico) ?>">
                    <input type="hidden" name="id_medico" id="idMedicoInput" value="<?= (int)$id_medico ?>">
                    <div class="doctor-grid" id="doctorGrid">
                        <?php if (empty($medicos)): ?>
                            <div class="detail-box" style="grid-column: 1 / -1;">
                                <strong>No hay fisioterapeutas disponibles.</strong> Pide al administrador cargar médicos en la base de datos.
                            </div>
                        <?php else: ?>
                            <?php foreach ($medicos as $m):
                                $idMedicoItem = (int)($m['id_medico'] ?? 0);
                                $nombreMedicoItem = (string)($m['nombre'] ?? '');
                                $especialidadMedicoItem = (string)($m['especialidad'] ?? 'Fisioterapeuta');
                                $horariosMedicoItem = (string)($m['horarios_disponibles'] ?? '');
                                $isActiveMedico = ($id_medico > 0 && $id_medico === $idMedicoItem) || ($medico !== '' && $medico === $nombreMedicoItem);
                            ?>
                                <div class="doctor-option <?= $isActiveMedico ? 'active' : '' ?>" data-doctor-id="<?= $idMedicoItem ?>" data-doctor="<?= htmlspecialchars($nombreMedicoItem, ENT_QUOTES, 'UTF-8') ?>" data-slots="<?= htmlspecialchars($horariosMedicoItem, ENT_QUOTES, 'UTF-8') ?>">
                                    <div class="doctor-name"><?= htmlspecialchars($nombreMedicoItem, ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="doctor-spec"><?= htmlspecialchars($especialidadMedicoItem, ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="mini-btn" id="btnMostrarHorarios" <?= empty($medicos) ? 'disabled' : '' ?>>Mostrar horarios disponibles</button>
                    </div>
                </div>

                <div class="wizard-section hidden-step" id="pasoHorario">
                    <div class="section-head">Elegir horario</div>
                    <input type="hidden" name="hora" id="horaInput" value="<?= htmlspecialchars($hora) ?>">
                    <div class="slot-grid" id="slotGrid"></div>
                    <div class="status-text" id="statusHorario">Selecciona un horario disponible.</div>
                    <div class="mt-3">
                        <button type="button" class="mini-btn-secondary" id="btnVolverMedico">Regresar</button>
                    </div>
                </div>

                <div class="wizard-section hidden-step" id="pasoDetalle">
                    <div class="section-head">Visualizar detalle de cita</div>
                    <div class="detail-box" id="detalleCita">
                        <div class="detail-item"><strong>Médico:</strong> <span id="detalleMedico">-</span></div>
                        <div class="detail-item"><strong>Fecha:</strong> <span id="detalleFecha">-</span></div>
                        <div class="detail-item"><strong>Horario:</strong> <span id="detalleHora">-</span></div>
                        <div class="detail-item"><strong>Motivo:</strong> <span id="detalleMotivo">-</span></div>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="mini-btn-secondary" id="btnVolverHorario">Regresar</button>
                        <button type="button" class="mini-btn" id="btnAgendar">Agendar cita</button>
                    </div>
                </div>

                <div class="wizard-section hidden-step" id="pasoPago">
                    <div class="section-head">Realizar pago</div>
                    <input type="hidden" name="pago_confirmado" id="pagoConfirmado" value="0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre del titular</label>
                            <input type="text" name="nombre_tarjeta" id="nombreTarjeta" class="form-control" placeholder="Nombre completo">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Número de tarjeta</label>
                            <input type="text" name="numero_tarjeta" id="numeroTarjeta" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha de vencimiento</label>
                            <input type="month" name="vencimiento_tarjeta" id="vencimientoTarjeta" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CVV</label>
                            <input type="password" name="cvv_tarjeta" id="cvvTarjeta" class="form-control" maxlength="4" placeholder="***">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="mini-btn-secondary" id="btnVolverDetalle">Regresar</button>
                        <button type="button" class="mini-btn" id="btnPagar">Realizar pago</button>
                        <div class="status-text" id="statusPago">Completa los datos de tarjeta para continuar.</div>
                    </div>
                </div>

                <div class="wizard-section hidden-step" id="pasoConfirmar">
                    <div class="section-head">Cita confirmada</div>
                    <p class="status-text" style="margin-top:0;">Tu pago se realizó correctamente y tu cita quedó confirmada.</p>
                    <div class="detail-box" id="detalleConfirmacion">
                        <div class="detail-item"><strong>Médico:</strong> <span id="confirmMedico">-</span></div>
                        <div class="detail-item"><strong>Fecha:</strong> <span id="confirmFecha">-</span></div>
                        <div class="detail-item"><strong>Horario:</strong> <span id="confirmHora">-</span></div>
                        <div class="detail-item"><strong>Motivo:</strong> <span id="confirmMotivo">-</span></div>
                    </div>
                    <button type="submit" id="btnSubmitHidden" style="display:none;">Guardar cita</button>
                </div>
            </form>
        </div>

        <h3 style="font-weight: 700; color: #333; margin: 3rem 0 1.5rem 0;">Mis Citas</h3>
        <?php if (empty($citas)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="far fa-calendar"></i></div>
                <h5 style="color: #888; font-weight: 600;">No tienes citas agendadas</h5>
                <p style="color: #999; margin-bottom: 0;">Agenda tu primera cita usando el formulario de arriba</p>
            </div>
        <?php else: ?>
            <?php foreach ($citas as $c): 
                    $estado = $c['estado'] ?? 'pendiente';
                    $fecha_cita = $c['fecha'];
                    $hoy = date('Y-m-d');
                    if ($estado === 'pendiente' && $fecha_cita < $hoy) {
                        $estado = 'acabada';
                    }
                    $estadoClass = $estado === 'cancelada' ? 'estado-cancelada' : ($estado === 'acabada' ? 'estado-acabada' : 'estado-pendiente');
                    $estadoTexto = ucfirst($estado);
                    $cardClass = $estado === 'cancelada' ? 'cita-card cancelada' : ($estado === 'acabada' ? 'cita-card acabada' : 'cita-card');
            ?>
                <div class="<?= $cardClass ?>">
                    <div class="row">
                        <div class="col-md-3">
                            <div style="font-weight: 700; color: #bc8fec; font-size: 1.1rem; margin-bottom: 0.4rem;"><i class="far fa-calendar"></i> <?= htmlspecialchars($c['fecha'], ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <div class="col-md-3">
                            <div style="color: #dc3545; font-weight: 600; margin-bottom: 0.6rem;"><i class="far fa-clock"></i> <?= htmlspecialchars($c['hora'], ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <div class="col-md-6">
                            <div style="color: #555; margin-bottom: 0.4rem;">
                                <strong>Motivo:</strong> <?= htmlspecialchars($c['motivo'], ENT_QUOTES, 'UTF-8') ?>
                                <span class="estado-badge <?= $estadoClass ?>"><?= $estadoTexto ?></span>
                            </div>
                        </div>
                    </div>
                    <div style="color: #999; font-size: 0.85rem; margin-top: 0.8rem;">Registrada: <?= htmlspecialchars($c['created_at'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php if ($estado === 'pendiente'): ?>
                        <div style="margin-top: 0.9rem; text-align: right;">
                            <form method="post" style="display: inline;" onsubmit="return confirm('¿Seguro que deseas cancelar esta cita?');">
                                <input type="hidden" name="accion" value="cancelar_cita">
                                <input type="hidden" name="id_cita" value="<?= (int)$c['id_cita'] ?>">
                                <button type="submit" style="background: #dc3545; color: #fff; border: none; border-radius: 7px; padding: 0.5rem 0.9rem; font-weight: 700;">Cancelar cita</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        var form = document.getElementById('citaForm');
        if (!form) return;

        var doctorGrid = document.getElementById('doctorGrid');
        var medicoInput = document.getElementById('medicoInput');
        var idMedicoInput = document.getElementById('idMedicoInput');
        var horaInput = document.getElementById('horaInput');
        var fechaInput = document.getElementById('fechaInput');
        var motivoInput = document.getElementById('motivoInput');
        var pasoHorario = document.getElementById('pasoHorario');
        var pasoDetalle = document.getElementById('pasoDetalle');
        var pasoPago = document.getElementById('pasoPago');
        var pasoConfirmar = document.getElementById('pasoConfirmar');
        var btnMostrarHorarios = document.getElementById('btnMostrarHorarios');
        var btnAgendar = document.getElementById('btnAgendar');
        var btnPagar = document.getElementById('btnPagar');
        var btnVolverMedico = document.getElementById('btnVolverMedico');
        var btnVolverHorario = document.getElementById('btnVolverHorario');
        var btnVolverDetalle = document.getElementById('btnVolverDetalle');
        var btnSubmitHidden = document.getElementById('btnSubmitHidden');
        var slotGrid = document.getElementById('slotGrid');
        var statusHorario = document.getElementById('statusHorario');
        var statusPago = document.getElementById('statusPago');
        var pagoConfirmado = document.getElementById('pagoConfirmado');
        var nombreTarjetaInput = document.getElementById('nombreTarjeta');
        var numeroTarjetaInput = document.getElementById('numeroTarjeta');
        var vencimientoTarjetaInput = document.getElementById('vencimientoTarjeta');
        var cvvTarjetaInput = document.getElementById('cvvTarjeta');
        var detalleMedico = document.getElementById('detalleMedico');
        var detalleFecha = document.getElementById('detalleFecha');
        var detalleHora = document.getElementById('detalleHora');
        var detalleMotivo = document.getElementById('detalleMotivo');
        var confirmMedico = document.getElementById('confirmMedico');
        var confirmFecha = document.getElementById('confirmFecha');
        var confirmHora = document.getElementById('confirmHora');
        var confirmMotivo = document.getElementById('confirmMotivo');
        var wizardSections = [pasoMedico, pasoHorario, pasoDetalle, pasoPago, pasoConfirmar];

        function irASeccion(seccionObjetivo) {
            wizardSections.forEach(function(sec) {
                if (!sec) return;
                if (sec === seccionObjetivo) {
                    sec.classList.remove('hidden-step');
                } else {
                    sec.classList.add('hidden-step');
                }
            });
        }

        function parseSlots(rawSlots) {
            if (!rawSlots) return [];
            return rawSlots.split(',').map(function(slot) {
                return slot.trim();
            }).filter(function(slot) {
                return slot.length > 0;
            });
        }

        function actualizarDetalle() {
            detalleMedico.textContent = medicoInput.value || '-';
            detalleFecha.textContent = fechaInput.value || '-';
            detalleHora.textContent = horaInput.value || '-';
            detalleMotivo.textContent = motivoInput.value || '-';
            if (confirmMedico) confirmMedico.textContent = medicoInput.value || '-';
            if (confirmFecha) confirmFecha.textContent = fechaInput.value || '-';
            if (confirmHora) confirmHora.textContent = horaInput.value || '-';
            if (confirmMotivo) confirmMotivo.textContent = motivoInput.value || '-';
        }

        function limpiarPago() {
            pagoConfirmado.value = '0';
            if (nombreTarjetaInput) nombreTarjetaInput.value = '';
            if (numeroTarjetaInput) numeroTarjetaInput.value = '';
            if (vencimientoTarjetaInput) vencimientoTarjetaInput.value = '';
            if (cvvTarjetaInput) cvvTarjetaInput.value = '';
            statusPago.textContent = 'Completa los datos de tarjeta para continuar.';
        }

        function limpiarPasosPosteriores() {
            horaInput.value = '';
            slotGrid.innerHTML = '';
            limpiarPago();
            actualizarDetalle();
            irASeccion(pasoMedico);
        }

        function renderHorarios() {
            var doctorCard = doctorGrid.querySelector('.doctor-option.active');
            var horarios = doctorCard ? parseSlots(doctorCard.getAttribute('data-slots')) : [];
            slotGrid.innerHTML = '';

            if (horarios.length === 0) {
                statusHorario.textContent = 'Este fisioterapeuta no tiene horarios configurados.';
                return;
            }

            horarios.forEach(function(hora) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'slot-btn';
                btn.textContent = hora;
                btn.addEventListener('click', function() {
                    var slots = slotGrid.querySelectorAll('.slot-btn');
                    slots.forEach(function(s) { s.classList.remove('active'); });
                    btn.classList.add('active');
                    horaInput.value = hora;
                    statusHorario.textContent = 'Horario seleccionado: ' + hora;
                    irASeccion(pasoDetalle);
                    actualizarDetalle();
                });
                slotGrid.appendChild(btn);
            });
        }

        if (doctorGrid) {
            doctorGrid.addEventListener('click', function(e) {
                var target = e.target.closest('.doctor-option');
                if (!target) return;
                var cards = doctorGrid.querySelectorAll('.doctor-option');
                cards.forEach(function(card) { card.classList.remove('active'); });
                target.classList.add('active');
                idMedicoInput.value = target.getAttribute('data-doctor-id') || '';
                medicoInput.value = target.getAttribute('data-doctor') || '';
                limpiarPasosPosteriores();
            });
        }

        btnMostrarHorarios.addEventListener('click', function() {
            if (!medicoInput.value || !idMedicoInput.value) {
                alert('Selecciona un médico antes de ver horarios.');
                return;
            }
            if (!fechaInput.value || !motivoInput.value.trim()) {
                alert('Completa la fecha y el motivo para continuar.');
                return;
            }
            renderHorarios();
            irASeccion(pasoHorario);
            statusHorario.textContent = 'Horarios disponibles mostrados.';
            actualizarDetalle();
        });

        btnAgendar.addEventListener('click', function() {
            if (!horaInput.value) {
                alert('Elige un horario para agendar la cita.');
                return;
            }
            irASeccion(pasoPago);
            actualizarDetalle();
        });

        btnPagar.addEventListener('click', function() {
            var nombre = (nombreTarjetaInput.value || '').trim();
            var numero = (numeroTarjetaInput.value || '').trim();
            var vencimiento = (vencimientoTarjetaInput.value || '').trim();
            var cvv = (cvvTarjetaInput.value || '').trim();

            if (!nombre || !numero || !vencimiento || !cvv) {
                pagoConfirmado.value = '0';
                statusPago.textContent = 'Faltan datos en el pago.';
                alert('Completa todos los campos del pago.');
                irASeccion(pasoPago);
                return;
            }

            pagoConfirmado.value = '1';
            statusPago.textContent = 'Pago realizado con éxito. Ya puedes confirmar tu cita.';
            irASeccion(pasoConfirmar);
            setTimeout(function() {
                if (pagoConfirmado.value !== '1') return;
                if (typeof form.requestSubmit === 'function' && btnSubmitHidden) {
                    form.requestSubmit(btnSubmitHidden);
                } else {
                    form.submit();
                }
            }, 1200);
        });

        if (btnVolverMedico) {
            btnVolverMedico.addEventListener('click', function() {
                horaInput.value = '';
                var slots = slotGrid.querySelectorAll('.slot-btn');
                slots.forEach(function(s) { s.classList.remove('active'); });
                statusHorario.textContent = 'Selecciona un horario disponible.';
                actualizarDetalle();
                irASeccion(pasoMedico);
            });
        }

        if (btnVolverHorario) {
            btnVolverHorario.addEventListener('click', function() {
                irASeccion(pasoHorario);
            });
        }

        if (btnVolverDetalle) {
            btnVolverDetalle.addEventListener('click', function() {
                limpiarPago();
                irASeccion(pasoDetalle);
            });
        }

        form.addEventListener('submit', function(e){
            var fecha = fechaInput.value;
            var hora = horaInput.value;
            var motivo = motivoInput.value.trim();
            var medico = medicoInput.value;
            var idMedico = idMedicoInput.value;
            if (!fecha || !hora || !motivo || !medico || !idMedico) {
                e.preventDefault();
                alert('Completa todos los pasos antes de confirmar la cita.');
                return;
            }
            if (pagoConfirmado.value !== '1') {
                e.preventDefault();
                alert('Debes realizar el pago antes de confirmar.');
                return;
            }

            var parts = fecha.split('-');
            if (parts.length !== 3){
                e.preventDefault();
                alert('Fecha inválida.');
                return;
            }
            var y = parseInt(parts[0],10), m = parseInt(parts[1],10)-1, d = parseInt(parts[2],10);
            var timeParts = hora.split(':');
            if (timeParts.length < 2){
                e.preventDefault();
                alert('Hora inválida.');
                return;
            }
            var hh = parseInt(timeParts[0],10), mm = parseInt(timeParts[1],10);
            var selected = new Date(y, m, d, hh, mm);
            var now = new Date();
            if (selected.getTime() + 30000 < now.getTime()){
                e.preventDefault();
                alert('No puedes agendar una cita en el pasado. Elige una fecha/hora futura.');
            }
        });

        actualizarDetalle();
        irASeccion(pasoMedico);
    });
    </script>
</body>
</html>
