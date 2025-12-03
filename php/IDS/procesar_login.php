<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('session.cookie_path', '/'); // <-- AÑADE ESTA LÍNEA
session_start();

// --- Configuración de Conexión ---
$host = "srv562.hstgr.io";
$user = "u162512390_Admin";
$pass = "biuqkb>O3";
$db = "u162512390_HaoMeiLai";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos."]);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validar campos vacíos
    if (empty($email) || empty($password)) {
        header('Location: iniciodesesion.php?error=campos');
        exit();
    }

    // 1️⃣ Verificar usuarios/admins (YA CORREGIDO, SIN DUPLICADOS)
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ? AND contraseña = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $stmt->close(); // Corregido el typo

    if ($usuario) {
        // 3️⃣ Iniciar sesión para usuario
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['cliente_id'] = $usuario['cliente_id'];
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['email'] = $usuario['email'];
        session_write_close();
    
        if ($usuario['rol'] === 'admin') {
            header("Location: panel");
        } else {
            header("Location: inicio"); // <-- ¡Aquí está la redirección!
        }
        exit();
    }

    // 2️⃣ Verificar empleados
    $stmt2 = $conn->prepare("SELECT * FROM empleados WHERE numero_trabajador = ? AND contraseña = ?");
    $stmt2->bind_param("ss", $email, $password);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $empleado = $result2->fetch_assoc();
    $stmt2->close(); // Corregido el typo

    if ($empleado) {
        // INICIO DE SESIÓN Y REDIRECCIÓN DEL EMPLEADO
        $_SESSION['usuario'] = $empleado['nombre']; // O el campo que uses
        // Asumiendo que tu tabla empleados tiene 'empleado_id'
        $_SESSION['empleado_id'] = $empleado['empleado_id']; 
        $_SESSION['rol'] = 'Empleado'; // Forzar rol
        header('Location: caja'); // Ruta de tu .htaccess
        exit();
    }

    // Si no encontró a nadie, regresa al login con error
    header('Location: iniciodesesion.php?error=noencontrado');
    exit();

} else {
    // Si no es POST, redirigir
    header('Location: iniciodesesion.php');
    exit();
}
?>