<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

try {
    $conn = conectarDB();

    $result = $conn->query(
        "SELECT id, nombre, grado, nota, asistencia, estado, mensaje, 
                DATE_FORMAT(fecha, '%d/%m/%Y %H:%i') AS fecha
         FROM evaluaciones 
         ORDER BY fecha DESC 
         LIMIT 20"
    );

    $registros = [];

    while ($row = $result->fetch_assoc()) {
        $registros[] = $row;
    }

    $conn->close();

    echo json_encode($registros);

} catch (Exception $e) {
    echo json_encode(['error' => 'No se pudieron cargar los registros.']);
}
?>