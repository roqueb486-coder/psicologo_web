<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'conexion.php'; // Incluye el archivo de conexión a la base de datos

// Verificar si el usuario ya está autenticado
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Procesar el formulario de login
$mensaje_error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $mensaje_error = "Por favor, completa todos los campos.";
    } else {
        try {
            // Consultar el usuario en la base de datos
            $sql = "SELECT id, username, password FROM usuarios WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar si el usuario existe
            if (!$user) {
                $mensaje_error = "El correo electrónico no está registrado.";
            } elseif (!password_verify($password, $user['password'])) {
                $mensaje_error = "Contraseña incorrecta.";
            } else {
                // Iniciar sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: dashboard.php");
                exit;
            }
        } catch (PDOException $e) {
            $mensaje_error = "Error en la base de datos: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Psicólogo Virtual</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb; /* Vibrant blue */
            --primary-dark: #1e40af;
            --secondary: #14b8a6; /* Teal for energy */
            --accent: #f59e0b; /* Warm yellow for pop */
            --success: #22c55e;
            --white: #ffffff;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-600: #4b5563;
            --gray-800: #1f2937;
            --gradient-primary: linear-gradient(135deg, #2563eb 0%, #60a5fa 100%);
            --gradient-hero: linear-gradient(135deg, #2563eb 0%, #14b8a6 50%, #f59e0b 100%);
            --gradient-body: linear-gradient(135deg, #dbeafe 0%, #ccfbf1 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: url('https://images.unsplash.com/photo-1516321310764-8d8f6c0c77c0?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat fixed;
            color: var(--gray-800);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.85);
            z-index: -1;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            max-width: 400px;
            width: 90%;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 0.5s ease-in-out;
            position: relative;
            text-align: center;
            z-index: 1;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: var(--white);
            border-radius: 50%;
            text-decoration: none;
            transition: background 0.3s, transform 0.3s, box-shadow 0.3s;
        }

        .back-button:hover {
            background: var(--primary-dark);
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .back-button i {
            font-size: 18px;
        }

        .logo {
            margin-bottom: 25px;
            margin-top: 20px;
        }

        .logo img {
            width: 120px;
            height: auto;
            filter: drop-shadow(0 2px 5px rgba(0, 0, 0, 0.2));
            transition: transform 0.3s ease;
        }

        .logo:hover img {
            transform: scale(1.05);
        }

        h2 {
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        label {
            display: block;
            color: var(--gray-800);
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .form-group:focus-within label {
            color: var(--primary);
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
            background: var(--gray-100);
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 8px rgba(37, 99, 235, 0.3);
            background: var(--white);
        }

        .form-group i {
            position: absolute;
            top: 38px;
            right: 15px;
            color: var(--gray-600);
            cursor: pointer;
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .form-group:focus-within i {
            color: var(--primary);
        }

        button {
            width: 100%;
            padding: 14px;
            background: var(--gradient-primary);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        button:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .error {
            color: #dc2626;
            font-size: 14px;
            background: #fee2e2;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: fadeInError 0.3s ease-in;
        }

        @keyframes fadeInError {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: var(--gray-600);
        }

        .register-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: #d97706;
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .container {
                padding: 25px;
            }

            h2 {
                font-size: 24px;
            }

            button {
                font-size: 14px;
                padding: 12px;
            }

            .logo img {
                width: 100px;
            }

            .back-button {
                width: 35px;
                height: 35px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-button" title="Volver al inicio">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="logo">
            <img src="inicio.png" alt="Psicólogo Virtual Logo">
        </div>
        <h2>Iniciar Sesión</h2>
        
        <?php if ($mensaje_error): ?>
            <p class="error"><?php echo htmlspecialchars($mensaje_error); ?></p>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required>
                <i class="fas fa-envelope"></i>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
                <i class="fas fa-eye toggle-password"></i>
            </div>
            <button type="submit">Iniciar Sesión</button>
        </form>
        
        <div class="register-link">
            <p>¡Crea una cuenta aquí si no tienes una! <a href="register.php">Regístrate</a></p>
        </div>
    </div>

    <script>
        // Alternar visibilidad de la contraseña
        const togglePassword = document.querySelector('.toggle-password');
        const passwordInput = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>