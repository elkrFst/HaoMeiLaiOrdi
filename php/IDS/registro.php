<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Lógica para manejar y definir el mensaje de error
$error_message = '';
$has_error = false; 

// Variables para conservar los datos ingresados
$old_nombre = '';
$old_email = '';

// Si hay un error, procesamos el mensaje y recuperamos los datos antiguos
if (isset($_GET['error'])) {
    $error_code = $_GET['error'];
    $has_error = true; 

    $old_nombre = isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : '';
    $old_email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';

    if ($error_code === 'gmail_required') {
        $error_message = '⚠️ Debes incluir @gmail.com para poder continuar';
    } elseif ($error_code === 'email_exists') {
        $error_message = '❌ El correo ya está registrado. Intenta iniciar sesión.';
    } elseif ($error_code === 'db_error') {
        $error_message = '🚨 Ocurrió un error al intentar registrarte. Intenta de nuevo.';
    } elseif ($error_code === 'terminos_required') {
        $error_message = '⚠️ Debes aceptar los Términos y Condiciones para continuar.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Hao Mei Lai</title>
    <link rel="stylesheet" href="css/stylelogin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Estilos para hacer el mensaje de error más notorio */
        .error-message {
            color: #d9534f;
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            padding: 10px;
            margin-top: -5px; 
            margin-bottom: 15px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.95em;
        }
        /* NUEVOS ESTILOS para el botón de Google */
        .btn-google {
            background-color: #4285F4;
            color: white;
            padding: 12px;
            font-size: 1em;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px 0 rgba(0,0,0,.25);
            transition: background-color 0.3s;
        }
        .btn-google:hover {
            background-color: #3374dc;
        }
        .btn-google img {
            width: 18px;
            height: 18px;
            margin-right: 10px;
        }
        .divider {
            text-align: center;
            margin: 15px 0;
            color: #aaa;
            font-size: 0.9em;
        }
        /* Estilos para el checkbox de términos */
        .terminos-container {
            margin: 15px 0;
            padding: 12px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .terminos-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .terminos-checkbox input[type="checkbox"] {
            margin-top: 4px;
            width: 18px;
            height: 18px;
            cursor: pointer;
            flex-shrink: 0;
        }
        .terminos-checkbox label {
            font-size: 0.9em;
            color: #555;
            line-height: 1.5;
            cursor: pointer;
        }
        .terminos-link {
            color: #d32f2f;
            text-decoration: underline;
            font-weight: bold;
            cursor: pointer;
        }
        .terminos-link:hover {
            color: #b71c1c;
        }
        .checkbox-error {
            border-color: #d9534f;
            background-color: #ffe6e6;
        }
        /* PASO 2: Estilos para el "Ojito" de Contraseña en Registro */
        .password-container {
            position: relative;
            width: 100%; 
            margin-bottom: 20px;
        }
        
        .password-container input {
            /* IMPORTANTE: Asegura que toma el ancho completo del contenedor */
            width: 300px; 
            /* Deja espacio para el icono */
            padding-right: 40px !important; 
        }
        
        .toggle-password {
            position: absolute;
            /* Usamos 45% para subirlo, como en el login */
            top: 40%; 
            right: 15px; 
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            z-index: 10;
        }
        /* PASO 3: Media Query para optimización en móviles */
        @media (max-width: 600px) {
            
            /* Reduce el padding interior del formulario en pantallas muy pequeñas */
            .login-container {
                padding: 15px;
                width: 95%; /* Ocupa más ancho en móvil */
            }
            
            /* Ajusta el icono para que no choque con el borde derecho */
            .toggle-password {
                right: 10px; /* Mueve el icono un poco más a la izquierda */
                top: 40%; /* Si se movió mucho, puedes reajustar el top aquí */
            }
            
            /* Asegura que el input tenga suficiente espacio */
            .password-container input {
                padding-right: 30px !important;
                width: 285px;
            }
        }
    </style>
</head>
<body class="login-page-bg">
    <div class="login-bg">
        <div class="login-container">
            <div class="login-logo">
                <img src="imagenes/logo comida.png" alt="Hao Mei Lai Logo">
            </div>
            <h2>Crear Nueva Cuenta</h2>

            <button class="btn-google" onclick="window.location.href='google_auth.php?action=register'">
                <svg style="margin-right: 10px;" width="18" height="18" viewBox="0 0 48 48">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                </svg> 
                Registrarse con Google
            </button>
            <div class="divider">O con correo y contraseña</div>

            <form action="procesar_registro.php" method="post" id="registroForm">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" value="<?php echo $old_nombre; ?>" required>
                
                <label for="email">Correo electrónico</label>
                <input type="text" id="email" name="email" placeholder="tu@email.com" value="<?php echo $old_email; ?>" required>
                
                <?php 
                // Muestra el mensaje de error si existe
                if (!empty($error_message)) {
                    echo '<p class="error-message">' . $error_message . '</p>';
                }
                ?>

                <label for="password">Contraseña</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" placeholder="********" required>
                    <i class="fas fa-eye-slash toggle-password" data-target="password"></i>
                </div>
                
                <!-- Checkbox de Términos y Condiciones -->
                <div class="terminos-container" id="terminosContainer">
                    <div class="terminos-checkbox">
                        <input type="checkbox" id="aceptar_terminos" name="aceptar_terminos" required>
                        <label for="aceptar_terminos">
                            He leído y acepto los 
                            <span class="terminos-link" onclick="abrirTerminos()">Términos y Condiciones</span> 
                            y el 
                            <span class="terminos-link" onclick="abrirPrivacidad()">Aviso de Privacidad</span> 
                            de Hao Mei Lai
                        </label>
                    </div>
                </div>
                
                <!-- Checkbox opcional para Marketing -->
                <div class="terminos-container" style="background-color: #f0f8ff;">
                    <div class="terminos-checkbox">
                        <input type="checkbox" id="acepto_marketing" name="acepto_marketing">
                        <label for="acepto_marketing">
                            <strong>(Opcional)</strong> Deseo recibir promociones, ofertas especiales y novedades por correo electrónico
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">Registrarse</button>
            </form>
            <div class="divider"></div>
            <button class="btn-register" onclick="window.location.href='login'">Volver al inicio de sesión</button>
        </div>
    </div>

    <script>
        // Función para abrir términos y condiciones en nueva ventana
        function abrirTerminos() {
            window.open('terminos_condiciones.php', 'Términos y Condiciones', 'width=900,height=700,scrollbars=yes,resizable=yes');
        }

        // Validación del formulario
        document.getElementById('registroForm').addEventListener('submit', function(e) {
            const checkbox = document.getElementById('aceptar_terminos');
            const container = document.getElementById('terminosContainer');
            
            if (!checkbox.checked) {
                e.preventDefault();
                container.classList.add('checkbox-error');
                alert('⚠️ Debes aceptar los Términos y Condiciones para continuar.');
                checkbox.focus();
                
                // Remover el error después de 3 segundos
                setTimeout(function() {
                    container.classList.remove('checkbox-error');
                }, 3000);
            }
        });

        // Remover clase de error cuando se marca el checkbox
        document.getElementById('aceptar_terminos').addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('terminosContainer').classList.remove('checkbox-error');
            }
        });

        <?php if ($has_error): ?>
        // Elimina los parámetros de error de la URL
        if (window.history.replaceState) {
            const url = new URL(window.location.href);
            if (url.searchParams.has('error')) {
                url.searchParams.delete('error');
                url.searchParams.delete('nombre'); 
                url.searchParams.delete('email'); 
                window.history.replaceState({path: url.href}, '', url.href);
            }
        }
        <?php endif; ?>
    </script>
    <script>
        // PASO 3: Script para la funcionalidad del "Ojito" de Contraseña
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('.toggle-password');
            const passwordInput = document.getElementById('password');
    
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function (e) {
                    // Alterna entre 'password' y 'text'
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    
                    // Aplica el cambio
                    passwordInput.setAttribute('type', type);
                    
                    // Cambia el icono (ojo abierto <-> ojo cerrado)
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            }
        });
    </script>
</body>
</html>