<?php
require 'conexion.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Actualizar color_alerta (basado en tu código original)
try {
    $sql = "UPDATE mensajes SET color_alerta = CASE 
        WHEN LOWER(nivel_alerta) = 'rojo' THEN '#FF0000'
        WHEN LOWER(nivel_alerta) = 'anaranjado' THEN '#FF8000'
        WHEN LOWER(nivel_alerta) = 'verde' THEN '#00FF00'
        ELSE '#808080'
    END
    WHERE color_alerta IS NULL OR color_alerta = ''";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $filas_afectadas = $stmt->rowCount();
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Psicólogo Virtual</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        .container { max-width: 600px; margin: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #f4f4f4; }
        /* Estilo para la celda de icono */
        .icon-cell {
            width: 50px; /* Tamaño para el icono */
            height: 30px;
            min-width: 50px;
            min-height: 30px;
            text-align: center; /* Centrar el icono */
            font-size: 20px; /* Tamaño del icono */
        }
        /* Clases para colorear filas según nivel_alerta */
        .fila-verde {
            background-color: #d4edda; /* Verde claro */
            color: #155724;
        }
        .fila-anaranjado {
            background-color: #fff3cd; /* Naranja claro */
            color: #856404;
        }
        .fila-rojo {
            background-color: #f8d7da; /* Rojo claro */
            color: #721c24;
        }
        .fila-default {
            background-color: #f4f4f4; /* Gris claro */
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
        <?php if (isset($filas_afectadas)) : ?>
            <p>Se actualizaron <strong><?php echo $filas_afectadas; ?></strong> registros en color_alerta.</p>
        <?php endif; ?>
        <?php if (isset($error)) : ?>
            <p>Error: <?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <p><a href="logout.php">Cerrar Sesión</a></p>
        <h3>Mensajes</h3>
        <table>
            <tr>
                <th>Mensaje</th>
                <th>Nivel Alerta</th>
                <th>Icono</th>
                <th>Fecha</th>
            </tr>
            <?php
            $sql = "SELECT mensaje, nivel_alerta, color_alerta, created_at FROM mensajes WHERE usuario_id = :usuario_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['usuario_id' => $_SESSION['user_id']]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Determinar clase de la fila según nivel_alerta
                $clase_fila = '';
                switch (strtolower($row['nivel_alerta'])) {
                    case 'verde':
                        $clase_fila = 'fila-verde';
                        break;
                    case 'anaranjado':
                        $clase_fila = 'fila-anaranjado';
                        break;
                    case 'rojo':
                        $clase_fila = 'fila-rojo';
                        break;
                    default:
                        $clase_fila = 'fila-default';
                }
                echo "<tr class='$clase_fila'>";
                echo "<td>" . htmlspecialchars($row['mensaje']) . "</td>";
                echo "<td>" . htmlspecialchars($row['nivel_alerta']) . "</td>";
                echo "<td class='icon-cell'>" . (strtolower($row['nivel_alerta']) == 'verde' ? '✅' : (strtolower($row['nivel_alerta']) == 'anaranjado' ? '⚠️' : (strtolower($row['nivel_alerta']) == 'rojo' ? '🚨' : '⬜'))) . "</td>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>