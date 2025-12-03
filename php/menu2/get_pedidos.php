<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('session.cookie_path', '/'); // <-- AÑADE ESTA LÍNEA
session_start();
header('Content-Type: application/json');

// 1. Verificar si el usuario ha iniciado sesión
// Reviso 'cliente_id' porque así se llama en tu tabla 'usuarios'
if (!isset($_SESSION['cliente_id'])) {
    http_response_code(403); // Prohibido
    // Devolvemos el JSON que el JS SÍ espera
    echo json_encode(['ok' => false, 'error' => 'No ha iniciado sesión.']);
    exit;
}

// --- Configuración de Conexión (Copiada de tu ver_factura.php) ---
$host = "srv562.hstgr.io";
$user = "u162512390_Admin";
$pass = "biuqkb>O3";
$db = "u162512390_HaoMeiLai";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    // Devolvemos el JSON que el JS SÍ espera
    echo json_encode(['ok' => false, 'error' => 'Error de conexión a la base de datos.']);
    exit;
}
$conn->set_charset("utf8mb4");

try {
    $cliente_id = $_SESSION['cliente_id'];

    // 2. Preparar y ejecutar la consulta segura
    // (Basado en tu pedidos.sql)
    $stmt = $conn->prepare(
        "SELECT pedido_id, codigo_pedido, total, fecha_creacion, estado 
         FROM pedidos 
         WHERE cliente_id = ? 
         ORDER BY fecha_creacion DESC
         LIMIT 20" // Limitar a los 20 más recientes
    );
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pedidos = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();

    // 3. Devolver los pedidos en el formato JSON correcto
    echo json_encode(['ok' => true, 'pedidos' => $pedidos]);

} catch (Exception $e) {
    http_response_code(500); // Error del servidor
    // Devolvemos el JSON que el JS SÍ espera
    echo json_encode(['ok' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
}
?>