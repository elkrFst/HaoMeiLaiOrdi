<?php
// buscar_pedido.php
session_start();
header('Content-Type: application/json');

// --- Configuración de Conexión ---
$host = "srv562.hstgr.io";
$user = "u162512390_Admin";
$pass = "biuqkb>O3";
$db = "u162512390_HaoMeiLai";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Error de conexión."]);
    exit();
}

// 1. Validar Sesión de Empleado
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Empleado') {
    echo json_encode(["success" => false, "message" => "Acceso denegado."]);
    exit();
}

// 2. Obtener Código
$codigo_pedido = $_POST['codigo'] ?? '';
if (empty($codigo_pedido)) {
    echo json_encode(["success" => false, "message" => "No se proporcionó código."]);
    exit();
}

// 3. Buscar Pedido
$stmt = $conn->prepare("SELECT pedido_id, estado FROM pedidos WHERE codigo_pedido = ?");
$stmt->bind_param("s", $codigo_pedido);
$stmt->execute();
$result_pedido = $stmt->get_result();

if ($result_pedido->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Código de pedido no encontrado."]);
    exit();
}

$pedido = $result_pedido->fetch_assoc();
$pedido_id = $pedido['pedido_id'];

// 4. Verificar Estado del Pedido
if ($pedido['estado'] === 'Pagado') {
    echo json_encode(["success" => false, "message" => "Este pedido ya fue pagado."]);
    exit();
}
if ($pedido['estado'] === 'Cancelado') {
    echo json_encode(["success" => false, "message" => "Este pedido fue cancelado."]);
    exit();
}

// 5. Obtener Detalles (Items del carrito)
$stmt_detalles = $conn->prepare("
    SELECT dp.producto_id, a.producto, dp.cantidad, dp.precio_unidad 
    FROM detalle_pedidos dp
    JOIN almacen a ON dp.producto_id = a.id
    WHERE dp.pedido_id = ?
");
$stmt_detalles->bind_param("i", $pedido_id);
$stmt_detalles->execute();
$result_detalles = $stmt_detalles->get_result();

$carrito = [];
while ($row = $result_detalles->fetch_assoc()) {
    $carrito[] = [
        'id' => $row['producto_id'],
        'nombre' => $row['producto'],
        'cantidad' => $row['cantidad'],
        'precio' => $row['precio_unidad']
    ];
}

if (empty($carrito)) {
    echo json_encode(["success" => false, "message" => "Este pedido no tiene productos."]);
    exit();
}

// 6. Devolver Carrito
echo json_encode([
    "success" => true,
    "carrito" => $carrito,
    "codigo_pedido" => $codigo_pedido // Devolvemos el código para guardarlo
]);

$stmt->close();
$stmt_detalles->close();
$conn->close();
?>