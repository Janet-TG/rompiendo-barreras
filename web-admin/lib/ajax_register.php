<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$telefono = trim($_POST['telefono'] ?? '');

if ($nombre === '' || $email === '' || $password === '' || $telefono === '') {
    echo json_encode(['success' => false, 'error' => 'Completa todos los campos requeridos.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Correo inválido.']);
    exit;
}

// verificar duplicado
$checkSql = "SELECT id_usr FROM usuarios WHERE mail = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $checkSql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if (mysqli_fetch_assoc($res)) {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'error' => 'Ya existe una cuenta con ese correo.']);
        exit;
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'error' => 'Error en la verificación de usuarios.']);
    exit;
}

// Guardar contraseña sin hash
$hash = $password;

// Iniciar transacción para crear usuario y cliente juntos
mysqli_begin_transaction($conn);

try {
    $insertSql = "INSERT INTO usuarios (nom_usr, mail, pass, telefono, tip_usu) VALUES (?, ?, ?, ?, ?)";
    $stmt2 = mysqli_prepare($conn, $insertSql);
    if (!$stmt2) {
        throw new Exception('Error en la consulta de inserción de usuario.');
    }

    $tip_usu = 2; // cliente por defecto
    mysqli_stmt_bind_param($stmt2, 'ssssi', $nombre, $email, $hash, $telefono, $tip_usu);
    if (!mysqli_stmt_execute($stmt2)) {
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmt2);
        throw new Exception('Error al crear el usuario. ' . $err);
    }
    mysqli_stmt_close($stmt2);

    $nuevoIdUsr = mysqli_insert_id($conn);

    // Nota: Ya no creamos registro en clientes, todo está en usuarios
    // Los datos principales (nombre, correo, teléfono) están en usuarios

    // Confirmar transacción
    mysqli_commit($conn);

    echo json_encode(['success' => true, 'message' => 'Registro completado. Por favor, inicia sesión.']);
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
