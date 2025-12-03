<?php
// cargar_pedido.php (API para el empleado)
session_start();
header('Content-Type: application/json');

// Credenciales de conexión (obtenidas de empleado (3).php)
$host = "srv562.hstgr.io";
$user = "u162512390_Admin";
$pass = "biuqkb>O3";
$db = "u162512390_HaoMeiLai";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['codigo_pedido'])) {
    echo json_encode(["success" => false, "message" => "Código de pedido no recibido."]);
    exit();
}

$codigo_pedido = trim($_POST['codigo_pedido']);

// Obtener detalles del pedido, incluyendo el estado y la información del producto
$sql = "SELECT dp.cantidad, dp.precio_unidad, a.id, a.producto, p.estado
        FROM detalle_pedido dp
        JOIN almacen a ON dp.producto_id = a.id
        JOIN pedidos p ON dp.pedido_id = p.id
        WHERE dp.pedido_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $codigo_pedido);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Pedido no encontrado o código incorrecto."]);
    exit();
}

$productos_pedido = [];
$estado_pedido = '';

while ($row = $result->fetch_assoc()) {
    // El estado es el mismo para todos los rows del mismo pedido
    if (empty($estado_pedido)) {
        $estado_pedido = $row['estado'];
    }
    $productos_pedido[] = [
        'id' => $row['id'],
        'nombre' => $row['producto'],
        'precio' => floatval($row['precio_unidad']), // Usamos precio_unidad como precio del item
        'cantidad' => $row['cantidad']
    ];
}

$stmt->close();
$conn->close();

if ($estado_pedido !== 'Pendiente') {
    echo json_encode(["success" => false, "message" => "El pedido #{$codigo_pedido} ya ha sido procesado (Estado: {$estado_pedido})."]);
    exit();
}


echo json_encode([
    "success" => true,
    "message" => "Pedido cargado con éxito.",
    "pedido_id" => $codigo_pedido,
    "productos" => $productos_pedido
]);
?>