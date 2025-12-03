<?php
// php/menu2/enviar_recibo.php
session_start();
header('Content-Type: application/json');

// 1. Verificar sesión
if (!isset($_SESSION['cliente_id'])) {
    echo json_encode(['success' => false, 'message' => 'No hay sesión activa']);
    exit;
}

// 2. Conexión
$host = "srv562.hstgr.io";
$user = "u162512390_Admin";
$pass = "biuqkb>O3";
$db = "u162512390_HaoMeiLai";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error conexión BD']);
    exit;
}

// 3. Recibir ID del pedido
$input = json_decode(file_get_contents('php://input'), true);
$pedido_id = $input['pedido_id'] ?? 0;

if (!$pedido_id) {
    echo json_encode(['success' => false, 'message' => 'ID de pedido inválido']);
    exit;
}

// 4. Obtener datos del usuario y del pedido
// Verificamos que el pedido pertenezca al usuario logueado por seguridad
$sql = "SELECT p.codigo_pedido, p.total, p.fecha_pago, u.email, u.nombre 
        FROM pedidos p 
        JOIN usuarios u ON p.cliente_id = u.cliente_id 
        WHERE p.pedido_id = ? AND p.cliente_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $pedido_id, $_SESSION['cliente_id']);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Pedido no encontrado.']);
    exit;
}

$data = $res->fetch_assoc();
$to = $data['email'];
$subject = "Recibo de tu pedido #" . $data['codigo_pedido'] . " - HAO MEI LAI";

// 5. Construir cuerpo del correo
$mensaje = "
<html>
<head><title>Recibo de Compra</title></head>
<body style='font-family: Arial, sans-serif; color: #333;'>
    <div style='max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 20px; border-radius: 10px;'>
        <h2 style='color: #8B0000;'>¡Gracias por tu compra, {$data['nombre']}!</h2>
        <p>Hemos recibido tu pago correctamente a través de PayPal.</p>
        <hr>
        <p><strong>Código de Pedido:</strong> {$data['codigo_pedido']}</p>
        <p><strong>Fecha:</strong> {$data['fecha_pago']}</p>
        <h3>Detalle:</h3>
        <ul>";

// Obtener los productos del pedido
$sql_det = "SELECT dp.cantidad, dp.precio_unidad, a.producto 
            FROM detalle_pedidos dp 
            JOIN almacen a ON dp.producto_id = a.id 
            WHERE dp.pedido_id = ?";
$stmt_det = $conn->prepare($sql_det);
$stmt_det->bind_param("i", $pedido_id);
$stmt_det->execute();
$res_det = $stmt_det->get_result();

while ($item = $res_det->fetch_assoc()) {
    $subtotal = $item['cantidad'] * $item['precio_unidad'];
    $mensaje .= "<li>{$item['producto']} (x{$item['cantidad']}) - $" . number_format($subtotal, 2) . "</li>";
}

$mensaje .= "
        </ul>
        <h3 style='text-align: right;'>Total Pagado: $" . number_format($data['total'], 2) . "</h3>
        <hr>
        <p style='font-size: 12px; color: #777; text-align: center;'>HAO MEI LAI - Sabor Oriental</p>
    </div>
</body>
</html>";

// 6. Enviar Correo
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: no-reply@haomeilai.com" . "\r\n"; // Cambia esto por tu dominio real si tienes

if (mail($to, $subject, $mensaje, $headers)) {
    echo json_encode(['success' => true, 'message' => 'Correo enviado a ' . $to]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al enviar el correo.']);
}

$conn->close();
?>