<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../../iniciodesesion.php');
    exit();
}
require_once '../../conexion.php';
$anio = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM pedidos WHERE YEAR(fecha)=YEAR(CURDATE())"))[0];
$mes = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM pedidos WHERE MONTH(fecha)=MONTH(CURDATE())"))[0];
$semana = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM pedidos WHERE WEEK(fecha)=WEEK(CURDATE())"))[0];
$dia = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM pedidos WHERE DATE(fecha)=CURDATE()"))[0];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Detalle Pedidos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
body {
    background: linear-gradient(135deg, #fff7e6 0%, #ffe0b2 100%);
    font-family: 'Segoe UI', 'Arial', sans-serif;
}
h2 {
    color: #a33d3d;
    font-weight: bold;
    margin-bottom: 2rem;
    letter-spacing: 1px;
    text-align: center;
}
.card {
    border: none;
    border-radius: 18px;
    box-shadow: 0 4px 16px rgba(163,61,61,0.08);
    background: #fff;
    margin-bottom: 18px;
    transition: transform 0.15s;
}
.card:hover {
    transform: translateY(-4px) scale(1.03);
    box-shadow: 0 8px 24px rgba(163,61,61,0.13);
}
.card h5 {
    color: #a33d3d;
    font-weight: 600;
    font-size: 1.1em;
    margin-bottom: 0.5rem;
}
.card .fs-2 {
    color: #f6c453;
    font-weight: bold;
    font-size: 2.2em;
}
.btn-primary {
    background: #a33d3d;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    transition: box-shadow 0.2s;
}
.btn-primary:hover {
    background: #f6c453;
    color: #a33d3d;
    box-shadow: 0 2px 8px rgba(163,61,61,0.13);
}
</style>
</head>
<body>
<div class="container mt-4">
    <h2>Detalle de Pedidos</h2>
    <div class="row">
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>Año</h5><p class="fs-2"><?php echo $anio; ?></p></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>Mes</h5><p class="fs-2"><?php echo $mes; ?></p></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>Semana</h5><p class="fs-2"><?php echo $semana; ?></p></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>Día</h5><p class="fs-2"><?php echo $dia; ?></p></div></div></div>
    </div>
    <a href="dashboard.php" class="btn btn-primary mt-4">Regresar al Dashboard</a>
</div>
</body>
</html>
