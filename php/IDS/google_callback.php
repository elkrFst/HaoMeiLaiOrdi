<?php
ini_set('session.cookie_path', '/'); 
session_start();
require 'conexion.php'; // Asegúrate de que esta ruta sea correcta

// ------------------------------------------------------------------------------------------------
// TUS VARIABLES DE CONFIGURACIÓN DE GOOGLE
// ------------------------------------------------------------------------------------------------
const CLIENT_ID = '117774502467-9dooa96c54u43t1pm95utpp1ug9vgufe.apps.googleusercontent.com';
const CLIENT_SECRET = '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';
const REDIRECT_URI = 'https://haomeilai.shop/google_callback.php';
// ------------------------------------------------------------------------------------------------


if (isset($_GET['code'])) {
    $action = $_SESSION['google_action'] ?? 'login';
    unset($_SESSION['google_action']); 
    
    try {
        // 1. INCLUIR LA LIBRERÍA
        require_once 'vendor/autoload.php';
        
        // 2. Configurar el cliente
        $client = new Google_Client();
        $client->setClientId(CLIENT_ID);
        $client->setClientSecret(CLIENT_SECRET);
        $client->setRedirectUri(REDIRECT_URI);
        $client->addScope('email');
        $client->addScope('profile');
        
        // 3. Obtener el token de acceso
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        if (isset($token['error'])) {
            throw new Exception("Error al obtener token: " . $token['error']);
        }
        $client->setAccessToken($token['access_token']);
        
        // 4. Obtener la información del usuario
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        $email = $google_account_info->email;
        $nombre = $google_account_info->name;

        // 5. Conectar a la B.D. (YA INCLUIDA EN conexion.php)
        if (!isset($conn)) {
             throw new Exception("Error: El archivo conexion.php no definió la variable \$conn.");
        }

        // 6. Buscar al usuario en la B.D.
        $stmt_check = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        $usuario = $result->fetch_assoc();
        $stmt_check->close();

        // 7. Decidir si hacer LOGIN o REGISTRO
        
        if ($usuario) {
            // ✅ LOGIN: Usuario encontrado
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['cliente_id'] = $usuario['cliente_id'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['email'] = $usuario['email']; // ¡La línea que probamos que funciona!
            
            // --- ¡¡LA SOLUCIÓN!! ---
            session_write_close(); // Guardamos la sesión ANTES de redirigir
            // ------------------------
            
            header("Location: inicio");
            exit();
        
        } elseif ($action === 'register') {
            // ✅ REGISTRO: Usuario no encontrado y solicitó registrarse
            $sql = "INSERT INTO usuarios (nombre, email, contraseña, rol, fecha_registro) VALUES (?, ?, '', 'usuario', NOW())";
            $stmt_insert = $conn->prepare($sql);
            $stmt_insert->bind_param("ss", $nombre, $email);
            
            if ($stmt_insert->execute()) {
                 // Iniciar sesión después del registro
                 $_SESSION['nombre'] = $nombre;
                 $_SESSION['cliente_id'] = $conn->insert_id;
                 $_SESSION['rol'] = 'usuario';
                 $_SESSION['email'] = $email; // ¡La línea que probamos que funciona!
                 
                // --- ¡¡LA SOLUCIÓN!! ---
                session_write_close(); // Guardamos la sesión ANTES de redirigir
                // ------------------------
                 
                 header("Location: login");
                 exit();
            } else {
                 header("Location: registro.php?error=db_error_google");
                 exit();
            }
            $stmt_insert->close();
            
        } else {
            // ❌ Error: Usuario no encontrado y la acción fue LOGIN
            header("Location: iniciodesesion.php?error=no_google_user");
            exit();
        }

    } catch (Exception $e) {
        // Error de la API o la librería
        error_log("Google Callback Error: " . $e->getMessage());
        header('Location: iniciodesesion.php?error=google_api_fail');
        exit();
    }

} else {
    // Error o cancelación por parte del usuario en la pantalla de Google
    header('Location: iniciodesesion.php?error=google_cancel');
    exit();
}
?>