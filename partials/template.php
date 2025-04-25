<?php
// partials/template.php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle ?? 'PMS Daniya Denia'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- CSS personalizado -->
    <link rel="stylesheet" href="css/style.css">
    <?php if (isset($extraStyles)) echo $extraStyles; ?>
</head>

<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="d-flex" style="margin-top:1rem;">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <div class="main-content container">
            <?php
            include __DIR__ . '/breadcrumbs.php';
            echo getBreadcrumbs();
            ?>

            <!-- Contenido específico de la página -->
            <?php if (isset($pageContent)) echo $pageContent; ?>
        </div>
    </div>

    <!-- Bootstrap Bundle con Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Scripts personalizados -->
    <script src="js/theme.js"></script>
    <script src="js/navigation.js"></script>
    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>

</html>