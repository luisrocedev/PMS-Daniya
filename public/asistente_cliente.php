<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Asistente Virtual del Hotel</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .asistente-container {
            max-width: 500px;
            margin: 40px auto;
            padding: 24px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #0001;
        }

        .asistente-mensaje {
            margin-bottom: 16px;
        }

        .asistente-respuesta {
            margin-top: 16px;
            font-weight: bold;
        }

        .asistente-contacto {
            margin-top: 24px;
        }

        button {
            padding: 8px 16px;
            border: none;
            background: #007bff;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>
    <div class="asistente-container">
        <h2>Asistente Virtual del Hotel</h2>
        <form id="formAsistente">
            <label for="mensaje">¿En qué podemos ayudarte?</label><br>
            <input type="text" id="mensaje" name="mensaje" required style="width: 100%; padding: 8px; margin-top: 8px;">
            <button type="submit" style="margin-top: 12px;">Preguntar</button>
        </form>
        <div id="respuesta" class="asistente-respuesta"></div>
        <div id="contacto" class="asistente-contacto" style="display:none;">
            <p>¿Prefieres hablar con un recepcionista?</p>
            <button onclick="window.location.href='tel:+34966123456'">Llamar a recepción</button>
            <p>Teléfono: <strong>+34 966 123 456</strong> (24h)</p>
        </div>
    </div>
    <script>
        const form = document.getElementById('formAsistente');
        const respuestaDiv = document.getElementById('respuesta');
        const contactoDiv = document.getElementById('contacto');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            respuestaDiv.textContent = 'Consultando...';
            contactoDiv.style.display = 'none';
            const mensaje = document.getElementById('mensaje').value;
            try {
                const res = await fetch('http://localhost:3000/analizar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        mensaje
                    })
                });
                if (!res.ok) {
                    respuestaDiv.textContent = `Error HTTP: ${res.status}`;
                    console.error('Respuesta HTTP no OK:', res);
                    return;
                }
                const data = await res.json();
                console.log('Respuesta recibida:', data);
                let mostrar = data.respuesta || data.sugerencia || 'No se recibió respuesta.';
                respuestaDiv.textContent = mostrar;
                if (
                    data.intencion_detectada === 'no_entendido' ||
                    (mostrar && mostrar.toLowerCase().includes('no entiendo'))
                ) {
                    contactoDiv.style.display = 'block';
                }
            } catch (err) {
                respuestaDiv.textContent = 'Error al conectar con el asistente.';
                console.error('Error en fetch:', err);
            }
        });
    </script>
</body>

</html>