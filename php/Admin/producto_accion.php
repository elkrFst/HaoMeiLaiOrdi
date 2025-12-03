<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../../iniciodesesion.php');
    exit();
}
require_once '../../conexion.php';

$accion = $_POST['accion'] ?? '';

if ($accion == 'agregar' || $accion == 'editar') {
    $producto = $_POST['producto'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $categoria = $_POST['categoria'];
    $imagen = 'default.jpg';

    // Si hay imagen subida
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $nombreImg = uniqid() . '_' . basename($_FILES['imagen']['name']);
        $rutaDestino = '../menu2/imagenes_productos/' . $nombreImg;
        move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino);
        $imagen = $nombreImg;
    }

    if ($accion == 'agregar') {
        $sql = "INSERT INTO almacen (producto, precio, stock, imagen, categoria, activo) VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdiss", $producto, $precio, $stock, $imagen, $categoria);
        $stmt->execute();
    } else { // editar
        $id = $_POST['id'];
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $sql = "UPDATE almacen SET producto=?, precio=?, stock=?, imagen=?, categoria=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdissi", $producto, $precio, $stock, $imagen, $categoria, $id);
        } else {
            $sql = "UPDATE almacen SET producto=?, precio=?, stock=?, categoria=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdssi", $producto, $precio, $stock, $categoria, $id);
        }
        $stmt->execute();
    }
    header("Location: ../../php/Admin/dashboard.php?section=almacen");
    exit();

} elseif ($accion == 'papelera') {
    // NUEVA ACCIÓN: Mover a papelera (0) o Restaurar (1)
    $id = $_POST['id'];
    $estado = $_POST['estado']; // 0 = Desactivar, 1 = Activar

    $sql = "UPDATE almacen SET activo = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $estado, $id);
    
    if($stmt->execute()) {
        echo "ok";
    } else {
        echo "error";
    }
    exit(); // Importante salir aquí para respuestas AJAX

} elseif ($accion == 'eliminar_permanente') {
    // Acción para borrar DE VERDAD (desde el modal de papelera)
    $id = $_POST['id'];
    
    // 1. Obtener nombre de imagen para borrarla del servidor
    $query = $conn->prepare("SELECT imagen FROM almacen WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $res = $query->get_result();
    if ($row = $res->fetch_assoc()) {
        $rutaImagen = '../menu2/imagenes_productos/' . $row['imagen'];
        if ($row['imagen'] !== 'default.jpg' && file_exists($rutaImagen)) {
            unlink($rutaImagen); // Borrar archivo
        }
    }

    // 2. Borrar de la BD
    $sql = "DELETE FROM almacen WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        echo "ok";
    } else {
        echo "error";
    }
    exit();
}
?>