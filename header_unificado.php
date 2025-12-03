<?php
/**
 * header_unificado.php - Header Universal para Hao Mei Lai
 * Ubicación recomendada: /header_unificado.php o /includes/header_unificado.php
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// CALCULAR LA RUTA BASE AUTOMÁTICAMENTE
// ============================================
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . '://' . $host;

// Detectar si estamos en un subdirectorio
$script_name = dirname($_SERVER['SCRIPT_NAME']);
if ($script_name !== '/') {
    $base_url .= $script_name;
}

// Variables configurables (se pueden sobrescribir antes de incluir)
if (!isset($site_name)) $site_name = "Hao Mei Lai";
if (!isset($site_desc)) $site_desc = "Auténtica Comida China";
if (!isset($logo_url)) $logo_url = $base_url . "/imagenes/hao me lai.jpg";

// ============================================
// DETERMINAR LA PÁGINA ACTIVA
// ============================================
if (!isset($current_page)) {
    $request_uri = $_SERVER['REQUEST_URI'];
    $script_filename = basename($_SERVER['SCRIPT_FILENAME']);
    
    // Detectar página activa
    if ($script_filename === 'index.php' || preg_match('/\/$/', $request_uri)) {
        $current_page = 'inicio';
    } elseif (strpos($request_uri, 'menu2.php') !== false || strpos($request_uri, '/menu2/') !== false) {
        $current_page = 'menu';
    } elseif (strpos($request_uri, 'about.php') !== false || strpos($request_uri, '/AboutUs/') !== false) {
        $current_page = 'about';
    } else {
        $current_page = 'none';
    }
}

// ============================================
// GENERAR RUTAS DE NAVEGACIÓN RELATIVAS
// ============================================
// Detectar profundidad desde la raíz
$current_dir = dirname($_SERVER['SCRIPT_FILENAME']);
$document_root = $_SERVER['DOCUMENT_ROOT'];
$relative_path = str_replace($document_root, '', $current_dir);
$depth = substr_count($relative_path, '/') - 1;
$depth = max(0, $depth); // No puede ser negativo

// Crear prefijo relativo (../ según profundidad)
$path_prefix = str_repeat('../', $depth);
if ($depth === 0) {
    $path_prefix = './';
}

// Rutas de navegación
$url_inicio = $path_prefix . 'index.php';
$url_menu = $path_prefix . 'php/menu2/menu2.php';
$url_about = $path_prefix . 'php/AboutUs/about.php';
$url_login = $path_prefix . 'php/IDS/iniciodesesion.php';
$url_logout = $path_prefix . 'php/IDS/cerrarsesion.php';
?>

<style>
/* ================================================= */
/* ESTILOS DEL HEADER */
/* ================================================= */

* {
    box-sizing: border-box;
}

.main-header {
    background: linear-gradient(135deg, #ffffff 0%, #f8f8f8 100%);
    color: #222;
    padding: 0 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 85px;
    border-bottom: 3px solid #b30000;
    box-shadow: 0 4px 12px rgba(179, 0, 0, 0.15);
    position: sticky;
    top: 0;
    z-index: 999;
    transition: all 0.3s ease;
}

/* Sección izquierda: Logo + Marca */
.header-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.header-logo {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    border: 3px solid #b30000;
    background: #fff;
    object-fit: cover;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
}

.header-logo:hover {
    transform: scale(1.05) rotate(5deg);
    box-shadow: 0 6px 20px rgba(179, 0, 0, 0.3);
}

.header-brand {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.header-site-name {
    margin: 0;
    font-size: 1.6em;
    font-family: 'Times New Roman', serif;
    color: #b30000;
    font-weight: bold;
    letter-spacing: 1.5px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

.header-site-desc {
    margin: 0;
    font-size: 1em;
    color: #666;
    font-family: 'Segoe UI', Arial, sans-serif;
    font-style: italic;
}

/* Sección central: Navegación */
.header-center {
    display: flex;
    justify-content: center;
    align-items: center;
}

.header-nav {
    display: flex;
    align-items: center;
}

.header-nav-menu {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    gap: 35px;
}

.header-nav-menu a {
    color: #222;
    text-decoration: none;
    font-weight: 600;
    font-size: 1.1em;
    letter-spacing: 0.5px;
    padding: 10px 16px;
    border-radius: 8px;
    transition: all 0.3s ease;
    position: relative;
}

.header-nav-menu a::after {
    content: '';
    position: absolute;
    bottom: 5px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 3px;
    background: #b30000;
    transition: width 0.3s ease;
}

.header-nav-menu a:hover {
    color: #b30000;
    background: rgba(179, 0, 0, 0.08);
}

.header-nav-menu a:hover::after {
    width: 80%;
}

.header-nav-menu a.active {
    color: #b30000;
    background: rgba(179, 0, 0, 0.12);
    font-weight: 700;
}

.header-nav-menu a.active::after {
    width: 80%;
}

/* Sección derecha: Controles */
.header-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

/* Botón de Modo Oscuro/Claro */
.theme-toggle-btn {
    background: linear-gradient(135deg, #b30000 0%, #8b0000 100%);
    border: none;
    border-radius: 50%;
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 3px 10px rgba(179, 0, 0, 0.3);
}

.theme-toggle-btn:hover {
    transform: scale(1.1) rotate(15deg);
    box-shadow: 0 5px 15px rgba(179, 0, 0, 0.5);
}

.theme-toggle-btn svg {
    width: 24px;
    height: 24px;
    fill: #ffffff;
    transition: transform 0.3s ease;
}

.theme-toggle-btn:hover svg {
    transform: rotate(180deg);
}

/* Botón de Traductor */
.lang-toggle-btn {
    background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
    border: none;
    border-radius: 20px;
    padding: 8px 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 3px 10px rgba(76, 175, 80, 0.3);
    font-weight: 600;
    color: #ffffff;
    font-size: 0.95em;
}

.lang-toggle-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(76, 175, 80, 0.5);
}

.lang-toggle-btn svg {
    width: 18px;
    height: 18px;
    fill: #ffffff;
}

/* Usuario y Dashboard */
.header-user-section {
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
}

.header-user-name {
    color: #b30000;
    font-size: 1.1em;
    font-weight: 700;
    white-space: nowrap;
    font-family: 'Montserrat', Sans-Serif;
}

.header-dashboard-icon {
    cursor: pointer;
    padding: 10px;
    border-radius: 50%;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border: 2px solid transparent;
}

.header-dashboard-icon:hover {
    background-color: rgba(179, 0, 0, 0.1);
    border-color: #b30000;
}

.header-dashboard-menu {
    position: absolute;
    top: 60px;
    right: 0;
    min-width: 220px;
    background: #fff;
    border: 2px solid #b30000;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    display: none;
    flex-direction: column;
    z-index: 100;
    overflow: hidden;
    opacity: 0;
    transform: translateY(-10px);
    transition: all 0.3s ease;
}

.header-dashboard-menu.show {
    display: flex;
    opacity: 1;
    transform: translateY(0);
}

.header-dashboard-menu a,
.header-dashboard-menu button {
    padding: 15px 20px;
    color: #333;
    text-decoration: none;
    font-size: 1.05em;
    font-weight: 500;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 10px;
    background: none;
    border: none;
    border-bottom: 1px solid #f0f0f0;
    text-align: left;
    width: 100%;
    cursor: pointer;
}

.header-dashboard-menu a:hover,
.header-dashboard-menu button:hover {
    background: linear-gradient(135deg, #ffd7d7 0%, #ffebeb 100%);
    color: #b30000;
    padding-left: 25px;
}

.header-dashboard-menu a:last-child,
.header-dashboard-menu button:last-child {
    border-bottom: none;
}

/* ===================================== */
/* MODO OSCURO */
/* ===================================== */

body.dark-mode .main-header {
    background: linear-gradient(135deg, #1f1f1f 0%, #121212 100%);
    border-bottom: 3px solid #ffd700;
    box-shadow: 0 4px 12px rgba(255, 215, 0, 0.2);
}

body.dark-mode .header-logo {
    border-color: #ffd700;
}

body.dark-mode .header-site-name {
    color: #ffd700;
    text-shadow: 1px 1px 3px rgba(255, 215, 0, 0.3);
}

body.dark-mode .header-site-desc {
    color: #e0e0e0;
}

body.dark-mode .header-nav-menu a {
    color: #e0e0e0;
}

body.dark-mode .header-nav-menu a::after {
    background: #ffd700;
}

body.dark-mode .header-nav-menu a:hover {
    color: #ffd700;
    background: rgba(255, 215, 0, 0.1);
}

body.dark-mode .header-nav-menu a.active {
    color: #ffd700;
    background: rgba(255, 215, 0, 0.15);
}

body.dark-mode .theme-toggle-btn {
    background: linear-gradient(135deg, #ffd700 0%, #a08c00 100%);
}

body.dark-mode .theme-toggle-btn svg {
    fill: #121212;
}

body.dark-mode .lang-toggle-btn {
    background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
}

body.dark-mode .header-user-name {
    color: #ffd700;
}

body.dark-mode .header-dashboard-icon svg rect {
    fill: #ffd700;
}

body.dark-mode .header-dashboard-icon:hover {
    background-color: rgba(255, 215, 0, 0.1);
    border-color: #ffd700;
}

body.dark-mode .header-dashboard-menu {
    background: #2c2c2c;
    border-color: #ffd700;
}

body.dark-mode .header-dashboard-menu a,
body.dark-mode .header-dashboard-menu button {
    color: #e0e0e0;
    border-bottom-color: #444;
}

body.dark-mode .header-dashboard-menu a:hover,
body.dark-mode .header-dashboard-menu button:hover {
    background: #ffd700;
    color: #121212;
}

/* Responsive */
@media (max-width: 768px) {
    .main-header {
        padding: 0 20px;
        flex-wrap: wrap;
        min-height: auto;
    }
    
    .header-center {
        order: 3;
        width: 100%;
        margin-top: 10px;
    }
    
    .header-nav-menu {
        gap: 15px;
        justify-content: center;
    }
    
    .header-nav-menu a {
        font-size: 0.95em;
        padding: 8px 12px;
    }
    
    .header-site-name {
        font-size: 1.3em;
    }
    
    .header-site-desc {
        font-size: 0.9em;
    }
}
</style>

<header class="main-header">
    <div class="header-left">
        <img src="<?php echo htmlspecialchars($logo_url); ?>" 
             alt="Logo de <?php echo htmlspecialchars($site_name); ?>" 
             class="header-logo"
             onclick="window.location.href='<?php echo $url_inicio; ?>'">
        <div class="header-brand">
            <h1 class="header-site-name"><?php echo htmlspecialchars($site_name); ?></h1>
            <p class="header-site-desc" data-translate><?php echo htmlspecialchars($site_desc); ?></p>
        </div>
    </div>

    <div class="header-center">
        <nav class="header-nav">
            <ul class="header-nav-menu">
                <li>
                    <a href="<?php echo $url_inicio; ?>" 
                       class="<?php echo ($current_page === 'inicio') ? 'active' : ''; ?>" 
                       data-translate>Inicio</a>
                </li>
                <li>
                    <a href="<?php echo $url_menu; ?>" 
                       class="<?php echo ($current_page === 'menu') ? 'active' : ''; ?>" 
                       data-translate>Menú</a>
                </li>
                <li>
                    <a href="<?php echo $url_about; ?>" 
                       class="<?php echo ($current_page === 'about') ? 'active' : ''; ?>" 
                       data-translate>Sobre Nosotros</a>
                </li>
            </ul>
        </nav>
    </div>

    <div class="header-right">
        <button class="theme-toggle-btn" 
                id="headerThemeToggle" 
                onclick="toggleDarkModeHeader()"
                aria-label="Cambiar tema">
            <svg id="headerThemeIcon" viewBox="0 0 24 24">
                <path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zM2 13h2v-2H2v2zm18 0h2v-2h-2v2zM11 2v2h2V2h-2zm0 18v2h2v-2h-2zM5.9 5.9l1.41 1.41-1.41 1.41-1.41-1.41 1.41-1.41zm12.39 12.39l1.41-1.41-1.41-1.41-1.41 1.41 1.41 1.41zM17.39 5.9l1.41 1.41 1.41-1.41-1.41-1.41-1.41 1.41zM5.9 17.39l1.41 1.41 1.41-1.41-1.41-1.41-1.41 1.41z"/>
            </svg>
        </button>

        <button class="lang-toggle-btn" 
                id="headerLangToggle" 
                onclick="toggleLanguageHeader()"
                aria-label="Cambiar idioma">
            <svg viewBox="0 0 24 24">
                <path d="M12.87 15.07l-2.54-2.51.03-.03c1.74-1.94 2.98-4.17 3.71-6.53H17V4h-7V2H8v2H1v1.99h11.17C11.5 7.92 10.44 9.75 9 11.35 8.07 10.32 7.3 9.19 6.69 8h-2c.73 1.63 1.73 3.17 2.98 4.56l-5.09 5.02L4 19l5-5 3.11 3.11.76-2.04zM18.5 10h-2L12 22h2l1.12-3h4.75L21 22h2l-4.5-12zm-2.62 7l1.62-4.33L19.12 17h-3.24z"/>
            </svg>
            <span id="headerCurrentLang">ES</span>
        </button>

        <div class="header-user-section">
            <?php if (!empty($_SESSION['nombre']) && $_SESSION['rol'] !== 'invitado'): ?>
                <span class="header-user-name"><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
            <?php endif; ?>
            
            <div class="header-dashboard-icon" 
                 tabindex="0" 
                 onclick="toggleHeaderDashboard(event)">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                    <rect x="4" y="7" width="16" height="2" rx="1" fill="#b30000"/>
                    <rect x="4" y="11" width="16" height="2" rx="1" fill="#b30000"/>
                    <rect x="4" y="15" width="16" height="2" rx="1" fill="#b30000"/>
                </svg>
            </div>
            
            <div class="header-dashboard-menu" id="headerDashboardMenu">
                <a href="#pedidos" data-translate>
                    <i class="fas fa-receipt"></i> Mis pedidos
                </a>
                
                <?php if (!isset($_SESSION['nombre']) || (empty($_SESSION['nombre']) && (isset($_SESSION['rol']) && $_SESSION['rol'] === 'invitado'))): ?>
                    <a href="<?php echo $url_login; ?>" data-translate>
                        <i class="fas fa-sign-in-alt"></i> Iniciar sesión
                    </a>
                <?php else: ?>
                    <a href="<?php echo $url_logout; ?>" data-translate>
                        <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<script>
// Variables globales para el header
let currentLang = 'es';
let isDarkMode = false;

// Función para alternar el modo oscuro
function toggleDarkModeHeader() {
    document.body.classList.toggle('dark-mode');
    isDarkMode = document.body.classList.contains('dark-mode');
    
    const icon = document.getElementById('headerThemeIcon');
    if (isDarkMode) {
        icon.innerHTML = `<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>`;
    } else {
        icon.innerHTML = `<path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zM2 13h2v-2H2v2zm18 0h2v-2h-2v2zM11 2v2h2V2h-2zm0 18v2h2v-2h-2zM5.9 5.9l1.41 1.41-1.41 1.41-1.41-1.41 1.41-1.41zm12.39 12.39l1.41-1.41-1.41-1.41-1.41 1.41 1.41 1.41zM17.39 5.9l1.41 1.41 1.41-1.41-1.41-1.41-1.41 1.41zM5.9 17.39l1.41 1.41 1.41-1.41-1.41-1.41-1.41 1.41z"/>`;
    }
    
    localStorage.setItem('darkMode', isDarkMode);
}

// Función para alternar el idioma - CORREGIDA
function toggleLanguageHeader() {
    // Cambiar el idioma
    currentLang = (currentLang === 'es') ? 'en' : 'es';
    
    // Actualizar el texto del botón
    document.getElementById('headerCurrentLang').textContent = currentLang.toUpperCase();
    
    // Guardar en localStorage
    localStorage.setItem('selectedLanguage', currentLang);
    
    // Llamar a la función de traducción si existe
    if (typeof solicitarTraduccion === 'function') {
        solicitarTraduccion(currentLang);
    }
    
    console.log('Idioma cambiado a:', currentLang);
}

// Función para mostrar/ocultar el menú del dashboard
function toggleHeaderDashboard(event) {
    event.stopPropagation();
    const menu = document.getElementById('headerDashboardMenu');
    menu.classList.toggle('show');
    
    if (menu.classList.contains('show')) {
        document.addEventListener('click', closeHeaderDashboardOutside);
    } else {
        document.removeEventListener('click', closeHeaderDashboardOutside);
    }
}

// Función para cerrar el menú al hacer clic fuera
function closeHeaderDashboardOutside(event) {
    const menu = document.getElementById('headerDashboardMenu');
    const icon = document.querySelector('.header-dashboard-icon');
    
    if (menu.classList.contains('show') && 
        !menu.contains(event.target) && 
        !icon.contains(event.target)) {
        menu.classList.remove('show');
        document.removeEventListener('click', closeHeaderDashboardOutside);
    }
}

// Cargar preferencias al iniciar - CORREGIDO
document.addEventListener('DOMContentLoaded', () => {
    // Cargar modo oscuro
    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
        isDarkMode = true;
        const icon = document.getElementById('headerThemeIcon');
        icon.innerHTML = `<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>`;
    }
    
    // Cargar idioma guardado
    const savedLang = localStorage.getItem('selectedLanguage');
    if (savedLang) {
        currentLang = savedLang;
        document.getElementById('headerCurrentLang').textContent = currentLang.toUpperCase();
        
        // Aplicar traducción al cargar la página
        if (typeof solicitarTraduccion === 'function') {
            solicitarTraduccion(currentLang);
        }
        
        console.log('Idioma cargado desde localStorage:', currentLang);
    }
});
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">