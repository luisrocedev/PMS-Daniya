<?php
// login.php

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
        // Verificamos password en texto plano (por ahora sin hash)
        if ($usuario['password'] === $password) {
            // Login válido: guardamos datos en sesión
            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['usuario_nombre'] = $usuario['username'];

            // Redireccionar a un "dashboard" o a index
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

<!-- Formulario sencillo de login -->
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Login - PMS Daniya Denia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css"> <!-- Asegúrate de enlazar al CSS -->
</head>

<body>

    <div class="login-container">
        <h1>Iniciar Sesión</h1>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Acceder</button>
        </form>
    </div>

</body>

</html>