<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Se guarda en texto plano, como lo tenías
    $aceptar_terminos = isset($_POST['aceptar_terminos']) ? 1 : 0;
    $acepto_marketing = isset($_POST['acepto_marketing']) ? 1 : 0; // 1 si está marcado, 0 si no
    $rol = 'usuario';
    
    // =========================================================
    // FIX: Tu columna 'fecha_registro' es DATE, no DATETIME.
    // Usamos 'Y-m-d' para que coincida con tu tabla.
    // =========================================================
    $fecha_registro = date('Y-m-d'); 
    
    // Codificar las variables para la URL en caso de error
    $nombre_url = urlencode($nombre);
    $email_url = urlencode($email);
    
    // 🚩 VALIDACIÓN 0: Verifica que se aceptaron los términos
    if ($aceptar_terminos == 0) {
        header("Location: registro.php?error=terminos_required&nombre={$nombre_url}&email={$email_url}");
        exit(); 
    }
    
    // 🚩 VALIDACIÓN 1: Verifica si el correo contiene "@gmail.com"
    if (strpos($email, '@gmail.com') === false) {
        header("Location: registro.php?error=gmail_required&nombre={$nombre_url}&email={$email_url}");
        exit(); 
    }
    
    // 🚩 VALIDACIÓN 2: Verifica si el correo ya existe
    $sql_check = "SELECT cliente_id FROM usuarios WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows > 0) {
        $stmt_check->close();
        header("Location: registro.php?error=email_exists&nombre={$nombre_url}&email={$email_url}");
        exit(); 
    } else {
        $stmt_check->close(); 
        
        // =========================================================
        // FIX: Añadimos 'acepto_marketing' que ahora sí existe en la BD
        // Mantenemos 'contraseña' (con ñ) y todos tus nombres de columna.
        // =========================================================
        $sql = "INSERT INTO usuarios (
                    nombre, email, contraseña, rol, fecha_registro, 
                    terminos_aceptados, fecha_aceptacion_terminos, acepto_marketing
                ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            // Si esto falla, el SQL tiene un error.
            die("Error 500: Fallo al preparar la consulta: " . htmlspecialchars($conn->error));
        }

        // =========================================================
        // FIX: El bind_param ahora debe tener 7 variables (sssssii)
        // s = string, i = integer
        // =========================================================
        $stmt->bind_param(
            "sssssii", 
            $nombre, 
            $email, 
            $password, // Se envía el texto plano
            $rol, 
            $fecha_registro,
            $aceptar_terminos, // Se envía el 1
            $acepto_marketing  // Se envía el 1 o 0
        );
        
        if ($stmt->execute()) {
            // Éxito en el registro
            header("Location: iniciodesesion.php?registro=exito");
            exit();
        } else {
            // Error genérico de base de datos
            // die("Error al ejecutar: " . htmlspecialchars($stmt->error)); // Descomenta para depurar
            header("Location: registro.php?error=db_error&nombre={$nombre_url}&email={$email_url}");
            exit(); 
        }
        $stmt->close();
    }
    
    $conn->close();
} else {
    // Si se accede directamente, redirige al formulario
    header("Location: registro.php");
    exit();
}
?>