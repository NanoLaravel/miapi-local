<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidad - Norte de Santander</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0f766e;
            --primary-dark: #0f172a;
            --accent: #14b8a6;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text: #334155;
            --text-light: #64748b;
            --border: #e2e8f0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            line-height: 1.7;
            margin: 0;
            padding: 0;
        }

        header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            color: white;
            padding: 3rem 1.5rem;
            text-align: center;
            border-bottom: 4px solid var(--accent);
        }

        header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        header p {
            margin: 0.5rem 0 0 0;
            font-size: 1.1rem;
            color: #cbd5e1;
            font-weight: 300;
        }

        .container {
            max-width: 800px;
            margin: 3rem auto;
            padding: 0 1.5rem;
        }

        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 2.5rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
        }

        h2 {
            color: var(--primary-dark);
            font-size: 1.5rem;
            font-weight: 600;
            margin-top: 2rem;
            border-bottom: 2px solid var(--border);
            padding-bottom: 0.5rem;
        }

        h2:first-of-type {
            margin-top: 0;
        }

        p, li {
            font-size: 1rem;
            color: var(--text);
        }

        ul {
            padding-left: 1.5rem;
        }

        li {
            margin-bottom: 0.5rem;
        }

        .footer {
            text-align: center;
            margin-top: 4rem;
            padding: 2rem 0;
            color: var(--text-light);
            font-size: 0.9rem;
            border-top: 1px solid var(--border);
        }

        .highlight {
            background-color: #f0fdfa;
            border-left: 4px solid var(--accent);
            padding: 1rem;
            border-radius: 0 0.5rem 0.5rem 0;
            margin: 1.5rem 0;
        }

        .highlight p {
            margin: 0;
            font-size: 0.95rem;
            color: var(--primary);
        }
    </style>
</head>
<body>

    <header>
        <h1>Política de Privacidad</h1>
        <p>Aplicación Móvil Turística - Departamento Norte de Santander</p>
    </header>

    <div class="container">
        <div class="card">
            <p><strong>Última actualización:</strong> 4 de julio de 2026</p>
            
            <p>Para nosotros, la privacidad de nuestros usuarios es de suma importancia. Esta Política de Privacidad describe cómo recopilamos, utilizamos y protegemos la información personal obtenida a través de nuestra aplicación móvil turística.</p>

            <h2>1. Información que Recopilamos</h2>
            <p>Al utilizar nuestra aplicación e iniciar sesión a través de métodos estándar o credenciales sociales (Google y Facebook), recopilamos la siguiente información:</p>
            <ul>
                <li><strong>Datos de Perfil Básico:</strong> Nombre completo, correo electrónico y foto de perfil (avatar).</li>
                <li><strong>Identificadores de Proveedor:</strong> ID único asignado por Google o Facebook para vincular tu sesión de forma segura.</li>
                <li><strong>Información de Ubicación:</strong> Solo si otorgas permisos de geolocalización de forma explícita en tu dispositivo para encontrar lugares de interés cercanos a ti.</li>
            </ul>

            <h2>2. Uso de la Información</h2>
            <p>Utilizamos la información recopilada únicamente para los siguientes propósitos:</p>
            <ul>
                <li>Permitir el inicio de sesión y autenticación segura dentro de la plataforma.</li>
                <li>Personalizar tu experiencia dentro de la aplicación móvil (como guardar tus lugares favoritos).</li>
                <li>Proporcionar sugerencias de turismo y eventos basadas en tu ubicación actual si está activada la opción.</li>
            </ul>

            <div class="highlight">
                <p><strong>Nota importante sobre tus datos:</strong> No vendemos, alquilamos ni compartimos tus datos personales con terceros con fines comerciales o de publicidad masiva.</p>
            </div>

            <h2>3. Conservación y Seguridad de Datos</h2>
            <p>Implementamos medidas de seguridad técnicas y organizativas para proteger tus datos contra el acceso no autorizado, la alteración o la destrucción. Tus datos se almacenan en servidores seguros con cifrado de conexión SSL (HTTPS).</p>

            <h2>4. Tus Derechos y Control de Datos</h2>
            <p>Tienes pleno control sobre tu información personal. En cualquier momento puedes:</p>
            <ul>
                <li>Actualizar tus datos de perfil desde la configuración de la aplicación móvil.</li>
                <li>Revocar los permisos de geolocalización o permisos de la red social correspondiente.</li>
                <li>Solicitar la eliminación completa de tu cuenta y toda la información asociada en nuestras bases de datos en cualquier momento.</li>
            </ul>

            <p>Si deseas solicitar la eliminación completa de tu cuenta, consulta nuestras <a href="/deletion">Instrucciones de Eliminación de Datos de Usuario</a>.</p>

            <h2>5. Contacto</h2>
            <p>Si tienes preguntas o dudas sobre esta política de privacidad, puedes ponerte en contacto con el equipo de soporte técnico a través del correo electrónico: <strong>soporte@nortedesantander.com</strong>.</p>
        </div>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} Gobernación de Norte de Santander. Todos los derechos reservados.
    </div>

</body>
</html>
