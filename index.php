<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('session.cookie_path', '/');
session_start();

// --- INICIO DE LA SOLUCIÓN ANTI-CACHÉ ---
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
// --- FIN DE LA SOLUCIÓN ---

// PHP para el header del restaurante
$site_name = "Hao Mei Lai";
$site_desc = "Auténtica Comida China";
$logo_url = "/imagenes/logo comida.png";
$current_page = 'inicio';

// Incluir archivo para obtener t productos
$top_productos = [];
if (file_exists('get_top_productos.php')) {
    try {
        require_once 'get_top_productos.php';
        $top_productos = obtenerTop3Productos();
    } catch (Exception $e) {
        error_log("Error al cargar productos: " . $e->getMessage());
        $top_productos = [];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/User.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-translate><?php echo htmlspecialchars($site_name); ?> Comida China</title>
    
    <style>
    /* Ocultamos el header original que viene de User.css */
    .header {
        display: none !important;
    }
    
    * {
        box-sizing: border-box;
    }
    
    html {
        margin: 0 !important;
        padding: 0 !important;
        overflow-x: hidden;
        overflow-y: auto;
    }
    
    body {
        margin: 0 !important;
        padding: 0 !important;
        min-height: 100vh;
    }
    
    main {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }

    /* Ajuste para que el Hero ocupe toda la pantalla */
    .bienvenida-hero {
        height: 60vh;
        min-height: 350px;
    }
    
    /* Estilos del Modo Oscuro */
    body.dark-mode {
        background-color: #121212;
        color: #e0e0e0;
    }
    
    body.dark-mode h2 {
        color: #ffd700 !important;
    }
    
    body.dark-mode .bienvenida-hero .bienvenida-overlay {
        background: rgba(0, 0, 0, 0.6);
    }
    
    body.dark-mode .icon-btn-menu {
        background: #2c2c2c;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.4), 0 1.5px 8px rgba(0, 0, 0, 0.3);
    }
    
    body.dark-mode .icon-btn-menu span {
        color: #ccc !important;
    }
    
    body.dark-mode .menu-card-top {
        background: #2c2c2c;
        border: 2.5px solid #ffd700;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.4), 0 1.5px 8px rgba(0, 0, 0, 0.3);
    }
    
    body.dark-mode .menu-card-top h3 {
        color: #ffd700;
    }
    
    body.dark-mode .menu-card-body p {
        color: #e0e0e0;
    }
    
    body.dark-mode .footer {
        background-color: #1f1f1f;
        color: #e0e0e0;
    }
    
    body.dark-mode .footer a {
        color: #9c9c9c;
    }
    
    body.dark-mode .footer a:hover {
        color: #ffd700;
    }
    
    body.dark-mode .footer-bottom span {
        color: #9c9c9c;
    }
    
    body.dark-mode .ventas-badge {
        background: #ffd700;
        color: #121212;
    }
    
    /* --- INICIO: Estilos para Modal de Pedidos --- */
    .modal-backdrop {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.5);
    }
    
    .modal-content {
        background-color: #fefefe;
        margin: 10% auto;
        padding: 0;
        border: 1px solid #888;
        width: 90%;
        max-width: 600px;
        border-radius: 18px;
        box-shadow: 0 4px 25px rgba(0,0,0,0.2);
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from { transform: translateY(-50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    .modal-header {
        padding: 16px 24px;
        background-color: #b30028;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 18px 18px 0 0;
    }
    
    .modal-header h2 {
        color: white !important;
        margin: 0;
        font-size: 1.4em;
    }
    
    .modal-close {
        color: white;
        font-size: 32px;
        font-weight: bold;
        background: none;
        border: none;
        cursor: pointer;
        line-height: 1;
        padding: 0;
        opacity: 0.8;
    }
    
    .modal-close:hover {
        opacity: 1;
    }
    
    .modal-body {
        padding: 24px;
        max-height: 60vh;
        overflow-y: auto;
    }
    
    .modal-body .pedidos-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .pedido-item {
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 12px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        font-size: 0.95em;
    }
    
    .pedido-item strong {
        color: #b30028;
    }
    
    .pedido-item .pedido-codigo {
        grid-column: 1 / -1;
        font-size: 1.1em;
        font-weight: bold;
    }
    
    .pedido-item .pedido-total {
        font-weight: bold;
        color: #333;
    }
    
    .pedido-estado {
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 5px;
        text-align: center;
    }
    
    .pedido-estado.pendiente { background-color: #f0ad4e; color: white; }
    .pedido-estado.pagado { background-color: #5cb85c; color: white; }
    .pedido-estado.cancelado { background-color: #d9534f; color: white; }
    
    body.dark-mode .modal-content {
        background-color: #2c2c2c;
        border-color: #ffd700;
    }
    
    body.dark-mode .modal-header {
        background-color: #1f1f1f;
        border-bottom: 1px solid #ffd700;
    }
    
    body.dark-mode .modal-close {
        color: #e0e0e0;
    }
    
    body.dark-mode .modal-body {
        color: #e0e0e0;
    }
    
    body.dark-mode .pedido-item {
        background: #3a3a3a;
        border-color: #555;
    }
    
    body.dark-mode .pedido-item strong {
        color: #ffd700;
    }
    
    body.dark-mode .pedido-item .pedido-total {
        color: #e0e0e0;
    }

    .pedido-link {
        color: #b30028;
        text-decoration: none;
        font-weight: bold;
        transition: text-decoration 0.2s;
    }
    
    .pedido-link:hover {
        text-decoration: underline;
    }
    
    body.dark-mode .pedido-link {
        color: #ffd700;
    }
    
    /* Estilos para botones de categoría */
    .icon-btn-menu {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        background: #fff;
        border: none;
        border-radius: 16px;
        padding: 18px 16px;
        cursor: pointer;
        box-shadow: 0 4px 18px rgba(220, 0, 0, 0.10), 0 1.5px 8px rgba(0,0,0,0.08);
        transition: border 0.2s, box-shadow 0.2s;
    }
    
    .icon-btn-menu:hover {
        border: 2px solid #ffd700;
        box-shadow: 0 6px 24px rgba(220, 0, 0, 0.18), 0 2px 12px rgba(0,0,0,0.12);
    }
    
    .icon-btn-menu svg {
        margin-bottom: 4px;
    }
    
    /* Estilos para tarjetas de productos */
    .menu-card-top {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 4px 18px rgba(220, 0, 0, 0.10), 0 1.5px 8px rgba(0,0,0,0.08);
        border: 2.5px solid #b30028;
        overflow: hidden;
        width: 260px;
        margin-bottom: 18px;
        transition: transform 0.3s, box-shadow 0.3s, border 0.2s;
        cursor: pointer;
        position: relative;
    }
    
    .menu-card-top:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 28px rgba(220, 0, 0, 0.20), 0 3px 14px rgba(0,0,0,0.15);
        border: 2.5px solid #ffd700;
    }
    
    .menu-card-top img {
        display: block;
        width: 100%;
        height: 160px;
        object-fit: cover;
        transition: transform 0.3s;
    }
    
    .menu-card-top:hover img {
        transform: scale(1.05);
    }
    
    .menu-card-body {
        padding: 18px 16px 14px 16px;
    }
    
    .menu-card-top h3 {
        color: #b30028;
        font-size: 1.18em;
        margin-bottom: 8px;
    }
    
    .menu-card-body p {
        color: #666;
        font-size: 0.95em;
        line-height: 1.4;
        margin-bottom: 8px;
    }
    
    .precio-ventas {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 12px;
    }
    
    .precio {
        font-size: 1.2em;
        font-weight: bold;
        color: #b30028;
    }
    
    .ventas-badge {
        background: #b30028;
        color: #fff;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.85em;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .menu-categorias {
            flex-direction: column;
            align-items: center;
        }
        .icon-btn-menu {
            width: 90%;
            max-width: 300px;
        }
    }
    </style>
</head>
<body>
    
    <?php 
    // INCLUSIÓN DEL HEADER UNIFICADO
    // Si está en la raíz del proyecto:
    if (file_exists('header_unificado.php')) {
        require_once 'header_unificado.php';
    } 
    // Si está en una carpeta includes:
    elseif (file_exists('includes/header_unificado.php')) {
        require_once 'includes/header_unificado.php';
    }
    // Si no se encuentra, mostrar un mensaje de error
    else {
        echo '<!-- ERROR: No se pudo encontrar header_unificado.php -->';
    }
    ?>
    
    <main>
        <section class="bienvenida-hero">
            <div class="bienvenida-overlay">
                <h2>Hao Mei Lai <span data-translate>Te da la bienvenida</span>!</h2>
                <p data-translate>Disfruta de la <b>auténtica experiencia culinaria china</b> en un ambiente inigualable.</p>
            </div>
        </section>
        
        <section class="menu-section">
            <h2 style="text-align:center; color: #b30000; margin-top:32px;" data-translate>Explora por categoría</h2>
            <div class="menu-categorias" style="display:flex;justify-content:center;gap:38px;margin-bottom:32px;flex-wrap:wrap;">
                <button class="icon-btn-menu" title="Entrantes" data-categoria="Entrantes">
                    <svg width="36" height="36" viewBox="0 0 32 32" fill="none">
                        <ellipse cx="16" cy="22" rx="12" ry="5" fill="#ffe5b4"/>
                        <ellipse cx="16" cy="22" rx="10" ry="3.5" fill="#fff"/>
                        <rect x="12" y="10" width="8" height="8" rx="4" fill="#ffd700"/>
                        <ellipse cx="16" cy="14" rx="4" ry="2" fill="#ffe5b4"/>
                    </svg>
                    <span style="font-size:1.18em;color:#666;" data-translate>Entrantes</span>
                </button>
                
                <button class="icon-btn-menu" title="Sopas" data-categoria="Sopas">
                    <svg width="36" height="36" viewBox="0 0 32 32" fill="none">
                        <ellipse cx="16" cy="22" rx="10" ry="4" fill="#ffe5b4"/>
                        <ellipse cx="16" cy="22" rx="8" ry="2.5" fill="#fff"/>
                        <rect x="22" y="10" width="2" height="8" rx="1" fill="#b3b3b3" transform="rotate(30 22 10)"/>
                        <ellipse cx="16" cy="16" rx="7" ry="4" fill="#ffd700"/>
                    </svg>
                    <span style="font-size:1.18em;color:#666;" data-translate>Sopas</span>
                </button>
                
                <button class="icon-btn-menu" title="Principales" data-categoria="Principales">
                    <svg width="36" height="36" viewBox="0 0 32 32" fill="none">
                        <ellipse cx="16" cy="22" rx="11" ry="4.5" fill="#ffe5b4"/>
                        <ellipse cx="16" cy="22" rx="9" ry="2.8" fill="#fff"/>
                        <rect x="10" y="8" width="2" height="14" rx="1" fill="#b30000" transform="rotate(15 10 8)"/>
                        <rect x="20" y="8" width="2" height="14" rx="1" fill="#ffd700" transform="rotate(-15 20 8)"/>
                        <ellipse cx="16" cy="16" rx="5" ry="2.5" fill="#ffd700"/>
                    </svg>
                    <span style="font-size:1.18em;color:#666;" data-translate>Principales</span>
                </button>
                
                <button class="icon-btn-menu" title="Vegetariano" data-categoria="Vegetariano">
                    <svg width="36" height="36" viewBox="0 0 32 32" fill="none">
                        <path d="M16 28c-6-6-8-14-8-18 0-2 2-4 4-4 2 0 4 2 4 4 0-2 2-4 4-4 2 0 4 2 4 4 0 4-2 12-8 18z" fill="#7ed957"/>
                        <path d="M16 28V10" stroke="#388e3c" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span style="font-size:1.18em;color:#666;" data-translate>Vegetariano</span>
                </button>
                
                <button class="icon-btn-menu" title="Bebidas" data-categoria="Bebidas">
                    <svg width="36" height="36" viewBox="0 0 32 32" fill="none">
                        <rect x="12" y="12" width="8" height="12" rx="3" fill="#b3e0ff"/>
                        <rect x="15" y="6" width="2" height="8" rx="1" fill="#00bcd4"/>
                        <rect x="14" y="6" width="4" height="2" rx="1" fill="#ffd700"/>
                        <rect x="13" y="24" width="6" height="2" rx="1" fill="#fff"/>
                    </svg>
                    <span style="font-size:1.18em;color:#666;" data-translate>Bebidas</span>
                </button>
            </div>
        </section>
        
        <section class="menu-section">
            <h2 style="text-align:center; color: #b30000; margin-top:18px;" data-translate>Top 3 más comprados</h2>
            <div class="menu-top3" style="display:flex;justify-content:center;gap:38px;flex-wrap:wrap;">
                <?php if (!empty($top_productos)): ?>
                    <?php foreach ($top_productos as $producto): ?>
                        <div class="menu-card-top" onclick="irAProducto(<?php echo $producto['id']; ?>)">
                            <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" 
                                 alt="<?php echo htmlspecialchars($producto['nombre']); ?>" 
                                 onerror="this.src='/imagenes/placeholder-food.jpg';">
                            <div class="menu-card-body">
                                <h3 data-translate><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                                <p data-translate><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                                <div class="precio-ventas">
                                    <span class="precio">$<?php echo number_format($producto['precio'], 2); ?></span>
                                    <?php if ($producto['total_ventas'] > 0): ?>
                                        <span class="ventas-badge" data-translate><?php echo $producto['total_ventas']; ?> ventas</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="menu-card-top">
                        <img src="imagenes/placeholder-food.jpg" alt="Producto" style="width:100%;height:160px;object-fit:cover;border-radius:18px 18px 0 0;">
                        <div class="menu-card-body">
                            <h3 data-translate>Próximamente</h3>
                            <p data-translate>Estamos preparando nuestros mejores platillos para ti.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Modal de Pedidos -->
    <div id="modalPedidos" class="modal-backdrop">
        <div class="modal-content">
            <div class="modal-header">
                <h2 data-translate>Mis Pedidos</h2>
                <button class="modal-close" onclick="cerrarModalPedidos()">&times;</button>
            </div>
            <div id="modalPedidosBody" class="modal-body">
                <p data-translate>Cargando pedidos...</p>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-main">
            <div class="footer-column">
                <h3 data-translate>Más información sobre Hao Mei Lai</h3>
                <ul>
                    <li><a href="/php/AboutUs/about" data-translate>Contáctanos</a></li>
                    <li><a href="/php/IDS/iniciodesesion.php" data-translate>Regístrate y recibe información</a></li>
                    <li><a href="#" data-translate>Hao Mei Lai en el mundo</a></li>
                </ul>
            </div>
            <div class="footer-column footer-social">
                <h3 data-translate>Síguenos</h3>
                <div class="social-icons">
                    <a href="https://www.facebook.com/profile.php?id=61582778918382" target="_blank" aria-label="Facebook">
                        <svg width="28" height="28" viewBox="0 0 32 32" fill="none">
                            <circle cx="16" cy="16" r="16" fill="#1877F3"/>
                            <path d="M21 16.02h-3v8h-3v-8h-2v-3h2v-1.5c0-2.07 1.02-3.5 3.5-3.5h2.5v3h-2c-.47 0-.5.18-.5.5V13h2.5l-.5 3z" fill="#fff"/>
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/hao_mei_lai/" target="_blank" aria-label="Instagram">
                        <svg width="28" height="28" viewBox="0 0 32 32" fill="none">
                            <radialGradient id="ig" cx="0.5" cy="0.5" r="0.8">
                                <stop offset="0%" stop-color="#fdf497"/>
                                <stop offset="45%" stop-color="#fdf497"/>
                                <stop offset="60%" stop-color="#fd5949"/>
                                <stop offset="90%" stop-color="#d6249f"/>
                                <stop offset="100%" stop-color="#285AEB"/>
                            </radialGradient>
                            <rect x="4" y="4" width="24" height="24" rx="8" fill="url(#ig)"/>
                            <circle cx="16" cy="16" r="6" fill="none" stroke="#fff" stroke-width="2"/>
                            <circle cx="22" cy="10" r="1.5" fill="#fff"/>
                        </svg>
                    </a>
                    <a href="https://x.com/HaoMeiLai" target="_blank" aria-label="Twitter">
                        <svg width="28" height="28" viewBox="0 0 32 32" fill="none">
                            <circle cx="16" cy="16" r="16" fill="#1DA1F2"/>
                            <path d="M24 12.3c-.5.2-1 .4-1.5.5.5-.3.9-.8 1.1-1.3-.5.3-1 .5-1.6.6-.5-.5-1.2-.8-2-.8-1.5 0-2.7 1.2-2.7 2.7 0 .2 0 .4.1.6-2.2-.1-4.1-1.2-5.4-2.8-.2.4-.3.8-.3 1.3 0 .9.5 1.7 1.2 2.2-.4 0-.8-.1-1.1-.3v.1c0 1.3.9 2.3 2.1 2.6-.2.1-.4.1-.7.1-.2 0-.3 0-.5-.1.3 1 1.3 1.7 2.4 1.7-1 .8-2.2 1.3-3.5 1.3-.2 0-.4 0-.6-.1 1.2.8 2.6 1.3 4.1 1.3 4.9 0 7.6-4 7.6-7.6v-.3c.5-.4 1-.9 1.3-1.5z" fill="#fff"/>
                        </svg>
                    </a>
                    <a href="https://www.tiktok.com/@hao_mei_lai" aria-label="TikTok">
                        <svg width="28" height="28" viewBox="0 0 32 32" fill="none">
                            <circle cx="16" cy="16" r="16" fill="#000"/>
                            <path d="M22.5 14.5c-1.1 0-2-.9-2-2V9.5h-2v9c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2c.2 0 .4 0 .5.1v-2.1c-.2 0-.3-.1-.5-.1-2.2 0-4 1.8-4 4s1.8 4 4 4 4-1.8 4-4v-3.5c.6.4 1.3.6 2 .6v-2z" fill="#fff"/>
                            <path d="M20.5 9.5v3c0 1.1.9 2 2 2v-2c-.6 0-1-.4-1-1v-2h-1z" fill="#25F4EE"/>
                            <path d="M18.5 9.5v9c0 1.1-.9 2-2 2v2c2.2 0 4-1.8 4-4v-9h-2z" fill="#FE2C55"/>
                        </svg>
                    </a>
                </div>
                
                <div class="footer-logo">
                    <img src="/imagenes/logo comida.png" alt="Logo Hao Mei Lai">
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; <?php echo date("Y"); ?> Hao Mei Lai. <span data-translate>Todos los derechos reservados.</span></span>
            <span> | <a href="/php/Inicio/acepto.marketing.php" data-translate>Privacidad de datos</a> | <a href="/php/Inicio/terminos_condiciones.php" data-translate>Términos y condiciones</a> | </span>
        </div>
    </footer>
    
    <script>
    // Función para ir al producto específico
    function irAProducto(idProducto) {
        window.location.href = 'php/menu2/menu2.php?producto_id=' + idProducto;
    }
    
    // Enlace de "Mis Pedidos" en el header
    document.addEventListener('DOMContentLoaded', () => {
        const pedidosLink = document.querySelector('#headerDashboardMenu a[href="#pedidos"]');
        if (pedidosLink) {
            pedidosLink.addEventListener('click', (e) => {
                e.preventDefault();
                mostrarPedidos();
                document.getElementById('headerDashboardMenu').classList.remove('show');
            });
        }
        
        // Cargar modo oscuro si está activado
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
        }
    });
    
    // Redirección al menú con categoría
    document.querySelectorAll('.icon-btn-menu').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoria = btn.getAttribute('data-categoria');
            if (categoria) {
                window.location.href = 'php/menu2/menu2.php?categoria=' + encodeURIComponent(categoria);
            }
        });
    });

    // Obtener referencias a los elementos del modal
    const modalPedidos = document.getElementById('modalPedidos');
    const modalPedidosBody = document.getElementById('modalPedidosBody');

    // Función para CERRAR el modal
    function cerrarModalPedidos() {
        modalPedidos.style.display = 'none';
    }

    // Función para ABRIR el modal y CARGAR los datos
    async function mostrarPedidos() {
        modalPedidos.style.display = 'block';
        modalPedidosBody.innerHTML = '<p data-translate>Cargando pedidos...</p>';

        try {
            const response = await fetch('/php/menu2/get_pedidos.php');
            
            if (!response.ok) {
                if (response.status === 403) {
                    modalPedidosBody.innerHTML = '<p data-translate>Debes iniciar sesión para ver tus pedidos.</p> <a href="/php/IDS/iniciodesesion.php" class="button-primary" data-translate>Iniciar sesión</a>';
                } else {
                    throw new Error(`Error ${response.status}: ${response.statusText}`);
                }
                return;
            }

            const data = await response.json();

            if (data.ok) {
                if (data.pedidos.length > 0) {
                    let html = '<ul class="pedidos-list">';
                    
                    data.pedidos.forEach(pedido => {
                        const fecha = new Date(pedido.fecha_creacion).toLocaleString('es-ES', {
                            day: '2-digit', month: '2-digit', year: 'numeric',
                            hour: '2-digit', minute: '2-digit'
                        });
                        
                        html += `
                            <li class="pedido-item">
                                <div class="pedido-codigo">
                                    <strong data-translate>Pedido:</strong> 
                                    <a href="/php/menu2/ver_factura.php?codigo=${pedido.codigo_pedido}" target="_blank" class="pedido-link">
                                        ${pedido.codigo_pedido}
                                    </a>
                                </div>
                                <div>
                                    <strong data-translate>Fecha:</strong> ${fecha}
                                </div>
                                <div class="pedido-total">
                                    <strong data-translate>Total:</strong> ${parseFloat(pedido.total).toFixed(2)}
                                </div>
                                <div>
                                    <strong data-translate>Estado:</strong> 
                                    <span class="pedido-estado ${String(pedido.estado).toLowerCase()}" data-translate>${pedido.estado}</span>
                                </div>
                            </li>
                        `;
                    });
                    
                    html += '</ul>';
                    modalPedidosBody.innerHTML = html;
                } else {
                    modalPedidosBody.innerHTML = '<p data-translate>No tienes ningún pedido anterior.</p>';
                }
            } else {
                modalPedidosBody.innerHTML = `<p data-translate>Error: ${data.error}</p>`;
            }

        } catch (error) {
            console.error('Error al cargar pedidos:', error);
            modalPedidosBody.innerHTML = '<p data-translate>Error al conectar con el servidor. Inténtalo de nuevo.</p>';
        }
    }

    // Cerrar el modal si se hace clic fuera
    window.onclick = function(event) {
        if (event.target == modalPedidos) {
            cerrarModalPedidos();
        }
    }
    </script>
    
    <script src="/php/Traductor/traductor.js"></script>
</body>
</html>