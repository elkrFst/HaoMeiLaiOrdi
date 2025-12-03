<?php
session_start();
require_once '../../conexion.php'; // Asegúrate que esta ruta a tu conexión es correcta

// Establecer la cabecera para devolver JSON
header('Content-Type: application/json');

// Verificar que sea admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// --- MEJORA IMPORTANTE: Validación de ID ---
$pedido_id = 0;
if (isset($_GET['id'])) {
    $pedido_id = intval($_GET['id']); // Convertir a número
}

// Si el ID no es un número válido o es 0, detenemos la ejecución
if ($pedido_id <= 0) {
    // Este es el error que probablemente estabas viendo
    echo json_encode(['success' => false, 'message' => 'ID de pedido no válido o no proporcionado']);
    exit();
}
// --- FIN DE LA MEJORA ---

$response = [
    'success' => false,
    'pedido_id' => $pedido_id
];

// --- 1. Obtener datos principales del pedido y el cliente ---
$sql_pedido = "
    SELECT 
        p.total, 
        p.fecha_pago,
        u.nombre AS nombre_cliente
    FROM pedidos p
    JOIN usuarios u ON p.cliente_id = u.cliente_id 
    WHERE p.pedido_id = ?
";

$stmt_pedido = $conn->prepare($sql_pedido);
$stmt_pedido->bind_param("i", $pedido_id);
$stmt_pedido->execute();
$result_pedido = $stmt_pedido->get_result();

if ($result_pedido->num_rows > 0) {
    $pedido_info = $result_pedido->fetch_assoc();
    
    $response['cliente'] = $pedido_info['nombre_cliente'];
    $response['fecha'] = $pedido_info['fecha_pago']; 
    $response['total'] = $pedido_info['total'];

    // --- 2. Obtener los productos del pedido ---
    $sql_productos = "
        SELECT 
            a.producto AS producto_nombre,
            dp.cantidad,
            dp.precio_unidad
        FROM detalle_pedidos dp
        JOIN almacen a ON dp.producto_id = a.id
        WHERE dp.pedido_id = ?
    ";

    $stmt_productos = $conn->prepare($sql_productos);
    $stmt_productos->bind_param("i", $pedido_id);
    $stmt_productos->execute();
    $result_productos = $stmt_productos->get_result();
    
    $productos = [];
    while($row = $result_productos->fetch_assoc()) {
        $productos[] = $row;
    }

    $response['productos'] = $productos;
    $response['success'] = true;

} else {
    // Si el ID es válido pero no se encuentra en la BD
    $response['message'] = 'Pedido no encontrado (ID: ' . $pedido_id . ')';
}

// Cerramos conexiones
$stmt_pedido->close();
if (isset($stmt_productos)) {
    $stmt_productos->close();
}
$conn->close();

// Devolvemos la respuesta JSON
echo json_encode($response);

?>