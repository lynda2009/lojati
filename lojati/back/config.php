<?php

header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$banco = "LojaTI";
$usuario = "root";
$senha = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        "erro" => "Não foi possível conectar ao banco LojaTI."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

