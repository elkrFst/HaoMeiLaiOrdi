<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require '../../conexion.php';
$error = '';
$token = $_GET['token'] ?? '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $password = $_POST['password'];
    // Verifica el token y su expiración
    $sql = "SELECT cliente_id, token_expira FROM usuarios WHERE token_recuperacion=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (strtotime($row['token_expira']) > time()) {
            // Actualiza la contraseña y elimina el token
            $sql_upd = "UPDATE usuarios SET contraseña=?, token_recuperacion=NULL, token_expira=NULL WHERE cliente_id=?";
            $stmt_upd = $conn->prepare($sql_upd);
            $stmt_upd->bind_param("si", $password, $row['cliente_id']);
            $stmt_upd->execute();
            header("Location: /php/IDS/iniciodesesion.php?/php/IDS/registro=exito");
            exit();
        } else {
            $error = "El enlace ha expirado. Solicita uno nuevo.";
        }
    } else {
        $error = "Token inválido.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar Contraseña - Hao Mei Lai</title>
    <link rel="stylesheet" href="../../css/stylelogin.css">
</head>
<body style="background: url('../../imagenes/fondo comida.jpg') no-repeat center center fixed; background-size: cover;">
    <?php if ($error): ?>
        <div class="error-floating"><?= $error ?></div>
    <?php endif; ?>
    <div class="login-bg">
        <div class="login-container">
            <div class="login-logo">
                <img src="../../imagenes/logo comida.png" alt="Hao Mei Lai Logo">
            </div>
            <h2>Cambiar Contraseña</h2>
            <form action="cambiar_contrasena.php" method="post">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <label for="password">Nueva contraseña</label>
                <input type="password" id="password" name="password" required>
                <button type="submit" class="btn-login">Guardar y acceder</button>
            </form>
            <div class="divider"></div>
            <button class="btn-register" onclick="window.location.href='/php/IDS/iniciodesesion.php'">Volver al inicio de sesión</button>
        </div>
    </div>
</body>
</html>