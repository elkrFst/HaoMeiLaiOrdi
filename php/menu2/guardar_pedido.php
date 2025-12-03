<?php
// guardar_pedido.php
session_start();

header('Content-Type: application/json');

// Credenciales de conexión
$host = "srv562.hstgr.io";
$user = "u162512390_Admin";
$pass = "biuqkb>O3";
$db = "u162512390_HaoMeiLai";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos."]);
    exit();
}

// 1. Verificación de sesión de usuario y datos POST
$cliente_id = $_SESSION['cliente_id'] ?? null;
$nombre_cliente = $_SESSION['nombre'] ?? 'Cliente Desconocido'; 
$cliente_rol = strtolower($_SESSION['rol'] ?? '');

if ($cliente_rol !== 'usuario' || !$cliente_id) {
    echo json_encode(["success" => false, "message" => "Acceso denegado. Debe iniciar sesión como usuario."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['carrito_data']) || empty($_POST['carrito_data'])) {
    echo json_encode(["success" => false, "message" => "Datos de carrito no recibidos."]);
    exit();
}

// 2. Decodificar el JSON del carrito
$carrito = json_decode($_POST['carrito_data'], true);

if (!is_array($carrito) || count($carrito) === 0) {
    echo json_encode(["success" => false, "message" => "El carrito está vacío o tiene un formato inválido."]);
    exit();
}

// 3. Calcular el total del pedido en el servidor (más seguro)
$total_pedido = 0;
foreach ($carrito as $item) {
    $total_pedido += $item['precio'] * $item['cantidad'];
}

// Iniciar transacción para asegurar integridad (Pedido + Detalles + Stock)
$conn->begin_transaction();

try {
    // 4. Insertar el pedido principal
    $sql_pedido = "INSERT INTO pedidos (cliente_id, fecha_pago, total, estado) VALUES (?, NOW(), ?, 'Pendiente')";
    $stmt_pedido = $conn->prepare($sql_pedido);
    $stmt_pedido->bind_param("id", $cliente_id, $total_pedido);
    
    if (!$stmt_pedido->execute()) {
        throw new Exception("Error al insertar el pedido: " . $stmt_pedido->error);
    }
    
    $codigo_pedido = $conn->insert_id; // Obtener el ID del pedido generado
    $stmt_pedido->close();

    // Preparar consultas reutilizables
    $sql_detalle = "INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unidad) VALUES (?, ?, ?, ?)";
    $stmt_detalle = $conn->prepare($sql_detalle);

    // --- AQUI ESTABA LO COMENTADO (YA HABILITADO) ---
    $sql_stock_update = "UPDATE almacen SET stock = stock - ? WHERE id = ?";
    $stmt_stock_update = $conn->prepare($sql_stock_update);

    foreach ($carrito as $item) {
        $producto_id = $item['id'];
        $cantidad = $item['cantidad'];
        $precio_unidad = $item['precio'];

        // a) Insertar detalle
        $stmt_detalle->bind_param("iiid", $codigo_pedido, $producto_id, $cantidad, $precio_unidad);
        if (!$stmt_detalle->execute()) {
            throw new Exception("Error al insertar detalle para producto ID " . $producto_id . ": " . $stmt_detalle->error);
        }

        // b) Reducción de Stock (ACTIVADO)
        $stmt_stock_update->bind_param("ii", $cantidad, $producto_id);
        if (!$stmt_stock_update->execute()) {
             throw new Exception("Error al actualizar stock para producto ID " . $producto_id . ": " . $stmt_stock_update->error);
        }
    }
    $stmt_detalle->close();
    $stmt_stock_update->close(); 

    // Confirmar la transacción
    $conn->commit();

    // 5. Devolver éxito con la información del pedido
    echo json_encode([
        "success" => true,
        "message" => "¡Pedido **#{$codigo_pedido}** guardado con éxito!",
        "codigo" => $codigo_pedido,
        "nombre_cliente" => $nombre_cliente,
        "fecha" => date('Y-m-d H:i:s'),
        "total" => number_format($total_pedido, 2, '.', ','),
        "total_raw" => $total_pedido
    ]);

} catch (Exception $e) {
    // Si algo falla, deshacer todos los cambios en la base de datos
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

$conn->close();
?>