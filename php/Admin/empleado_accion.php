<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../../conexion.php';

// Iniciar sesión para poder usar $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Deshabilitar el caché del navegador para esta página
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Verificar si la sesión no está activa o si el rol no es 'admin'
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    // Si no está logueado o no es admin, redirigir al login
    header("Location: /login");
    exit();
}

require_once '../../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        // ==============================
        // AGREGAR EMPLEADO
        // ==============================
        case 'agregar':
            $nombre = $_POST['nombre'] ?? '';
            $numero = $_POST['numero_trabajador'] ?? '';
            $password = $_POST['contraseña'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $fecha_registro = date("Y-m-d H:i:s");

            // Validar campos requeridos
            if (empty($nombre) || empty($numero) || empty($password)) {
                $_SESSION['error'] = "Todos los campos obligatorios deben ser completados.";
                header("Location: dashboard.php#trabajadores");
                exit();
            }

            // <-- VALIDACIÓN DE NÚMERO DE TRABAJADOR -->
            if (!ctype_digit($numero) || strlen($numero) !== 5) {
                $_SESSION['error'] = "El número de trabajador debe ser un número de 5 dígitos.";
                header("Location: dashboard.php#trabajadores");
                exit();
            }

            // Manejo de foto
            $foto = "default.jpg";
            if (!empty($_FILES['foto']['name'])) {
                $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
                $extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                
                if (!in_array($extension, $extensiones_permitidas)) {
                    $_SESSION['error'] = "Solo se permiten imágenes (jpg, jpeg, png, gif).";
                    header("Location: dashboard.php#trabajadores");
                    exit();
                }

                $foto = time() . "_" . basename($_FILES['foto']['name']);
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], "../../trabajadores/" . $foto)) {
                    $_SESSION['error'] = "Error al subir la foto.";
                    header("Location: dashboard.php#trabajadores");
                    exit();
                }
            }
            
            $sql = "INSERT INTO empleados (nombre, descripcion, numero_trabajador, contraseña, fecha_registro, foto) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("ssssss", $nombre, $descripcion, $numero, $password, $fecha_registro, $foto);
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Empleado agregado correctamente.";
                } else {
                    $_SESSION['error'] = "Error al agregar empleado: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $_SESSION['error'] = "Error en la preparación de la consulta: " . $conn->error;
            }
            break;

        // ==============================
        // EDITAR EMPLEADO
        // ==============================
        case 'editar':
            $id = $_POST['id'] ?? 0;
            $nombre = $_POST['nombre'] ?? '';
            $numero = $_POST['numero_trabajador'] ?? '';
            $password = $_POST['contraseña'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';

            // Verificar que el ID sea válido
            if ($id <= 0) {
                $_SESSION['error'] = "ID de empleado no válido.";
                header("Location: dashboard.php#trabajadores");
                exit();
            }

            // Validar campos requeridos
            if (empty($nombre) || empty($numero)) {
                $_SESSION['error'] = "El nombre y número de trabajador son obligatorios.";
                header("Location: dashboard.php#trabajadores");
                exit();
            }

            // <-- VALIDACIÓN DE NÚMERO DE TRABAJADOR -->
            if (!ctype_digit($numero) || strlen($numero) !== 5) {
                $_SESSION['error'] = "El número de trabajador debe ser un número de 5 dígitos.";
                header("Location: dashboard.php#trabajadores");
                exit();
            }

            // Construir la consulta de actualización
            $campos = ["nombre = ?", "numero_trabajador = ?", "descripcion = ?"];
            $params = [$nombre, $numero, $descripcion];
            $types = "sss";
            
            // Contraseña solo si se proporciona y no está vacía
            if (!empty($password)) {
                $campos[] = "contraseña = ?";
                $params[] = $password;
                $types .= "s";
            }

            // Foto solo si se sube una nueva
            if (!empty($_FILES['foto']['name'])) {
                $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
                $extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                
                if (!in_array($extension, $extensiones_permitidas)) {
                    $_SESSION['error'] = "Solo se permiten imágenes (jpg, jpeg, png, gif).";
                    header("Location: dashboard.php#trabajadores");
                    exit();
                }

                $foto = time() . "_" . basename($_FILES['foto']['name']);
                if (move_uploaded_file($_FILES['foto']['tmp_name'], "../../trabajadores/" . $foto)) {
                    $campos[] = "foto = ?";
                    $params[] = $foto;
                    $types .= "s";
                } else {
                    $_SESSION['error'] = "Error al subir la nueva foto.";
                    header("Location: dashboard.php#trabajadores");
                    exit();
                }
            }
            
            $sql = "UPDATE empleados SET " . implode(", ", $campos) . " WHERE id = ?";
            $params[] = $id;
            $types .= "i";
            
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param($types, ...$params);
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Empleado actualizado correctamente.";
                } else {
                    $_SESSION['error'] = "Error al actualizar empleado: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $_SESSION['error'] = "Error en la preparación de la consulta: " . $conn->error;
            }
            break;

        // ==============================
        // ELIMINAR EMPLEADO
        // ==============================
        case 'eliminar':
            $id = $_POST['id'] ?? 0;

            if ($id <= 0) {
                $_SESSION['error'] = "ID de empleado no válido.";
                header("Location: dashboard.php#trabajadores");
                exit();
            }

            // Buscar foto actual para eliminarla
            $fotoQuery = $conn->prepare("SELECT foto FROM empleados WHERE id = ?");
            if ($fotoQuery) {
                $fotoQuery->bind_param("i", $id);
                $fotoQuery->execute();
                $result = $fotoQuery->get_result();
                $empleado = $result->fetch_assoc();

                // Eliminar foto si no es la default
                if ($empleado && $empleado['foto'] !== "default.jpg" && file_exists("../../trabajadores/" . $empleado['foto'])) {
                    unlink("../../trabajadores/" . $empleado['foto']);
                }
                $fotoQuery->close();
            }

            // Eliminar empleado
            $sql = "DELETE FROM empleados WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Empleado eliminado correctamente.";
                } else {
                    $_SESSION['error'] = "Error al eliminar empleado: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $_SESSION['error'] = "Error en la preparación de la consulta: " . $conn->error;
            }
            break;

        default:
            $_SESSION['error'] = "Acción no válida.";
            break;
    }

    // Regresar al dashboard mostrando la sección trabajadores
    header("Location: dashboard.php#trabajadores");
    exit();
} else {
    $_SESSION['error'] = "Método no permitido.";
    header("Location: panel");
    exit();
}

$conn->close();
?>