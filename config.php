<?php
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
?>