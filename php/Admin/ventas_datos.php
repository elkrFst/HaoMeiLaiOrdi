<?php
require_once '../../conexion.php';
$rango = $_GET['rango'] ?? 'dia';
$labels = [];
$data = [];

// Todas las consultas filtran estado='Pagado'
if ($rango === 'dia') {
    for ($i = 6; $i >= 0; $i--) {
        $fecha = date('Y-m-d', strtotime("-$i days"));
        $count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM ventas WHERE estado='Pagado' AND DATE(fecha)='$fecha'"))[0];
        $labels[] = date('d M', strtotime($fecha));
        $data[] = $count;
    }
} elseif ($rango === 'semana') {
    for ($i = 3; $i >= 0; $i--) {
        $sem = date('W', strtotime("-$i week"));
        $count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM ventas WHERE estado='Pagado' AND WEEK(fecha)=$sem"))[0];
        $labels[] = "Semana $sem";
        $data[] = $count;
    }
} elseif ($rango === 'mes') {
    for ($i = 5; $i >= 0; $i--) {
        $mes = date('m', strtotime("-$i month"));
        $count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM ventas WHERE estado='Pagado' AND MONTH(fecha)=$mes"))[0];
        $labels[] = date('M', strtotime("-$i month"));
        $data[] = $count;
    }
}

echo json_encode(['labels' => $labels, 'data' => $data]);
