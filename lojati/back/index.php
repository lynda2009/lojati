<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

require_once "config.php";

$rota = $_GET["rota"] ?? "";
$metodo = $_SERVER["REQUEST_METHOD"];

if ($rota === "clientes" && $metodo === "GET") {
    readClientes($pdo);
} elseif ($rota === "clientes/create" && $metodo === "POST") {
    createCliente($pdo);
} else {
    http_response_code(404);

    echo json_encode([
        "erro" => "Rota não encontrada."
    ], JSON_UNESCAPED_UNICODE);
}

function readClientes($pdo)
{
    try {
        $sql = "SELECT ClienteID, Nome, Email, Telefone, Endereco
                FROM Clientes
                ORDER BY ClienteID";

        $consulta = $pdo->query($sql);
        $clientes = $consulta->fetchAll();

        echo json_encode(
            $clientes,
            JSON_UNESCAPED_UNICODE
        );

    } catch (PDOException $e) {
        http_response_code(500);

        echo json_encode([
            "erro" => "Erro ao consultar os clientes."
        ], JSON_UNESCAPED_UNICODE);
    }
}

function createCliente($pdo)
{
    $conteudo = file_get_contents("php://input");
    $dados = json_decode($conteudo, true);

    if (
        json_last_error() !== JSON_ERROR_NONE ||
        !is_array($dados)
    ) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Os dados enviados não são um JSON válido."
        ], JSON_UNESCAPED_UNICODE);

        return;
    }

    $nome = trim($dados["Nome"] ?? "");
    $email = trim($dados["Email"] ?? "");
    $telefone = trim($dados["Telefone"] ?? "");
    $endereco = trim($dados["Endereco"] ?? "");

    if ($nome === "" || $email === "") {
        http_response_code(400);

        echo json_encode([
            "erro" => "Nome e Email são obrigatórios."
        ], JSON_UNESCAPED_UNICODE);

        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Digite um e-mail válido."
        ], JSON_UNESCAPED_UNICODE);

        return;
    }

    if (
        strlen($nome) > 100 ||
        strlen($email) > 100 ||
        strlen($telefone) > 15 ||
        strlen($endereco) > 100
    ) {
        http_response_code(400);

        echo json_encode([
            "erro" => "Um ou mais campos ultrapassam o limite permitido."
        ], JSON_UNESCAPED_UNICODE);

        return;
    }

    try {
        $sql = "INSERT INTO Clientes
                (Nome, Email, Telefone, Endereco)
                VALUES (?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $nome,
            $email,
            $telefone !== "" ? $telefone : null,
            $endereco !== "" ? $endereco : null
        ]);

        http_response_code(201);

        echo json_encode([
            "mensagem" => "Cliente cadastrado com sucesso!",
            "ClienteID" => $pdo->lastInsertId()
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        if ($e->getCode() === "23000") {
            http_response_code(409);

            echo json_encode([
                "erro" => "Este e-mail já está cadastrado."
            ], JSON_UNESCAPED_UNICODE);

            return;
        }

        http_response_code(500);

        echo json_encode([
            "erro" => "Erro ao salvar o cliente no banco de dados."
        ], JSON_UNESCAPED_UNICODE);
    }
}
