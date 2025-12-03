<?php
session_start();

// 1. SEGURIDAD Y CONEXIÓN
// Ajusta la ruta de require_once si tu archivo de conexión está en otro lado
require_once '../../conexion.php'; 

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /login");
    exit();
}

// 2. LÓGICA DE PAGINACIÓN
$registros_por_pagina = 20;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;

$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Contar total de pedidos para saber cuántas páginas hay
$sql_conteo = "SELECT COUNT(*) as total FROM pedidos";
$resultado_conteo = mysqli_query($conn, $sql_conteo);
$fila_conteo = mysqli_fetch_assoc($resultado_conteo);
$total_registros = $fila_conteo['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// 3. CONSULTA DE PEDIDOS (TODOS)
$sql_pedidos = "
    SELECT 
        p.pedido_id, 
        p.codigo_pedido,
        u.nombre AS nombre_cliente, 
        p.total, 
        p.fecha_pago,
        p.fecha_creacion,
        p.estado
    FROM pedidos p
    JOIN usuarios u ON p.cliente_id = u.cliente_id 
    ORDER BY p.fecha_creacion DESC 
    LIMIT $offset, $registros_por_pagina
";
$result_pedidos = mysqli_query($conn, $sql_pedidos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial Completo de Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-boxes"></i> Historial de Pedidos</h2>
        <a href="/panel" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Panel
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result_pedidos) > 0): ?>
                            <?php while($pedido = mysqli_fetch_assoc($result_pedidos)): ?>
                                <tr>
                                    <td><?php echo $pedido['pedido_id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($pedido['codigo_pedido']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($pedido['nombre_cliente']); ?></td>
                                    <td>
                                        <?php 
                                            // Usar fecha de pago si existe, si no, fecha de creación
                                            $fecha = $pedido['fecha_pago'] ? $pedido['fecha_pago'] : $pedido['fecha_creacion'];
                                            echo date("d/m/Y H:i", strtotime($fecha)); 
                                        ?>
                                    </td>
                                    <td>$<?php echo number_format($pedido['total'], 2); ?></td>
                                    <td>
                                        <?php 
                                            $badgeClass = 'bg-secondary';
                                            if($pedido['estado'] == 'Pagado') $badgeClass = 'bg-success';
                                            if($pedido['estado'] == 'Cancelado') $badgeClass = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo $pedido['estado']; ?></span>
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-sm text-white" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDetallePedido" 
                                                data-pedido-id="<?php echo $pedido['pedido_id']; ?>">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">No se encontraron pedidos.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <nav aria-label="Navegación de páginas">
                <ul class="pagination justify-content-center mt-3">
                    <li class="page-item <?php echo ($pagina_actual <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>">Anterior</a>
                    </li>

                    <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                        <li class="page-item <?php echo ($pagina_actual == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?php echo ($pagina_actual >= $total_paginas) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>">Siguiente</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetallePedido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detalle del Pedido <span id="detallePedidoId"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3 p-3 bg-light rounded">
                    <div class="col-md-4"><strong>Cliente:</strong> <br><span id="detalleCliente" class="text-primary"></span></div>
                    <div class="col-md-4"><strong>Fecha:</strong> <br><span id="detalleFecha"></span></div>
                    <div class="col-md-4"><strong>Total:</strong> <br><span id="detalleTotal" class="fw-bold text-success fs-5"></span></div>
                </div>
                
                <h6 class="border-bottom pb-2">Productos Comprados</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-end">Precio Unit.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detalleProductosBody">
                            </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalDetalle = document.getElementById('modalDetallePedido');

    if (modalDetalle) {
        modalDetalle.addEventListener('show.bs.modal', function(event) {
            const link = event.relatedTarget;
            const pedidoId = link.getAttribute('data-pedido-id');
            
            // Limpieza visual
            document.getElementById('detallePedidoId').textContent = `#${pedidoId}`;
            document.getElementById('detalleCliente').textContent = 'Cargando...';
            document.getElementById('detalleProductosBody').innerHTML = '<tr><td colspan="4" class="text-center">Cargando datos...</td></tr>';

            // Petición AJAX (Ajusta la ruta si es necesario)
            fetch('../Empleado/get_detalle_pedido.php?id=' + encodeURIComponent(pedidoId))
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('detalleCliente').textContent = data.cliente_nombre;
                    document.getElementById('detalleFecha').textContent = data.fecha;
                    document.getElementById('detalleTotal').textContent = `$${data.total}`;
                    
                    const tbody = document.getElementById('detalleProductosBody');
                    tbody.innerHTML = '';
                    
                    if (data.productos && data.productos.length > 0) {
                        data.productos.forEach(item => {
                            const cantidad = parseFloat(item.cantidad);
                            const precio = parseFloat(item.precio_unidad);
                            const subtotal = (cantidad * precio).toFixed(2);
                            
                            tbody.innerHTML += `
                                <tr>
                                    <td>${item.producto_nombre}</td>
                                    <td class="text-center">${item.cantidad}</td>
                                    <td class="text-end">$${precio.toFixed(2)}</td>
                                    <td class="text-end">$${subtotal}</td>
                                </tr>`;
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="4" class="text-center">Sin productos</td></tr>';
                    }
                } else {
                    document.getElementById('detalleProductosBody').innerHTML = `<tr><td colspan="4" class="text-danger text-center">${data.message}</td></tr>`;
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('detalleProductosBody').innerHTML = `<tr><td colspan="4" class="text-danger text-center">Error de conexión</td></tr>`;
            });
        });
    }
});
</script>

</body>
</html>