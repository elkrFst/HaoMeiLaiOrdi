<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../../iniciodesesion.php');
    exit();
}
require_once '../../conexion.php';
$anio = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM clientes WHERE atendido=1 AND YEAR(fecha)=YEAR(CURDATE())"))[0];
$mes = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM clientes WHERE atendido=1 AND MONTH(fecha)=MONTH(CURDATE())"))[0];
$semana = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM clientes WHERE atendido=1 AND WEEK(fecha)=WEEK(CURDATE())"))[0];
$dia = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM clientes WHERE atendido=1 AND DATE(fecha)=CURDATE()"))[0];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Detalle Clientes Atendidos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>Detalle de Clientes Atendidos</h2>
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
