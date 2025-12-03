<?php
// obtener_papelera.php
require_once '../../conexion.php';

header('Content-Type: application/json');

$sql = "SELECT id, producto FROM almacen WHERE activo = 0";
$result = $conn->query($sql);

$papelera = [];
while($row = $result->fetch_assoc()) {
    $papelera[] = $row;
}

echo json_encode($papelera);
?>