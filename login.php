<?php
session_start();

require_once __DIR__ . '/core/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Conectar a la BD
    $pdo = Database::getInstance()->getConnection();

    // Buscamos al usuario
    $sql = "SELECT * FROM usuarios WHERE username = :username AND activo = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':username', $username);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Verificar contraseña con hash (actualizar cuando se implementen hashes)
        if ($usuario['password'] === $password) {
            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['usuario_nombre'] = $usuario['username'];

            // Registrar el inicio de sesión exitoso
            $ip = $_SERVER['REMOTE_ADDR'];
            $sql = "INSERT INTO login_logs (usuario_id, ip_address, estado) VALUES (:usuario_id, :ip, 'success')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['usuario_id' => $usuario['id_usuario'], 'ip' => $ip]);

            header('Location: public/dashboard.php');
            exit;
        } else {
            $error = "Contraseña incorrecta";
        }
    } else {
        $error = "Usuario no encontrado o inactivo";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PMS Daniya Denia</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Animate.css -->
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <!-- CSS personalizado -->
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="public/css/themes.css">

    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-main);
            padding: 1rem;
        }

        .login-card {
            background: var(--bg-card);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            width: 100%;
            max-width: 400px;
            border: 1px solid var(--border-color);
            animation: fadeIn 0.5s ease-out;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: var(--primary-color);
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }

        .error-message {
            background-color: var(--danger-color);
            color: var(--text-light);
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            animation: fadeIn 0.3s ease-out;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>PMS Daniya Denia</h1>
                <p class="text-muted">Acceso al Sistema</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="animate__animated animate__fadeIn">
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="fas fa-user me-2"></i>Usuario
                    </label>
                    <input type="text"
                        class="form-control"
                        id="username"
                        name="username"
                        required
                        autocomplete="username"
                        placeholder="Ingrese su usuario">
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-2"></i>Contraseña
                    </label>
                    <div class="input-group">
                        <input type="password"
                            class="form-control"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Ingrese su contraseña">
                        <button class="btn btn-outline-secondary"
                            type="button"
                            id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Acceder
                </button>
            </form>

            <div class="mt-4 text-center">
                <button class="theme-toggle" id="theme-toggle" title="Cambiar tema">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/theme.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                icon.className = 'fas fa-eye';
            }
        });
    </script>
</body>

</html>