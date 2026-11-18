<?php
// config/db.php
// Conexión a la base de datos en modo procedimental. No llamar a session_start() aquí.

$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'bts8';
$db_name = 'rompiendo_barreras';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$conn) {
    // En desarrollo es útil ver el error; en producción registra el error y muestra un mensaje genérico.
    die('Error de conexión a la base de datos: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');

// Usa la variable $conn (procedimental) en los scripts que incluyan este archivo.

?>