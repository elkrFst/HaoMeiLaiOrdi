<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// google_auth.php

session_start();

// ------------------------------------------------------------------------------------------------
// ⚠️ TUS VARIABLES DE CONFIGURACIÓN DE GOOGLE
// ------------------------------------------------------------------------------------------------
const CLIENT_ID = '117774502467-9dooa96c54u43t1pm95utpp1ug9vgufe.apps.googleusercontent.com'; // <<-- ¡REEMPLAZAR!
const CLIENT_SECRET = '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'; // <<-- ¡REEMPLAZAR!
const REDIRECT_URI = 'https://haomeilai.shop/google_callback.php'; // <<-- ¡REEMPLAZAR con tu dominio!
// ------------------------------------------------------------------------------------------------


if (isset($_GET['action'])) {
    $_SESSION['google_action'] = $_GET['action']; 
    
    try {
        // 1. INCLUIR LA LIBRERÍA (Ya instalada en la carpeta vendor/)
        require_once 'vendor/autoload.php'; 

        // 2. Crear y configurar el cliente
        $client = new Google_Client();
        $client->setClientId(CLIENT_ID);
        $client->setClientSecret(CLIENT_SECRET);
        $client->setRedirectUri(REDIRECT_URI);
        // Pedir el email y el perfil
        $client->addScope('email');
        $client->addScope('profile');
        
        // 3. Redirigir al usuario a Google para el consentimiento
        $auth_url = $client->createAuthUrl();
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
        exit();

    } catch (Exception $e) {
        // En caso de error de configuración o de la librería
        error_log("Google Auth Error: " . $e->getMessage());
        header('Location: /php/IDS/iniciodesesion.php?error=google_config_error');
        exit();
    }
} else {
    header('Location: /php/IDS/iniciodesesion.php');
    exit();
}
?>