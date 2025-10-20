<?php
require 'conexion.php';

try {
    $sql = "
        CREATE TABLE IF NOT EXISTS emociones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            estado VARCHAR(50) NOT NULL,
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
        )
    ";
    $pdo->exec($sql);
    echo "Tabla 'emociones' creada exitosamente en la base de datos psicologo_virtual.";
} catch (PDOException $e) {
    echo "Error al crear la tabla: " . htmlspecialchars($e->getMessage());
}
?>