<?php
session_start();

// --- INICIO DE LA SOLUCIÓN ANTI-CACHÉ ---
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0"); // Para navegadores antiguos
// --- FIN DE LA SOLUCIÓN ---

// PHP para el header del restaurante
$site_name = "Hao Mei Lai"; // Nombre del restaurante
$site_desc = "Auténtica Comida China"; // Descripción del restaurante
// RUTA DE IMAGEN CORREGIDA: Usando la ruta relativa estándar que tienes
$logo_url = "/imagenes/logo comida.png"; 
// Definir la página actual para marcar como "active" en el menú
$current_page = "about"; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-translate>Sobre Nosotros | Hao Mei Lai</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
  <style> 
  
/* ================================================= */ 
/* === ESTILOS BASE DE LA PÁGINA 'SOBRE NOSOTROS' === */ 
/* ================================================= */ 

/* Estilos del Hero Section - Versión mejorada */
.hero {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 60px 30px;
    margin: 20px auto 40px auto;
    max-width: 900px;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
}

/* Efecto hover - se levanta y aumenta la sombra */
.hero:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 25px rgba(179, 0, 0, 0.2);
}

/* Línea decorativa inferior sutil */

/* Título del hero */
.hero h1 {
    color: #b30000;
    font-size: 2.8em;
    margin-bottom: 20px;
    font-weight: bold;
    position: relative;
    padding-bottom: 15px;
}


/* Párrafo del hero */
.hero p {
    color: #444;
    font-size: 1.3em;
    line-height: 1.6;
    max-width: 700px;
    margin: 0 auto;
    font-weight: 300;
}

/* Contenedor principal */
.about-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center; /* Centrar todos los hijos */
}

/* Títulos de sección con línea centrada */
.section-title {
    color: #b30000;
    font-size: 2.2em;
    margin-bottom: 30px;
    padding-bottom: 10px;
    text-align: center;
    position: relative;
    width: 90%; /* Mismo ancho que los contenedores */
    max-width: 900px; /* Mismo máximo que hero */
}

/* Línea debajo del título - ajustada al texto */
.section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background-color: #b30000;
    border-radius: 1.5px;
    width: 100%; /* Ocupa todo el ancho del contenedor del título */
}

/* Para títulos H3 normales (no dentro de content-card) */
.about-container > h3 {
    color: #b30000;
    font-size: 1.8em;
    margin: 30px auto 20px auto;
    text-align: center;
    max-width: 900px; /* Mismo que hero */
    width: 90%; /* Mismo ancho relativo */
    padding-bottom: 8px;
    position: relative;
}

/* Línea debajo de H3 */
.about-container > h3::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background-color: #ffd700;
    border-radius: 1px;
    width: 100%;
}

/* Para títulos H4 (como "El Chef Principal") */
.about-container > h4 {
    color: #333;
    font-size: 1.5em;
    margin: 25px auto 15px auto;
    text-align: center;
    max-width: 900px;
    width: 90%;
    font-weight: 600;
    padding-bottom: 6px;
    position: relative;
}

/* Línea sutil debajo de H4 */
.about-container > h4::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 25%;
    right: 25%;
    height: 1px;
    background-color: #b30000;
    border-radius: 0.5px;
    opacity: 0.7;
}

.content-card:hover {
    transform: translateY(-8px); /* Movimiento hacia arriba */
    box-shadow: 0 12px 30px rgba(179, 0, 0, 0.15); /* Sombra más pronunciada */
}

/* Centrar cuadros (content-card) */
.content-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 30px;
    margin: 0 auto 30px auto;
    text-align: left;
    transition: transform 0.3s ease, box-shadow 0.3s ease; 
    max-width: 900px; /* Mismo que hero */
    width: 90%; /* Mismo ancho relativo */
}

/* Para títulos h3 dentro de content-card */
.content-card h3 {
    color: #b30000; /* ROJO */
    font-size: 1.6em;
    margin-top: 0;
    margin-bottom: 15px;
    border-left: 5px solid #ffd700;
    padding-left: 15px;
    text-align: left;
}

/* Específicamente para h3 con data-translate */
.content-card h3[data-translate] {
    color: #b30000; /* ROJO */
}

/* Centrar el hero */
.hero {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 60px 30px;
    margin: 20px auto 40px auto;
    max-width: 900px;
    width: 90%;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
}

/* =========================================== */
/* MODO OSCURO */
/* =========================================== */


body.dark-mode .content-card {
    background: #2c2c2c;
    box-shadow: 0 4px 15px rgba(255, 215, 0, 0.1);
}

body.dark-mode .content-card:hover {
    transform: translateY(-8px); /* Mismo movimiento */
    box-shadow: 0 12px 30px rgba(255, 215, 0, 0.25); /* Sombra dorada */
}

body.dark-mode .content-card h3 {
    color: #ffd700; /* Dorado en modo oscuro */
}

body.dark-mode .section-title {
    color: #ffd700;
}

body.dark-mode .section-title::after {
    background-color: #ffd700;
}

body.dark-mode .about-container > h3 {
    color: #ffd700;
}

body.dark-mode .about-container > h3::after {
    background-color: #b30000;
}

body.dark-mode .about-container > h4 {
    color: #e0e0e0;
}

body.dark-mode .about-container > h4::after {
    background-color: #ffd700;
    opacity: 0.8;
}

/* =========================================== */
/* RESPONSIVE */
/* =========================================== */
@media (max-width: 768px) {
    .content-card {
        width: 95%;
        padding: 20px;
    }
    
    /* Efecto hover más sutil en móviles */
    .content-card:hover {
        transform: translateY(-5px);
    }
}
@media (max-width: 768px) {
    .about-container {
        padding: 15px;
    }
    
    .section-title {
        font-size: 1.8em;
        width: 95%;
    }
    
    .section-title::after {
        width: 100%;
    }
    
    .content-card {
        width: 95%;
        padding: 20px;
    }
    
    .hero {
        width: 95%;
        padding: 40px 20px;
    }
    
    .about-container > h3 {
        width: 95%;
        font-size: 1.6em;
    }
    
    .about-container > h3::after {
        width: 100%;
    }
    
    .about-container > h4 {
        width: 95%;
        font-size: 1.3em;
    }
    
    .about-container > h4::after {
        left: 20%;
        right: 20%;
    }
}
/* Secciones de contenido */ 

/* Estilos del Footer */ 
.footer { 
    background-color: #333; 
    color: #fff; 
    padding: 30px 20px; 
    margin-top: auto; 
    /* Empuja el footer hacia abajo */ 
    border-top: 5px solid #b30000; 
} 

.footer-main { 
    display: flex; 
    justify-content: space-around; 
    max-width: 1200px; 
    margin: 0 auto 20px auto; 
    flex-wrap: wrap; 
} 

.footer-column { 
    flex: 1; 
    min-width: 200px; 
    margin: 15px; 
} 

.footer-column h3 { 
    border-bottom: 2px solid #b30000; 
    padding-bottom: 10px; 
    margin-bottom: 15px; 
    font-size: 1.2em; 
} 

.footer-column ul { 
    list-style: none; 
    padding: 0; 
} 

.footer-column ul li a { 
    color: #ccc; 
    text-decoration: none; 
    line-height: 2; 
    transition: color 0.2s; 
} 

.footer-column ul li a:hover { 
    color: #ffd700; 
} 

.footer-social .social-icons { 
    display: flex; 
    gap: 15px; 
    margin-top: 10px; 
} 

.footer-logo img { 
    max-width: 120px; 
    height: auto; 
    margin-top: 20px; 
} 

.footer-bottom { 
    text-align: center; 
    border-top: 1px solid #444; 
    padding-top: 15px; 
    font-size: 0.9em; 
} 

.footer-bottom span { 
    color: #999; 
} 

.footer-bottom a { 
    color: #fff; 
    text-decoration: none; 
    transition: color 0.2s; 
} 

.footer-bottom a:hover { 
    color: #ffd700; 
} 

/* ================================================= */ 
/* === MODO OSCURO PARA ABOUTUS === */ 
/* ================================================= */ 

/* MODO OSCURO - Hero Section */
body.dark-mode .hero {
    background: #2c2c2c; /* Fondo oscuro */
    border-color: #ffd700; /* Borde dorado */
    color: #ffd700; /* Texto dorado */
}

body.dark-mode { 
    background-color: #121212; 
    color: #e0e0e0; 
} 

body.dark-mode .section-title { 
    color: #ffd700; 
    border-bottom-color: #ffd700; 
} 

body.dark-mode .content-card { 
    background: #2c2c2c; 
    box-shadow: 0 4px 15px rgba(255, 215, 0, 0.1); 
} 

body.dark-mode .content-card:hover { 
    box-shadow: 0 6px 20px rgba(255, 215, 0, 0, 0.2); 
} 

body.dark-mode .content-card h3 { 
    color: #ffd700; 
    border-left-color: #b30000; 
} 

body.dark-mode .content-card p { 
    color: #ccc; 
} 

body.dark-mode .footer { 
    background-color: #1f1f1f; 
    border-top-color: #ffd700; 
} 

body.dark-mode .footer-column h3 { 
    border-bottom-color: #ffd700; 
} 

body.dark-mode .footer-bottom a { 
    color: #ffd700; 
} 

/* Media Queries para Responsividad */ 
@media (max-width: 768px) { 
    .hero { 
        padding: 50px 10px; 
    } 

    .hero h1 { 
        font-size: 2.5em; 
    } 

    .hero p { 
        font-size: 1em; 
    } 

    .section-title { 
        font-size: 1.8em; 
    } 

    .footer-main { 
        flex-direction: column; 
        align-items: center; 
    } 
} 
</style>
</head>
<body>
    
    <?php require_once 'header_unificado.php'; ?>
    
    <main>
        <div class="about-container">
            <section class="hero">
                <h1 data-translate>Nuestra Historia</h1>
                <p data-translate>Desde el corazón de China hasta su mesa, trayendo la auténtica tradición culinaria.</p>
            </section>

            <section class="filosofia">
                <h2 class="section-title" data-translate>Filosofía y Misión</h2>
                
                <div class="content-card">
                    <h3 data-translate>Nuestros Valores</h3>
                    <p data-translate>En Hao Mei Lai, creemos que la comida es un puente entre culturas. Nuestra misión es simple: ofrecer la más alta calidad de ingredientes, técnicas de cocina auténticas y un servicio excepcional. Cada platillo se prepara con dedicación, honrando las recetas ancestrales chinas.</p>
                </div>
                
                <div class="content-card">
                    <h3 data-translate>Ingredientes Frescos</h3>
                    <p data-translate>Nos comprometemos a utilizar solo ingredientes frescos, de origen local siempre que sea posible, complementados con especias y condimentos importados directamente de China para garantizar el sabor original. La frescura y la calidad son la base de nuestro menú.</p>
                </div>
            </section>

            <section class="equipo">
                <h2 class="section-title" data-translate>Conoce a Nuestro Equipo</h2>
                
                <div class="content-card">
                    <h3 data-translate>El Chef Principal: Wei Chen</h3>
                    <p data-translate>El Chef Wei Chen trae consigo más de 20 años de experiencia, habiendo trabajado en renombrados restaurantes en Beijing y Shanghai. Su pasión es fusionar la precisión moderna con el alma de la cocina tradicional, creando platos que son tanto visualmente impresionantes como deliciosos.</p>
                </div>
                
                <div class="content-card">
                    <h3 data-translate>Nuestro Compromiso con la Comunidad</h3>
                    <p data-translate>Hao Mei Lai se enorgullece de ser parte de esta comunidad. Apoyamos eventos locales y trabajamos para crear un ambiente de trabajo inclusivo y positivo para todos nuestros empleados. ¡Somos más que un restaurante, somos una familia!</p>
                </div>
            </section>
        </div>
    </main>
    
    <footer class="footer">
        <div class="footer-main">
            <div class="footer-column">
                <h3 data-translate>Más información sobre Hao Mei Lai</h3>
                <ul>
                    <li><a href="#" data-translate>Contáctanos</a></li>
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
    
    <script src="/php/Traductor/traductor.js"></script>
    
    <script>
        // EL SCRIPT ORIGINAL DEL ABOUT US HA SIDO REEMPLAZADO 
        // POR EL SCRIPT UNIFICADO DENTRO DE header_unificado.php.
        // Las funciones como toggleDarkModeUniversal, toggleLanguageUniversal
        // y el manejo del dashboard están ahora centralizados.

        // Asegurarse de que el modo oscuro se aplique al cargar
        document.addEventListener('DOMContentLoaded', () => {
             if (localStorage.getItem('darkMode') === 'true') {
                 document.body.classList.add('dark-mode');
             }
        });
    </script>
</body>
</html>