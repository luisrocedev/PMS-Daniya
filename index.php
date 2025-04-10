<?php
// index.php

session_start();

// Si el usuario NO está logueado, mandarlo al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Si el usuario ESTÁ logueado, redirigirlo al dashboard
header('Location: public/dashboard.php');
exit;
