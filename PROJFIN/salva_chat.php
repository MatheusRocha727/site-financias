<?php
session_start();
require "conexao.php";

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'erro' => 'Não autenticado']);
    exit;
}

$idUsuario = (int) $_SESSION['id_usuario'];

// lê JSON cru do body
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

$pergunta = trim($data['pergunta'] ?? '');
$resposta = trim($data['resposta'] ?? '');
$updateId = isset($data['update_id']) ? (int)$data['update_id'] : 0;

if ($pergunta === '' && $resposta === '' && $updateId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Nada para salvar']);
    exit;
}

// 1) Se veio update_id -> atualiza apenas a resposta na linha existente
if ($updateId > 0 && $resposta !== '') {
    $upd = $con->prepare("UPDATE chat_log SET resposta = ? WHERE id = ? AND id_usuario = ?");
    $upd->bind_param('sii', $resposta, $updateId, $idUsuario);
    if ($upd->execute()) {
        echo json_encode(['ok' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'erro' => 'Falha ao atualizar resposta']);
    }
    exit;
}

// 2) Inserções
try {
    $stmt = $con->prepare("INSERT INTO chat_log (id_usuario, pergunta, resposta, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
    $p = $pergunta !== '' ? $pergunta : '';
    $r = $resposta !== '' ? $resposta : '';
    $stmt->bind_param('iss', $idUsuario, $p, $r);
    if ($stmt->execute()) {
        $insertId = $con->insert_id;
        echo json_encode(['ok' => true, 'id' => $insertId]);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'erro' => 'Falha ao gravar no banco']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
