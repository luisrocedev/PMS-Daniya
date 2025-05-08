<?php
header('Content-Type: application/json');
// Simulación de tipos de habitación
$tipos = [
    ["id" => 1, "nombre" => "Individual"],
    ["id" => 2, "nombre" => "Doble"],
    ["id" => 3, "nombre" => "Suite"]
];
echo json_encode(["success" => true, "data" => $tipos]);
