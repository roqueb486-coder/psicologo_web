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
    $sql = "SELECT username, grade, phone, age, email FROM usuarios WHERE id = :user_id";
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
    $phone = $user['phone'] ?? '';
    $age = $user['age'] ?? '';
    $email = $user['email'] ?? '';
    // Extraer los 9 dígitos de manera segura
    $phone_digits = '';
    if (!empty($phone) && preg_match('/^\+51(\d{9})$/', $phone, $matches)) {
        $phone_digits = $matches[1] ?? '';
    }
} catch (PDOException $e) {
    $mensaje_error = "Error al obtener datos del usuario: " . $e->getMessage();
}

// Procesar actualización del perfil
$mensaje_exito = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $new_username = trim($_POST['username']);
    $new_grade = trim($_POST['grade']);
    $new_other_grade = isset($_POST['other_grade']) ? trim($_POST['other_grade']) : '';
    $new_phone_digits = trim($_POST['phone_digits'] ?? '');
    $new_age = trim($_POST['age']);
    $new_email = trim($_POST['email']);

    // Construir el grado final
    $final_grade = ($new_grade === 'Otro') ? $new_other_grade : $new_grade;

    // Construir el número completo con +51
    $new_phone = $new_phone_digits ? '+51' . $new_phone_digits : null;

    // Validar campos
    if (empty($new_username) || empty($new_grade)) {
        $mensaje_error = "Por favor, completa los campos obligatorios (nombre de usuario y grado).";
    } elseif ($new_grade === 'Otro' && empty($new_other_grade)) {
        $mensaje_error = "Por favor, especifica tu grado en el campo 'Otro'.";
    } elseif (!empty($new_phone_digits) && !preg_match('/^\d{9}$/', $new_phone_digits)) {
        $mensaje_error = "El número de celular debe tener exactamente 9 dígitos.";
    } elseif (!empty($new_age) && ($new_age < 1 || $new_age > 120)) {
        $mensaje_error = "La edad debe estar entre 1 y 120 años.";
    } elseif (!empty($new_email) && !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $mensaje_error = "Por favor, ingresa un correo electrónico válido.";
    } else {
        try {
            $sql = "UPDATE usuarios SET username = :username, grade = :grade, phone = :phone, age = :age, email = :email WHERE id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'username' => $new_username,
                'grade' => $final_grade,
                'phone' => $new_phone,
                'age' => $new_age ?: null,
                'email' => $new_email ?: null,
                'user_id' => $user_id
            ]);
            $mensaje_exito = "Perfil actualizado correctamente.";
            $username = $new_username;
            $grade = $final_grade;
            $phone = $new_phone;
            $age = $new_age;
            $email = $new_email;
            $phone_digits = $new_phone_digits;
        } catch (PDOException $e) {
            $mensaje_error = "Error al actualizar el perfil: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Psicólogo Virtual</title>
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
            align-items: flex-start;
            padding: 20px;
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
            max-width: 600px;
            width: 100%;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 0.5s ease-in-out;
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 20px;
            z-index: 1;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
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
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
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
            font-size: 28px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .error {
            color: #dc2626;
            font-size: 14px;
            background: #fee2e2;
            padding: 8px;
            border-radius: 8px;
            margin-bottom: 15px;
            animation: fadeInError 0.3s ease-in;
        }

        .success {
            color: #15803d;
            font-size: 14px;
            background: #dcfce7;
            padding: 8px;
            border-radius: 8px;
            margin-bottom: 15px;
            animation: fadeInError 0.3s ease-in;
        }

        @keyframes fadeInError {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .form-group {
            margin-bottom: 15px;
            background: var(--gray-100);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: left;
        }

        input[type="text"], input[type="tel"], input[type="number"], input[type="email"], select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 6px rgba(37, 99, 235, 0.3);
        }

        .form-group label {
            display: block;
            color: var(--gray-800);
            font-size: 14px;
            margin-bottom: 6px;
            font-weight: 500;
        }

        select {
            appearance: none;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"><path fill="%234b5563" d="M7 10l5 5 5-5z"/></svg>') no-repeat right 12px center;
            background-size: 12px;
        }

        .phone-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .phone-container input[type="tel"] {
            flex: 0 0 50px;
            background: var(--gray-200);
            cursor: not-allowed;
        }

        .phone-container input[type="text"] {
            flex: 1;
        }

        #other_grade_group {
            display: none;
            animation: fadeInUp 0.3s ease-in-out;
            background: var(--gray-100);
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .button {
            display: inline-block;
            padding: 12px 20px;
            background: var(--gradient-primary);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            width: 100%;
            text-align: center;
        }

        .button:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
        }

        hr {
            border: 0;
            height: 1px;
            background: var(--gray-200);
            margin: 15px 0;
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

            .button {
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

            .action-buttons {
                flex-direction: column;
                gap: 8px;
            }

            .phone-container {
                flex-direction: column;
                gap: 5px;
            }

            .phone-container input[type="tel"] {
                flex: 0 0 100%;
            }

            .phone-container input[type="text"] {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-button" title="Volver al dashboard">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="logo">
            <img src="editar perfil.jpg" alt="Psicólogo Virtual Logo">
        </div>
        <h2>Editar Perfil</h2>

        <?php if (isset($mensaje_error)): ?>
            <p class="error"><?php echo htmlspecialchars($mensaje_error); ?></p>
        <?php endif; ?>
        <?php if (isset($mensaje_exito)): ?>
            <p class="success"><?php echo htmlspecialchars($mensaje_exito); ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Nombre de Usuario</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label for="grade">Grado Escolar</label>
                <select id="grade" name="grade" required>
                    <option value="">Selecciona tu grado</option>
                    <option value="Primero" <?php echo $grade == 'Primero' ? 'selected' : ''; ?>>Primero</option>
                    <option value="Segundo" <?php echo $grade == 'Segundo' ? 'selected' : ''; ?>>Segundo</option>
                    <option value="Tercero" <?php echo $grade == 'Tercero' ? 'selected' : ''; ?>>Tercero</option>
                    <option value="Cuarto" <?php echo $grade == 'Cuarto' ? 'selected' : ''; ?>>Cuarto</option>
                    <option value="Quinto" <?php echo $grade == 'Quinto' ? 'selected' : ''; ?>>Quinto</option>
                    <option value="Otro" <?php echo ($grade != 'Primero' && $grade != 'Segundo' && $grade != 'Tercero' && $grade != 'Cuarto' && $grade != 'Quinto') ? 'selected' : ''; ?>>Otro</option>
                </select>
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="form-group" id="other_grade_group">
                <label for="other_grade">Especifica tu Grado</label>
                <input type="text" id="other_grade" name="other_grade" value="<?php echo ($grade != 'Primero' && $grade != 'Segundo' && $grade != 'Tercero' && $grade != 'Cuarto' && $grade != 'Quinto') ? htmlspecialchars($grade) : ''; ?>" placeholder="Ej: Preparatoria, Universidad" maxlength="50">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="form-group">
                <label for="phone">Número de Celular</label>
                <div class="phone-container">
                    <input type="tel" id="phone_prefix" name="phone_prefix" value="+51" readonly>
                    <input type="text" id="phone_digits" name="phone_digits" value="<?php echo htmlspecialchars($phone_digits); ?>" pattern="[0-9]{9}" maxlength="9" placeholder="Ej: 912345678">
                </div>
            </div>
            <div class="form-group">
                <label for="age">Edad</label>
                <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($age); ?>" min="1" max="120" placeholder="Ej: 25">
            </div>
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Ej: ejemplo@correo.com">
            </div>
            <button type="submit" name="update_profile" class="button">Actualizar Perfil</button>
        </form>

        <hr>

        <div class="action-buttons">
            <a href="dashboard.php" class="button">Volver al Dashboard</a>
        </div>
    </div>

    <script>
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
        } else {
            otherGradeGroup.style.display = 'block';
            otherGradeInput.setAttribute('required', 'required');
        }
    </script>
</body>
</html>