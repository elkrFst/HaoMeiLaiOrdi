<?php
// verificar_empleado.php
ini_set('session.cookie_path', '/');
session_start();

header('Content-Type: application/json');

// 1. Verificar que el empleado actual sea un empleado
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Empleado') {
    echo json_encode(['success' => false, 'message' => 'Acción no autorizada.']);
    exit();
}

// 2. Conectar a la base de datos
$host = "srv562.hstgr.io";
$user = "u162512390_Admin";
$pass = "biuqkb>O3";
$db = "u162512390_HaoMeiLai";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de B.D.']);
    exit();
}
$conn->set_charset("utf8mb4");

// 3. Obtener datos del POST
$empleado_id = $_POST['empleado_id'] ?? 0;
$password = $_POST['password'] ?? '';

if (empty($empleado_id) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit();
}

// 4. Buscar al empleado y verificar la contraseña
// (Basado en tu .sql, la contraseña se guarda en texto plano)
$stmt = $conn->prepare("SELECT * FROM empleados WHERE id = ? AND contraseña = ?");
$stmt->bind_param("is", $empleado_id, $password);
$stmt->execute();
$result = $stmt->get_result();
$empleado = $result->fetch_assoc();
$stmt->close();

if ($empleado) {
    // ¡Contraseña correcta!
    
    // 5. Destruir la sesión antigua
    session_destroy();
    
    // 6. Iniciar una sesión nueva y limpia
    session_start();
    
    // 7. Llenar la sesión con los datos del NUEVO empleado
    // (Estos nombres de sesión DEBEN COINCIDIR con los que usas en empleado.php)
    $_SESSION['usuario'] = $empleado['nombre'];
    $_SESSION['empleado_id'] = $empleado['id']; 
    $_SESSION['rol'] = 'Empleado'; 
    
    session_write_close(); // Guardar la nueva sesión
    
    echo json_encode(['success' => true]);
    
} else {
    // Contraseña incorrecta
    echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta.']);
}

$conn->close();
?>