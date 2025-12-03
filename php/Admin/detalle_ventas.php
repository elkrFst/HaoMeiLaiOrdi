<?php
session_start();
require_once '../../conexion.php';

// Filtro de rango seleccionado
$rango = isset($_GET['rango']) ? $_GET['rango'] : 'dia';

// Construcción de la consulta según rango
if ($rango == 'semana') {
    $query = "SELECT DATE(fecha_pago) AS fecha, SUM(total) AS total 
              FROM pedidos 
              WHERE estado='Pagado' 
              AND YEARWEEK(fecha_pago, 1)=YEARWEEK(CURDATE(), 1)
              GROUP BY DATE(fecha_pago)";
} elseif ($rango == 'mes') {
    $query = "SELECT DATE(fecha_pago) AS fecha, SUM(total) AS total 
              FROM pedidos 
              WHERE estado='Pagado' 
              AND MONTH(fecha_pago)=MONTH(CURDATE()) 
              AND YEAR(fecha_pago)=YEAR(CURDATE())
              GROUP BY DATE(fecha_pago)";
} else { // Día actual
    $query = "SELECT HOUR(fecha_pago) AS fecha, SUM(total) AS total 
              FROM pedidos 
              WHERE estado='Pagado' 
              AND DATE(fecha_pago)=CURDATE()
              GROUP BY HOUR(fecha_pago)";
}

$resultado = mysqli_query($conn, $query);
$labels = [];
$datos = [];

while ($fila = mysqli_fetch_assoc($resultado)) {
    $labels[] = $fila['fecha'];
    $datos[] = $fila['total'];
}

// Totales generales (en dinero)
$total_dia = mysqli_fetch_row(mysqli_query($conn, 
    "SELECT IFNULL(SUM(total),0) FROM pedidos WHERE estado='Pagado' AND DATE(fecha_pago)=CURDATE()"))[0];

$total_semana = mysqli_fetch_row(mysqli_query($conn, 
    "SELECT IFNULL(SUM(total),0) FROM pedidos WHERE estado='Pagado' AND YEARWEEK(fecha_pago, 1)=YEARWEEK(CURDATE(), 1)"))[0];

$total_mes = mysqli_fetch_row(mysqli_query($conn, 
    "SELECT IFNULL(SUM(total),0) FROM pedidos WHERE estado='Pagado' AND MONTH(fecha_pago)=MONTH(CURDATE()) AND YEAR(fecha_pago)=YEAR(CURDATE())"))[0];

$total_general = mysqli_fetch_row(mysqli_query($conn, 
    "SELECT IFNULL(SUM(total),0) FROM pedidos WHERE estado='Pagado'"))[0];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Detalle de Ventas Pagadas</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #fff3e0, #ffe0b2);
    margin: 0;
    padding: 30px;
}
.container {
    background: #fff;
    max-width: 950px;
    margin: auto;
    padding: 25px;
    border-radius: 18px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.08);
}
h1 {
    text-align: center;
    color: #a33d3d;
    margin-bottom: 25px;
}
.stats {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    margin-bottom: 25px;
}
.card {
    background: #a33d3d;
    color: white;
    flex: 1;
    margin: 8px;
    border-radius: 12px;
    padding: 18px;
    text-align: center;
    min-width: 120px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.card h3 {
    font-size: 17px;
    margin-bottom: 8px;
    font-weight: normal;
}
.card p {
    font-size: 22px;
    font-weight: bold;
}
select {
    padding: 8px 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    margin-bottom: 15px;
    font-size: 15px;
}
canvas {
    margin-top: 20px;
}
.volver {
    display: inline-block;
    background: #a33d3d;
    color: white;
    padding: 10px 18px;
    border-radius: 10px;
    text-decoration: none;
    transition: 0.3s;
    font-weight: 500;
}
.volver:hover {
    background: #872c2c;
}
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}
</style>
</head>
<body>

<div class="container">
    <div class="header">
        <a href="/panel" class="volver">⬅ Volver al panel</a>
        <h1>💹 Ventas Pagadas</h1>
        <div style="width:90px;"></div> <!-- Para alinear el título -->
    </div>

    <div class="stats">
        <div class="card"><h3>Hoy</h3><p>$<?= number_format($total_dia, 2) ?></p></div>
        <div class="card"><h3>Semana</h3><p>$<?= number_format($total_semana, 2) ?></p></div>
        <div class="card"><h3>Mes</h3><p>$<?= number_format($total_mes, 2) ?></p></div>
        <div class="card"><h3>Total</h3><p>$<?= number_format($total_general, 2) ?></p></div>
    </div>

    <form method="GET" style="text-align:center;">
        <label><b>Mostrar por:</b></label>
        <select name="rango" onchange="this.form.submit()">
            <option value="dia" <?= $rango=='dia'?'selected':'' ?>>Día</option>
            <option value="semana" <?= $rango=='semana'?'selected':'' ?>>Semana</option>
            <option value="mes" <?= $rango=='mes'?'selected':'' ?>>Mes</option>
        </select>
    </form>

    <canvas id="grafica"></canvas>
</div>

<script>
const ctx = document.getElementById('grafica').getContext('2d');
const gradient = ctx.createLinearGradient(0, 0, 0, 400);
gradient.addColorStop(0, 'rgba(163,61,61,0.6)');
gradient.addColorStop(1, 'rgba(255,255,255,0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Ventas Pagadas ($)',
            data: <?= json_encode($datos) ?>,
            fill: true,
            backgroundColor: gradient,
            borderColor: '#a33d3d',
            tension: 0.4,
            borderWidth: 3,
            pointBackgroundColor: '#f6c453',
            pointRadius: 5,
            pointHoverRadius: 8
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Monto ($)' }
            },
            x: {
                title: { display: true, text: '<?= ucfirst($rango) ?>' }
            }
        },
        plugins: {
            legend: {
                display: true,
                labels: { color: '#a33d3d', font: { size: 14 } }
            }
        }
    }
});
</script>
</body>
</html>
