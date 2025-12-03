<?php
// pago.php
session_start();

// Evitar caché
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Seguridad: Verificar rol de Empleado
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Empleado') {
    header("Location: /php/IDS/iniciodesesion.php");
    exit();
}

// Lógica de Procesamiento
$host = "srv562.hstgr.io";
$user = "u162512390_Admin";
$pass = "biuqkb>O3";
$db = "u162512390_HaoMeiLai";

$carrito = [];
$total = 0;
$pago_exitoso = false;
$codigo_pedido_procesado = null;
$logo_url = "../../imagenes/logo comida.png"; // Ruta al logo

// Recibir carrito
if (isset($_POST['carrito'])) {
    $carrito = json_decode($_POST['carrito'], true);
}

// Calcular total (sin IVA)
if (is_array($carrito)) {
    foreach ($carrito as $item) {
        $precio_item = filter_var($item['precio'] ?? 0, FILTER_VALIDATE_FLOAT);
        $cantidad_item = filter_var($item['cantidad'] ?? 0, FILTER_VALIDATE_INT);
        if ($precio_item !== false && $cantidad_item > 0) {
            $total += $precio_item * $cantidad_item;
        }
    }
}

// *** REDONDEAR HACIA ARRIBA ***
$total_con_iva = ceil($total * 1.16);

// Procesar confirmación de pago
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_pago'])) {
    $metodo_pago = $_POST['metodo_pago'] ?? 'desconocido';
    $pago_efectivo = isset($_POST['pago_efectivo']) ? floatval($_POST['pago_efectivo']) : 0;
    $codigo_pedido = $_POST['codigo_pedido'] ?? null;
    $id_empleado_sesion = $_SESSION['empleado_id'] ?? null;

    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) die("Error de conexión.");

    $conn->begin_transaction();
    try {
        if (!empty($codigo_pedido)) { // Actualizar pedido existente
            $stmt_get_id = $conn->prepare("SELECT pedido_id FROM pedidos WHERE codigo_pedido = ? AND estado = 'Pendiente'");
            $stmt_get_id->bind_param("s", $codigo_pedido); $stmt_get_id->execute(); $result_id = $stmt_get_id->get_result();
            if ($result_id->num_rows === 0) throw new Exception("Pedido $codigo_pedido no encontrado o ya pagado.");
            $pedido_id = $result_id->fetch_assoc()['pedido_id']; $stmt_get_id->close();

            $stmt_update = $conn->prepare("UPDATE pedidos SET estado = 'Pagado', metodo_pago = ?, empleado_id = ?, fecha_pago = NOW() WHERE pedido_id = ?");
            $stmt_update->bind_param("sii", $metodo_pago, $id_empleado_sesion, $pedido_id);
            if (!$stmt_update->execute()) throw new Exception("Error al actualizar pedido: " . $stmt_update->error);
            $stmt_update->close(); $codigo_pedido_procesado = $codigo_pedido;

        } else { // Crear nuevo pedido
            $codigo_pedido_procesado = "EMP-" . strtoupper(substr(uniqid(), 7, 6)); 
            $cliente_id_mostrador = 1;
            $stmt_pedido = $conn->prepare("INSERT INTO pedidos (cliente_id, empleado_id, codigo_pedido, total, estado, metodo_pago, fecha_creacion, fecha_pago) VALUES (?, ?, ?, ?, 'Pagado', ?, NOW(), NOW())");
            $stmt_pedido->bind_param("iisds", $cliente_id_mostrador, $id_empleado_sesion, $codigo_pedido_procesado, $total_con_iva, $metodo_pago);
            if (!$stmt_pedido->execute()) throw new Exception("Error al crear pedido: " . $stmt_pedido->error);
            $pedido_id = $conn->insert_id; 
            $stmt_pedido->close();

            $stmt_detalle = $conn->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unidad) VALUES (?, ?, ?, ?)");
            if (is_array($carrito)) {
                foreach ($carrito as $item) {
                    $producto_id_item = filter_var($item['id'] ?? null, FILTER_VALIDATE_INT);
                    $cantidad_item = filter_var($item['cantidad'] ?? 0, FILTER_VALIDATE_INT);
                    $precio_item = filter_var($item['precio'] ?? 0, FILTER_VALIDATE_FLOAT);
                    if ($producto_id_item && $cantidad_item > 0 && $precio_item !== false) {
                        $stmt_detalle->bind_param("iiid", $pedido_id, $producto_id_item, $cantidad_item, $precio_item);
                        if (!$stmt_detalle->execute()) throw new Exception("Error al insertar detalle: " . $stmt_detalle->error);
                    }
                }
            }
            $stmt_detalle->close();
        }
        $conn->commit(); 
        $pago_exitoso = true;

    } catch (Exception $e) { 
        $conn->rollback(); 
        $error_pago = $e->getMessage(); 
    }
    $conn->close();
}

// Redirigir si no hay carrito
if (empty($carrito) && !$pago_exitoso) { header("Location: /php/Empleado/empleado.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Pago</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #fff7e6; display: flex; justify-content: center; align-items: center; min-height: 100vh; font-family: sans-serif; padding: 1rem; }
        .pago-container { background: #fff; padding: 2.5rem; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); width: 100%; max-width: 550px; text-align: center; }
        .pago-logo img { height: 60px; margin-bottom: 1.5rem; }
        h2 { color: #a33d3d; font-weight: 700; margin-bottom: 1rem; }
        .tabla-pedido { text-align: left; margin-bottom: 2rem; border: 1px solid #eee; border-radius: 8px; overflow: hidden; }
        .tabla-pedido table { width: 100%; border-collapse: collapse; }
        .tabla-pedido th, .tabla-pedido td { padding: 0.75rem 1rem; border-bottom: 1px solid #eee; }
        .tabla-pedido th { background-color: #f8f9fa; font-weight: 600; font-size: 0.9em; color: #555; }
        .tabla-pedido td { font-size: 0.95em; }
        .tabla-pedido .text-end { text-align: right; }
        .tabla-pedido tr:last-child td { border-bottom: none; }
        .total-a-pagar { font-size: 2.5rem; font-weight: bold; color: #333; margin-bottom: 1.5rem; }
        .total-a-pagar span { color: #a33d3d; }
        .form-label { font-weight: 600; color: #555; text-align: left; display: block; margin-bottom: 0.5rem; }
        .form-control, .form-select { border-radius: 8px; border: 1px solid #ddd; padding: 0.75rem 1rem; font-size: 1rem; margin-bottom: 1rem; width: 100%; box-sizing: border-box; }
        .form-control:focus, .form-select:focus { box-shadow: 0 0 0 3px rgba(163, 61, 61, 0.15); border-color: #a33d3d; outline: none; }
        #panel-efectivo, #panel-transferencia { text-align: left; }
        #panel-transferencia { background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 1.5rem; margin-top: 1rem; margin-bottom: 1.5rem; text-align: left; font-size: 0.95em; }
        #panel-transferencia h5 { color: #a33d3d; font-weight: 600; margin-bottom: 1rem; border-bottom: 1px solid #ddd; padding-bottom: 0.5rem; }
        #panel-transferencia p { margin-bottom: 0.5rem; }
        #panel-transferencia strong { color: #333; }

        .botones-pago { display: flex; justify-content: space-between; margin-top: 1rem; }
        .botones-pago .btn { width: 48%; padding: 0.8rem 1rem; font-size: 1.1rem; border-radius: 8px; font-weight: 600; }
        .btn-success { background: #28a745; border: none; transition: background 0.2s; color: white; }
        .btn-success:hover { background: #218838; }
        .btn-success:disabled { background: #aaa; cursor: not-allowed; }
        .btn-outline-secondary { border-color: #6c757d; color: #6c757d; }
        .btn-outline-secondary:hover { background: #f1f1f1; color: #5a6268; border-color: #6c757d; }
        .change-display { font-size: 1.3rem; font-weight: 600; text-align: center; margin-top: 1rem; padding: 1rem; border-radius: 8px; background: #e6f7ec; color: #28a745; }
        .change-display.change-negative { background: #fdecea; color: #dc3545; }
        .lead { font-size: 1.1rem; } 
        .total-final { font-size: 1.6rem; font-weight: bold; margin-top: 1rem; } 
        .cambio-final { font-size: 1.3rem; color: #333; }
        .btn-primary { background: #a33d3d; border-color: #a33d3d; padding: 0.8rem 1.5rem; font-size: 1.1rem; border-radius: 8px; }
        .alert { padding: 1rem; border-radius: 8px; margin-top: 1rem; text-align: left; } 
        .alert-danger { background-color: #f8d7da; border-color: #f5c2c7; color: #842029; }
    </style>
</head>
<body>
    <div class="pago-container">

        <div class="pago-logo"> 
            <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Logo"> 
        </div>

        <?php if ($pago_exitoso): ?>
            <i class="fas fa-check-circle" style="font-size: 80px; color: #198754;"></i>
            <h2 class="mt-3">Pago Exitoso</h2>
            <p class="lead">Pedido <strong><?php echo $codigo_pedido_procesado; ?></strong> registrado.</p>
            <div class="total-final">Total Pagado: $<?php echo number_format($total_con_iva, 0); ?></div>
            <?php if ($metodo_pago === 'efectivo' && $pago_efectivo > $total_con_iva): ?>
                <div class="cambio-final">Cambio: $<?php echo number_format($pago_efectivo - $total_con_iva, 2); ?></div>
            <?php endif; ?>
            <a href="empleado.php" class="btn btn-primary mt-4">Siguiente Pedido</a>

        <?php elseif (isset($error_pago)): ?>
            <i class="fas fa-times-circle" style="font-size: 80px; color: #dc3545;"></i>
            <h2 class="mt-3">Error en el Pago</h2>
            <p class="lead">No se pudo procesar el pago.</p>
            <div class="alert alert-danger"><strong>Detalle:</strong> <?php echo htmlspecialchars($error_pago); ?></div>
            <a href="/caja" class="btn btn-secondary mt-4">Volver a Caja</a>

        <?php else: ?>
            <form method="POST" action="/php/Empleado/pago.php">
                <input type="hidden" name="carrito" value="<?php echo htmlspecialchars(json_encode($carrito)); ?>">
                <input type="hidden" name="codigo_pedido" value="<?php echo htmlspecialchars($_POST['codigo_pedido'] ?? ''); ?>">
                <input type="hidden" name="confirmar_pago" value="1">

                <h2>Confirmar Pago</h2>

                <div class="tabla-pedido">
                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-end">Cant.</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (is_array($carrito)): ?>
                            <?php foreach ($carrito as $item):
                                $precio_item = filter_var($item['precio'] ?? 0, FILTER_VALIDATE_FLOAT);
                                $cantidad_item = filter_var($item['cantidad'] ?? 0, FILTER_VALIDATE_INT);
                                $subtotal_item = ($precio_item !== false && $cantidad_item > 0) ? $precio_item * $cantidad_item : 0;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['nombre'] ?? 'N/A'); ?></td>
                                <td class="text-end"><?php echo $cantidad_item; ?></td>
                                <td class="text-end">$<?php echo number_format($precio_item, 2); ?></td>
                                <td class="text-end">$<?php echo number_format($subtotal_item, 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted">No hay productos.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="total-a-pagar">
                    Total (IVA): 
                    $<span><?php echo number_format($total_con_iva, 0); ?></span>
                </div>

                <div class="mb-3">
                    <label class="form-label">Método de Pago</label>
                    <select class="form-select" name="metodo_pago" id="metodo_pago" required>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="transferencia">Transferencia</option>
                    </select>
                </div>

                <div id="panel-efectivo">
                    <div class="mb-3">
                        <label for="cashAmount" class="form-label">Efectivo Recibido</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="cashAmount" name="pago_efectivo" oninput="calculateChange()">
                    </div>
                    <div class="change-display" id="changeDisplay" style="display: none;">
                        <span id="changeText"></span>
                    </div>
                </div>

                <div id="panel-transferencia" style="display: none;">
                    <h5>Datos para Transferencia Bancaria</h5>
                    <p><strong>Banco:</strong> Tu Banco S.A.</p>
                    <p><strong>Titular:</strong> Hao Mei Lai Restaurante</p>
                    <p><strong>Número de Cuenta:</strong> 1234567890</p>
                    <p><strong>CLABE:</strong> 012345678901234567</p>
                    <p><strong>Referencia:</strong> <?php echo htmlspecialchars($_POST['codigo_pedido'] ?? 'Venta Local'); ?></p>
                </div>

                <div class="botones-pago">
                    <a href="/php/Empleado/empleado.php" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success" id="btn-confirmar" disabled> Confirmar Pago </button>
                </div>

            </form>
        <?php endif; ?>

    </div>

<script>
    const totalToPay = <?php echo $total_con_iva; ?>;

    const metodoPagoEl = document.getElementById('metodo_pago');
    const btnConfirmar = document.getElementById('btn-confirmar');
    const cashInput = document.getElementById('cashAmount');
    const changeDisplay = document.getElementById('changeDisplay');
    const changeText = document.getElementById('changeText');

    function calculateChange() {
        const cash = parseFloat(cashInput.value) || 0;

        if (metodoPagoEl.value !== 'efectivo') {
            changeDisplay.style.display = 'none';
            btnConfirmar.disabled = false;
            return;
        }

        if (cash >= totalToPay) {
            const change = cash - totalToPay;
            changeDisplay.style.display = 'block';
            changeDisplay.classList.remove('change-negative');
            changeText.textContent = "Cambio: $" + change.toFixed(2);
            btnConfirmar.disabled = false;
        } else if (cash > 0) {
            const faltan = totalToPay - cash;
            changeDisplay.style.display = 'block';
            changeDisplay.classList.add('change-negative');
            changeText.textContent = "Faltan $" + faltan.toFixed(2);
            btnConfirmar.disabled = true;
        } else {
            changeDisplay.style.display = 'none';
            btnConfirmar.disabled = true;
        }
    }

    metodoPagoEl.addEventListener('change', () => {
        if (metodoPagoEl.value === 'efectivo') {
            document.getElementById('panel-efectivo').style.display = 'block';
            document.getElementById('panel-transferencia').style.display = 'none';
            btnConfirmar.disabled = true;
        } else if (metodoPagoEl.value === 'transferencia') {
            document.getElementById('panel-efectivo').style.display = 'none';
            document.getElementById('panel-transferencia').style.display = 'block';
            changeDisplay.style.display = 'none';
            btnConfirmar.disabled = false;
        } else {
            document.getElementById('panel-efectivo').style.display = 'none';
            document.getElementById('panel-transferencia').style.display = 'none';
            changeDisplay.style.display = 'none';
            btnConfirmar.disabled = false;
        }
    });
</script>

</body>
</html>
