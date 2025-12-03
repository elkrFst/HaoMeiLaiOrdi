<?php
session_start();

// --- INICIO DE LA SOLUCIÓN ANTI-CACHÉ ---
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0"); // Para navegadores antiguos
// --- FIN DE LA SOLUCIÓN ---

// Verificar si la sesión no está activa o si el rol no es 'admin'
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    // Si no está logueado o no es admin, redirigir al login
    header("Location: /login");
    exit();
}

require_once '../../conexion.php';

    // Obtener datos para tarjetas
    $ventas = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM ventas"))[0] ?? 0;
    // MODIFICADO: Contar solo pedidos pagados
    $pedidos = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM pedidos WHERE estado = 'Pagado'"))[0] ?? 0;
    $clientes = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM clientes WHERE atendido=1"))[0] ?? 0;
    // MODIFICADO: Obtener los últimos pedidos para la tabla
    $sql_pedidos = "
        SELECT 
            p.pedido_id, 
            p.codigo_pedido,
            u.nombre AS nombre_cliente, 
            p.total, 
            p.fecha_pago, -- <<< USAR fecha_pago
            p.estado
        FROM pedidos p
        JOIN usuarios u ON p.cliente_id = u.cliente_id 
        WHERE p.estado = 'Pagado' -- <<< FILTRAR por Pagado
        ORDER BY p.fecha_pago DESC -- <<< ORDENAR por fecha de pago reciente
        LIMIT 6
    ";
    $result_pedidos = mysqli_query($conn, $sql_pedidos);
    // Manejo de error en consulta
    if (!$result_pedidos) {
        $ultimos_pedidos = [];
        $error_pedidos = "Error al consultar pedidos: " . mysqli_error($conn);
    } else {
        $ultimos_pedidos = mysqli_fetch_all($result_pedidos, MYSQLI_ASSOC);
        $error_pedidos = null;
    }

    // Paginación productos
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 8;
    $offset = ($page - 1) * $limit;
    $total = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM almacen"))[0];
    $pages = ceil($total / $limit);
    $productos = mysqli_query($conn, "SELECT * FROM almacen LIMIT $offset, $limit");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ======= ESTILOS MODO OSCURO ======= */
        body.dark-mode {
            background-color: #333 !important; /* Fondo oscuro */
            color: #f1f1f1 !important; /* Texto claro */
        }
        
        body.dark-mode .sidebar {
            background-color: #1a1a1a !important; /* Sidebar más oscuro */
        }
        
        body.dark-mode .sidebar .nav-link {
            color: #f1f1f1 !important;
        }
        
        body.dark-mode .sidebar .nav-link:hover,
        body.dark-mode .sidebar .nav-link.active {
            background-color: #4a4a4a !important;
        }
        
        body.dark-mode .content h1,
        body.dark-mode .card h3 {
            color: #f6c453 !important; /* Títulos en color de acento claro */
        }
        
        body.dark-mode .card {
            background-color: #444 !important; /* Fondo de tarjetas oscuro */
            box-shadow: 0 4px 6px rgba(255, 255, 255, 0.15) !important;
        }
        
        body.dark-mode table,
        body.dark-mode .table-responsive {
            background-color: #555 !important;
        }
        
        body.dark-mode thead {
            background-color: #f6c453 !important; /* Encabezado de tabla en color de acento */
            color: #1a1a1a !important;
        }
        
        body.dark-mode tbody tr:hover {
            background-color: #666 !important;
            color: #fff !important;
        }
        
        /* Modal */
        body.dark-mode .modal-content {
            background-color: #444;
            color: #f1f1f1;
        }
        
        body.dark-mode .modal-header {
            background-color: #1a1a1a !important;
            color: white;
        }
        body.dark-mode .modal-title {
            color: white;
        }
        body.dark-mode .btn-close {
            filter: invert(1); /* Hace que la X del modal sea visible */
        }
        
        /* Textos dentro de tarjetas de ventas */
        body.dark-mode .card-title,
        body.dark-mode small,
        body.dark-mode p.text-dark {
            color: #f1f1f1 !important;
        }
        
        /* Paginación */
        body.dark-mode .page-link {
            background-color: #444;
            color: #f1f1f1;
        }
        body.dark-mode .page-item.active .page-link {
            background-color: #7b2c2c;
            border-color: #7b2c2c;
            color: white;
        }
        body.dark-mode .page-link:hover {
            background-color: #666;
            color: white;
        }
        
        .seleccionable {
        cursor: pointer;
        border: 2px dashed red;
        }
        
        .seleccionada {
            background-color: rgba(255,0,0,0.1);
        }

        body { background: #f8f9fa; }
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            color: #fff;
        }
        .sidebar .nav-link {
            color: #fff;
        }
        .sidebar .nav-link.active {
            background: #495057;
        }
        .content {
            padding: 2rem;
        }
        .hide { display: none !important; 
        }
        
        /* Estilo para el código clickeable */
        .codigo-pedido-link {
            font-weight: 600;
            color: #a33d3d;
            text-decoration: none;
        }
        .codigo-pedido-link:hover {
            color: #f6c453;
            text-decoration: underline;
        }
        /* Estilos para el estado del pedido */
        .badge.bg-success { background-color: #198754 !important; }
        .badge.bg-warning { background-color: #ffc107 !important; }
        .badge.bg-danger { background-color: #dc3545 !important; }
    </style>
    <style>
    /* ======= ESTILO DASHBOARD CHINO ======= */
    
    .hide{
        display: none !important;
    }

    body {
        background-color: #f6e7d8 !important;
        font-family: Arial, sans-serif;
    }

    /* Sidebar */
    .sidebar {
        background-color: #7b2c2c !important;
        padding-top: 20px;
        color: #fff;
    }
    .sidebar .nav-link {
        color: #fff !important;
        font-weight: bold;
        margin: 5px 0;
        border-radius: 8px;
        transition: background 0.3s;
    }
    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        background-color: #a33d3d !important;
    }
    .sidebar .logo {
        text-align: center;
        margin-bottom: 30px;
    }
    .sidebar .logo img {
        width: 80px;
        margin-bottom: 10px;
    }
    .sidebar .logo h2 {
        font-size: 18px;
        margin: 0;
        color: #fff;
    }

    /* Contenido principal */
    .content h1 {
        color: #7b2c2c;
        font-weight: bold;
        margin-bottom: 20px;
    }

    /* Tarjetas */
    .card {
        border: none !important;
        border-radius: 15px !important;
        box-shadow: 0 4px 6px rgba(0,0,0,0.15);
        text-align: center;
        padding: 20px;
    }
    .card h3 {
        font-size: 18px;
        margin-bottom: 10px;
        color: #7b2c2c;
    }
    .card p {
        font-size: 26px;
        font-weight: bold;
        color: #333;
    }

    /* Botones */
    .btn-primary {
        background-color: #a33d3d !important;
        border: none !important;
        border-radius: 10px;
    }
    .btn-primary:hover {
        background-color: #7b2c2c !important;
    }

    /* Tablas */
    table {
        border-radius: 10px;
        overflow: hidden;
    }
    thead {
        background-color: #7b2c2c;
        color: white;
    }
    tbody tr:hover {
        background-color: #f6c453;
    }

    /* Gráficas */
    .chart-box {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-top: 20px;
    }

    /* ======= ESTILOS MEJORADOS PARA TRABAJADORES ======= */
    .empleado-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .empleado-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.2) !important;
    }
    
    .empleado-card .card {
        overflow: hidden;
        height: 100%;
        position: relative;
    }
    
    .empleado-card img.empleado-foto { /* Usamos el selector de la clase 'empleado-foto' */
        border-radius: 50%;
        width: 110px;
        height: 110px;
        object-fit: cover;
        /* Ajustes clave para centrar y que fluya */
        display: block; /* Asegura que sea un elemento de bloque */
        margin: 18px auto 0 auto; /* Centra horizontalmente y añade margen superior */
        /* El resto de tus estilos */
        border: 4px solid #f6c453;
        background: #fff;
        box-shadow: 0 2px 8px rgba(163,61,61,0.10);
    }
    
    .empleado-foto:hover {
        transform: scale(1.05);
    }
    
    .empleado-info {
        padding: 15px;
        text-align: left;
    }
    
    .empleado-info h5 {
        color: #7b2c2c;
        font-weight: bold;
        margin-bottom: 10px;
        font-size: 1.2rem;
    }
    
    .empleado-info .numero-trabajador {
        background: #7b2c2c;
        color: white;
        padding: 3px 8px;
        border-radius: 15px;
        font-size: 0.85rem;
        font-weight: bold;
        display: inline-block;
        margin-bottom: 8px;
    }
    
    .empleado-info .descripcion {
        color: #666;
        font-size: 0.9rem;
        line-height: 1.4;
        margin-bottom: 0;
    }
    
    .empleado-actions {
        position: absolute;
        bottom: 10px;
        right: 10px;
        display: flex;
        gap: 5px;
    }
    
    .btn-action {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        opacity: 0.9;
        transition: opacity 0.3s ease;
    }
    
    .btn-action:hover {
        opacity: 1;
    }
    
    /* Mejoras al modal */
    .modal-header {
        background-color: #7b2c2c;
        color: white;
        border-radius: 15px 15px 0 0;
    }
    
    .modal-content {
        border-radius: 15px;
        border: none;
        overflow: hidden;
    }
    
    .form-control:focus {
        border-color: #7b2c2c;
        box-shadow: 0 0 0 0.2rem rgba(123, 44, 44, 0.25);
    }
    
    .empleado-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .empleado-stats {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .stat-badge {
        background: #f8f9fa;
        padding: 8px 15px;
        border-radius: 20px;
        border-left: 4px solid #7b2c2c;
        font-size: 0.9rem;
    }
</style>

</head>
<body>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 sidebar py-4">
            <div class="logo">
                <img src="../../imagenes/logo comida.png" alt="Logo">
            <h2>HAO MEI LAI</h2>
            </div>

            <h4 class="text-center mb-4">Menú</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="#" onclick="showSection('dashboard')">Dashboard</a>
                </li>
                <!-- === INSERTA ESTE NUEVO BLOQUE AQUÍ === -->
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="showSection('ventas')">
                        <!-- Puedes añadir un ícono como los demás si quieres, ej: <i class="fas fa-chart-bar"></i> -->
                        Ventas
                    </a>
                </li>
                <!-- === FIN DEL BLOQUE NUEVO === -->
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="showSection('almacen')">Almacén</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="showSection('trabajadores')">Trabajadores</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="darkModeToggle" title="Modo Oscuro/Claro">
                    Modo Oscuro
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link" href="/php/IDS/cerrarsesion.php">Cerrar Sesión</a>
                </li>
            </ul>
        </nav>
        <main class="col-md-10 content">
            <!-- Dashboard -->
            <div id="dashboardSection">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Últimos Pedidos Recibidos</h4>
                    <a href="php/Admin/ver_pedidos.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-list"></i> Ver todos los pedidos
                    </a>
                </div>
            
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Código Pedido</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($ultimos_pedidos)): ?>
                                <?php foreach ($ultimos_pedidos as $pedido): ?>
                                    <tr class="pedido-row">
                                        <td>
                                            <a href="#" class="pedido-link"
                                               data-bs-toggle="modal"
                                               data-bs-target="#modalDetallePedido"
                                               data-pedido-id="<?php echo htmlspecialchars($pedido['pedido_id']); ?>">
                                                <?php echo htmlspecialchars($pedido['pedido_id']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($pedido['nombre_cliente']); ?></td>
                                        <td><?php echo date("d/m/Y H:i A", strtotime($pedido['fecha_pago'])); ?></td>
                                        <td><?php echo number_format($pedido['total'], 2); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($pedido['estado'] ?? 'Pendiente'); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No hay pedidos recientes.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal fade" id="modalDetallePedido" tabindex="-1" aria-labelledby="modalDetallePedidoLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalDetallePedidoLabel">Detalle del Pedido <span id="detallePedidoId"></span></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-md-4"><strong>Cliente:</strong> <span id="detalleCliente"></span></div>
                                <div class="col-md-4"><strong>Fecha:</strong> <span id="detalleFecha"></span></div>
                                <div class="col-md-4"><strong>Total:</strong> <span id="detalleTotal"></span></div>
                            </div>
            
                            <h6>Productos del Pedido:</h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th style="width: 100px;">Cantidad</th>
                                            <th style="width: 100px;">Precio Unit.</th>
                                            <th style="width: 100px;">Subtotal</th>
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
                <!-- ==================================== -->
                <!-- ===   NUEVA SECCIÓN DE VENTAS    === -->
                <!-- ==================================== -->
             <div id="ventasSection" class="hide">
                <div class="container-fluid mt-4">
                    <h2 class="mb-4">Estadísticas de Ventas</h2>
            
                    <div class="row g-3 mb-4">
                        
                        <div class="col-md-3">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body d-flex flex-column justify-content-center">
                                    <label for="periodoVentas" class="form-label fw-bold text-muted">Periodo:</label>
                                    <select id="periodoVentas" class="form-select border-secondary-subtle">
                                        <option value="semana" selected>Esta Semana</option>
                                        <option value="mes">Este Mes</option>
                                        <option value="ano">Este Año</option>
                                    </select>
                                </div>
                            </div>
                        </div>
            
                        <div class="col-md-3">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title text-muted text-uppercase small">Total Vendido</h6>
                                    <p id="totalVentasValor" class="fs-3 fw-bold mt-2 text-dark">$0.00</p>
                                    <small id="totalVentasTitulo" class="text-muted">Esta Semana</small>
                                </div>
                            </div>
                        </div>
            
                        <div class="col-md-3">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title text-muted text-uppercase small">Clientes Atendidos</h6>
                                    <p id="clientesAtendidos" class="fs-3 fw-bold mt-2 text-dark">0</p>
                                    <small class="text-muted">En el periodo seleccionado</small>
                                </div>
                            </div>
                        </div>
            
                        <div class="col-md-3">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title text-muted text-uppercase small">Promedio diario</h6>
                                    <p id="promedioDiario" class="fs-3 fw-bold mt-2 text-dark">$0.00</p>
                                    <small class="text-muted">Según el rango</small>
                                </div>
                            </div>
                        </div>
            
                    </div>
            
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-body">
                                    <h5 class="card-title text-muted mb-4">Resumen Gráfico</h5>
                                    <div style="height: 400px; width: 100%;">
                                        <canvas id="salesChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                <!-- === FIN NUEVA SECCIÓN DE VENTAS === -->
            <!-- Almacén -->
            <div id="almacenSection" class="hide">
                <div class="container-fluid mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4>Gestión de Productos</h4>
                        <div>
                            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                                <i class="fas fa-plus"></i> Nuevo Producto
                            </button>
                            
                            <button
                            type="button"
                            class="btn btn-info"
                            data-bs-toggle="modal"
                            data-bs-target="#modalPapelera"
                            >
                                        <i class="fas fa-archive"></i> Ver Papelera
                                        </button>
                                        </div>
                            
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Reiniciar puntero del resultado si es necesario o filtrar
                                if($productos) mysqli_data_seek($productos, 0);
                                
                                while($row = mysqli_fetch_assoc($productos)): 
                                    // FILTRO: Solo mostrar activos (activo = 1)
                                    if(isset($row['activo']) && $row['activo'] == 0) continue;
                                ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td class="fw-bold"><?php echo $row['producto']; ?></td>
                                        <td>$<?php echo number_format($row['precio'], 2); ?></td>
                                        <td>
                                            <span class="badge <?php echo $row['stock'] < 10 ? 'bg-danger' : 'bg-success'; ?>">
                                                <?php echo $row['stock']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-sm me-1" 
                                                onclick="editarProducto(<?php echo $row['id']; ?>, '<?php echo $row['producto']; ?>', <?php echo $row['precio']; ?>, <?php echo $row['stock']; ?>, '<?php echo $row['categoria']; ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button 
                                            type="button" 
                                            class="btn btn-warning btn-sm"
                                            onclick="prepararPapelera(<?php echo $row['id']; ?>)" 
                                            >
                                                    <i class="fas fa-trash"></i>
                                                    </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for($i=1; $i<=$pages; $i++): ?>
                                <li class="page-item <?php if($i==$page) echo 'active'; ?>">
                                    <a class="page-link" href="#" onclick="changePage(<?php echo $i; ?>); return false;"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            </div>
            
            <div class="modal fade" id="modalPapelera" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-secondary text-white">
                            <h5 class="modal-title"><i class="fas fa-trash-restore"></i> Papelera de Reciclaje</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-sm text-muted">
                                    <thead>
                                        <tr><th>Producto</th><th class="text-end">Acciones</th></tr>
                                    </thead>
                                    <tbody id="cuerpoPapelera">
                                        <tr><td colspan="2" class="text-center">Cargando productos eliminados...</td></tr>
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

            <!-- Trabajadores Mejorado -->
            <div id="trabajadoresSection" class="hide">
                <div class="empleado-header">
                    <h4><i class="fas fa-users me-2"></i>Gestión de Trabajadores</h4>
                    <div class="empleado-stats">
                        <?php 
                        $total_empleados = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM empleados"))[0];
                        ?>
                        <div class="stat-badge">
                            <i class="fas fa-user-tie me-1"></i>
                            Total: <?php echo $total_empleados; ?> empleados
                        </div>
                    </div>
                </div>
                
                <div style="text-align: right; margin-bottom: 25px;">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarEmpleado" id="btnAgregarEmpleado">
                        <i class="fas fa-user-plus me-2"></i>Agregar empleado
                    </button>
                    <button class="btn btn-secondary d-none" id="btnCancelarEliminar">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button class="btn btn-danger" id="btnEliminarEmpleado">
                        <i class="fas fa-user-minus me-2"></i>Eliminar empleado
                    </button>
                </div>

                <?php
                    $empleados = mysqli_query($conn, "SELECT * FROM empleados");
                ?>

                <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
                <?php while($emp = mysqli_fetch_assoc($empleados)): ?>
                    <div class="col empleado-card">
                        <div class="card h-100">
                            <!-- Foto clickeable -->
                            <div style="position: relative;">
                                <img src="../../trabajadores/<?php echo $emp['foto']; ?>" 
                                     class="empleado-foto"
                                     alt="Foto de <?php echo $emp['nombre']; ?>"
                                     onclick="abrirModalEmpleado(
                                         <?php echo $emp['id']; ?>, 
                                         '<?php echo htmlspecialchars($emp['nombre'], ENT_QUOTES); ?>', 
                                         '<?php echo $emp['numero_trabajador']; ?>', 
                                         '<?php echo htmlspecialchars($emp['contraseña'], ENT_QUOTES); ?>', 
                                         '<?php echo htmlspecialchars($emp['descripcion'], ENT_QUOTES); ?>', 
                                         '<?php echo $emp['foto']; ?>')">
                            </div>
                            
                            <!-- Información del empleado -->
                            <div class="empleado-info"
                                 data-id="<?php echo $emp['id']; ?>"
                                 data-nombre="<?php echo htmlspecialchars($emp['nombre'], ENT_QUOTES); ?>"
                                 data-numero="<?php echo $emp['numero_trabajador']; ?>"
                                 data-password="<?php echo htmlspecialchars($emp['contraseña'], ENT_QUOTES); ?>"
                                 data-descripcion="<?php echo htmlspecialchars($emp['descripcion'], ENT_QUOTES); ?>"
                                 data-foto="<?php echo $emp['foto']; ?>">
                                
                                <h5 class="card-title"><?php echo $emp['nombre']; ?></h5>
                                <span class="numero-trabajador">
                                    <i class="fas fa-id-badge me-1"></i>N° <?php echo $emp['numero_trabajador']; ?>
                                </span>
                                <p class="descripcion"><?php echo $emp['descripcion'] ? $emp['descripcion'] : 'Sin descripción disponible'; ?></p>
                            </div>
                            
                            <!-- Botones de acción -->
                            <div class="empleado-actions">
                                <button class="btn btn-primary btn-action" 
                                        onclick="abrirModalEmpleado(
                                            <?php echo $emp['id']; ?>, 
                                            '<?php echo htmlspecialchars($emp['nombre'], ENT_QUOTES); ?>', 
                                            '<?php echo $emp['numero_trabajador']; ?>', 
                                            '<?php echo htmlspecialchars($emp['contraseña'], ENT_QUOTES); ?>', 
                                            '<?php echo htmlspecialchars($emp['descripcion'], ENT_QUOTES); ?>', 
                                            '<?php echo $emp['foto']; ?>')"
                                        title="Editar empleado">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                            
                            <!-- Formulario de eliminación oculto -->
                            <form method="POST" action="/php/Admin/empleado_accion.php" class="delete-form hide" style="position: absolute; top: 10px; right: 10px;">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?php echo $emp['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
                </div>
                
                <!-- Modal Eliminar Empleado -->
                <div class="modal fade" id="modalEliminarEmpleado" tabindex="-1">
                  <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="/php/Admin/empleado_accion.php">
                      <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Eliminar Empleado</h5>
                      </div>
                      <div class="modal-body">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" id="deleteEmpId">
                        <div class="text-center">
                            <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem; margin-bottom: 15px;"></i>
                            <p class="fs-5">¿Estás seguro que deseas eliminar este empleado?</p>
                            <p class="text-muted">Esta acción no se puede deshacer.</p>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Eliminar
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
                
                <!-- Modal para Editar Empleado -->
                <div class="modal fade" id="modalEmpleado" tabindex="-1">
                  <div class="modal-dialog modal-lg">
                    <form class="modal-content" method="POST" action="/php/Admin/empleado_accion.php" enctype="multipart/form-data">
                      <div class="modal-body">
                        <input type="hidden" name="accion" value="editar">
                        <input type="hidden" name="id" id="empId">
                        
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Foto actual</label>
                                    <div>
                                        <img id="empFotoPreview" src="" alt="Foto actual" 
                                             style="width: 150px; height: 150px; object-fit: cover; border-radius: 10px; border: 3px solid #7b2c2c;">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cambiar foto</label>
                                    <input type="file" name="foto" id="empFoto" class="form-control" accept="image/*" onchange="previewImage(this, 'empFotoPreview')">
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-user me-2"></i>Nombre completo
                                    </label>
                                    <input type="text" name="nombre" id="empNombre" class="form-control" required>
                                </div>
                        
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-id-badge me-2"></i>Número de trabajador
                                    </label>
                                    <input type="text" name="numero_trabajador" id="empNumero" class="form-control" required pattern="[0-9]{5}">
                                    <span id="empNumeroError" class="text-danger"></span>
                                </div>
                        
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-lock me-2"></i>Contraseña
                                    </label>
                                    <div class="input-group">
                                        <input type="password" name="contraseña" id="empPassword" class="form-control" required>
                                        <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                        
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-comment me-2"></i>Descripción / Puesto
                                    </label>
                                    <textarea name="descripcion" id="empDescripcion" class="form-control" rows="3" 
                                              placeholder="Ej: Mesero con 2 años de experiencia..."></textarea>
                                </div>
                            </div>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Guardar cambios
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
                        
                <!-- Modal Agregar Empleado -->
                <div class="modal fade" id="modalAgregarEmpleado" tabindex="-1">
                  <div class="modal-dialog modal-lg">
                    <form class="modal-content" method="POST" action="/php/Admin/empleado_accion.php" enctype="multipart/form-data">
                      <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Agregar Nuevo Empleado</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <input type="hidden" name="accion" value="agregar">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Vista previa</label>
                                    <div>
                                        <img id="newEmpFotoPreview" src="../../trabajadores/default-user.png" alt="Vista previa" 
                                             style="width: 150px; height: 150px; object-fit: cover; border-radius: 10px; border: 3px solid #7b2c2c;">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Foto del empleado *</label>
                                    <input type="file" name="foto" class="form-control" accept="image/*" required onchange="previewImage(this, 'newEmpFotoPreview')">
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-user me-2"></i>Nombre completo *
                                    </label>
                                    <input type="text" name="nombre" class="form-control" required placeholder="Ej: Juan Pérez García">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-id-badge me-2"></i>Número de trabajador *
                                    </label>
                                    <input type="text" name="numero_trabajador" id="newEmpNumero" class="form-control" required pattern="[0-9]{5}" placeholder="Ej: 12345">
                                    <span id="newEmpNumeroError" class="text-danger"></span>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-lock me-2"></i>Contraseña *
                                    </label>
                                    <div class="input-group">
                                        <input type="password" name="contraseña" id="newEmpPassword" class="form-control" required>
                                        <button type="button" class="btn btn-outline-secondary" onclick="toggleNewPassword()">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-comment me-2"></i>Descripción / Puesto
                                    </label>
                                    <textarea name="descripcion" class="form-control" rows="3" 
                                              placeholder="Describe el puesto o responsabilidades del empleado..."></textarea>
                                </div>
                            </div>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-user-plus me-2"></i>Agregar empleado
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
            </div>

            <!-- Seguridad -->
            <div id="seguridadSection" class="hide">
                <h4>Seguridad</h4>
                <p>Solo usuarios con rol administrador pueden acceder a esta página.</p>
            </div>
        </main>
    </div>
</div>

<!-- Modal Agregar -->
<div class="modal fade" id="modalAgregar" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="/php/Admin/producto_accion.php" enctype="multipart/form-data">
      <div class="modal-header"><h5 class="modal-title">Agregar Producto</h5></div>
      <div class="modal-body">
        <input type="hidden" name="accion" value="agregar">
        <div class="mb-2"><input type="text" name="producto" class="form-control" placeholder="Producto" required></div>
<div class="mb-2"><input type="number" step="0.01" name="precio" class="form-control" placeholder="Precio" required></div>
<div class="mb-2"><input type="number" name="stock" class="form-control" placeholder="Stock" required></div>
<div class="mb-2">
    <label>Categoría</label>
    <select name="categoria" class="form-control" required>
        <option value="">Selecciona categoría</option>
        <option value="Arroces">Arroces</option>
        <option value="Bebidas">Bebidas</option>
        <option value="Platos fuertes">Platos fuertes</option>
        <option value="Entrantes">Entrantes</option>
        <option value="Fideos">Fideos</option>
        <option value="Mariscos">Mariscos</option>
        <option value="Sopas">Sopas</option>
        <option value="Vegetariano">Vegetariano</option>
        <option value="Platos especiales">Platos especiales</option>
    </select>
</div>
<div class="mb-2"><label for="imagen">Imagen</label><input type="file" name="imagen" id="imagen" class="form-control" accept="image/*"></div>

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Guardar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="modalEditar" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="/php/Admin/producto_accion.php" enctype="multipart/form-data">
      <div class="modal-header"><h5 class="modal-title">Editar Producto</h5></div>
      <div class="modal-body">
        <input type="hidden" name="accion" value="editar">
        <input type="hidden" name="id" id="editId">
        <div class="mb-2"><input type="text" name="producto" id="editProducto" class="form-control" required></div>
<div class="mb-2"><input type="number" step="0.01" name="precio" id="editPrecio" class="form-control" required></div>
<div class="mb-2"><input type="number" name="stock" id="editStock" class="form-control" required></div>
<div class="mb-2">
    <label>Categoría</label>
    <select name="categoria" id="editCategoria" class="form-control" required>
        <option value="">Selecciona categoría</option>
        <option value="Arroces">Arroces</option>
        <option value="Bebidas">Bebidas</option>
        <option value="Platos fuertes">Platos fuertes</option>
        <option value="Entrantes">Entrantes</option>
        <option value="Fideos">Fideos</option>
        <option value="Mariscos">Mariscos</option>
        <option value="Sopas">Sopas</option>
        <option value="Vegetariano">Vegetariano</option>
        <option value="Platos especiales">Platos especiales</option>
    </select>
</div>
<div class="mb-2"><label for="editImagen">Imagen (opcional)</label><input type="file" name="imagen" id="editImagen" class="form-control" accept="image/*"></div>

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="/php/Admin/producto_accion.php">
      <div class="modal-header"><h5 class="modal-title">Mover a Papelera</h5></div>
      <div class="modal-body">
        <input type="hidden" name="accion" value="papelera">
        <input type="hidden" name="estado" value="0">
        <input type="hidden" name="id" id="deleteId"> 
        <p>¿Seguro que deseas **mover este producto a la papelera**? (Dejará de ser visible en la lista principal).</p>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-warning">Mover a Papelera</button> 
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    console.log("✅ 1. El script JS ha comenzado a leerse.");
    
/* ==========================================================================
   1. NAVEGACIÓN Y UTILIDADES GENERALES
   ========================================================================== */

// Función para cambiar entre pestañas del Dashboard
function showSection(section) {
    // A. Ocultar todas las secciones por ID
    const sections = ['dashboard', 'ventas', 'almacen', 'trabajadores', 'seguridad'];
    sections.forEach(sec => {
        const el = document.getElementById(sec + 'Section');
        if (el) el.classList.add('hide');
    });

    // B. Mostrar la sección solicitada
    const target = document.getElementById(section + 'Section');
    if (target) target.classList.remove('hide');

    // C. Actualizar la clase 'active' en el menú lateral
    document.querySelectorAll('.sidebar .nav-link').forEach(link => link.classList.remove('active'));

    // Mapeo de índices (Ajusta los números según el orden de tus botones HTML)
    const menuMap = {
        'dashboard': 0,
        'ventas': 1,
        'almacen': 2,
        'trabajadores': 3,
        'seguridad': 4
    };

    const navLinks = document.querySelectorAll('.sidebar .nav-link');
    if (navLinks[menuMap[section]]) {
        navLinks[menuMap[section]].classList.add('active');
    }

    // D. Ajuste especial para gráfica de ventas
    if (section === 'ventas' && typeof mySalesChart !== 'undefined') {
        setTimeout(() => {
            mySalesChart.resize();
            const selector = document.getElementById('periodoVentas');
            if(selector) fetchSalesData(selector.value);
        }, 100);
    }
}

// Función para manejar la paginación sin recargar toda la página (opcional)
function changePage(page) {
    const url = new URL(window.location.href);
    url.searchParams.set('page', page);
    window.history.pushState({}, '', url);
    location.reload();
}

// Inicialización al cargar la página (Detecta qué sección mostrar)
window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    // Si viene de una acción de empleado o hay hash #trabajadores
    if (urlParams.has('empleado_action') || window.location.hash === '#trabajadores') {
        showSection('trabajadores');
    } 
    // Si hay paginación (usualmente almacén)
    else if (window.location.search.includes('page=')) {
        showSection('almacen');
    } 
    // Por defecto Dashboard
    else {
        showSection('dashboard');
    }
}


/* ==========================================================================
   2. GESTIÓN DE ALMACÉN (PRODUCTOS + PAPELERA + DRAG&DROP)
   ========================================================================== */

// Editar Producto
function editarProducto(id, producto, precio, stock, categoria) {
    document.getElementById('editId').value = id;
    document.getElementById('editProducto').value = producto;
    document.getElementById('editPrecio').value = precio;
    document.getElementById('editStock').value = stock;
    document.getElementById('editCategoria').value = categoria;
    new bootstrap.Modal(document.getElementById('modalEditar')).show();
}

// Mover a Papelera (Soft Delete)
function moverPapelera(id) {
    
    const modalElement = document.getElementById('modalEliminar');
    const modal = bootstrap.Modal.getInstance(modalElement);
    modal.hide();

    const formData = new FormData();
    formData.append('accion', 'papelera');
    formData.append('id', id);
    formData.append('estado', 0); // 0 = Desactivado

    fetch('/php/Admin/producto_accion.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(response => {
        if (response.trim() === 'ok') {
            location.reload();
        } else {
            alert("Error al mover a papelera: " + response);
        }
    })
    .catch(err => console.error(err));
}

// Acciones desde el Modal de Papelera (Restaurar o Borrar Definitivo)
function accionPapelera(id, estado) {
    const formData = new FormData();
    formData.append('accion', 'papelera');
    formData.append('id', id);
    formData.append('estado', estado); // 1 = Restaurar

    fetch('/php/Admin/producto_accion.php', { method: 'POST', body: formData })
    .then(() => {
        cargarPapelera(); // Refrescar tabla del modal
        location.reload(); // Refrescar tabla principal
    });
}

function eliminarDefinitivo(id) {
    if (!confirm("¿Estás seguro? Esto borrará el producto y su imagen permanentemente.")) return;

    const formData = new FormData();
    formData.append('accion', 'eliminar_permanente');
    formData.append('id', id);

    fetch('/php/Admin/producto_accion.php', { method: 'POST', body: formData })
    .then(() => cargarPapelera());
}

// Cargar lista de papelera
function cargarPapelera() {
    const tbody = document.getElementById('cuerpoPapelera');
    if(!tbody) return;
    
    tbody.innerHTML = '<tr><td colspan="2" class="text-center">Cargando...</td></tr>';

    fetch('/php/Admin/obtener_papelera.php')
    .then(res => res.json())
    .then(data => {
        tbody.innerHTML = '';
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="2" class="text-center">Papelera vacía</td></tr>';
            return;
        }
        data.forEach(item => {
            tbody.innerHTML += `
                <tr>
                    <td class="align-middle">${item.producto}</td>
                    <td class="text-end">
                        <button class="btn btn-success btn-sm me-2" onclick="accionPapelera(${item.id}, 1)">
                            <i class="fas fa-trash-restore"></i> Restaurar
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="eliminarDefinitivo(${item.id})">
                            <i class="fas fa-times"></i> Borrar
                        </button>
                    </td>
                </tr>
            `;
        });
    })
    .catch(err => {
        console.error(err);
        tbody.innerHTML = '<tr><td colspan="2" class="text-center text-danger">Error al cargar</td></tr>';
    });
}
// La función que se ejecuta al hacer clic en el botón de basura
function prepararPapelera(idProducto) {
    // 1. Establecer el ID del producto en el input oculto de la modal
    document.getElementById('deleteId').value = idProducto;
    
    // 2. Mostrar la modal usando la API de Bootstrap
    // (Asegúrate de tener la librería de Bootstrap JavaScript incluida)
    const modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
    modal.show();
    
    // NOTA: Si ya usaste los atributos data-bs-toggle/target en el HTML del botón, 
    // puedes omitir los pasos 2 y 3.
}


/* ==========================================================================
   3. GESTIÓN DE TRABAJADORES (EMPLEADOS) - ¡AQUÍ ESTABA LO FALTANTE!
   ========================================================================== */

// Abrir modal para editar empleado
function abrirModalEmpleado(id, nombre, numero, password, descripcion, foto) {
    document.getElementById('empId').value = id;
    document.getElementById('empNombre').value = nombre;
    document.getElementById('empNumero').value = numero;
    document.getElementById('empPassword').value = password;
    document.getElementById('empDescripcion').value = descripcion;
    
    const preview = document.getElementById('empFotoPreview');
    // Ajusta la ruta si tus fotos están en otra carpeta
    if(preview) preview.src = "../../trabajadores/" + foto;

    // Limpiar mensajes de error previos
    const errorSpan = document.getElementById('empNumeroError');
    if(errorSpan) errorSpan.textContent = '';
    document.getElementById('empNumero').classList.remove('is-invalid');

    new bootstrap.Modal(document.getElementById('modalEmpleado')).show();
}

// Previsualizar imagen al seleccionar archivo
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Alternar visibilidad de contraseña
function toggleNewPassword() {
    const passInput = document.getElementById('newEmpPassword');
    // Busca el icono dentro del botón hermano
    const icon = passInput.parentElement.querySelector('button i');
    
    if (passInput.type === 'password') {
        passInput.type = 'text';
        if(icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
    } else {
        passInput.type = 'password';
        if(icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
    }
}

// Validación de número de empleado (Solo 5 dígitos)
function validarNumero(inputId, errorId) {
    const input = document.getElementById(inputId);
    const errorSpan = document.getElementById(errorId);
    if(!input) return true; // Si no existe el input, pasamos

    const numero = input.value.trim();
    let esValido = true;
    let errores = [];

    errorSpan.textContent = '';
    input.classList.remove('is-invalid');

    if (!/^\d+$/.test(numero)) { errores.push('Solo dígitos (0-9).'); esValido = false; }
    if (numero.length !== 5) { errores.push('Debe tener 5 dígitos.'); esValido = false; }

    if (!esValido) {
        errorSpan.innerHTML = `⚠️ Error:<br>- ${errores.join('<br>- ')}`;
        input.classList.add('is-invalid');
    }
    return esValido;
}


/* ==========================================================================
   4. EVENTOS DOM CONTENT LOADED (INICIALIZACIÓN MASIVA)
   ========================================================================== */

    document.addEventListener("DOMContentLoaded", function() {
    
        // --- A. Inicialización de Papelera (Drag & Drop) ---
        const dropZone = document.getElementById('trashDropZone');
        const productRows = document.querySelectorAll('.producto-row');
        let draggedId = null;
    
    
        // --- B. Listeners para Modal Papelera ---
        const modalPapelera = document.getElementById('modalPapelera');
        if(modalPapelera) {
            modalPapelera.addEventListener('show.bs.modal', cargarPapelera);
        }
    
        // --- C. Listeners para Sección Trabajadores ---
        
        // 1. Validar al enviar formulario de Edición
        const formEditEmp = document.querySelector('#modalEmpleado form');
        if(formEditEmp) {
            formEditEmp.addEventListener('submit', function(e) {
                if (!validarNumero('empNumero', 'empNumeroError')) e.preventDefault();
            });
        }
    
        // 2. Validar al enviar formulario de Agregar
        const formAddEmp = document.querySelector('#modalAgregarEmpleado form');
        if(formAddEmp) {
            formAddEmp.addEventListener('submit', function(e) {
                if (!validarNumero('newEmpNumero', 'newEmpNumeroError')) e.preventDefault();
            });
        }
    
        // 3. Validar mientras escriben
        const inputEmpNum = document.getElementById('empNumero');
        if(inputEmpNum) inputEmpNum.addEventListener('input', () => validarNumero('empNumero', 'empNumeroError'));
    
        const inputNewEmpNum = document.getElementById('newEmpNumero');
        if(inputNewEmpNum) inputNewEmpNum.addEventListener('input', () => validarNumero('newEmpNumero', 'newEmpNumeroError'));
    
        // 4. Botones de eliminar empleado (Modo selección)
        const btnEliminarEmp = document.getElementById("btnEliminarEmpleado");
        const btnCancelarEmp = document.getElementById("btnCancelarEliminar");
        const btnAgregarEmp = document.getElementById("btnAgregarEmpleado");
        const tarjetasEmp = document.querySelectorAll(".empleado-card");
        let modoEliminarEmp = false;
    
        // Toggle para ver contraseña en edición
        const togglePassBtn = document.getElementById('togglePassword');
        if(togglePassBtn){
            togglePassBtn.addEventListener('click', function() {
                const passInput = document.getElementById('empPassword');
                const icon = this.querySelector('i');
                if (passInput.type === 'password') {
                    passInput.type = 'text';
                    icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash');
                } else {
                    passInput.type = 'password';
                    icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye');
                }
            });
        }
    
        // Activar modo eliminar
        if(btnEliminarEmp){
            btnEliminarEmp.addEventListener("click", function() {
                modoEliminarEmp = true;
                if(btnAgregarEmp) btnAgregarEmp.classList.add("d-none");
                btnEliminarEmp.classList.add("d-none");
                if(btnCancelarEmp) btnCancelarEmp.classList.remove("d-none");
    
                tarjetasEmp.forEach(card => {
                    card.classList.add("seleccionable"); // Añade borde o efecto visual
                    card.addEventListener("click", seleccionarTarjetaEmp);
                });
            });
        }
    
        // Cancelar modo eliminar
        if(btnCancelarEmp){
            btnCancelarEmp.addEventListener("click", function() {
                modoEliminarEmp = false;
                if(btnAgregarEmp) btnAgregarEmp.classList.remove("d-none");
                if(btnEliminarEmp) btnEliminarEmp.classList.remove("d-none");
                btnCancelarEmp.classList.add("d-none");
    
                tarjetasEmp.forEach(card => {
                    card.classList.remove("seleccionable");
                    card.classList.remove("seleccionada");
                    card.removeEventListener("click", seleccionarTarjetaEmp);
                });
            });
        }
    
        function seleccionarTarjetaEmp(e) {
            // Evitar que se dispare si clickean la foto (que abre modal editar)
            if (!modoEliminarEmp || e.target.classList.contains('empleado-foto')) return;
    
            const card = e.currentTarget;
            // Obtener ID del dataset
            const infoDiv = card.querySelector(".empleado-info");
            const id = infoDiv ? infoDiv.dataset.id : null;
    
            if(id) {
                document.getElementById('deleteEmpId').value = id;
                new bootstrap.Modal(document.getElementById('modalEliminarEmpleado')).show();
            }
        }
    
    
        // --- D. Gráfica de Ventas (Chart.js) ---
        if (typeof Chart === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.onload = initSalesChart;
            document.head.appendChild(script);
        } else {
            initSalesChart();
        }
    
    
        // E. Modal Detalle Pedido (Ventas)
        const modalDetalle = document.getElementById('modalDetallePedido');
    
        if (modalDetalle) {
            modalDetalle.addEventListener('show.bs.modal', function(event) {
                // 1. Obtener el botón que abrió el modal
                const link = event.relatedTarget;
                const pedidoId = link.getAttribute('data-pedido-id');
                
                // 2. Limpiar modal antes de cargar (Feedback visual)
                document.getElementById('detallePedidoId').textContent = `#${pedidoId}`;
                document.getElementById('detalleCliente').textContent = 'Cargando...';
                document.getElementById('detalleFecha').textContent = '';
                document.getElementById('detalleTotal').textContent = '';
                const tbody = document.getElementById('detalleProductosBody');
                tbody.innerHTML = '<tr><td colspan="4" class="text-center">Cargando datos...</td></tr>';
    
                // 3. Petición al PHP CORRECTO
                // CAMBIO CLAVE: Usamos '?id=' porque el PHP espera $_GET['id']
                // Verifica que la ruta 'php/Empleado/...' sea la correcta en tus carpetas
                fetch('php/Empleado/get_detalle_pedido.php?id=' + encodeURIComponent(pedidoId))
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // 4. Llenar datos generales
                        // Este PHP devuelve 'cliente_nombre', 'fecha' y 'total' ya formateados
                        document.getElementById('detalleCliente').textContent = data.cliente_nombre;
                        document.getElementById('detalleFecha').textContent = data.fecha;
                        document.getElementById('detalleTotal').textContent = `$${data.total}`;
                        
                        // 5. Llenar tabla de productos
                        tbody.innerHTML = '';
                        if (data.productos && data.productos.length > 0) {
                            data.productos.forEach(item => {
                                // Cálculos matemáticos
                                const cantidad = parseFloat(item.cantidad);
                                const precio = parseFloat(item.precio_unidad);
                                const subtotal = (cantidad * precio).toFixed(2);
                                
                                tbody.innerHTML += `
                                    <tr>
                                        <td>${item.producto_nombre}</td>
                                        <td>${item.cantidad}</td>
                                        <td>$${precio.toFixed(2)}</td>
                                        <td>$${subtotal}</td>
                                    </tr>`;
                            });
                        } else {
                            tbody.innerHTML = '<tr><td colspan="4" class="text-center">No hay productos en este pedido.</td></tr>';
                        }
                    } else {
                        // Mensaje de error si success es false
                        tbody.innerHTML = `<tr><td colspan="4" class="text-danger text-center">${data.message}</td></tr>`;
                    }
                })
                .catch(err => {
                    console.error(err);
                    tbody.innerHTML = `<tr><td colspan="4" class="text-danger text-center">Error de conexión con el servidor.</td></tr>`;
                });
            });
        }
    });
    
    
    const darkModeBtn = document.getElementById('darkModeToggle');
        if(darkModeBtn) {
            darkModeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                toggleDarkMode();
            });
        }
    
    function toggleDarkMode() {
        const body = document.body;
        body.classList.toggle('dark-mode');
        
        // 1. Guardar la preferencia en localStorage
        const isDarkMode = body.classList.contains('dark-mode');
        localStorage.setItem('darkMode', isDarkMode ? 'enabled' : 'disabled');
        
        // 2. Actualizar el icono del botón (mejorar la usabilidad)
        updateDarkModeButton(isDarkMode);
    }
    
    function updateDarkModeButton(isDarkMode) {
        const btn = document.getElementById('darkModeToggle');
        if (btn) {
            // Simplemente cambia el texto del botón
            if (isDarkMode) {
                btn.innerHTML = `Modo Claro`;
            } else {
                btn.innerHTML = `Modo Oscuro`;
            }
        }
    }
    
    // Cargar la preferencia al iniciar la página
    (function() {
        const preference = localStorage.getItem('darkMode');
        const body = document.body;
        
        if (preference === 'enabled' || (!preference && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            // Habilitar si la preferencia es 'enabled' o si no hay preferencia guardada y el sistema es oscuro
            body.classList.add('dark-mode');
            updateDarkModeButton(true);
        } else {
            updateDarkModeButton(false);
        }
    })();


/* ==========================================================================
   5. FUNCIONES DE GRÁFICA (CHART.JS)
   ========================================================================== */
    
    let mySalesChart;
    
    function initSalesChart() {
        const ctx = document.getElementById('salesChart');
        if (!ctx) return;
    
        mySalesChart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Ventas ($)',
                    data: [],
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { callback: v => '$' + v.toLocaleString('es-MX') } }
                },
                plugins: { legend: { display: false } }
            }
        });
    
        const selector = document.getElementById('periodoVentas');
        if(selector) {
            fetchSalesData(selector.value);
            selector.addEventListener('change', e => fetchSalesData(e.target.value));
        }
    }
    
    async function fetchSalesData(periodo) {
        if (!mySalesChart) return;
        
        // Elementos existentes (Total Vendido)
        const valEl = document.getElementById('totalVentasValor');
        const titEl = document.getElementById('totalVentasTitulo');
        
        // ✅ NUEVOS ELEMENTOS: Clientes Atendidos y Promedio Diario
        const clientesEl = document.getElementById('clientesAtendidos');
        const promedioEl = document.getElementById('promedioDiario');
        
        // Mensajes de carga (Feedback visual)
        if(valEl) valEl.textContent = 'Cargando...';
        if(clientesEl) clientesEl.textContent = 'Cargando...'; // ¡Nueva línea!
        if(promedioEl) promedioEl.textContent = 'Cargando...'; // ¡Nueva línea!
    
        try {
            const res = await fetch(`/php/Admin/get_sales_data.php?periodo=${periodo}`);
            if(!res.ok) throw new Error('Error servidor');
            const data = await res.json();
            
            // Validación de error de PHP (si lo hubiera)
            if (data.error) {
                 console.error("Error en PHP:", data.error);
                 throw new Error(data.error);
            }
    
            // 1. Actualizar Gráfica
            mySalesChart.data.labels = data.labels || [];
            mySalesChart.data.datasets[0].data = data.data || [];
            mySalesChart.update();
    
            // 2. Actualizar Total Vendido (Moneda)
            const total_format = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(data.total_ventas || 0);
            if(valEl) valEl.textContent = total_format;
            if(titEl) titEl.textContent = periodo === 'ano' ? 'Total (Este Año)' : (periodo === 'mes' ? 'Total (Este Mes)' : 'Total (Esta Semana)');
    
            // 3. ✅ Actualizar Clientes Atendidos (Entero)
            // La columna 'total_clientes' viene del PHP
            if(clientesEl) clientesEl.textContent = (data.total_clientes || 0).toLocaleString('es-MX');
    
            // 4. ✅ Actualizar Promedio Diario (Moneda)
            // La columna 'promedio_diario' viene del PHP
            const promedio_format = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(data.promedio_diario || 0);
            if(promedioEl) promedioEl.textContent = promedio_format;
    
    
        } catch (e) {
            console.error("Error al obtener datos:", e);
            // Manejo de errores
            if(valEl) valEl.textContent = '$0.00';
            if(clientesEl) clientesEl.textContent = 'Error'; // Muestra error en caso de fallo
            if(promedioEl) promedioEl.textContent = '$0.00'; // Muestra error en caso de fallo
        }
    }
</script>
</body>
</html>