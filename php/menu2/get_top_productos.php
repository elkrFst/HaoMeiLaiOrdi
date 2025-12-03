<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// get_top_productos.php
// Archivo para obtener los 3 productos más comprados de la base de datos

function obtenerTop3Productos() {
    // OPCIÓN 1: Si usas una variable global de conexión
    // Descomenta y ajusta según tu archivo de conexión
    /*
    global $conexion;
    */
    
    // OPCIÓN 2: Si necesitas incluir el archivo de conexión aquí
    // Ajusta la ruta según tu estructura
    if (file_exists('php/conexion.php')) {
        require_once 'php/conexion.php';
    } elseif (file_exists('../php/conexion.php')) {
        require_once '../php/conexion.php';
    } elseif (file_exists('conexion.php')) {
        require_once 'conexion.php';
    } else {
        error_log("No se encontró el archivo de conexión");
        return obtenerProductosPorDefecto();
    }
    
    // Verificar si existe la conexión
    if (!isset($conexion)) {
        error_log("Variable de conexión no definida");
        return obtenerProductosPorDefecto();
    }
    
    try {
        // Consulta SQL para obtener los 3 productos más vendidos
        // AJUSTA LOS NOMBRES DE LAS TABLAS SEGÚN TU BASE DE DATOS
        $sql = "SELECT 
                    p.id,
                    p.nombre,
                    p.descripcion,
                    p.imagen,
                    p.precio,
                    COUNT(pd.producto_id) as total_ventas
                FROM productos p
                INNER JOIN pedidos_detalle pd ON p.id = pd.producto_id
                GROUP BY p.id, p.nombre, p.descripcion, p.imagen, p.precio
                ORDER BY total_ventas DESC
                LIMIT 3";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Si no hay suficientes productos con ventas, completar con productos aleatorios
        if (count($resultados) < 3) {
            $ids_existentes = array_column($resultados, 'id');
            $placeholders = !empty($ids_existentes) ? implode(',', array_fill(0, count($ids_existentes), '?')) : '0';
            
            $sql_fallback = "SELECT 
                                id,
                                nombre,
                                descripcion,
                                imagen,
                                precio,
                                0 as total_ventas
                            FROM productos
                            WHERE id NOT IN ($placeholders)
                            ORDER BY RAND()
                            LIMIT " . (3 - count($resultados));
            
            $stmt_fallback = $conexion->prepare($sql_fallback);
            if (!empty($ids_existentes)) {
                $stmt_fallback->execute($ids_existentes);
            } else {
                $stmt_fallback->execute();
            }
            $productos_adicionales = $stmt_fallback->fetchAll(PDO::FETCH_ASSOC);
            
            $resultados = array_merge($resultados, $productos_adicionales);
        }
        
        return !empty($resultados) ? $resultados : obtenerProductosPorDefecto();
        
    } catch (PDOException $e) {
        error_log("Error PDO al obtener top productos: " . $e->getMessage());
        return obtenerProductosPorDefecto();
    } catch (Exception $e) {
        error_log("Error general al obtener top productos: " . $e->getMessage());
        return obtenerProductosPorDefecto();
    }
}

// Función para devolver productos por defecto si hay error
function obtenerProductosPorDefecto() {
    return [
        [
            'id' => 1,
            'nombre' => 'Chow Mein',
            'descripcion' => 'Fideos salteados con verduras y salsa especial. ¡El más pedido!',
            'imagen' => 'php/menu2/imagenes_productos/chow_mein.jpg',
            'precio' => 85.00,
            'total_ventas' => 0
        ],
        [
            'id' => 2,
            'nombre' => 'Rollos Primavera',
            'descripcion' => 'Crujientes rollos rellenos de vegetales frescos. ¡Clásico favorito!',
            'imagen' => 'php/menu2/imagenes_productos/rollos_primavera.jpg',
            'precio' => 65.00,
            'total_ventas' => 0
        ],
        [
            'id' => 3,
            'nombre' => 'Pollo Gongbao',
            'descripcion' => 'Pollo salteado con cacahuate y salsa picante. ¡Top ventas!',
            'imagen' => 'php/menu2/imagenes_productos/pollo_gongbao.jpg',
            'precio' => 95.00,
            'total_ventas' => 0
        ]
    ];
}

// Si se llama directamente, devolver JSON
if (basename($_SERVER['PHP_SELF']) == 'get_top_productos.php') {
    header('Content-Type: application/json');
    echo json_encode(obtenerTop3Productos());
}
?>