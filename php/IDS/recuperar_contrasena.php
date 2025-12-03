<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'php/PHPMailer/src/PHPMailer.php';
require 'php/PHPMailer/src/SMTP.php';
require 'php/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    if (empty($email)) {
        $error = "Ingresa tu correo electrónico.";
    } else {
    require 'conexion.php';
        $sql = "SELECT cliente_id FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            // Genera un token único
            $token = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
            // Guarda el token en la base de datos
            $sql_token = "UPDATE usuarios SET token_recuperacion=?, token_expira=? WHERE email=?";
            $stmt_token = $conn->prepare($sql_token);
            $stmt_token->bind_param("sss", $token, $expira, $email);
            $stmt_token->execute();
            // Envía el correo (ajusta los datos SMTP según tu servidor)
            $link = "http://haomeilai.shop/cambiar_contrasena.php?token=$token";
            
            // Configuración de PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Configuración SMTP
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'recuperartucontrasena2@gmail.com'; // Tu correo Gmail
                $mail->Password   = 'jvcr wkff prov tyqh';     // Tu contraseña o App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Remitente y destinatario
                $mail->setFrom('tu_correo@gmail.com', 'Hao Mei Lai');
                $mail->addAddress($email); // $email es el destinatario

                // Contenido
                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                $mail->Subject = 'Recupera tu contraseña - Hao Mei Lai';
                $mail->Body    = "Haz clic en el siguiente enlace para cambiar tu contraseña:<br><a href='$link'>$link</a>";

                $mail->send();
            } catch (Exception $e) {
                // Puedes mostrar un mensaje de error si lo deseas
            }
            
            header("Location: recuperar_contrasena.php?enviado=1");
            exit();
        } else {
            $error = "El correo no está registrado.";
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Hao Mei Lai</title>
    <link rel="stylesheet" href="css/stylelogin.css">
</head>
<body style="background: url('imagenes/fondo comida.jpg') no-repeat center center fixed; background-size: cover;">
    <?php if (isset($_GET['enviado'])): ?>
        <div class="success-floating">¡Revisa tu correo para cambiar tu contraseña!</div>
    <?php elseif ($error): ?>
        <div class="error-floating"><?= $error ?></div>
    <?php endif; ?>
    <div class="login-bg">
        <div class="login-container">
            <div class="login-logo">
                <img src="imagenes/logo comida.png" alt="Hao Mei Lai Logo">
            </div>
            <h2>Recuperar Contraseña</h2>
            <form action="recuperar_contrasena.php" method="post">
                <label for="email">Correo electrónico</label>
                <input type="text" id="email" name="email" required>
                <button type="submit" class="btn-login">Continuar</button>
            </form>
            <div class="divider"></div>
            <button class="btn-register" onclick="window.location.href='login'">Volver al inicio de sesión</button>
        </div>
    </div>
</body>
</html>