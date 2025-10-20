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

$user_id = $_SESSION['user_id'];

// Configuración de OpenAI
$api_key = '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'; // Tu API key de OpenAI

// Procesar mensaje enviado vía AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'send_message') {
    $mensaje = trim($_POST['mensaje']);
    if (empty($mensaje)) {
        echo json_encode(['error' => 'Por favor, escribe un mensaje.']);
        exit;
    }
    try {
        // Llamada a OpenAI API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'Eres un psicólogo virtual empático y profesional. Responde en español de manera útil y supportive. Mantén las respuestas breves pero significativas.'],
                ['role' => 'user', 'content' => $mensaje]
            ],
            'max_tokens' => 150,
            'temperature' => 0.7
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200 || !$response) {
            echo json_encode(['error' => 'No se pudo conectar con la IA']);
            exit;
        }

        $data = json_decode($response, true);
        $respuesta_texto = $data['choices'][0]['message']['content'] ?? 'No se pudo obtener respuesta de la IA';

        // Lógica mejorada para determinar nivel_alerta
        $nivel_alerta = 'verde'; // Valor por defecto (normal)
        $mensaje_lower = strtolower($mensaje);
        
        // Palabras o frases para nivel "rojo" (dañino o peligroso)
        $palabras_rojo = ['suicidio', 'quiero morir', 'autolesión', 'cortarme', 'matarme', 'crisis severa', 'ayuda urgente', 'desesperado'];
        
        // Palabras o frases para nivel "anaranjado" (raro o preocupante)
        $palabras_anaranjado = ['ansiedad', 'ansioso', 'triste', 'deprimido', 'estresado', 'nervioso', 'preocupado', 'confundido', 'solo', 'abrumado', 'problemas serios'];
        
        // Palabras o frases para nivel "verde" (neutro o positivo, opcional para confirmación)
        $palabras_verde = ['hola', 'cómo estás', 'bien', 'feliz', 'motivado', 'gracias', 'consejo', 'ayuda simple', 'duda'];

        // Verificar nivel "rojo" primero
        foreach ($palabras_rojo as $palabra) {
            if (stripos($mensaje_lower, $palabra) !== false) {
                $nivel_alerta = 'rojo';
                break;
            }
        }

        // Si no es "rojo", verificar nivel "anaranjado"
        if ($nivel_alerta === 'verde') {
            foreach ($palabras_anaranjado as $palabra) {
                if (stripos($mensaje_lower, $palabra) !== false) {
                    $nivel_alerta = 'anaranjado';
                    break;
                }
            }
        }

        // Guardar mensaje en la base de datos con fecha automática
        $sql = "INSERT INTO mensajes (user_id, mensaje, respuesta_ia, nivel_alerta, fecha) VALUES (:user_id, :mensaje, :respuesta_ia, :nivel_alerta, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $user_id,
            'mensaje' => $mensaje,
            'respuesta_ia' => $respuesta_texto,
            'nivel_alerta' => $nivel_alerta
        ]);

        // Obtener el ID del último mensaje insertado
        $last_id = $pdo->lastInsertId();
        echo json_encode(['success' => true, 'mensaje' => $mensaje, 'respuesta' => $respuesta_texto, 'nivel_alerta' => $nivel_alerta, 'id' => $last_id, 'fecha' => date('d/m/Y H:i')]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error al procesar el mensaje: ' . $e->getMessage()]);
        exit;
    }
}

// Obtener historial de mensajes para la carga inicial
$sql = "SELECT mensaje, respuesta_ia, nivel_alerta, fecha FROM mensajes WHERE user_id = :user_id ORDER BY fecha ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Psicólogo Virtual</title>
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
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .success {
            color: #15803d;
            font-size: 14px;
            background: #dcfce7;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        @keyframes fadeInError {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .chat-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .chat-history {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid var(--gray-200);
            border-radius: 12px;
            padding: 15px;
            background: var(--gray-100);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            flex-grow: 1;
        }

        .message {
            margin-bottom: 15px;
            padding: 12px;
            border-radius: 10px;
            font-size: 14px;
            line-height: 1.5;
            transition: transform 0.3s ease;
        }

        .message:hover {
            transform: translateY(-3px);
        }

        .user-message {
            background: #dbeafe;
            border-left: 4px solid var(--primary);
        }

        .ia-message {
            background: #ccfbf1;
            border-left: 4px solid var(--secondary);
        }

        .alert-verde {
            background-color: #d4edda !important;
            border-left-color: var(--success) !important;
            color: #155724;
        }

        .alert-anaranjado {
            background-color: #fff3cd !important;
            border-left-color: #f97316 !important;
            color: #856404;
        }

        .alert-rojo {
            background-color: #f8d7da !important;
            border-left-color: #ef4444 !important;
            color: #721c24;
        }

        .timestamp {
            font-size: 12px;
            color: var(--gray-600);
            margin-top: 5px;
            text-align: right;
        }

        .form-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .form-group input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 6px rgba(37, 99, 235, 0.3);
        }

        .form-group button {
            padding: 10px 20px;
            background: var(--accent);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s, transform 0.3s, box-shadow 0.3s;
        }

        .form-group button:hover {
            background: #d97706;
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }

        .suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .suggestion-button {
            padding: 8px 12px;
            background: var(--gray-100);
            color: var(--gray-800);
            border: none;
            border-radius: 10px;
            font-size: 13px;
            cursor: pointer;
            transition: background 0.3s, transform 0.3s, box-shadow 0.3s;
        }

        .suggestion-button:hover {
            background: var(--secondary);
            color: var(--white);
            transform: scale(1.05);
            box-shadow: 0 2px 8px rgba(20, 184, 166, 0.3);
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: var(--gray-600);
            text-align: center;
            padding: 10px;
            background: var(--gray-100);
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .loading-bar {
            width: 100%;
            height: 4px;
            height: 4px;
            background: var(--gray-200);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            display: none;
        }

        .loading-bar::after {
            content: '';
            display: block;
            height: 100%;
            width: 0;
            background: var(--gradient-primary);
            animation: load 1.5s infinite;
        }

        @keyframes load {
            0% { width: 0; }
            50% { width: 50%; }
            100% { width: 100%; }
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

            .form-group button {
                font-size: 13px;
                padding: 8px 15px;
            }

            .chat-history {
                max-height: 200px;
            }

            .suggestions {
                gap: 5px;
            }

            .suggestion-button {
                font-size: 12px;
                padding: 6px 10px;
            }

            .footer {
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="loading-bar" id="loadingBar"></div>
    <div class="container">
        <a href="index.php" class="back-button" title="Volver al inicio">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="logo">
            <img src="images (2).jpeg" alt="Psicólogo Virtual Logo">
        </div>
        <h2>Chatea con tu Psicólogo Virtual</h2>

        <div class="error" id="errorMessage"></div>
        <div class="success" id="successMessage"></div>

        <div class="chat-container">
            <div class="chat-history" id="chatHistory">
                <?php foreach ($mensajes as $msg): ?>
                    <div class="message user-message alert-<?php echo htmlspecialchars($msg['nivel_alerta']); ?>">
                        <strong>Tú:</strong> <?php echo htmlspecialchars($msg['mensaje']); ?>
                        <div class="timestamp"><?php echo date('d/m/Y H:i', strtotime($msg['fecha'])); ?></div>
                    </div>
                    <?php if (!empty($msg['respuesta_ia'])): ?>
                        <div class="message ia-message alert-<?php echo htmlspecialchars($msg['nivel_alerta']); ?>">
                            <strong>IA:</strong> <?php echo htmlspecialchars($msg['respuesta_ia']); ?>
                            <div class="timestamp"><?php echo date('d/m/Y H:i', strtotime($msg['fecha'])); ?></div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div class="suggestions">
                <button class="suggestion-button" onclick="sendSuggestion('¿Cómo manejar el estrés diario?')">¿Cómo manejar el estrés?</button>
                <button class="suggestion-button" onclick="sendSuggestion('Siento ansiedad')">Siento ansiedad</button>
                <button class="suggestion-button" onclick="sendSuggestion('¿Consejos para mejorar el sueño?')">Mejorar el sueño</button>
                <button class="suggestion-button" onclick="sendSuggestion('¿Cómo lidiar con la tristeza?')">Lidiar con tristeza</button>
                <button class="suggestion-button" onclick="sendSuggestion('¿Ejercicios de relajación?')">Ejercicios de relajación</button>
            </div>
            <div class="form-group">
                <input type="text" id="messageInput" placeholder="Escribe tu mensaje..." required>
                <button type="button" onclick="sendMessage()"><i class="fas fa-paper-plane"></i> Enviar</button>
            </div>
        </div>
        <div class="footer">
            &copy; 2025 Psicólogo Virtual - Todos los derechos reservados. Recuerda, este es un soporte virtual; busca ayuda profesional si es necesario.
        </div>
    </div>

    <script>
        // Hacer scroll al final del historial de chat
        function scrollToBottom() {
            const chatHistory = document.getElementById('chatHistory');
            if (chatHistory) {
                chatHistory.scrollTop = chatHistory.scrollHeight;
            }
        }

        // Enviar mensaje con AJAX
        function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            const errorMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');
            const loadingBar = document.getElementById('loadingBar');

            if (!message) {
                errorMessage.textContent = 'Por favor, escribe un mensaje.';
                errorMessage.style.display = 'block';
                setTimeout(() => errorMessage.style.display = 'none', 3000);
                return;
            }

            // Mostrar barra de carga
            loadingBar.style.display = 'block';

            fetch('chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=send_message&mensaje=${encodeURIComponent(message)}`
            })
            .then(response => response.json())
            .then(data => {
                loadingBar.style.display = 'none';
                if (data.error) {
                    errorMessage.textContent = data.error;
                    errorMessage.style.display = 'block';
                    setTimeout(() => errorMessage.style.display = 'none', 3000);
                } else {
                    successMessage.textContent = 'Mensaje enviado.';
                    successMessage.style.display = 'block';
                    setTimeout(() => successMessage.style.display = 'none', 3000);

                    // Agregar mensaje al historial
                    const chatHistory = document.getElementById('chatHistory');
                    const userMessage = document.createElement('div');
                    userMessage.className = `message user-message alert-${data.nivel_alerta}`;
                    userMessage.innerHTML = `<strong>Tú:</strong> ${data.mensaje}<div class="timestamp">${data.fecha}</div>`;
                    chatHistory.appendChild(userMessage);

                    const iaMessage = document.createElement('div');
                    iaMessage.className = `message ia-message alert-${data.nivel_alerta}`;
                    iaMessage.innerHTML = `<strong>IA:</strong> ${data.respuesta}<div class="timestamp">${data.fecha}</div>`;
                    chatHistory.appendChild(iaMessage);

                    scrollToBottom();
                }
            })
            .catch(error => {
                loadingBar.style.display = 'none';
                errorMessage.textContent = 'Error al enviar el mensaje.';
                errorMessage.style.display = 'block';
                setTimeout(() => errorMessage.style.display = 'none', 3000);
            });

            messageInput.value = '';
        }

        // Enviar sugerencia
        function sendSuggestion(suggestion) {
            document.getElementById('messageInput').value = suggestion;
            sendMessage();
        }

        // Llamar al cargar la página
        window.onload = scrollToBottom;

        // Escuchar Enter para enviar
        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    </script>
</body>
</html>