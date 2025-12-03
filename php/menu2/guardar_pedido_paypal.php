<?php
// php/menu2/guardar_pedido_paypal.php
session_start();
header('Content-Type: application/json');

// --- CONEXIÓN ---
$host = "srv562.hstgr.io";
$user = "u162512390_Admin";
$pass = "biuqkb>O3";
$db = "u162512390_HaoMeiLai";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Error de conexión BD"]);
    exit();
}

// --- VALIDACIONES ---
$cliente_id = $_SESSION['cliente_id'] ?? null;
if (!$cliente_id) {
    echo json_encode(["success" => false, "message" => "Sesión expirada. Inicia sesión."]);
    exit();
}

// Leer JSON recibido
$input = json_decode(file_get_contents('php://input'), true);
$carrito = $input['carrito'] ?? [];
$detalles_paypal = $input['detalles'] ?? null;

if (empty($carrito) || !$detalles_paypal) {
    echo json_encode(["success" => false, "message" => "Datos incompletos o carrito vacío."]);
    exit();
}

// --- CALCULAR TOTAL ---
$total_pedido = 0;
foreach ($carrito as $item) {
    $total_pedido += $item['precio'] * $item['cantidad'];
}

// --- TRANSACCIÓN ---
$conn->begin_transaction();

try {
    // 1. Generar Código de Pedido Único
    // Usamos un prefijo + un identificador único basado en el tiempo
    $codigo_pedido = 'PAY-' . strtoupper(substr(uniqid(), -6)); 
    $status = 'Pagado'; 

    // 2. Insertar Pedido (Incluyendo el codigo_pedido generado)
    // NOTA: Si tu tabla 'pedidos' tiene la columna 'codigo_pedido', la agregamos aquí.
    $sql_pedido = "INSERT INTO pedidos (cliente_id, codigo_pedido, fecha_pago, total, estado) VALUES (?, ?, NOW(), ?, ?)";
    $stmt = $conn->prepare($sql_pedido);
    
    // "isds" = integer, string, double, string
    $stmt->bind_param("isds", $cliente_id, $codigo_pedido, $total_pedido, $status);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al crear pedido: " . $stmt->error);
    }
    
    $pedido_id = $conn->insert_id; // ID autoincremental interno
    $stmt->close();

    // 3. Insertar Detalles y Bajar Stock
    $sql_detalle = "INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unidad) VALUES (?, ?, ?, ?)";
    $stmt_detalle = $conn->prepare($sql_detalle);

    $sql_stock = "UPDATE almacen SET stock = stock - ? WHERE id = ?";
    $stmt_stock = $conn->prepare($sql_stock);

    foreach ($carrito as $item) {
        $pid = $item['id'];
        $cant = $item['cantidad'];
        $precio = $item['precio'];

        // Guardar detalle
        $stmt_detalle->bind_param("iiid", $pedido_id, $pid, $cant, $precio);
        if (!$stmt_detalle->execute()) throw new Exception("Error al guardar detalle del producto ID: $pid");

        // Descontar stock
        $stmt_stock->bind_param("ii", $cant, $pid);
        if (!$stmt_stock->execute()) throw new Exception("Error al actualizar stock del producto ID: $pid");
    }

    $conn->commit();

    echo json_encode([
        "success" => true, 
        "message" => "¡Pago exitoso! Pedido #$codigo_pedido procesado correctamente.",
        "codigo_pedido" => $codigo_pedido,
        "pedido_id" => $pedido_id
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

$conn->close();
?>