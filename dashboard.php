<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'conexion.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Obtener información del usuario
$user_id = $_SESSION['user_id'];
try {
    $sql = "SELECT username, grade FROM usuarios WHERE id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        $mensaje_error = "Usuario no encontrado.";
        unset($_SESSION['user_id']);
        header("Location: login.php");
        exit;
    }
    $username = $user['username'];
    $grade = $user['grade'];
} catch (PDOException $e) {
    $mensaje_error = "Error al obtener datos del usuario: " . $e->getMessage();
}

// Determinar mensaje de bienvenida según la hora
$hora = (int)date('H');
$saludo = match (true) {
    $hora >= 5 && $hora < 12 => "¡Buenos días",
    $hora >= 12 && $hora < 18 => "¡Buenas tardes",
    default => "¡Buenas noches",
};

// Procesar estado emocional
$mensaje_exito = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['estado_emocional'])) {
    $estado = trim($_POST['estado_emocional']);
    if (!empty($estado)) {
        try {
            $sql = "INSERT INTO emociones (user_id, estado) VALUES (:user_id, :estado)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['user_id' => $user_id, 'estado' => $estado]);
            $mensaje_exito = "Estado emocional registrado correctamente.";
        } catch (PDOException $e) {
            $mensaje_error = "Error al registrar estado emocional: " . $e->getMessage();
        }
    } else {
        $mensaje_error = "Por favor, selecciona un estado emocional.";
    }
}

// Contar mensajes del usuario
$conteo_mensajes = 0;
try {
    $sql = "SELECT COUNT(*) as total FROM mensajes WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $conteo_mensajes = $result['total'] ?? 0;
} catch (PDOException $e) {
    // Silenciar error si la tabla mensajes no existe
}

// Procesar cierre de sesión
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Psicólogo Virtual</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #1e3a8a, #6b7280);
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background: white;
            max-width: 600px;
            width: 100%;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.5s ease-in-out;
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .back-button {
            position: absolute;
            top: 15px;
            left: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: #3b82f6;
            color: #ffffff;
            border-radius: 50%;
            text-decoration: none;
            transition: background 0.3s, transform 0.3s, box-shadow 0.3s;
        }

        .back-button:hover {
            background: #1e40af;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .back-button i {
            font-size: 16px;
        }

        .logo {
            margin-bottom: 20px;
        }

        .logo img {
            width: 100px;
            height: auto;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        h2 {
            color: #1e3a8a;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        p {
            font-size: 16px;
            color: #1f2937;
            margin-bottom: 10px;
        }

        .button {
            display: inline-block;
            padding: 12px 20px;
            background: linear-gradient(90deg, #1e40af, #3b82f6);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            width: 100%;
            text-align: center;
        }

        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .error {
            color: #dc2626;
            font-size: 14px;
            background: #fee2e2;
            padding: 8px;
            border-radius: 6px;
            margin-bottom: 15px;
            animation: fadeInError 0.3s ease-in;
        }

        .success {
            color: #15803d;
            font-size: 14px;
            background: #dcfce7;
            padding: 8px;
            border-radius: 6px;
            margin-bottom: 15px;
            animation: fadeInError 0.3s ease-in;
        }

        @keyframes fadeInError {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .form-group {
            margin-bottom: 15px;
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        select {
            width: 100%;
            padding: 10px 40px 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
            appearance: none;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="%236b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>') no-repeat right 12px center;
            background-size: 12px;
        }

        select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 6px rgba(59, 130, 246, 0.3);
        }

        .form-group label {
            display: block;
            color: #1f2937;
            font-size: 14px;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .form-group i {
            position: absolute;
            top: 48px;
            right: 30px;
            color: #6b7280;
            font-size: 16px;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }

        hr {
            border: 0;
            height: 1px;
            background: #d1d5db;
            margin: 15px 0;
        }

        .background-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.1;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
        }

        .shape-1 {
            width: 400px;
            height: 400px;
            top: 5%;
            left: 5%;
            animation: float 8s infinite ease-in-out;
        }

        .shape-2 {
            width: 300px;
            height: 300px;
            bottom: 10%;
            right: 10%;
            animation: float 10s infinite ease-in-out;
        }

        @keyframes float {
            0% { transform: translateY(0); }
            50% { transform: translateY(-50px); }
            100% { transform: translateY(0); }
        }

        @media (max-width: 480px) {
            body {
                padding: 15px;
            }

            .container {
                padding: 20px;
            }

            .logo img {
                width: 80px;
            }

            h2 {
                font-size: 24px;
            }

            .back-button {
                width: 32px;
                height: 32px;
            }

            .back-button i {
                font-size: 14px;
            }

            .button {
                font-size: 14px;
                padding: 10px 12px;
            }

            select {
                font-size: 13px;
                padding: 8px 35px 8px 10px;
            }

            .form-group {
                padding: 12px;
            }

            .form-group i {
                top: 46px;
                right: 28px;
                font-size: 14px;
            }

            .action-buttons {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            hr {
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="background-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>

    <div class="container">
        <a href="index.php" class="back-button" title="Volver al inicio">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="logo">
            <img src="perfil.jpeg"Psicólogo Virtual Logo">
        </div>
        <h2><?php echo $saludo . ", " . htmlspecialchars($username) . "!"; ?></h2>

        <?php if (isset($mensaje_error)): ?>
            <p class="error"><?php echo htmlspecialchars($mensaje_error); ?></p>
        <?php endif; ?>
        <?php if (isset($mensaje_exito)): ?>
            <p class="success"><?php echo htmlspecialchars($mensaje_exito); ?></p>
        <?php endif; ?>

        <p>Estás registrado en: <?php echo htmlspecialchars($grade); ?></p>
        <p>Has enviado <?php echo $conteo_mensajes; ?> mensaje<?php echo $conteo_mensajes != 1 ? 's' : ''; ?> al chat.</p>

        <hr>

        <div class="form-group">
            <label for="estado_emocional">¿Cómo te sientes hoy?</label>
            <form method="POST">
                <i class="fas fa-heart"></i>
                <select id="estado_emocional" name="estado_emocional" required>
                    <option value="" disabled selected>Selecciona tu estado</option>
                    <option value="Feliz">Feliz</option>
                    <option value="Triste">Triste</option>
                    <option value="Estresado">Estresado</option>
                    <option value="Ansioso">Ansioso</option>
                    <option value="Cansado">Cansado</option>
                    <option value="Motivado">Motivado</option>
                </select>
                <button type="submit" class="button" style="margin-top: 10px;">Registrar Estado</button>
            </form>
        </div>

        <hr>

        <div class="action-buttons">
            <a href="chat.php" class="button">Ir al Chat con el Psicólogo Virtual</a>
            <a href="edit_profile.php" class="button">Editar Perfil</a>
            <form method="POST">
                <button type="submit" name="logout" class="button">Cerrar Sesión</button>
            </form>
        </div>
    </div>
</body>
</html>