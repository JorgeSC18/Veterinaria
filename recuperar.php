<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - VetVital</title>
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --brand-green: #115e59; 
            --brand-green-hover: #0f4c48;
            --bg-light: #f8fafc;
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
        }

        h3 {
            font-size: 1.8rem;
            color: var(--text-dark);
            margin: 0 0 8px 0;
            text-align: center;
        }

        .subtitle {
            color: var(--text-muted);
            margin: 0 0 30px 0;
            font-size: 0.95rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .input-container input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            border-radius: 8px;
            font-size: 0.95rem;
            box-sizing: border-box;
            transition: all 0.2s;
        }

        .input-container input:focus {
            outline: none;
            border-color: var(--brand-green);
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(17, 94, 89, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: var(--brand-green);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-bottom: 15px;
        }

        .btn-submit:hover {
            background-color: var(--brand-green-hover);
        }

        .back-link {
            display: block;
            text-align: center;
            font-size: 0.9rem;
            color: var(--brand-green);
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="form-container">
        <h3>¿Olvidaste tu contraseña?</h3>
        <p class="subtitle">Ingresa tu correo electrónico para enviarte las instrucciones de restablecimiento.</p>

        <form action="procesar_recuperar" method="POST">
            <div class="form-group">
                <label for="correo">Correo electrónico</label>
                <div class="input-container">
                    <input type="email" id="correo" name="correo" placeholder="ejemplo@correo.com" required autocomplete="off">
                </div>
            </div>

            <button type="submit" class="btn-submit">Enviar enlace de recuperación</button>
            <a href="login" class="back-link"><i class="fa-solid fa-arrow-left"></i> Volver al inicio de sesión</a>
        </form>
    </div>

</body>
</html>