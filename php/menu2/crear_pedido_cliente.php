<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// AÑADIMOS ESTO PARA FORZAR VER ERRORES
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- 1. CONFIGURACIÓN DE SESIÓN (¡LA CLAVE!) ---
ini_set('session.cookie_path', '/');
session_start();

// El script responderá en formato JSON
header('Content-Type: application/json');

// --- 2. CARGAR PHPMailer (Sabemos que esto funciona) ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'php/PHPMailer/src/Exception.php';
require 'php/PHPMailer/src/PHPMailer.php';
require 'php/PHPMailer/src/SMTP.php';

// --- 3. CONEXIÓN A B.D. (Sabemos que esto funciona) ---
$host = "srv562.hstgr.io";
$user = "u162512390_Admin";
$pass = "biuqkb>O3";
$db = "u162512390_HaoMeiLai";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Error de conexión a la B.D.: " . $conn->connect_error]);
    exit();
}
$conn->set_charset("utf8mb4");

// --- 4. VERIFICAR SESIÓN Y CARRITO (Sabemos que esto funciona) ---

if (!isset($_SESSION['cliente_id']) || !isset($_SESSION['email']) || !isset($_SESSION['nombre'])) {
    echo json_encode(["success" => false, "message" => "Error: No has iniciado sesión."]);
    exit();
}

$email_cliente = $_SESSION['email'];
$cliente_id = $_SESSION['cliente_id'];
$nombre_cliente = $_SESSION['nombre'];

$carrito = json_decode($_POST['carrito_data'] ?? '[]', true);

if (empty($carrito)) {
    echo json_encode(["success" => false, "message" => "El carrito está vacío."]);
    exit();
}

// --- 5. LÓGICA DEL PEDIDO (¡EL "RESTO DEL CÓDIGO"!) ---
// Iniciamos una transacción. Si algo falla, deshacemos todo.
$conn->begin_transaction();

try {
    // 5a. Calcular Total y Generar Código
    $total_pedido = 0;
    foreach ($carrito as $item) {
        if (!is_numeric($item['precio']) || !is_numeric($item['cantidad']) || $item['precio'] <= 0 || $item['cantidad'] <= 0) {
            throw new Exception("Datos del carrito inválidos para el producto ID: " . $item['id']);
        }
        $total_pedido += $item['precio'] * $item['cantidad'];
    }
    $codigo_pedido = 'HML-' . strtoupper(bin2hex(random_bytes(4)));

    // 5b. Insertar en 'pedidos'
    $stmt_pedido = $conn->prepare("INSERT INTO pedidos (cliente_id, codigo_pedido, total, estado) VALUES (?, ?, ?, 'Pendiente')");
    if (!$stmt_pedido) {
        throw new Exception("Error al preparar la consulta de pedido: " . $conn->error);
    }
    $stmt_pedido->bind_param("isd", $cliente_id, $codigo_pedido, $total_pedido);
    $stmt_pedido->execute();
    $pedido_id = $conn->insert_id;
    $stmt_pedido->close();

    // 5c. Insertar en 'detalles_pedido' y Actualizar Stock
    $stmt_detalle = $conn->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unidad) VALUES (?, ?, ?, ?)");
    $stmt_stock = $conn->prepare("UPDATE almacen SET stock = stock - ? WHERE id = ? AND stock >= ?");

    if (!$stmt_detalle || !$stmt_stock) {
        throw new Exception("Error al preparar consultas de detalle o stock: " . $conn->error);
    }

    $detalles_html_email = ""; // Para el cuerpo del email

    foreach ($carrito as $item) {
        $producto_id = $item['id'];
        $cantidad = $item['cantidad'];
        $precio = $item['precio'];
        $nombre_producto = $item['nombre']; // Asumiendo que 'nombre' viene en el carrito desde JS

        // Insertar detalle
        $stmt_detalle->bind_param("iiid", $pedido_id, $producto_id, $cantidad, $precio);
        $stmt_detalle->execute();
        
        // Actualizar stock
        $stmt_stock->bind_param("iii", $cantidad, $producto_id, $cantidad);
        $stmt_stock->execute();
        
        if ($stmt_stock->affected_rows === 0) {
            // Si affected_rows es 0, significa que stock < cantidad. ¡No había suficiente!
            throw new Exception("Stock insuficiente para el producto: " . htmlspecialchars($nombre_producto));
        }

        // Construir la tabla para el email
        $subtotal_item = $cantidad * $precio;
        $detalles_html_email .= "<tr><td style='border:1px solid #ddd;padding:8px;'>" . htmlspecialchars($nombre_producto) . "</td><td style='border:1px solid #ddd;padding:8px;text-align:center;'>" . $cantidad . "</td><td style='border:1px solid #ddd;padding:8px;text-align:right;'>$" . number_format($precio, 2) . "</td><td style='border:1px solid #ddd;padding:8px;text-align:right;'>$" . number_format($subtotal_item, 2) . "</td></tr>";
    }
    $stmt_detalle->close();
    $stmt_stock->close();

    // 5d. Enviar Email de Confirmación
    $url_factura = "https://haomeilai.shop/ver_factura.php?codigo=" . $codigo_pedido;
    $asunto = "Confirmacion de tu Pedido: " . $codigo_pedido;
    
    $mensaje =
    " <html><head><style>
        body { font-family: Arial, sans-serif; } .container { width: 90%; margin: auto; padding: 20px; border: 1px solid #ddd; }
        .code { font-size: 28px; font-weight: bold; color: #d9534f; } table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; } th { background-color: #f2f2f2; }
        .button { background-color: #d9534f; color: #ffffff; padding: 12px 20px; text-decoration: none; border-radius: 5px; }
    </style></head>
    <body>
    <div class='container'>
        <p>¡Gracias por tu pedido, " . htmlspecialchars($nombre_cliente) . "!</p>
        <p>Tu código de pedido es:</p> <div class='code'>" . $codigo_pedido . "</div>
        <p>Presenta este código en caja para pagar y recoger tu comida.</p>
        <h3>Detalles del Pedido:</h3>
        <table>
            <thead><tr><th>Producto</th><th>Cantidad</th><th>Precio Unit.</th><th>Subtotal</th></tr></thead>
            <tbody>" . $detalles_html_email . "</tbody>
            <tfoot><tr><td colspan='3' style='text-align:right;font-weight:bold;'>TOTAL:</td><td style='text-align:right;font-weight:bold;'>$" . number_format($total_pedido, 2) . "</td></tr></tfoot>
        </table>
        <p style='text-align:center; margin-top: 25px;'><a href='" . $url_factura . "' class='button'>Ver Factura Online</a></p>
    </div>
    </body></html>";
    
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'recuperartucontrasena2@gmail.com';
        $mail->Password = 'jvcr wkff prov tyqh'; // Contraseña de aplicación
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        
        $mail->setFrom('recuperartucontrasena2@gmail.com', 'Hao Mei Lai'); // Debe ser el mismo que Username
        $mail->addAddress($email_cliente, $nombre_cliente); // Se envía al email de la SESIÓN
        
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $mensaje;
        $mail->CharSet = 'UTF-8';
        
        $mail->send();
        
    } catch (Exception $e) {
        // Si el email falla, NO revertimos la transacción, pero SÍ lanzamos una advertencia
        // Es mejor que el pedido se guarde y el email falle, a que no se guarde nada.
        throw new Exception("El pedido SÍ se guardó, pero el email falló. Error: " . $mail->ErrorInfo);
    }

    // 5e. ¡ÉXITO! Confirmar la transacción
    $conn->commit();

    // 6. Devolver éxito a JavaScript
    echo json_encode(["success" => true, "message" => "Pedido creado con éxito.", "codigo" => $codigo_pedido]);

} catch (Exception $e) {
    // 5f. ¡FALLO! Revertir la transacción si algo salió mal
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "Error al crear el pedido: " . $e->getMessage()]);
}

$conn->close();
?>