<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ver_factura.php
// --- Configuración de Conexión ---
$host = "srv562.hstgr.io";
$user = "u162512390_Admin";
$pass = "biuqkb>O3";
$db = "u162512390_HaoMeiLai";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Error de conexión.");
}
$conn->set_charset("utf8mb4"); // Buena práctica para acentos

$codigo_pedido = $_GET['codigo'] ?? '';
if (empty($codigo_pedido)) {
    die("Código no proporcionado.");
}

// 1. Buscar Pedido y Cliente (Sin comprobación de sesión, como antes)
$stmt = $conn->prepare("
    SELECT p.*, u.nombre, u.email
    FROM pedidos p
    JOIN usuarios u ON p.cliente_id = u.cliente_id
    WHERE p.codigo_pedido = ?
");
$stmt->bind_param("s", $codigo_pedido);
$stmt->execute();
$result_pedido = $stmt->get_result();

if ($result_pedido->num_rows === 0) {
    die("Pedido no encontrado.");
}
$pedido = $result_pedido->fetch_assoc();
// Obtenemos el ID del pedido para la siguiente consulta
$pedido_id_para_detalles = $pedido['pedido_id']; 
$stmt->close();

// 2. Buscar Detalles
$stmt_detalles = $conn->prepare("
    SELECT dp.cantidad, dp.precio_unidad, a.producto 
    FROM detalle_pedidos dp
    JOIN almacen a ON dp.producto_id = a.id
    WHERE dp.pedido_id = ?
");
// Usamos el ID que obtuvimos de la consulta anterior
$stmt_detalles->bind_param("i", $pedido_id_para_detalles); 
$stmt_detalles->execute();
$result_detalles = $stmt_detalles->get_result();
$detalles = $result_detalles->fetch_all(MYSQLI_ASSOC);
$stmt_detalles->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura <?php echo htmlspecialchars($pedido['codigo_pedido']); ?></title>
    <style>
        /* (Tus estilos de factura) */
        body { font-family: sans-serif; margin: 0; padding: 20px; background: #f9f9f9; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .invoice-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #b30028; padding-bottom: 20px; }
        .invoice-header img { max-width: 100px; }
        .invoice-header h1 { color: #b30028; margin: 0; }
        .details { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th { background: #f5f5f5; color: #b30028; padding: 10px; border-bottom: 2px solid #ddd; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        .align-right { text-align: right; }
        .total-row { font-weight: bold; font-size: 1.1em; }
        .total-row td { border-top: 2px solid #b30028; color: #b30028; }
        .footer { text-align: center; margin-top: 30px; font-size: 0.9em; color: #777; }
        .print-button { text-align: center; margin-top: 20px; }
        .print-button button { padding: 12px 25px; background: #b30028; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1em; }
        @media print {
            body { background: #fff; }
            .print-button { display: none; }
            .invoice-box { box-shadow: none; border: none; margin: 0; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="invoice-header">
            <img src="imagenes/logo comida.png" alt="Logo Hao Mei Lai">
            <h1>FACTURA</h1>
        </div>

        <div class="details">
            <div>
                <strong>Cliente:</strong><br>
                <?php echo htmlspecialchars($pedido['nombre']); ?><br>
                <strong>Email</strong><br>
                <?php echo htmlspecialchars($pedido['email']); ?></br>
            </div>
            <div>
                <strong>Pedido: <?php echo htmlspecialchars($pedido['codigo_pedido']); ?></strong><br>
                Fecha: <?php echo date("d/m/Y H:i", strtotime($pedido['fecha_creacion'])); ?><br>
                Estado: <?php echo htmlspecialchars($pedido['estado']); ?>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="align-right">Cantidad</th>
                    <th class="align-right">Precio Unit.</th>
                    <th class="align-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $item): 
                    $subtotal = $item['cantidad'] * $item['precio_unidad'];
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['producto']); ?></td>
                    <td class="align-right"><?php echo $item['cantidad']; ?></td>
                    <td class="align-right">$<?php echo number_format($item['precio_unidad'], 2); ?></td>
                    <td class="align-right">$<?php echo number_format($subtotal, 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" class="align-right">TOTAL:</td>
                    <td class="align-right">$<?php echo number_format($pedido['total'], 2); ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <p>Gracias por tu compra. Presenta esta factura o el código en caja para pagar.</p>
        </div>
    </div>
    <div class="print-button">
        <button onclick="window.print()">Imprimir / Guardar como PDF</button>
    </div>
</body>
</html>