<?php
// menu2.php (Diseño Renovado - Buscador Compacto, Categorías en Lista y Layout Específico)
ini_set('session.cookie_path', '/');
session_start();

// --- INICIO DE LA SOLUCIÓN ANTI-CACHÉ ---
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
// --- FIN DE LA SOLUCIÓN ---

// Variable que verifica si es un usuario logueado
$is_logged_in = isset($_SESSION['rol']) && strtolower($_SESSION['rol']) === 'usuario';

// --- CONEXIÓN A LA BASE DE DATOS ---
$host = "srv562.hstgr.io";
$user = "u162512390_Admin";
$pass = "biuqkb>O3";
$db = "u162512390_HaoMeiLai";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$logo_url = "/imagenes/logo comida.png";

// --- OBTENCIÓN DE PRODUCTOS Y CATEGORÍAS ---
$sql = "SELECT id, producto, precio, stock, imagen, categoria FROM almacen WHERE activo = 1";
$result = $conn->query($sql);
$productos = [];
$categorias = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $productos[] = [
            "id" => $row['id'],
            "nombre" => $row['producto'],
            "precio" => $row['precio'],
            "categoria" => $row['categoria'],
            "imagen" => !empty($row['imagen']) ? '/php/menu2/imagenes_productos/' . $row['imagen'] : '/php/menu2/imagenes_productos/default.jpg'
        ];
        if (!in_array($row['categoria'], $categorias)) {
            $categorias[] = $row['categoria'];
        }
    }
}
sort($categorias);
$conn->close();

$nombre_usuario = $is_logged_in ? htmlspecialchars($_SESSION['nombre']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú - Hao Mei Lai</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
        <style>
        /* ============================ */
        /* Variables y Fondos          */
        /* ============================ */
        :root {
            --color-primary: #c62828; /* ROJO para botones (modo claro) */
            --color-secondary: #ffffff; /* BLANCO para texto sobre rojo (modo claro) */
            --color-accent: #b71c1c; /* Rojo más oscuro para hover (modo claro) */
            --color-dark: #000000; /* NEGRO para texto (modo claro) */
            --color-light: #f5f5f5;
            --color-bg: #ffffff; /* BLANCO para fondos (modo claro) */
            
            /* Colores para modo oscuro - NEGRO Y DORADO */
            --color-dorado: #D4AF37; /* Dorado principal */
            --color-dorado-claro: #FFD700; /* Dorado claro para acentos */
            --color-dorado-oscuro: #B8860B; /* Dorado oscuro para hover */
            --color-negro: #0a0a0a; /* Negro principal */
            --color-negro-claro: #1a1a1a; /* Negro más claro para fondos */
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #333;
            position: relative;
            overflow-x: hidden;
            min-height: 100vh;
        }

        body.dark-mode {
            background: linear-gradient(135deg, var(--color-negro) 0%, var(--color-negro-claro) 100%);
            color: #e0e0e0;
        }

        /* ============================ */
        /* Navbar y Logo - BLANCO CON LETRAS NEGRAS */
        /* ============================ */
        .navbar {
            background: #ffffff !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
           border-bottom: 2px solid #c62828 !important;
            padding: 0.8rem 0;
        }

        /* LOGO CIRCULAR Y MÁS GRANDE */
        .navbar-brand img {
            height: 70px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
            border-radius: 50%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .navbar-brand img:hover {
            transform: scale(1.05);
        }

        /* Aumento de espaciado en los enlaces del menú principal */
        .navbar-nav.mx-auto .nav-item {
            margin: 0 20px;
        }
        
        .nav-link {
            color: #000000 !important; /* NEGRO */
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: #c62828 !important; /* ROJO al pasar mouse */
            transform: translateY(-2px);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: #c62828; /* Línea roja */
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 80%;
        }

        /* ============================ */
        /* CATEGORÍAS TIPO LISTA - COLORES NUEVOS */
        /* ============================ */
        
        /* Estilo base del botón de categorías */
        .dropdown .btn {
            background: #ffffff; /* BLANCO */
            border: 2px solid #c62828; /* Borde ROJO */
            color: #000000; /* Letra NEGRA */
            border-radius: 25px;
            font-weight: 600;
            padding: 10px 20px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .dropdown .btn:hover {
            background: #c62828; /* Fondo ROJO al pasar mouse */
            color: #ffffff; /* Letra BLANCA */
            border-color: #c62828;
        }
        
        /* Estilo del menú desplegable */
        .dropdown-menu {
            background: #ffffff; /* Fondo BLANCO */
            border: 1px solid #c62828; /* Borde ROJO */
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(198, 40, 40, 0.2);
            max-height: 300px;
            overflow-y: auto;
        }
        
        /* Estilo de los items de la lista (categorías) */
        .dropdown-item {
            color: #000000; /* Letra NEGRA */
            font-weight: 500;
            transition: all 0.2s ease;
            padding: 10px 15px;
        }
        
        .dropdown-item:hover, .dropdown-item.activa {
            background: #c62828 !important; /* Fondo ROJO */
            color: #ffffff !important; /* Letra BLANCA */
            font-weight: 700;
        }
        
        /* Separador */
        .dropdown-divider {
            border-color: rgba(198, 40, 40, 0.2); /* Divisor rojo claro */
        }
        
        /* ============================ */
        /* Buscador Compacto y Expansible - AJUSTADO */
        /* ============================ */
        .search-wrapper {
            position: relative;
            width: 100%; 
        }

        .search-wrapper input {
            width: 50px;
            height: 45px;
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid rgba(0, 0, 0, 0.2);
            border-radius: 25px;
            padding: 12px 15px;
            color: #000000;
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            opacity: 0;
            pointer-events: none;
        }

        .search-wrapper.expanded input {
            width: 100%;
            opacity: 1;
            padding: 12px 75px 12px 20px;
            pointer-events: auto;
        }

        .search-wrapper input:focus {
            background: rgba(255, 255, 255, 1);
            border-color: #c62828; /* Borde ROJO al focus */
            box-shadow: 0 0 20px rgba(198, 40, 40, 0.3);
            outline: none;
        }

        .search-wrapper input::placeholder {
            color: rgba(0, 0, 0, 0.6);
        }

        .search-wrapper .search-icon {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            color: #000000;
            font-size: 1.2rem;
            background: rgba(255, 255, 255, 0.9);
            padding: 10px 15px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            z-index: 10;
            border: 2px solid rgba(0, 0, 0, 0.2);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .search-wrapper.expanded .search-icon {
            right: 50px;
            padding: 0;
            border: none;
            background: transparent;
            pointer-events: none;
            color: rgba(0, 0, 0, 0.6);
        }

        .search-wrapper .clear-btn {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #c62828; /* ROJO */
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 5px;
            transition: color 0.2s ease;
            display: none;
            z-index: 10;
        }
        
        .search-wrapper.expanded .clear-btn.visible {
            display: block;
        }
        
        /* ============================ */
        /* Grid de Productos - ESTILOS NUEVOS */
        /* ============================ */
        .grid-productos {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .producto-card {
            background: #ffffff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            position: relative;
            border: 1px solid #f0f0f0;
        }
        
        .producto-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(198, 40, 40, 0.15);
        }

        .producto-imagen-wrapper {
            position: relative;
            width: 100%;
            height: 250px;
            overflow: hidden;
            background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
        }

        .producto-imagen {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .producto-card:hover .producto-imagen {
            transform: scale(1.15);
        }

        .producto-info {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background: #fff;
        }

        /* Nombre del platillo en NEGRO */
        .producto-nombre {
            color: #000000 !important;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 10px;
            line-height: 1.3;
            min-height: 50px;
        }

        /* Precio en gris oscuro */
        .producto-precio {
            font-size: 1.4rem;
            font-weight: 800;
            color: #333333;
            margin-bottom: 15px;
        }

        /* BOTÓN AGREGAR - ROJO */
        .btn-agregar {
            background: #c62828; /* ROJO */
            color: white;
            border: none;
            font-weight: 600;
            border-radius: 10px;
            padding: 12px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
            box-shadow: 0 4px 10px rgba(198, 40, 40, 0.3);
        }

        .btn-agregar:hover {
            background: #b71c1c; /* Rojo más oscuro */
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(198, 40, 40, 0.4);
        }

        .btn-agregar i {
            margin-right: 5px;
        }

        /* Carrito Sidebar Premium      */
        .carrito-sidebar {
            position: fixed;
            top: 0;
            right: -450px;
            width: 450px;
            height: 100vh;
            background: linear-gradient(180deg, var(--color-negro) 0%, var(--color-negro-claro) 100%);
            z-index: 1050;
            transition: right 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            box-shadow: -10px 0 30px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            border-left: 3px solid var(--color-dorado); /* Línea dorada lateral */
        }

        .carrito-sidebar.open {
            right: 0;
        }

        .carrito-header {
            padding: 25px;
            border-bottom: 2px solid rgba(212, 175, 55, 0.3); /* Dorado transparente */
            background: rgba(212, 175, 55, 0.1); /* Fondo dorado muy claro */
            position: relative;
        }

        .carrito-header h4 {
            margin: 0;
            color: var(--color-dorado); /* DORADO */
            font-weight: 700;
            font-size: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .carrito-header .btn-close {
            position: absolute;
            top: 20px;
            right: 20px;
            filter: invert(1) grayscale(100%) brightness(200%);
            opacity: 0.8;
        }

        .carrito-body {
            flex-grow: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .carrito-body::-webkit-scrollbar {
            width: 8px;
        }

        .carrito-body::-webkit-scrollbar-thumb {
            background: var(--color-dorado); /* Dorado */
            border-radius: 10px;
        }

        .carrito-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(212, 175, 55, 0.05); /* Fondo dorado muy claro */
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .carrito-item:hover {
            background: rgba(212, 175, 55, 0.1); /* Fondo dorado más intenso al hover */
            transform: translateX(-5px);
        }

        .carrito-item-info {
            flex-grow: 1;
            margin: 0 15px;
        }

        .carrito-item-info h6 {
            margin: 0;
            font-weight: 600;
            color: var(--color-dorado-claro); /* Dorado claro */
            font-size: 1rem;
        }

        .carrito-item-info small {
            color: var(--color-dorado); /* Dorado */
        }

        .carrito-item-precio {
            font-weight: 700;
            color: var(--color-dorado-claro); /* Dorado claro */
            font-size: 1.1rem;
        }

        .carrito-cantidad {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .carrito-cantidad button {
            background: rgba(212, 175, 55, 0.2);
            border: 2px solid var(--color-dorado);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--color-dorado);
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .carrito-cantidad button:hover {
            background: var(--color-dorado);
            color: var(--color-negro);
            transform: scale(1.1);
        }

        .carrito-cantidad span {
            color: var(--color-dorado-claro);
            font-weight: 600;
            min-width: 25px;
            text-align: center;
        }

        .carrito-footer {
            padding: 25px;
            background: rgba(212, 175, 55, 0.15);
            border-top: 2px solid rgba(212, 175, 55, 0.3);
        }

        .total-display {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            color: var(--color-dorado-claro);
            font-size: 1rem;
        }

        .total-display.h5 {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--color-dorado); /* Dorado más intenso */
            padding-top: 10px;
            border-top: 2px solid rgba(212, 175, 55, 0.3);
        }

        #btn-generar-pedido {
            background: var(--color-dorado); /* DORADO */
            color: var(--color-negro); /* NEGRO */
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
            border: none;
        }

        #btn-generar-pedido:hover {
            background: var(--color-dorado-oscuro); /* Dorado oscuro */
            color: var(--color-negro);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.5);
        }

        .carrito-vacio {
            text-align: center;
            color: rgba(212, 175, 55, 0.5);
            padding: 60px 20px;
            font-size: 1.1rem;
        }

        .carrito-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
            z-index: 1049;
        }

        .carrito-overlay.open {
            display: block;
        }

        /* Icono de carrito en NEGRO (modo claro) */
        .cart-icon-wrapper {
            position: relative;
            cursor: pointer;
            font-size: 1.5rem;
            color: #000000; /* NEGRO (modo claro) */
            transition: all 0.3s ease;
        }

        .cart-icon-wrapper:hover {
            color: #c62828; /* ROJO al pasar mouse (modo claro) */
            transform: scale(1.1);
        }

        /* Punto del carrito en ROJO (modo claro) / DORADO (modo oscuro) */
        .cart-dot {
            position: absolute;
            top: -8px;
            right: -12px;
            min-width: 22px;
            height: 22px;
            background: #c62828; /* ROJO (modo claro) */
            color: white;
            border-radius: 50%;
            display: none;
            justify-content: center;
            align-items: center;
            font-size: 0.75rem;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(198, 40, 40, 0.5);
            animation: pulse 2s infinite;
        }

        .cart-dot.visible {
            display: flex;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .dropdown-header {
            color: #c62828;
        }

        #toast-container {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2000;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .toast-message {
            background: #2c2c2c;
            color: #fff;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            opacity: 0;
            transition: opacity 0.3s, transform 0.3s;
            transform: translateY(-20px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
            min-width: 280px;
            text-align: center;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .toast-message button {
            background: none;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            line-height: 1;
            margin-left: 10px;
            cursor: pointer;
        }

        .toast-message.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast-message.success {
            background-color: #4CAF50;
        }

        .toast-message.error {
            background-color: #c62828; /* ROJO para errores */
        }

        .toast-message.info {
            background-color: #2196F3;
        }

        @media (max-width: 768px) {
            .carrito-sidebar { 
                width: 100%; 
                right: -100%;
            }
            .grid-productos {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }
            .top-controls-row > div { margin-bottom: 10px; }
            .dropdown { width: 100%; }
            .dropdown .btn { width: 100%; }
            .search-wrapper input { height: 40px; }
            .search-wrapper .search-icon { padding: 8px 12px; }
            .navbar-nav.mx-auto .nav-item { margin: 0; }
        }

        /* ============================ */
        /* Modo Oscuro - NEGRO Y DORADO */
        /* ============================ */
        body.dark-mode .navbar {
            background: var(--color-negro) !important;
            border-bottom: 1px solid var(--color-dorado);
            box-shadow: 0 2px 10px rgba(212, 175, 55, 0.2) !important;
        }

        body.dark-mode .nav-link {
            color: var(--color-dorado-claro) !important; /* Dorado claro */
        }

        body.dark-mode .nav-link:hover {
            color: var(--color-dorado) !important; /* Dorado más intenso */
            transform: translateY(-2px);
        }

        body.dark-mode .nav-link::after {
            background: var(--color-dorado); /* Línea dorada */
        }

        body.dark-mode .dropdown .btn {
            background: var(--color-negro-claro);
            color: var(--color-dorado); /* Dorado */
            border-color: var(--color-dorado);
        }

        body.dark-mode .dropdown .btn:hover {
            background: var(--color-dorado); /* Dorado */
            color: var(--color-negro); /* Negro */
            border-color: var(--color-dorado);
        }

        body.dark-mode .dropdown-menu {
            background: var(--color-negro-claro);
            border-color: var(--color-dorado);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
        }

        body.dark-mode .dropdown-item {
            color: var(--color-dorado-claro);
        }

        body.dark-mode .dropdown-item:hover, 
        body.dark-mode .dropdown-item.activa {
            background: var(--color-dorado) !important;
            color: var(--color-negro) !important;
        }

        body.dark-mode .dropdown-divider {
            border-color: rgba(212, 175, 55, 0.3);
        }

        body.dark-mode .producto-card {
            background: var(--color-negro-claro);
            border: 1px solid rgba(212, 175, 55, 0.3);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.1);
        }

        body.dark-mode .producto-card:hover {
            box-shadow: 0 10px 25px rgba(212, 175, 55, 0.2);
        }

        body.dark-mode .producto-info { 
            background: transparent; 
        }

        body.dark-mode .producto-nombre { 
            color: var(--color-dorado-claro) !important; /* Dorado claro */
        }

        body.dark-mode .producto-precio { 
            color: var(--color-dorado) !important; /* Dorado */
        }

        body.dark-mode .btn-agregar {
            background: var(--color-dorado); /* Dorado */
            color: var(--color-negro); /* Negro */
            box-shadow: 0 4px 10px rgba(212, 175, 55, 0.3);
        }

        body.dark-mode .btn-agregar:hover {
            background: var(--color-dorado-oscuro); /* Dorado oscuro */
            color: var(--color-negro);
            box-shadow: 0 6px 15px rgba(212, 175, 55, 0.4);
        }

        body.dark-mode .cart-icon-wrapper {
            color: var(--color-dorado-claro); /* Dorado claro */
        }
        
        body.dark-mode .cart-icon-wrapper:hover {
            color: var(--color-dorado); /* Dorado más intenso */
        }

        body.dark-mode .cart-dot {
            background: var(--color-dorado); /* Dorado en modo oscuro */
            box-shadow: 0 2px 8px rgba(212, 175, 55, 0.5);
        }

        body.dark-mode .search-wrapper input {
            background: rgba(26, 26, 26, 0.95);
            border-color: rgba(212, 175, 55, 0.3);
            color: var(--color-dorado-claro);
        }

        body.dark-mode .search-wrapper input:focus {
            background: rgba(26, 26, 26, 1);
            border-color: var(--color-dorado);
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
        }

        body.dark-mode .search-wrapper input::placeholder {
            color: rgba(212, 175, 55, 0.6);
        }

        body.dark-mode .search-wrapper .search-icon {
            color: var(--color-dorado-claro);
            background: rgba(26, 26, 26, 0.9);
            border-color: rgba(212, 175, 55, 0.3);
        }

        body.dark-mode .search-wrapper.expanded .search-icon {
            color: rgba(212, 175, 55, 0.6);
        }

        body.dark-mode .search-wrapper .clear-btn {
            color: var(--color-dorado);
        }

        body.dark-mode .dropdown-header {
            color: var(--color-dorado);
        }

        body.dark-mode .toast-message {
            background: var(--color-negro-claro);
            border: 1px solid var(--color-dorado);
        }

        /* Fila del producto: Alineación horizontal */
        .cart-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            gap: 15px;
        }
        
        /* Contenedor de la imagen (Cuadrado fijo) */
        .carrito-img-wrapper {
            width: 50px;
            height: 50px;
            flex-shrink: 0;
            border-radius: 8px;
            overflow: hidden;
            background: #eee;
        }
        
        .carrito-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Información (Nombre y precio unitario) */
        .carrito-item-info {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin: 0;
        }
        
        .carrito-item-info h6 {
            font-size: 0.9rem;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 120px;
        }
        
        /* Botones de cantidad (Más compactos) */
        .carrito-cantidad {
            display: flex;
            align-items: center;
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 2px;
            gap: 5px;
        }
        
        .carrito-cantidad button {
            width: 22px;
            height: 22px;
            font-size: 12px;
            padding: 0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .carrito-cantidad span {
            font-size: 0.9rem;
            min-width: 18px;
            text-align: center;
        }
        
        /* Precio final alineado a la derecha */
        .carrito-item-precio {
            font-weight: bold;
            font-size: 0.95rem;
            text-align: right;
            min-width: 60px;
        }
        
    </style>
</head>
<body>

    <div id="toast-container"></div>
    
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/index.php">
                <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Logo Hao Mei Lai">
            </a>
            
            <div class="d-lg-none cart-icon-wrapper" id="cart-icon-mobile">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-dot" id="cart-dot-mobile">0</span>
            </div>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars" style="color: var(--color-secondary);"></i>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="/index.php" data-translate>Inicio</a></li>
                    <li class="nav-item"><a class="nav-link active" href="/php/menu2/menu2.php" data-translate>Menú</a></li>
                </ul>
                
                <ul class="navbar-nav align-items-center ms-auto">
                    <li class="nav-item d-none d-lg-block me-3">
                        <div class="cart-icon-wrapper" id="cart-icon-desktop">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-dot" id="cart-dot-desktop">0</span>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user me-1"></i> 
                            <span data-translate><?php echo $is_logged_in ? htmlspecialchars($_SESSION['nombre']) : 'Mi Cuenta'; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <?php if ($is_logged_in): ?>
                                <li class="dropdown-header"><span data-translate>Hola</span>, <?php echo htmlspecialchars($_SESSION['nombre']); ?></li>
                                <li><hr class="dropdown-divider"></li>
                                <?php if (strtolower($_SESSION['rol']) === 'administrador'): ?>
                                    <li><a class="dropdown-item" href="/admin/dashboard_admin.php"><i class="fas fa-user-shield me-2"></i> <span data-translate>Administrador</span></a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="/php/IDS/cerrarsesion.php"><i class="fas fa-sign-out-alt me-2"></i> <span data-translate>Cerrar Sesión</span></a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="/php/IDS/iniciodesesion.php"><i class="fas fa-sign-in-alt me-2"></i> <span data-translate>Iniciar Sesión</span></a></li>
                                <li><a class="dropdown-item" href="/php/IDS/registro.php"><i class="fas fa-user-plus me-2"></i> <span data-translate>Registrarse</span></a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    
                    <li class="nav-item ms-2">
                        <button class="btn btn-sm btn-link nav-link" id="btn-translate" onclick="toggleLanguage()">
                            <i class="fas fa-globe"></i> <span id="text-lang">English</span>
                        </button>
                    </li>

                    <li class="nav-item">
                        <button class="btn btn-sm btn-link nav-link" onclick="toggleDarkMode()" title="Modo Oscuro">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row mb-4 align-items-center g-3 top-controls-row">
            <div class="col-lg-7 col-md-6 col-12">
                <div class="dropdown">
                    <button class="btn dropdown-toggle" type="button" id="dropdownMenuCategories" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-list me-2"></i> <span data-translate>Categorías</span>
                    </button>
                    <ul class="dropdown-menu" id="categories-dropdown" aria-labelledby="dropdownMenuCategories">
                        <li><a class="dropdown-item category-item activa" href="#" data-categoria="Todos" data-translate>Todos los Platos</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php foreach ($categorias as $categoria): ?>
                            <li> 
                                <a class="dropdown-item category-item" href="#" data-categoria="<?php echo htmlspecialchars($categoria); ?>">
                                    <span data-translate><?php echo htmlspecialchars($categoria); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="col-lg-5 col-md-6 col-12 ms-auto">
                <div class="search-wrapper" id="search-wrapper">
                    <input type="text" id="buscador" class="form-control" placeholder="Buscar productos...">
                    <button class="clear-btn" id="clear-search-btn"><i class="fas fa-times"></i></button>
                    <i class="fas fa-search search-icon" id="search-toggle-icon"></i>
                </div>
            </div>
        </div>

        <div class="grid-productos" id="productos-container">
        </div>
    </div>
    
    <div class="carrito-overlay" id="carrito-overlay"></div>
    <div class="carrito-sidebar" id="carrito-sidebar">
        <div class="carrito-header">
            <h4><i class="fas fa-shopping-cart me-2"></i> <span data-translate>Tu Pedido</span></h4>
            <button type="button" class="btn-close" aria-label="Cerrar" id="carrito-close"></button>
        </div>
        <div class="carrito-body" id="carrito-body">
            <div class="carrito-vacio"><i class="fas fa-shopping-basket fa-3x mb-3 d-block"></i><span data-translate>Tu carrito está vacío</span></div>
        </div>
        
        <div class="carrito-footer">
            <div class="total-display"><span data-translate>Subtotal:</span><span id="carrito-subtotal">$0.00</span></div>
            <div class="total-display"><span data-translate>IVA (16%):</span><span id="carrito-iva">$0.00</span></div>
            <div class="total-display h5"><span data-translate>Total:</span><span id="carrito-total">$0.00</span></div>
            
            <button class="btn btn-lg w-100 mt-2 mb-3" id="btn-generar-pedido">
                <i class="fas fa-money-bill-wave me-2"></i> <span data-translate>Pagar en Caja / Efectivo</span>
            </button>

            <div class="text-center text-muted mb-2 small" data-translate>- O paga ahora con -</div>
            <div id="paypal-button-container"></div>
        </div>
    </div>

    <div class="modal fade" id="modalFactura" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-check-circle"></i> <span data-translate>¡Pago Exitoso!</span></h5>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-envelope-open-text fa-4x text-primary mb-3"></i>
                    <h4 data-translate>¿Deseas recibir tu recibo por correo?</h4>
                    <p class="text-muted" data-translate>Se enviará a tu dirección registrada.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" onclick="location.reload()" data-translate>No, gracias</button>
                    <button type="button" class="btn btn-primary" id="btnEnviarCorreo" onclick="enviarCorreo()">
                        <i class="fas fa-paper-plane me-2"></i> <span data-translate>Sí, enviar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://www.paypal.com/sdk/js?client-id=AfKc8Lh8_1YPvbkaGwrbPGH4RA7ZBvYQuaCw56PG-DGNOnimgBYndbk1NoJB5kIebh9dnRrD3hJ4dSCN&currency=MXN"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="/php/Traductor/traductor.js"></script>
    
<script>
    // --- DATOS ---
    const allProductos = <?php echo json_encode($productos); ?>;
    let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    
    // --- LÓGICA DE IDIOMA ---
    let currentLang = 'es'; // Idioma actual por defecto
    const btnLangText = document.getElementById('text-lang');

    // --- DOM ---
    const productosContainer = document.getElementById('productos-container');
    const searchWrapper = document.getElementById('search-wrapper');
    const buscador = document.getElementById('buscador');
    const clearSearchBtn = document.getElementById('clear-search-btn');
    const searchToggleIcon = document.getElementById('search-toggle-icon');
    const categoriesDropdown = document.getElementById('categories-dropdown');
    
    const sidebar = document.getElementById('carrito-sidebar');
    const overlay = document.getElementById('carrito-overlay');
    const cartIconDesktop = document.getElementById('cart-icon-desktop');
    const cartIconMobile = document.getElementById('cart-icon-mobile');
    const cartDotDesktop = document.getElementById('cart-dot-desktop');
    const cartDotMobile = document.getElementById('cart-dot-mobile');
    const closeBtn = document.getElementById('carrito-close');
    const carritoBody = document.getElementById('carrito-body');
    const subtotalEl = document.getElementById('carrito-subtotal');
    const ivaEl = document.getElementById('carrito-iva');
    const totalEl = document.getElementById('carrito-total');
    const btnGenerarPedido = document.getElementById('btn-generar-pedido');
    const dropdownButton = document.getElementById('dropdownMenuCategories');
    const toastContainer = document.getElementById('toast-container');

    // --- FUNCIÓN DEL TRADUCTOR ---
    async function toggleLanguage() {
        const btnIcon = document.querySelector('#btn-translate i');
        
        // 1. Mostrar estado de carga en el icono
        btnIcon.className = "fas fa-spinner fa-spin";
        
        try {
            if (currentLang === 'es') {
                // Cambiar a Inglés
                await window.solicitarTraduccion('en');
                currentLang = 'en';
                btnLangText.textContent = 'Español'; // Botón ofrece volver a Español
            } else {
                // Volver a Español
                await window.solicitarTraduccion('es');
                currentLang = 'es';
                btnLangText.textContent = 'English'; // Botón ofrece ir a Inglés
            }
        } catch (error) {
            console.error("Error al cambiar idioma:", error);
            showToast("Error al traducir", "error");
        } finally {
            // 2. Restaurar icono
            btnIcon.className = "fas fa-globe";
        }
    }

    // Helper para mantener la traducción en contenido dinámico
    function reaplicarTraduccion() {
        // Solo intentamos traducir si el usuario NO está en el idioma original (español)
        if (currentLang !== 'es') {
            window.solicitarTraduccion(currentLang);
        }
    }

    // --- LÓGICA DE URL ---
    const urlParams = new URLSearchParams(window.location.search);
    const categoriaURL = urlParams.get('categoria');
    let categoriaActiva = categoriaURL ? decodeURIComponent(categoriaURL) : "Todos";
    const isUserLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;

    // --- UTILIDADES ---
    function showToast(message, type = 'info') {
        if (!toastContainer) return;
        const toast = document.createElement('div');
        let icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-times-circle' : 'fa-info-circle');
        let typeClass = type;

        toast.className = `toast-message ${typeClass}`;
        toast.innerHTML = `<span><i class="fas ${icon} me-2"></i>${message}</span>`; 
        toastContainer.appendChild(toast);

        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    function toggleCarrito(show) {
        sidebar.classList.toggle('open', show);
        overlay.classList.toggle('open', show);
        document.body.style.overflow = show ? 'hidden' : 'auto';
        
        if (show && carrito.length > 0) {
            const container = document.getElementById('paypal-button-container');
            if (container.innerHTML === "") {
                initPayPalButton();
            }
        }
    }

    function saveCarrito() {
        localStorage.setItem('carrito', JSON.stringify(carrito));
        renderCarrito();
    }
    
    function formatPrice(price) {
        return parseFloat(price).toFixed(2);
    }

    // --- RENDERIZADO MENU ---
    function renderMenu(categoria, busqueda = "") {
        productosContainer.innerHTML = '';
        const filtrados = allProductos.filter(p => {
            const catMatch = (categoria === "Todos" || p.categoria === categoria);
            const searchMatch = busqueda === "" || p.nombre.toLowerCase().includes(busqueda.toLowerCase());
            return catMatch && searchMatch;
        });
        
        document.querySelectorAll('.category-item').forEach(item => {
            // Usamos contains para manejar si el texto cambia por traducción
            const itemCat = item.getAttribute('data-categoria');
            item.classList.toggle('activa', itemCat === categoria);
        });

        clearSearchBtn.classList.toggle('visible', busqueda.length > 0 && searchWrapper.classList.contains('expanded'));
        
        if (filtrados.length === 0) {
            // Agregamos data-translate al mensaje
            productosContainer.innerHTML = `<div class="col-12 text-center py-5"><p class="text-muted" data-translate>No se encontraron productos</p></div>`;
            reaplicarTraduccion(); 
            return;
        }

        filtrados.forEach(p => {
            const card = document.createElement('div');
            card.className = 'producto-card';
            card.setAttribute('data-producto-id', p.id);
            // Agregamos data-translate al NOMBRE y al botón AGREGAR
            card.innerHTML = `
                <div class="producto-imagen-wrapper">
                    <img src="${p.imagen}" alt="${p.nombre}" class="producto-imagen">
                </div>
                <div class="producto-info">
                    <h5 class="producto-nombre" data-translate>${p.nombre}</h5>
                    <p class="producto-precio">$${formatPrice(p.precio)}</p>
                    <button class="btn btn-agregar w-100" onclick="agregarAlCarrito('${p.id}')">
                        <i class="fas fa-plus"></i> <span data-translate>Agregar</span>
                    </button>
                </div>`;
            productosContainer.appendChild(card);
        });

        // IMPORTANTE: Si estamos en inglés, traducir las nuevas cartas generadas
        reaplicarTraduccion();
    }

    // --- RENDERIZADO CARRITO ---
    function renderCarrito() {
        carritoBody.innerHTML = "";
        let subtotal = 0, totalItems = 0;

        if (carrito.length === 0) {
            // Agregamos data-translate
            carritoBody.innerHTML = `<div class="carrito-vacio"><i class="fas fa-shopping-basket fa-3x mb-3 d-block"></i><span data-translate>Tu carrito está vacío</span></div>`;
            const containerPaypal = document.getElementById('paypal-button-container');
            if(containerPaypal) containerPaypal.innerHTML = '';
        } else {
            carrito.forEach(item => {
                const itemTotal = item.precio * item.cantidad;
                subtotal += itemTotal;
                totalItems += item.cantidad;
                
                const productoOriginal = allProductos.find(p => p.id == item.id);
                const imagenSrc = productoOriginal ? productoOriginal.imagen : '';

                const div = document.createElement('div');
                div.className = 'cart-item';
                
                div.innerHTML = `
                    <div class="carrito-img-wrapper">
                        <img src="${imagenSrc}" alt="${item.nombre}">
                    </div>
                    <div class="carrito-item-info">
                        <h6 data-translate>${item.nombre}</h6>
                        <small>$${formatPrice(item.precio)} c/u</small>
                    </div>
                    <div class="carrito-cantidad">
                        <button class="btn btn-sm" onclick="actualizarCantidad('${item.id}', -1)">-</button>
                        <span>${item.cantidad}</span>
                        <button class="btn btn-sm" onclick="actualizarCantidad('${item.id}', 1)">+</button>
                    </div>
                    <span class="carrito-item-precio">$${formatPrice(itemTotal)}</span>`;
                
                carritoBody.appendChild(div);
            });
        }

        const iva = subtotal * 0.16;
        const total = subtotal + iva;

        subtotalEl.textContent = `$${formatPrice(subtotal)}`;
        ivaEl.textContent = `$${formatPrice(iva)}`;
        totalEl.textContent = `$${formatPrice(total)}`;

        const showDot = totalItems > 0;
        cartDotDesktop.textContent = totalItems;
        cartDotMobile.textContent = totalItems;
        cartDotDesktop.classList.toggle('visible', showDot);
        cartDotMobile.classList.toggle('visible', showDot);

        // Traducir contenido del carrito si es necesario
        reaplicarTraduccion();
    }

    // --- FUNCIONES LÓGICAS ---
    function agregarAlCarrito(id) {
        if (!isUserLoggedIn) return showToast('Inicia sesión primero', 'error');
        
        const prod = allProductos.find(p => parseInt(p.id) === parseInt(id));
        if (!prod) return;

        const idx = carrito.findIndex(i => parseInt(i.id) === parseInt(id));
        if (idx > -1) {
            carrito[idx].cantidad++;
        } else {
            carrito.push({ id: prod.id, nombre: prod.nombre, precio: parseFloat(prod.precio), cantidad: 1 });
        }
        
        saveCarrito();
        showToast(`${prod.nombre} añadido`, 'success');
    }

    function actualizarCantidad(id, delta) {
        const idx = carrito.findIndex(i => parseInt(i.id) === parseInt(id));
        if (idx === -1) return;
        carrito[idx].cantidad += delta;
        if (carrito[idx].cantidad <= 0) carrito.splice(idx, 1);
        saveCarrito();
    }

    function procesarPedido() {
        if (carrito.length === 0) {
            showToast('🛒 Agrega productos antes de hacer el pedido', 'error');
            return;
        }
        
        btnGenerarPedido.disabled = true;
        // Agregamos data-translate al spinner
        btnGenerarPedido.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> <span data-translate>Procesando...</span>';
        reaplicarTraduccion();

        const formData = new FormData();
        formData.append('carrito_data', JSON.stringify(carrito));
        
        fetch('/crear_pedido_cliente.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('✅ ¡Pedido realizado! Revisa tu correo.', 'success');
                carrito = [];
                saveCarrito();
                toggleCarrito(false);
            } else {
                showToast(`⚠ Error: ${data.message}`, 'error');
            }
        })
        .catch(error => {
            console.error('Error en fetch:', error);
            showToast('⚠ Error de conexión. Intenta de nuevo.', 'error');
        })
        .finally(() => {
            btnGenerarPedido.disabled = false;
            btnGenerarPedido.innerHTML = '<i class="fas fa-money-bill-wave me-2"></i> <span data-translate>Pagar en Caja / Efectivo</span>';
            reaplicarTraduccion();
        });
    }

    let ultimoPedidoId = null;
    
    function initPayPalButton() {
        document.getElementById('paypal-button-container').innerHTML = '';
        
        paypal.Buttons({
            style: { shape: 'rect', color: 'gold', layout: 'vertical', label: 'pay' },
            createOrder: (data, actions) => {
                if (!isUserLoggedIn) {
                    showToast("Inicia sesión para pagar.", "error");
                    return actions.reject();
                }
                const subtotal = carrito.reduce((sum, i) => sum + (i.precio * i.cantidad), 0);
                const total = subtotal * 1.16; 
                return actions.order.create({ 
                    purchase_units: [{ amount: { value: total.toFixed(2) } }] 
                });
            },
            onApprove: (data, actions) => {
                return actions.order.capture().then(detalles => {
                    showToast("Procesando pedido...", "info");
                    
                    fetch('php/menu2/guardar_pedido_paypal.php', {
                        method: 'POST', 
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ carrito, detalles })
                    })
                    .then(r => r.json())
                    .then(res => {
                        if(res.success) {
                            ultimoPedidoId = res.pedido_id; 
                            carrito = []; 
                            saveCarrito(); 
                            toggleCarrito(false); 
                            
                            // Abrir modal y asegurar traducción
                            new bootstrap.Modal(document.getElementById('modalFactura')).show();
                            reaplicarTraduccion();
                        } else {
                            showToast("Error: " + res.message, "error");
                        }
                    })
                    .catch(e => {
                        console.error(e);
                        showToast("Error de conexión", "error");
                    });
                });
            },
            onError: (err) => { 
                console.error(err); 
                showToast("Error con PayPal", "error"); 
            }
        }).render('#paypal-button-container');
    }

    // --- EVENTOS ---
    document.addEventListener('DOMContentLoaded', () => {
        setDarkMode(localStorage.getItem('darkMode') === 'true');
        
        // Renderizado inicial del texto del botón dropdown
        let catText = categoriaActiva;
        if(catText === "Todos") catText = "Categorías";
        
        // Usamos span para poder traducirlo si es "Categorías" o el nombre de una categoría
        dropdownButton.innerHTML = `<i class="fas fa-list me-2"></i> <span data-translate>${catText}</span>`;

        renderMenu(categoriaActiva);
        renderCarrito();
    });

    // Dropdown Categorías
    categoriesDropdown.addEventListener('click', (e) => {
        // Corrección para capturar el click aunque sea en el span interno
        const target = e.target.closest('.category-item');
        if (target) {
            e.preventDefault();
            categoriaActiva = target.getAttribute('data-categoria');
            
            // Lógica visual del botón
            const textoBoton = (categoriaActiva === 'Todos') ? 'Categorías' : categoriaActiva;
            dropdownButton.innerHTML = `<i class="fas fa-list me-2"></i> <span data-translate>${textoBoton}</span>`;
            
            buscador.value = ""; 
            collapseSearch(); 
            renderMenu(categoriaActiva);
            // reaplicarTraduccion() se llama dentro de renderMenu
        }
    });

    // Buscador
    searchToggleIcon.addEventListener('click', expandSearch);
    buscador.addEventListener('input', (e) => {
        categoriaActiva = "Todos";
        dropdownButton.innerHTML = '<i class="fas fa-list me-2"></i> <span data-translate>Categorías</span>';
        renderMenu(categoriaActiva, e.target.value);
        clearSearchBtn.classList.toggle('visible', e.target.value.length > 0);
    });
    buscador.addEventListener('blur', collapseSearch);
    clearSearchBtn.addEventListener('click', () => { buscador.value=""; renderMenu(categoriaActiva); collapseSearch(); });

    // Carrito UI
    function abrirCarrito() {
        if (!isUserLoggedIn) return showToast('Inicia sesión', 'error');
        if (carrito.length === 0) return showToast('Carrito vacío', 'info');
        toggleCarrito(true);
    }
    cartIconDesktop.addEventListener('click', abrirCarrito);
    cartIconMobile.addEventListener('click', abrirCarrito);
    closeBtn.addEventListener('click', () => toggleCarrito(false));
    overlay.addEventListener('click', () => toggleCarrito(false));
    
    btnGenerarPedido.addEventListener('click', procesarPedido);

    // Modo Oscuro
    function setDarkMode(isDark) {
        document.body.classList.toggle('dark-mode', isDark);
        localStorage.setItem('darkMode', isDark);
    }
    function toggleDarkMode() { setDarkMode(!document.body.classList.contains('dark-mode')); }
    function expandSearch() { searchWrapper.classList.add('expanded'); buscador.focus(); if(buscador.value) clearSearchBtn.classList.add('visible'); }
    function collapseSearch() { if(!buscador.value) { searchWrapper.classList.remove('expanded'); clearSearchBtn.classList.remove('visible'); } }
    
    function enviarCorreo() {
        const btn = document.getElementById('btnEnviarCorreo');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> <span data-translate>Enviando...</span>';
        reaplicarTraduccion();

        fetch('php/menu2/enviar_recibo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ pedido_id: ultimoPedidoId })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                alert("✅ Correo enviado con éxito.");
            } else {
                alert("⚠ " + res.message);
            }
            location.reload(); 
        })
        .catch(e => {
            console.error(e);
            alert("Error al enviar correo.");
            location.reload();
        });
    }

</script>
</body>
</html>