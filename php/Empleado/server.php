<?php
// server.php
session_start();

// Obtener el pedido procesado
$pedido = $_SESSION['pedido'] ?? null;

// Si el pedido no existe, redirige al inicio
if (!$pedido) {
    header('Location: pago.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f7f9fa;
            color: #333;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: #fff;
            border: 2px solid #c0392b;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 650px;
            width: 100%;
            padding: 30px;
            text-align: center;
        }
        h1 {
            color: #27ae60;
            font-size: 2.2em;
            margin-bottom: 10px;
        }
        h3 {
            color: #c0392b;
            border-top: 1px solid #eee;
            padding-top: 20px;
            margin-top: 25px;
        }
        p {
            margin: 8px 0;
            font-size: 1.1em;
        }
        ul {
            list-style-type: none;
            padding: 0;
            margin-top: 10px;
        }
        li {
            background-color: #f9f9f9;
            border-left: 4px solid #e74c3c;
            padding: 12px;
            margin-bottom: 8px;
            text-align: left;
        }
        .total, .cambio {
            font-weight: bold;
            font-size: 1.2em;
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            background-color: #c0392b;
            color: #fff;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 30px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #a93226;
        }
        .correo {
            background-color: #ecf0f1;
            border-radius: 6px;
            padding: 10px;
            font-size: 1em;
            color: #555;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ ¡Pago exitoso!</h1>
        <p><strong>Fecha:</strong> <?= $pedido['fecha']; ?></p>
        <p><strong>Hora del pedido:</strong> <?= $pedido['hora']; ?></p>
        <p><strong>Método de pago:</strong> <?= ucfirst(htmlspecialchars($pedido['metodo'])); ?></p>
        <p><strong>Hora estimada de entrega:</strong> <?= htmlspecialchars($pedido['hora_entrega']); ?></p>

        <?php if ($pedido['metodo'] === 'paypal' && isset($_POST['paypal_email'])): ?>
            <div class="correo">
                <strong>Correo de confirmación enviado a:</strong><br>
                <?= htmlspecialchars($_POST['paypal_email']); ?>
            </div>
        <?php endif; ?>

        <h3>Resumen del Pedido</h3>
        <ul>
            <?php foreach ($pedido['carrito'] as $item): ?>
                <?php
                    $nombre = htmlspecialchars($item['nombre']);
                    $cantidad = $item['cantidad'];
                    $precio_total_item = number_format($item['precio'] * $cantidad, 2);
                ?>
                <li><?= "{$nombre} (x{$cantidad}) - \${$precio_total_item}"; ?></li>
            <?php endforeach; ?>
        </ul>

        <p class="total"><strong>Total:</strong> $<?= number_format($pedido['total'], 2); ?></p>

        <?php if (isset($pedido['cambio']) && $pedido['cambio'] > 0): ?>
            <p class="cambio"><strong>Cambio a devolver:</strong> $<?= number_format($pedido['cambio'], 2); ?></p>
        <?php endif; ?>

        <?php unset($_SESSION['pedido']); // Limpiar el pedido después de mostrarlo ?>

        <a href="empleado.php" class="btn">🏠 Volver al menú principal</a>
    </div>
</body>
</html>
