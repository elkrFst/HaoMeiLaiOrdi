<?php
// get_detalle_pedido.php
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

// 1. Validar Sesión de Admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(["success" => false, "message" => "Acceso denegado."]);
    exit();
}

$pedido_id = $_GET['id'] ?? 0;
if (empty($pedido_id)) {
    echo json_encode(["success" => false, "message" => "ID de pedido no válido."]);
    exit();
}

try {
    // 2. Obtener datos del pedido y cliente
    $stmt = $conn->prepare("
        SELECT p.codigo_pedido, p.total, p.fecha_creacion, p.estado, u.nombre AS cliente_nombre, u.email AS cliente_email
        FROM pedidos p
        JOIN usuarios u ON p.cliente_id = u.cliente_id
        WHERE p.pedido_id = ?
    ");
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $result_pedido = $stmt->get_result();
    
    if ($result_pedido->num_rows === 0) {
        throw new Exception("Pedido no encontrado.");
    }
    $pedido = $result_pedido->fetch_assoc();
    $stmt->close();

    // 3. Obtener productos del pedido
    $stmt_detalles = $conn->prepare("
        SELECT dp.cantidad, dp.precio_unidad, a.producto AS producto_nombre
        FROM detalle_pedidos dp
        JOIN almacen a ON dp.producto_id = a.id
        WHERE dp.pedido_id = ?
    ");
    $stmt_detalles->bind_param("i", $pedido_id);
    $stmt_detalles->execute();
    $result_detalles = $stmt_detalles->get_result();
    $productos = $result_detalles->fetch_all(MYSQLI_ASSOC);
    $stmt_detalles->close();

    // 4. Devolver todo
    echo json_encode([
        "success" => true,
        "codigo" => $pedido['codigo_pedido'],
        "cliente_nombre" => $pedido['cliente_nombre'],
        "cliente_email" => $pedido['cliente_email'],
        "fecha" => date("d/m/Y H:i", strtotime($pedido['fecha_creacion'])),
        "total" => number_format($pedido['total'], 2),
        "estado" => $pedido['estado'],
        "productos" => $productos
    ]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

$conn->close();
?>