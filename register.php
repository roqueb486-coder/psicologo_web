<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'conexion.php';

// Verificar si el usuario ya está autenticado
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Procesar el formulario de registro
$mensaje_error = '';
$mensaje_exito = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $grade = trim($_POST['grade']);
    $other_grade = isset($_POST['other_grade']) ? trim($_POST['other_grade']) : '';

    // Validaciones
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($grade)) {
        $mensaje_error = "Por favor, completa todos los campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje_error = "Por favor, ingresa un correo electrónico válido.";
    } elseif (strlen($password) < 8) {
        $mensaje_error = "La contraseña debe tener al menos 8 caracteres.";
    } elseif ($password !== $confirm_password) {
        $mensaje_error = "Las contraseñas no coinciden.";
    } elseif ($grade === 'Otro' && empty($other_grade)) {
        $mensaje_error = "Por favor, especifica tu grado en el campo 'Otro'.";
    } else {
        try {
            // Verificar si el usuario o correo ya existen
            $sql = "SELECT id FROM usuarios WHERE username = :username OR email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['username' => $username, 'email' => $email]);
            if ($stmt->fetch()) {
                $mensaje_error = "El usuario o correo ya está registrado.";
            } else {
                // Hashear la contraseña y determinar el grado a guardar
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $final_grade = ($grade === 'Otro') ? $other_grade : $grade;

                // Insertar el usuario
                $sql = "INSERT INTO usuarios (username, email, password, grade) VALUES (:username, :email, :password, :grade)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'password' => $hashed_password,
                    'grade' => $final_grade
                ]);
                $mensaje_exito = "¡Registro exitoso! Ahora puedes <a href='login.php'>iniciar sesión</a>.";
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
    <title>Registro - Psicólogo Virtual</title>
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
            background: url('https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat fixed;
            color: var(--gray-800);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
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
            max-width: 450px;
            width: 100%;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            animation: fadeInUp 0.5s ease-in-out;
            position: relative;
            text-align: center;
            z-index: 1;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(15px); }
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
            background: var(--primary);
            color: var(--white);
            border-radius: 50%;
            text-decoration: none;
            transition: background 0.3s, transform 0.3s, box-shadow 0.3s;
        }

        .back-button:hover {
            background: var(--primary-dark);
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .back-button i {
            font-size: 16px;
        }

        .logo {
            margin-bottom: 15px;
        }

        .logo img {
            width: 100px;
            height: auto;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            position: relative;
            text-align: left;
        }

        label {
            display: block;
            color: var(--gray-800);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px 40px 10px 12px;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
            background: var(--gray-100);
        }

        select {
            appearance: none;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24"><path fill="%234b5563" d="M7 10l5 5 5-5z"/></svg>') no-repeat right 12px center;
            background-size: 10px;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 6px rgba(37, 99, 235, 0.3);
            background: var(--white);
        }

        .form-group i {
            position: absolute;
            top: 34px;
            right: 12px;
            color: var(--gray-600);
            cursor: pointer;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .form-group:focus-within i {
            color: var(--primary);
        }

        #other_grade_group {
            display: none;
            animation: fadeInUp 0.3s ease-in-out;
            background: var(--gray-100);
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
        }

        #other_grade {
            background: var(--white);
        }

        #other_grade:focus {
            background: var(--white);
        }

        button {
            width: 100%;
            padding: 12px;
            background: var(--gradient-primary);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        button:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .error {
            color: #dc2626;
            font-size: 13px;
            background: #fee2e2;
            padding: 8px;
            border-radius: 6px;
            margin-bottom: 15px;
            animation: fadeInError 0.3s ease-in;
        }

        .success {
            color: #15803d;
            font-size: 13px;
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

        .login-link {
            margin-top: 15px;
            font-size: 13px;
            color: var(--gray-600);
        }

        .login-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #d97706;
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            body {
                align-items: flex-start;
                padding: 10px;
            }

            .container {
                padding: 20px;
                margin: 10px;
                border-radius: 12px;
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
            }

            .logo img {
                width: 80px;
            }

            h2 {
                font-size: 1.5rem;
                margin-bottom: 15px;
            }

            .back-button {
                width: 32px;
                height: 32px;
                top: 10px;
                left: 10px;
            }

            .back-button i {
                font-size: 14px;
            }

            .form-group {
                margin-bottom: 12px;
            }

            label {
                font-size: 12px;
            }

            input[type="text"],
            input[type="email"],
            input[type="password"],
            select {
                font-size: 13px;
                padding: 8px 36px 8px 10px;
                border-radius: 6px;
            }

            .form-group i {
                top: 32px;
                right: 10px;
                font-size: 14px;
            }

            select {
                background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24"><path fill="%234b5563" d="M7 10l5 5 5-5z"/></svg>') no-repeat right 10px center;
                background-size: 8px;
            }

            #other_grade_group {
                padding: 8px;
                margin-top: 8px;
            }

            button {
                font-size: 14px;
                padding: 10px;
                border-radius: 6px;
            }

            .error,
            .success {
                font-size: 12px;
                padding: 6px;
                border-radius: 4px;
            }

            .login-link {
                font-size: 12px;
                margin-top: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-button" title="Volver al inicio">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h2>Crear Cuenta</h2>
        
        <?php if ($mensaje_error): ?>
            <p class="error"><?php echo htmlspecialchars($mensaje_error); ?></p>
        <?php endif; ?>
        <?php if ($mensaje_exito): ?>
            <p class="success"><?php echo $mensaje_exito; ?></p>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Nombre Completo</label>
                <input type="text" id="username" name="username" placeholder="Ingresa tu usuario" required>
                <i class="fas fa-user"></i>
            </div>
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" placeholder="Ingresa tu correo" required>
                <i class="fas fa-envelope"></i>
            </div>
            <div class="form-group">
                <label for="grade">Grado Escolar</label>
                <select id="grade" name="grade" required>
                    <option value="">Selecciona tu grado</option>
                    <option value="1ro de Secundaria">1ro de Secundaria</option>
                    <option value="2do de Secundaria">2do de Secundaria</option>
                    <option value="3ro de Secundaria">3ro de Secundaria</option>
                    <option value="4to de Secundaria">4to de Secundaria</option>
                    <option value="5to de Secundaria">5to de Secundaria</option>
                    <option value="Otro">Otro</option>
                </select>
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="form-group" id="other_grade_group">
                <label for="other_grade">Especifica tu Grado</label>
                <input type="text" id="other_grade" name="other_grade" placeholder="Ej: Preparatoria, Universidad" maxlength="50">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                <i class="fas fa-eye toggle-password" data-target="password"></i>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmar Contraseña</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirma tu contraseña" required>
                <i class="fas fa-eye toggle-password" data-target="confirm_password"></i>
            </div>
            <button type="submit">Registrarse</button>
        </form>
        
        <div class="login-link">
            <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
        </div>
    </div>

    <script>
        // Alternar visibilidad de las contraseñas
        const toggleButtons = document.querySelectorAll('.toggle-password');
        toggleButtons.forEach(button => {
            button.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });

        // Mostrar/ocultar campo de "Otro" grado
        const gradeSelect = document.getElementById('grade');
        const otherGradeGroup = document.getElementById('other_grade_group');
        const otherGradeInput = document.getElementById('other_grade');

        gradeSelect.addEventListener('change', function () {
            if (this.value === 'Otro') {
                otherGradeGroup.style.display = 'block';
                otherGradeInput.setAttribute('required', 'required');
            } else {
                otherGradeGroup.style.display = 'none';
                otherGradeInput.removeAttribute('required');
                otherGradeInput.value = '';
            }
        });

        // Inicializar visibilidad del campo "Otro"
        if (gradeSelect.value !== 'Otro') {
            otherGradeGroup.style.display = 'none';
        }

        // Prevenir zoom no deseado al enfocar inputs en móviles
        const inputs = document.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                document.body.style.overflow = 'hidden';
            });
            input.addEventListener('blur', () => {
                document.body.style.overflow = '';
            });
        });
    </script>
</body>
</html>