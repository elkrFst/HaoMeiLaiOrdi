<?php
session_start();
// Evitar que el navegador use caché
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Empleado') {
    header("Location: /login");
    exit();
}

// Conexión a la base de datos
$host = "srv562.hstgr.io";
$user = "u162512390_Admin";
$pass = "biuqkb>O3";
$db = "u162512390_HaoMeiLai";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// --- NUEVO CÓDIGO PARA OBTENER EMPLEADOS ---
$sql_empleados = "SELECT id, nombre, numero_trabajador FROM empleados";
$result_empleados = $conn->query($sql_empleados);
$lista_empleados = [];
if ($result_empleados->num_rows > 0) {
    while($row = $result_empleados->fetch_assoc()) {
        $lista_empleados[] = $row;
    }
}

// Obtener productos del almacen (con categoría)
// IMPORTANTE: Asegurarnos de tener el 'id'
$sql = "SELECT id, producto, precio, stock, imagen, categoria, ingredientes FROM almacen";
$result = $conn->query($sql);

$productos = [];
$categorias = []; // Para los botones de categoría
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $productos[] = [
            "id" => $row['id'], // Necesitamos el ID
            "nombre" => $row['producto'],
            "precio" => $row['precio'],
            "categoria" => $row['categoria'],
            "imagen" => !empty($row['imagen']) ? $row['imagen'] : "default.jpg",
            "stock" => $row['stock'],
            "ingredientes" => $row['ingredientes'] // NUEVO: Agregar ingredientes
        ];
        if (!in_array($row['categoria'], $categorias)) {
            $categorias[] = $row['categoria'];
        }
    }
}
sort($categorias);
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Caja - Empleado</title>
  <link rel="stylesheet" href="../../css/Empleado.css">
  <link rel="stylesheet" href="../../css/Empleado1.css">
  
  <style>
  
  
  .card .ingredientes {
    font-size: 12px;
    color: #666;
    margin: 5px 0 10px 0;
    line-height: 1.3;
    max-height: 40px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2; /* Muestra máximo 2 líneas */
    -webkit-box-orient: vertical;
    padding: 5px;
    background-color: #f9f9f9;
    border-radius: 3px;
    border-left: 3px solid #ccc;
}

/* Si quieres que sea más visible al pasar el mouse */
.card:hover .ingredientes {
    background-color: #f0f0f0;
    color: #444;
}
  
    /* Estilo para el nuevo buscador de pedidos */
    .pedido-search-container {
        display: flex;
        align-items: center;
        margin-top: 10px;
        padding: 10px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }
    .pedido-search-container input {
        flex-grow: 1;
        border: 1px solid #ccc;
        padding: 8px 12px;
        border-radius: 5px 0 0 5px;
        outline: none;
        text-transform: uppercase;
        font-size: 0.9em;
    }
    .pedido-search-container button {
        padding: 8px 15px;
        background-color: #28a745; /* Verde */
        color: white;
        border: none;
        cursor: pointer;
        border-radius: 0 5px 5px 0;
        font-weight: bold;
        transition: background-color 0.2s;
    }
    .pedido-search-container button:hover {
        background-color: #218838;
    }
    .pedido-search-container button:disabled {
        background-color: #aaa;
        cursor: not-allowed;
    }
    
    /* Estilo para el aviso de pedido cargado en el carrito */
    #pedido-cargado-info {
        display: none; /* Oculto por defecto */
        padding: 10px;
        background: #e6fff0;
        border: 1px solid #28a745;
        border-radius: 5px;
        text-align: center;
        font-weight: 600;
        color: #218838;
        margin-bottom: 10px;
    }
    
    /* --- INICIO DE ARREGLO DROPDOWN --- */
    
    .user-info {
        /* Le decimos que sea el "ancla" para el menú desplegable */
        position: relative; 
    }
    
    .user-dropdown {
        /* MODIFICADO: Esta es la corrección visual */
        position: relative; /* Cambiado de 'absolute' a 'relative' */
    }

    /* El menú que se despliega SÍ es absoluto */
    .user-dropdown .dropdown-menu {
        position: absolute;
        top: 100%; /* Se despliega justo debajo del icono */
        
        /* ¡LA SOLUCIÓN! */
        left: 0; /* Alinea el inicio del dropdown con el inicio del logo */
        
        width: 200px; /* Dale un ancho fijo para que no se desborde */
        z-index: 1001; /* Asegurar que esté sobre otros elementos */
        
        /* (Tus estilos de .dropdown-menu como 'display: none', 'background', etc.) */
        /* Asegúrate de que tu CSS oculte .dropdown-menu por defecto */
        /* y lo muestre cuando .user-dropdown tiene la clase .open */
    }
    
    /* --- FIN DE ARREGLO DROPDOWN --- */

    /* Iconos (usando FontAwesome para los botones de búsqueda y carga) */
    @import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css");
    
    /* --- INICIO DE ESTILOS RESPONSIVOS --- */
    
    /* Para pantallas de 768px o menos (celulares y tablets) */
    @media (max-width: 768px) {
        
        .header {
            /* Permite que los elementos del header se reorganicen */
            flex-direction: column;
            align-items: flex-start;
        }
    
        .search-container {
            width: 100%; /* Ocupa todo el ancho en móvil */
            margin: 10px 0;
        }
    
        .user-info {
            /* Mueve la info de usuario al final en móvil */
            align-self: flex-end;
            margin-top: -50px; /* Ajusta según sea necesario */
        }
    
        /* Contenedor principal de la app */
        .app-container {
            flex-direction: column; /* Apila el menú y el carrito */
        }
    
        /* El menú de productos */
        .grid-menu {
            width: 100%; /* Ocupa todo el ancho */
            order: 2; /* Pone el menú después del carrito */
        }
    
        /* El carrito */
        .carrito {
            width: 100%; /* Ocupa todo el ancho */
            height: auto; /* Altura automática */
            max-height: 40vh; /* Limita la altura del carrito */
            order: 1; /* Pone el carrito primero */
            position: relative; /* Quita el 'sticky' */
            top: 0;
        }
    
        /* Ajusta los productos en el menú */
        .grid-productos {
            grid-template-columns: repeat(2, 1fr); /* 2 columnas en móvil */
            gap: 10px;
        }
    }
/* --- FIN DE ESTILOS RESPONSIVOS --- */

/* ============================================== */
/* === INICIO: ESTILOS PARA EL MODAL NUEVO === */
/* ============================================== */
.modal-overlay {
    display: none; /* Oculto por defecto */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}
.modal-container {
    /* display: none; -> El overlay se encarga de mostrar/ocultar */
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    width: 90%;
    max-width: 400px;
    z-index: 1001;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #eee;
}
.modal-header h3 {
    margin: 0;
}
.modal-body {
    padding: 20px;
}
.modal-body .form-control { /* Estilo para el input de pass */
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box; /* Importante */
}
.modal-footer {
    display: flex;
    justify-content: flex-end;
    padding: 15px;
    border-top: 1px solid #eee;
    gap: 10px;
}
.btn-close-modal {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    line-height: 1;
}
#lista-empleados-body .empleado-item {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
}
#lista-empleados-body .empleado-item:hover {
    background: #f9f9f9;
}
.error-msg {
    color: red;
    font-size: 0.9em;
    margin-top: 10px;
}
/* Clases de botones genéricas (si no las tienes) */
.btn {
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
.btn-secondary {
    background-color: #6c757d;
    color: white;
}
.btn-success {
    background-color: #28a745;
    color: white;
}
/* ============================================== */
/* === FIN: ESTILOS PARA EL MODAL NUEVO === */
/* ============================================== */
    
  </style>
</head>
<body>
  <header>
    <div class="header-container">
      <div class="logo-user">
        <img src="../../imagenes/logo comida.png" alt="Logo">
        
        <div class="user-dropdown">
            <div class="user-toggle">
                <?php echo htmlspecialchars($_SESSION['usuario'] ?? 'Empleado'); ?> 👤
            </div>
            <div class="dropdown-menu">
                <a href="#" id="btn-modal-cambiar-cuenta">🔄 Cambiar cuenta</a>
                <a href="/php/IDS/cerrarsesion.php">🚪 Cerrar sesión</a>
            </div>
        </div>
        </div>

      <div class="categorias-container">
          <button class="categoria-cuadro activa" data-categoria="Todos">Todos</button>
          <?php foreach ($categorias as $categoria): ?>
              <button class="categoria-cuadro" data-categoria="<?php echo htmlspecialchars($categoria); ?>">
                  <?php echo htmlspecialchars($categoria); ?>
              </button>
          <?php endforeach; ?>
      </div>

      <div class="search-container">
        <input type="text" id="buscador" placeholder="Buscar en el menú...">
        <div class="search-icon">🔍</div>
      </div>

      <div class="pedido-search-container">
          <input type="text" id="codigo_pedido_input" placeholder="Buscar Pedido (Ej: HML-XXXXXX)">
          <button id="btn_buscar_pedido">
              <i class="fas fa-search"></i> Buscar
          </button>
      </div>

      <button class="ver-todo-btn" onclick="renderMenu()" id="ver-todo" style="display: none;">👀 Ver todo</button>
    </div>
  </header>

  <main>
    <section class="menu" id="menu-productos"></section>
    <aside class="carrito" id="carrito-sidebar">
      <h2>Tu Pedido</h2>
      
      <div id="pedido-cargado-info">
          <i class="fas fa-check-circle"></i> Pedido <strong id="codigo-cargado-span"></strong> cargado.
      </div>
      
      <div id="carrito-items"></div>
      <div class="total" id="carrito-total">TOTAL: $0.00</div> <div class="acciones">
        <button class="cancelar" id="btn-cancelar">Cancelar</button>
        <button class="aceptar" id="btn-aceptar" onclick="procesarPago()">Aceptar</button>
      </div>
    </aside>
  </main>
  
  <div id="simple-alert-modal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <h4 id="simple-alert-title"></h4>
        <p id="simple-alert-message"></p>
        <button id="simple-alert-close">Cerrar</button>
    </div>
  </div>
  <style>
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1050; display: flex; justify-content: center; align-items: center; }
    .modal-content { background: white; padding: 25px; border-radius: 8px; min-width: 300px; max-width: 450px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); text-align: center; }
    .modal-content h4 { margin-top: 0; color: #a33d3d; }
    .modal-content button { background: #a33d3d; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-top: 15px; }
  </style>

<div class="modal-overlay" id="modal-cambiar-cuenta-overlay" style="display: none;">
    <div class="modal-container" id="modal-cambiar-cuenta-container">
        
        <div id="panel-lista-empleados">
            <div class="modal-header">
                <h3>Seleccionar Empleado</h3>
                <button class="btn-close-modal" id="btn-cerrar-modal-lista">&times;</button>
            </div>
            <div class="modal-body" id="lista-empleados-body">
                </div>
        </div>

        <div id="panel-ingresar-password" style="display: none;">
            <div class="modal-header">
                <h3 id="modal-password-titulo">Iniciar sesión como...</h3>
                <button class="btn-close-modal" id="btn-cerrar-modal-pass">&times;</button>
            </div>
            <div class="modal-body">
                <p>Por favor, ingresa tu contraseña:</p>
                <input type="password" id="input-password-empleado" class="form-control" placeholder="Contraseña">
                <div id="modal-password-error" class="error-msg" style="display: none;"></div>
                <input type="hidden" id="hidden-empleado-id">
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="btn-modal-regresar">Regresar</button>
                <button class="btn btn-success" id="btn-modal-verificar">Verificar</button>
            </div>
        </div>

    </div>
</div>
<script>
    const productos = <?php echo json_encode($productos); ?>;
    let carrito = [];
    let categoriaActiva = "Todos"; // Iniciar con "Todos"
    let codigoPedidoCargado = null; // NUEVO: Para guardar el código

    // --- Elementos del DOM ---
    const menu = document.getElementById('menu-productos');
    const verTodo = document.getElementById('ver-todo');
    const carritoSidebar = document.getElementById('carrito-sidebar');
    const carritoItems = document.getElementById('carrito-items');
    const carritoTotalEl = document.getElementById('carrito-total');
    const btnAceptar = document.getElementById('btn-aceptar');
    
    // --- NUEVOS Elementos Buscador Pedido ---
    const inputCodigo = document.getElementById('codigo_pedido_input');
    const btnBuscarCodigo = document.getElementById('btn_buscar_pedido');
    const infoPedidoCargado = document.getElementById('pedido-cargado-info');
    const spanCodigoCargado = document.getElementById('codigo-cargado-span');
    
    // --- Elementos del Modal (simple) ---
    const modal = document.getElementById('simple-alert-modal');
    const modalTitle = document.getElementById('simple-alert-title');
    const modalMessage = document.getElementById('simple-alert-message');
    const modalClose = document.getElementById('simple-alert-close');
    
    if(modal) {
        modalClose.onclick = () => modal.style.display = 'none';
    }
    
    function mostrarAlerta(titulo, mensaje) {
        if(modal) {
            modalTitle.textContent = titulo;
            modalMessage.textContent = mensaje;
            modal.style.display = 'flex';
        } else {
            alert(titulo + "\n" + mensaje); // Fallback
        }
    }


    function toggleCarrito(mostrar) {
      carritoSidebar.classList.toggle('visible', mostrar);
    }

    function renderMenu(categoria = "Todos", busqueda = '') { // Default a "Todos"
      menu.innerHTML = '';
      let filtrados = productos;
      
      // 1. Filtrar por categoría
      if (categoria && categoria !== "Todos") {
        filtrados = filtrados.filter(p => p.categoria.toLowerCase() === categoria.toLowerCase());
        categoriaActiva = categoria;
      } else {
        categoriaActiva = "Todos";
      }

      // 2. Filtrar por búsqueda
      if (busqueda) {
        filtrados = filtrados.filter(p => 
          p.nombre.toLowerCase().includes(busqueda.toLowerCase())
        );
      }
      
      // 3. Resaltar botón de categoría
      document.querySelectorAll('.categoria-cuadro').forEach(btn => {
        btn.classList.toggle('activa', categoriaActiva && btn.getAttribute('data-categoria').toLowerCase() === categoriaActiva.toLowerCase());
      });

     // 4. Renderizar productos
     
    
if (filtrados.length === 0) {
    menu.innerHTML = '<div class="no-resultados">No se encontraron productos.</div>';
} else {
    filtrados.forEach(p => {
        // MODIFICADO: Pasar 'id', 'nombre' y 'precio'
        menu.innerHTML += `
            <div class="card">
                <img src="/php/menu2/imagenes_productos/${p.imagen}" alt="${p.nombre}">
                <h3>${p.nombre}</h3>
                <!-- AGREGAR ESTA LÍNEA PARA MOSTRAR INGREDIENTES -->
                <div class="ingredientes">${p.ingredientes || 'Ingredientes no disponibles'}</div>
                <button onclick="agregarAlCarrito(${p.id}, '${p.nombre.replace(/'/g, "\\'")}', ${p.precio})">🛒 Agregar - $${p.precio}</button>
            </div>
        `;
    });
}
 verTodo.style.display = (categoria !== "Todos" || busqueda) ? 'block' : 'none';
    }

    function renderCarrito() {
      carritoItems.innerHTML = '';
      let subtotal = 0;

      if (carrito.length > 0) {
        toggleCarrito(true);
        carrito.forEach((item, idx) => {
          const itemTotal = item.precio * item.cantidad;
          subtotal += itemTotal;
          carritoItems.innerHTML += `
            <div class="item">
              <span>${item.nombre}</span>
              <div class="btns">
                <button onclick="cambiarCantidad(${idx}, -1)">-</button>
                <span>${item.cantidad}</span>
                <button onclick="cambiarCantidad(${idx}, 1)">+</button>
              </div>
              <span>$${itemTotal.toFixed(2)}</span>
              <button onclick="eliminarItem(${idx})">✖</button>
            </div>
          `;
        });
      } else {
        toggleCarrito(false);
      }

      // Calcular total con IVA
      const iva = subtotal * 0.16;
      const total = subtotal + iva;
      carritoTotalEl.textContent = `TOTAL (IVA incl.): $${total.toFixed(2)}`;
      
      // Habilitar/Deshabilitar botón de Aceptar
      btnAceptar.disabled = carrito.length === 0;

      // NUEVO: Mostrar/Ocultar info de pedido cargado
      if (codigoPedidoCargado) {
          spanCodigoCargado.textContent = codigoPedidoCargado;
          infoPedidoCargado.style.display = 'block';
      } else {
          infoPedidoCargado.style.display = 'none';
      }
    }

    // --- Funciones del Carrito (MODIFICADAS) ---

    // MODIFICADO: Aceptar id, nombre, precio
    window.agregarAlCarrito = function(id, nombre, precio) {
      // NUEVO: Bloquear si hay un pedido cargado
      if (codigoPedidoCargado) {
          mostrarAlerta('Pedido Cargado', 'Hay un pedido de cliente cargado. Cancele la orden actual para agregar productos manualmente.');
          return;
      }
      
      const idx = carrito.findIndex(item => item.id === id); // Buscar por ID
      if (idx > -1) {
          carrito[idx].cantidad += 1;
      } else {
          carrito.push({ id, nombre, precio, cantidad: 1 }); // Guardar objeto completo
      }
      renderCarrito();
    }

    window.cambiarCantidad = function(idx, cambio) {
      // NUEVO: Bloquear si hay un pedido cargado
      if (codigoPedidoCargado) {
          mostrarAlerta('Pedido Cargado', 'No puede modificar un pedido cargado. Cancele la orden si desea hacer cambios.');
          return;
      }
      
      if (carrito[idx]) {
        carrito[idx].cantidad += cambio;
        if (carrito[idx].cantidad < 1) carrito[idx].cantidad = 1;
        renderCarrito();
      }
    }

    window.eliminarItem = function(idx) {
      // NUEVO: Bloquear si hay un pedido cargado
      if (codigoPedidoCargado) {
          mostrarAlerta('Pedido Cargado', 'No puede modificar un pedido cargado. Cancele la orden si desea hacer cambios.');
          return;
      }
      
      if (carrito[idx]) {
        carrito.splice(idx, 1);
        renderCarrito();
      }
    }
    
    // --- NUEVA: Función para buscar pedido por código ---
    async function buscarPedido() {
        const codigo = inputCodigo.value.trim().toUpperCase();
        if (!codigo) {
            mostrarAlerta('Error', 'Ingrese un código de pedido.');
            return;
        }

        btnBuscarCodigo.disabled = true;
        btnBuscarCodigo.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        const formData = new FormData();
        formData.append('codigo', codigo);

        try {
            // Asegúrate de que la ruta a 'buscar_pedido.php' sea correcta
            // Si empleado.php está en /php/Empleado/, y buscar_pedido.php está en el mismo dir:
            const response = await fetch('/php/Empleado/buscar_pedido.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                 throw new Error('Error de red o servidor.');
            }
            
            const data = await response.json();

            if (data.success) {
                // Éxito: Cargar carrito
                carrito = data.carrito; // El carrito ahora contiene {id, nombre, cantidad, precio}
                codigoPedidoCargado = data.codigo_pedido;
                renderCarrito();
                inputCodigo.value = '';
                mostrarAlerta('Éxito', `Pedido ${data.codigo_pedido} cargado correctamente.`);
            } else {
                // Error lógico (pedido no encontrado, etc.)
                mostrarAlerta('Búsqueda Fallida', data.message);
                codigoPedidoCargado = null;
                // No limpiamos el carrito, por si el empleado estaba haciendo otro pedido
            }

        } catch (error) {
            console.error('Error:', error);
            mostrarAlerta('Error de Conexión', 'No se pudo conectar con el servidor. Inténtelo de nuevo.');
        } finally {
            btnBuscarCodigo.disabled = false;
            btnBuscarCodigo.innerHTML = '<i class="fas fa-search"></i> Buscar';
        }
    }


    // MODIFICADA: Función para ir a la página de pago
    function procesarPago() {
      if (carrito.length === 0) {
        mostrarAlerta('Carrito Vacío', 'Agrega al menos un producto.');
        return;
      }
      
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '/php/Empleado/pago.php'; // Redirige a pago.php (o /pago si usas htaccess)
      
      // 1. Input del carrito (ahora envía IDs)
      const inputCarrito = document.createElement('input');
      inputCarrito.type = 'hidden';
      inputCarrito.name = 'carrito';
      inputCarrito.value = JSON.stringify(carrito); // Envía el array de objetos
      form.appendChild(inputCarrito);

      // 2. NUEVO: Input del código de pedido (si existe)
      if (codigoPedidoCargado) {
          const inputCodigo = document.createElement('input');
          inputCodigo.type = 'hidden';
          inputCodigo.name = 'codigo_pedido';
          inputCodigo.value = codigoPedidoCargado;
          form.appendChild(inputCodigo);
      }

      document.body.appendChild(form);
      form.submit();
    }

    // --- Event Listeners ---

    document.querySelectorAll('.categoria-cuadro').forEach(btn => {
      btn.addEventListener('click', function() {
        document.getElementById('buscador').value = '';
        renderMenu(this.getAttribute('data-categoria'));
      });
    });

    document.getElementById('buscador').addEventListener('input', function() {
      // Desmarcar categorías al buscar
      document.querySelectorAll('.categoria-cuadro').forEach(b => b.classList.remove('activa'));
      categoriaActiva = null; 
      renderMenu(null, this.value);
    });

    document.getElementById('btn-cancelar').onclick = function() {
      carrito = [];
      codigoPedidoCargado = null; // NUEVO: Limpiar código también
      renderCarrito();
    };

    // NUEVO: Listeners para buscar pedido
    btnBuscarCodigo.addEventListener('click', buscarPedido);
    inputCodigo.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            buscarPedido();
        }
    });

    // Carga inicial
    renderMenu(); // Renderiza "Todos" por defecto
    renderCarrito();
    
    // Dropdown usuario
    document.querySelector(".user-toggle").addEventListener("click", function () {
        document.querySelector(".user-dropdown").classList.toggle("open");
    });

    // Cerrar al hacer click fuera
    document.addEventListener("click", function (e) {
      if (!document.querySelector(".user-dropdown").contains(e.target) && !e.target.matches('.user-toggle')) {
        document.querySelector(".user-dropdown").classList.remove("open");
      }
    });

    // ==========================================================
    // === INICIO: LÓGICA PARA CAMBIAR DE CUENTA ===
    // ==========================================================
    
    // Obtenemos la lista de empleados que pasamos desde PHP
    const listaEmpleados = <?php echo json_encode($lista_empleados); ?>;

    // Elementos del Modal
    const modalOverlay = document.getElementById('modal-cambiar-cuenta-overlay');
    const modalContainer = document.getElementById('modal-cambiar-cuenta-container');
    const panelLista = document.getElementById('panel-lista-empleados');
    const panelPassword = document.getElementById('panel-ingresar-password');
    const listaEmpleadosBody = document.getElementById('lista-empleados-body');
    const btnAbrirModal = document.getElementById('btn-modal-cambiar-cuenta');
    
    const btnCerrarModalLista = document.getElementById('btn-cerrar-modal-lista');
    const btnCerrarModalPass = document.getElementById('btn-cerrar-modal-pass');
    const btnRegresar = document.getElementById('btn-modal-regresar');
    const btnVerificar = document.getElementById('btn-modal-verificar');
    
    const inputPassword = document.getElementById('input-password-empleado');
    const hiddenEmpleadoId = document.getElementById('hidden-empleado-id');
    const modalTituloPass = document.getElementById('modal-password-titulo');
    const modalErrorPass = document.getElementById('modal-password-error');

    // Función para abrir el modal
    btnAbrirModal.addEventListener('click', (e) => {
        e.preventDefault();
        
        // 1. Popular la lista de empleados
        listaEmpleadosBody.innerHTML = ''; // Limpiar lista
        listaEmpleados.forEach(emp => {
            const item = document.createElement('div');
            item.className = 'empleado-item';
            item.textContent = emp.nombre + ' (' + emp.numero_trabajador + ')';
            item.setAttribute('data-id', emp.id);
            item.setAttribute('data-nombre', emp.nombre);
            listaEmpleadosBody.appendChild(item);
        });

        // 2. Mostrar el panel de lista
        panelLista.style.display = 'block';
        panelPassword.style.display = 'none';
        modalOverlay.style.display = 'flex'; // Cambiado a 'flex' para centrar
        // modalContainer.style.display = 'block'; // No es necesario, el overlay lo controla
    });

    // Función para cerrar el modal
    function cerrarModal() {
        modalOverlay.style.display = 'none';
        // modalContainer.style.display = 'none';
        inputPassword.value = ''; // Limpiar campos
        modalErrorPass.style.display = 'none';
    }

    // Listeners de los botones de cerrar
    btnCerrarModalLista.addEventListener('click', cerrarModal);
    btnCerrarModalPass.addEventListener('click', cerrarModal);
    modalOverlay.addEventListener('click', (e) => {
        // Cerrar solo si se hace clic en el fondo (overlay)
        if (e.target === modalOverlay) {
            cerrarModal();
        }
    });

    // Listener para el botón Regresar
    btnRegresar.addEventListener('click', () => {
        panelPassword.style.display = 'none';
        panelLista.style.display = 'block';
        modalErrorPass.style.display = 'none';
        inputPassword.value = '';
    });

    // Listener para la lista de empleados (delegación de eventos)
    listaEmpleadosBody.addEventListener('click', (e) => {
        if (e.target.classList.contains('empleado-item')) {
            const id = e.target.getAttribute('data-id');
            const nombre = e.target.getAttribute('data-nombre');
            
            // Guardar datos
            hiddenEmpleadoId.value = id;
            modalTituloPass.textContent = 'Iniciar sesión como ' + nombre;

            // Cambiar de panel
            panelLista.style.display = 'none';
            panelPassword.style.display = 'block';
            inputPassword.focus();
        }
    });

    // Listener para el botón final de VERIFICAR
    btnVerificar.addEventListener('click', async () => {
        const empleadoId = hiddenEmpleadoId.value;
        const password = inputPassword.value;
        
        if (password.length === 0) {
            modalErrorPass.textContent = 'La contraseña no puede estar vacía.';
            modalErrorPass.style.display = 'block';
            return;
        }

        modalErrorPass.style.display = 'none';
        btnVerificar.disabled = true;
        btnVerificar.textContent = 'Verificando...';

        try {
            // Usamos FormData para enviar los datos
            const formData = new FormData();
            formData.append('empleado_id', empleadoId);
            formData.append('password', password);
            
            // IMPORTANTE: Ruta absoluta al archivo PHP
            // Basado en tu .htaccess, la carpeta es /php/Empleado/
            const response = await fetch('/php/Empleado/verificar_empleado.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // ¡Éxito! La sesión se cambió en el backend.
                // Solo necesitamos recargar la página.
                cerrarModal();
                mostrarAlerta('Éxito', '¡Cambio de sesión exitoso!');
                // Esperar a que el usuario cierre el modal de éxito
                document.getElementById('simple-alert-close').onclick = () => {
                     window.location.reload(); // Recarga la página
                };
               
            } else {
                // Error de contraseña
                modalErrorPass.textContent = data.message || 'Contraseña incorrecta.';
                modalErrorPass.style.display = 'block';
            }

        } catch (error) {
            console.error('Error al verificar empleado:', error);
            modalErrorPass.textContent = 'Error de conexión. Inténtalo de nuevo.';
            modalErrorPass.style.display = 'block';
        } finally {
            btnVerificar.disabled = false;
            btnVerificar.textContent = 'Verificar';
        }
    });

    // Permitir "Enter" en el campo de contraseña
    inputPassword.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            btnVerificar.click();
        }
    });

    // ==========================================================
    // === FIN: LÓGICA PARA CAMBIAR DE CUENTA ===
    // ==========================================================

  </script>
</body>
</html>