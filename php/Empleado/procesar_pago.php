<?php
// procesar_pago.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carrito = isset($_POST['carrito']) ? json_decode($_POST['carrito'], true) : [];
    $metodo_pago = $_POST['metodo_pago'] ?? '';
    $hora_entrega = $_POST['hora_entrega'] ?? '';
    $correo_cliente = trim($_POST['correo_cliente'] ?? ''); // Nuevo campo para correo del cliente
    $total = 0;

    // Guardar carrito en sesión por si hay error
    $_SESSION['carrito_temp'] = $carrito;

    foreach ($carrito as $item) {
        $total += ($item['precio'] ?? 0) * ($item['cantidad'] ?? 1);
    }

    $pago_exitoso = false;
    $mensaje_error = "";
    $cambio = 0;

    if (empty($metodo_pago)) {
        $mensaje_error = "Debe seleccionar un método de pago.";
    } else {
        switch ($metodo_pago) {
            case 'tarjeta':
                $numero_tarjeta = trim($_POST['numero_tarjeta'] ?? '');
                $fecha_vencimiento = trim($_POST['fecha_vencimiento'] ?? '');
                $cvv = trim($_POST['cvv'] ?? '');

                if (empty($numero_tarjeta) || empty($fecha_vencimiento) || empty($cvv)) {
                    $mensaje_error = "Por favor, complete todos los datos de la tarjeta.";
                } else {
                    $pago_exitoso = true;

                    // Simulación de envío de correo de confirmación
                    if (!empty($correo_cliente) && filter_var($correo_cliente, FILTER_VALIDATE_EMAIL)) {
                        $to = $correo_cliente;
                        $subject = "Confirmación de pago con Tarjeta - Restaurante JUAN";
                        $message = "
                        <html>
                        <head><title>Confirmación de Pago</title></head>
                        <body>
                            <h2>¡Gracias por tu compra!</h2>
                            <p>Hemos recibido tu pago de <strong>$" . number_format($total, 2) . "</strong> con tarjeta.</p>
                            <p>Tu pedido será entregado aproximadamente a las <strong>" . htmlspecialchars($hora_entrega) . "</strong>.</p>
                            <p>¡Gracias por preferir Restaurante JUAN!</p>
                        </body>
                        </html>";
                        $headers = "MIME-Version: 1.0\r\n";
                        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                        $headers .= "From: <no-reply@restaurantejuan.com>\r\n";
                        @mail($to, $subject, $message, $headers);
                    }
                }
                break;

            case 'paypal':
                $paypal_email = trim($_POST['paypal_email'] ?? $correo_cliente);
                if (empty($paypal_email) || !filter_var($paypal_email, FILTER_VALIDATE_EMAIL)) {
                    $mensaje_error = "Por favor, ingrese un correo de PayPal válido.";
                } else {
                    $pago_exitoso = true;

                    // Simulación de envío de correo de confirmación
                    $to = $paypal_email;
                    $subject = "Confirmación de pago con PayPal - Restaurante JUAN";
                    $message = "
                    <html>
                    <head><title>Confirmación de Pago</title></head>
                    <body>
                        <h2>¡Gracias por tu compra!</h2>
                        <p>Hemos recibido tu pago de <strong>$" . number_format($total, 2) . "</strong> a través de PayPal.</p>
                        <p>Tu pedido será entregado aproximadamente a las <strong>" . htmlspecialchars($hora_entrega) . "</strong>.</p>
                        <p>¡Gracias por preferir Restaurante JUAN!</p>
                    </body>
                    </html>";
                    $headers = "MIME-Version: 1.0\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                    $headers .= "From: <no-reply@restaurantejuan.com>\r\n";
                    @mail($to, $subject, $message, $headers);
                }
                break;

            case 'efectivo':
                $cambio_para = isset($_POST['cambio_para']) ? floatval($_POST['cambio_para']) : 0;
                if ($cambio_para <= 0) {
                    $mensaje_error = "Por favor, ingrese con cuánto va a pagar.";
                } elseif ($cambio_para < $total) {
                    $mensaje_error = "El monto ingresado es menor al total del pedido.";
                } else {
                    $pago_exitoso = true;
                    $cambio = $cambio_para - $total;

                    // Simulación de correo al cliente
                    if (!empty($correo_cliente) && filter_var($correo_cliente, FILTER_VALIDATE_EMAIL)) {
                        $to = $correo_cliente;
                        $subject = "Confirmación de pago en efectivo - Restaurante JUAN";
                        $message = "
                        <html>
                        <head><title>Confirmación de Pago</title></head>
                        <body>
                            <h2>¡Gracias por tu compra!</h2>
                            <p>Tu pago de <strong>$" . number_format($total, 2) . "</strong> fue recibido en efectivo.</p>
                            <p>Recibiste con: <strong>$" . number_format($cambio_para, 2) . "</strong> — tu cambio es <strong>$" . number_format($cambio, 2) . "</strong>.</p>
                            <p>Tu pedido será entregado aproximadamente a las <strong>" . htmlspecialchars($hora_entrega) . "</strong>.</p>
                            <p>¡Gracias por preferir Restaurante JUAN!</p>
                        </body>
                        </html>";
                        $headers = "MIME-Version: 1.0\r\n";
                        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                        $headers .= "From: <no-reply@restaurantejuan.com>\r\n";
                        @mail($to, $subject, $message, $headers);
                    }
                }
                break;
        }
    }

    if ($pago_exitoso) {
        unset($_SESSION['carrito_temp']);

        $_SESSION['pedido'] = [
            'carrito' => $carrito,
            'total' => $total,
            'metodo' => $metodo_pago,
            'hora' => date('H:i:s'),
            'fecha' => date('Y-m-d'),
            'hora_entrega' => $hora_entrega,
            'cambio' => $cambio
        ];

        header('Location: server.php');
        exit;
    } else {
        $_SESSION['error_pago'] = $mensaje_error;
        header('Location: pago');
        exit;
    }
} else {
    header('Location: empleado.php');
    exit;
}
?>
