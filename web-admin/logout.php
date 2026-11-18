<?php
// Logout completo
session_start();
session_destroy();
setcookie(session_name(), '', time() - 3600, '/');

// Redirigir a la página principal
header("Location: index.php");
exit();
?>
