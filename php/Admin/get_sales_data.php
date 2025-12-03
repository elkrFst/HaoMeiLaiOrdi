<?php
session_start();
header('Content-Type: application/json');

// 1. Seguridad: Verificar rol de Admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}

// 2. Incluir conexión
require_once '../../conexion.php';

// 3. Obtener el periodo de la URL
$periodo = $_GET['periodo'] ?? 'semana'; // Default 'semana'

$labels = [];
$data = [];
$total_ventas = 0;
$total_clientes = 0;
$dias_periodo = 0; 

try {
    // Establecer la zona horaria correcta para las funciones de MySQL
    // Nota: Es crucial que esta zona horaria coincida con la configuración de tu servidor web y MySQL.
    $conn->query("SET time_zone = '-06:00'"); 

    // Base para las condiciones WHERE (Estado Pagado)
    $where_base = "estado = 'Pagado' AND fecha_pago IS NOT NULL AND ";
    $where_periodo = "";

    switch ($periodo) {
        
        // ===================================
        // Caso: Esta Semana (por día)
        // ===================================
        case 'semana':
            $labels = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
            $data = [0, 0, 0, 0, 0, 0, 0];
            
            $where_periodo = "WEEK(fecha_pago, 1) = WEEK(CURDATE(), 1) AND YEAR(fecha_pago) = YEAR(CURDATE())";
            
            $sql = "SELECT 
                        DAYOFWEEK(fecha_pago) as dia_semana, 
                        SUM(total) as total_dia 
                    FROM pedidos 
                    WHERE {$where_base} {$where_periodo}
                    GROUP BY dia_semana";
            
            $result = $conn->query($sql);
            
            // Mapeo de DAYOFWEEK() (1=Domingo, 2=Lunes, ...) a nuestro array (0=Lunes, ...)
            $map = [1 => 6, 2 => 0, 3 => 1, 4 => 2, 5 => 3, 6 => 4, 7 => 5];
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($map[$row['dia_semana']])) {
                        $dia_index = $map[$row['dia_semana']];
                        $data[$dia_index] = (float) $row['total_dia'];
                    }
                }
            }
            
            // Total de días: Días transcurridos hasta hoy en la semana
            $sql_dias = "SELECT DAYOFWEEK(CURDATE()) as hoy_dia";
            $dias_result = $conn->query($sql_dias);
            $dia_hoy = 0;
            if ($dias_result) {
                $dia_hoy = (int) $dias_result->fetch_assoc()['hoy_dia'];
            }
            // Mapeo inverso: 1 (Domingo) cuenta como 7, 2 (Lunes) como 1...
            $dias_periodo = ($dia_hoy == 1) ? 7 : $dia_hoy - 1; 

            // Si es Domingo (1), ha pasado 7 días. Si es Lunes (2), ha pasado 1 día.
            // Esto evita dividir por 0 si hoy es Lunes y solo ha pasado un día.
            if ($dias_periodo == 0) $dias_periodo = 1;

            break;

        // ===================================
        // Caso: Este Mes (por semana)
        // ===================================
        case 'mes':
            $labels = ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4', 'Semana 5'];
            $data = [0, 0, 0, 0, 0];
            
            $where_periodo = "MONTH(fecha_pago) = MONTH(CURDATE()) AND YEAR(fecha_pago) = YEAR(CURDATE())";
            
            $sql = "SELECT 
                        (WEEK(fecha_pago, 1) - WEEK(DATE_SUB(fecha_pago, INTERVAL DAYOFMONTH(fecha_pago)-1 DAY), 1) + 1) as semana_del_mes,
                        SUM(total) as total_semana
                    FROM pedidos 
                    WHERE {$where_base} {$where_periodo}
                    GROUP BY semana_del_mes";

            $result = $conn->query($sql);

            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $semana_index = (int)$row['semana_del_mes'] - 1;
                    if ($semana_index >= 0 && $semana_index < 5) {
                        $data[$semana_index] = (float) $row['total_semana'];
                    }
                }
            }
            
            // Total de días: Día del mes actual (días transcurridos)
            $sql_dias = "SELECT DAY(CURDATE()) as total_dias";
            $dias_result = $conn->query($sql_dias);
            if ($dias_result) {
                $dias_periodo = (int) $dias_result->fetch_assoc()['total_dias'];
            }
            break;

        // ===================================
        // Caso: Este Año (por mes)
        // ===================================
        case 'ano':
            $labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            $data = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            
            $where_periodo = "YEAR(fecha_pago) = YEAR(CURDATE())";
            
            $sql = "SELECT 
                        MONTH(fecha_pago) as mes, 
                        SUM(total) as total_mes 
                    FROM pedidos 
                    WHERE {$where_base} {$where_periodo}
                    GROUP BY mes";
            
            $result = $conn->query($sql);
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $mes_index = (int)$row['mes'] - 1; 
                    if ($mes_index >= 0 && $mes_index < 12) {
                        $data[$mes_index] = (float) $row['total_mes'];
                    }
                }
            }
            
            // Total de días: Días transcurridos del año
            $sql_dias = "SELECT DAYOFYEAR(CURDATE()) as total_dias";
            $dias_result = $conn->query($sql_dias);
            if ($dias_result) {
                $dias_periodo = (int) $dias_result->fetch_assoc()['total_dias'];
            }
            break;
        
        default:
            echo json_encode(['error' => 'Periodo no válido']);
            exit;
    }

    // 4. Ejecutar las consultas de TOTAL
    
    // A. Obtener Total Ventas
    $sql_total = "SELECT IFNULL(SUM(total), 0) as total 
                  FROM pedidos 
                  WHERE {$where_base} {$where_periodo}";
                  
    $total_result = $conn->query($sql_total);
    if ($total_result) {
        $total_ventas = (float) $total_result->fetch_assoc()['total'];
    }

    // B. Obtener Total Clientes Atendidos (Contar IDs de cliente únicos)
    // Usamos el nombre de columna correcto: cliente_id
    $sql_clientes = "SELECT COUNT(DISTINCT cliente_id) as total_c 
                     FROM pedidos 
                     WHERE {$where_base} {$where_periodo}";
                     
    $clientes_result = $conn->query($sql_clientes);
    if ($clientes_result) {
        $total_clientes = (int) $clientes_result->fetch_assoc()['total_c'];
    }

    // C. Calcular Promedio Diario
    $promedio_diario = ($total_ventas > 0 && $dias_periodo > 0) ? ($total_ventas / $dias_periodo) : 0;
    
    // 5. Devolver JSON (con los nuevos campos)
    echo json_encode([
        'labels' => $labels,
        'data' => $data,
        'total_ventas' => $total_ventas,
        'total_clientes' => $total_clientes,      
        'promedio_diario' => round($promedio_diario, 2) 
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}

$conn->close();
?>