<?php
header('Content-Type: application/json; charset=utf-8');
//require_once 'config.php';
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  
define('DB_PASS', '');           
define('DB_NAME', 'evaluacion_escolar');

function conectarDB() {
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        $connTemp = new mysqli(DB_HOST, DB_USER, DB_PASS);
        
        if ($connTemp->connect_error) {
            die(json_encode(['error' => 'Error de conexión a MySQL: ' . $connTemp->connect_error]));
        }

        $connTemp->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` 
                          CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $connTemp->close();

        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            die(json_encode(['error' => 'No se pudo conectar a la base de datos.']));
        }
    }

    $sql = "CREATE TABLE IF NOT EXISTS evaluaciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(120) NOT NULL,
        grado VARCHAR(20) NOT NULL,
        nota DECIMAL(5,2) NOT NULL,
        asistencia DECIMAL(5,2) DEFAULT NULL,
        estado VARCHAR(30) NOT NULL,
        mensaje TEXT NOT NULL,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $conn->query($sql);

    return $conn;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$nombre     = trim($_POST['nombre'] ?? '');
$grado      = trim($_POST['grado'] ?? '');
$nota       = isset($_POST['nota']) ? floatval($_POST['nota']) : -1;
$asistencia = (isset($_POST['asistencia']) && $_POST['asistencia'] !== '') 
                ? floatval($_POST['asistencia']) 
                : null;

if (empty($nombre) || empty($grado) || $nota < 0 || $nota > 100) {
    echo json_encode(['error' => 'Datos incompletos o inválidos']);
    exit;
}

function evaluarNota($nota) {
    if ($nota > 80) {
        return [
            'estado'  => 'excelente',
            'mensaje' => 'Aprobado con Excelente. El estudiante superó el año escolar con un desempeño sobresaliente.'
        ];
    } elseif ($nota > 70) {
        return [
            'estado'  => 'bueno',
            'mensaje' => 'Aprobado como Bueno. El estudiante cumplió satisfactoriamente los requisitos del año escolar.'
        ];
    } elseif ($nota > 50) {
        return [
            'estado'  => 'segunda',
            'mensaje' => 'Segunda Categoría. El estudiante debe presentar exámenes adicionales para poder ascender de grado.'
        ];
    } elseif ($nota >= 50) {
        return [
            'estado'  => 'recuperacion',
            'mensaje' => 'Examen de Recuperación. El estudiante tiene una última oportunidad mediante evaluación recuperatoria.'
        ];
    } else {
        return [
            'estado'  => 'reprobado',
            'mensaje' => 'Reprobado completamente. El estudiante debe repetir el año escolar.'
        ];
    }
}

$evaluacion = evaluarNota($nota);

try {
    $conn = conectarDB();

    $stmt = $conn->prepare(
        "INSERT INTO evaluaciones (nombre, grado, nota, asistencia, estado, mensaje) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "ssddss",
        $nombre,
        $grado,
        $nota,
        $asistencia,
        $evaluacion['estado'],
        $evaluacion['mensaje']
    );

    $stmt->execute();
    $id = $stmt->insert_id;

    $stmt->close();
    $conn->close();

    echo json_encode([
        'success'    => true,
        'id'         => $id,
        'nombre'     => $nombre,
        'grado'      => $grado,
        'nota'       => $nota,
        'asistencia' => $asistencia,
        'estado'     => $evaluacion['estado'],
        'mensaje'    => $evaluacion['mensaje']
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Error al guardar: ' . $e->getMessage()]);
}
?>