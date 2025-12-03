<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('session.cookie_path', '/'); // <-- AÑADE ESTA LÍNEA
session_start();
require '../../conexion.php';

$error_email = '';
$error_password = '';
$error_login = '';

// Lógica para "Entrar como Invitado"
if (isset($_POST['invitado'])) {
    $_SESSION['nombre'] = 'Invitado';
    $_SESSION['rol'] = 'invitado';
    header("Location: /index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $valid = true;

    if (empty($email)) {
        $error_email = "El correo es obligatorio.";
        $valid = false;
    }
    if (empty($password)) {
        $error_password = "La contraseña es obligatoria.";
        $valid = false;
    }

    if ($valid) {
        // 1️⃣ Verificar en tabla usuarios
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ? AND contraseña = ?");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        $stmt->close(); // ✅ Cerrar statement
        
        if ($usuario) {
        // 3️⃣ Iniciar sesión para usuario
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['cliente_id'] = $usuario['cliente_id'];
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['email'] = $usuario['email'];
        session_write_close();
        
        // --- ESTO ES EL CÓDIGO ORIGINAL (ACTIVADO) ---
        if ($usuario['rol'] === 'admin') {
            header("Location: /php/Admin/dashboard.php");
        } else {
            header("Location: /index.php"); 
        }
        exit();
    }

        // 2️⃣ Verificar empleados (SOLO si no se encontró usuario)
        $stmt2 = $conn->prepare("SELECT * FROM empleados WHERE numero_trabajador = ? AND contraseña = ?");
        $stmt2->bind_param("ss", $email, $password);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $empleado = $result2->fetch_assoc();
        $stmt2->close(); // ✅ Cerrar statement
        
        if ($empleado) {
            $_SESSION['nombre'] = $empleado['nombre']; 
            $_SESSION['numero_trabajador'] = $empleado['numero_trabajador'];
            $_SESSION['rol'] = 'Empleado'; // ✅ CORREGIDO: Ahora con mayúscula
            
            header('Location: /php/Empleado/empleado.php');
            exit();
        }
        
        // Si no se encuentra en ninguna tabla
        $error_login = "Credenciales incorrectas o usuario no encontrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Hao Mei Lai</title>
    <link rel="stylesheet" href="../../css/stylelogin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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
        .divider {
            text-align: center;
            margin: 15px 0;
            color: #aaa;
            font-size: 0.9em;
        }
        .error-message {
            color: #d9534f; 
            background-color: #f2dede; 
            border: 1px solid #ebccd1;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.9em;
            text-align: center;
        }
        /* PASO 2: Estilos CORREGIDOS para el "Ojito" de Contraseña */
        .password-container {
            position: relative; 
            /* IMPORTANTE: Asegura que el contenedor tenga el mismo ancho que tu formulario */
            width: 100%; 
            margin-bottom: 20px;
        }
        
        /* Aplicar tus estilos de input AL INPUT DENTRO del contenedor */
        .password-container input {
            /* Si tus inputs tienen un padding, debe estar aquí */
            padding-right: 40px !important; /* Deja espacio para el icono */
            /* Asegura que tome el 100% del contenedor */
                width: 300px; 
            /* Si tienes bordes, tamaños de fuente, etc., ponlos aquí también */
        }
        
        .toggle-password {
            position: absolute;
            /* top: 55%; --> Probamos con un valor fijo o ajustando */
            top: 20px; /* Ajuste: 50% más un pequeño desplazamiento si el input tiene padding vertical */
            right: 15px; 
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            z-index: 10; /* Asegura que el icono esté encima del campo */
        }
    </style>
</head>
<body class="login-page-bg">
    <?php if ($error_email || $error_password || $error_login): ?>
        <div class="error-message">
            <?= $error_email ?: ($error_password ?: $error_login) ?>
        </div>
    <?php endif; ?>
    <div class="login-bg">
        <div class="login-container">
            <div class="login-logo">
                <img src="../../imagenes/logo comida.png" alt="Hao Mei Lai Logo">
            </div>
            <h2>Bienvenido</h2>
            <p class="login-subtitle">Inicia sesión con tu cuenta</p>
            
            <button class="btn-google" onclick="window.location.href='google_auth.php?action=/php/IDS/iniciodesesion.php'">
                <svg style="margin-right: 10px;" width="18" height="18" viewBox="0 0 48 48">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                </svg>
                Iniciar Sesión con Google
            </button>
            <div class="divider">O</div>
            
            <form action="/php/IDS/iniciodesesion.php" method="post">
                <label for="email">Correo electrónico o N° Trabajador</label>
                <input type="text" id="email" name="email" required>

                <label for="password">Contraseña</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required>
                    <i class="fas fa-eye-slash toggle-password" data-target="password"></i>
                </div>

                <button type="submit" class="btn-login">Iniciar Sesión</button>
            </form>
            
            <form action="/php/IDS/iniciodesesion.php" method="post" style="margin-top: 20px;">
                <button type="submit" name="invitado" class="btn-register">Entrar como Invitado</button>
            </form>
            
            <div class="divider"></div>
            <button class="btn-register" onclick="window.location.href='registro.php'">Crear Nueva Cuenta</button>
            <p class="forgot-link">
                <a href="recuperar_contraseña.php">¿Olvidaste tu contraseña?</a>
            </p>
        </div>
        <footer></footer>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Selecciona el icono y el campo de contraseña
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