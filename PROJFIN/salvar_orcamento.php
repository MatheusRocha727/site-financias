<?php
session_start();
require "conexao.php";

if (empty($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$idUsuario   = (int) $_SESSION['id_usuario'];
$competencia = $_POST['competencia'] ?? '';
$rendaStr    = $_POST['renda'] ?? '';

// Professor, aqui estão os arrays das despesas que não estão chegando corretamente
// os respectivos arrays não estão sendo recuperados do form html 
$tipos      = $_POST['tipo']      ?? [];
$descricoes = $_POST['descricao'] ?? [];
$valores    = $_POST['valor']     ?? [];

// Validação básica
if ($competencia === '' || $rendaStr === '') {
    // volta para a tela com aviso simples (pode sofisticar depois)
    echo "Preencha competência e renda.";
    exit;
}

// converte "1.234,56" -> 1234.56
$rendaStr = str_replace(['.', ','], ['', '.'], $rendaStr);
$renda    = (float) $rendaStr;

// TRANSAÇÃO para não deixar o banco pela metade
$con->begin_transaction();

try {
    // 1) apaga o que já existe para esse usuário + competência
    $delRec = $con->prepare("DELETE FROM orcamento_receita WHERE id_usuario = ? AND competencia = ?");
    $delRec->bind_param("is", $idUsuario, $competencia);
    $delRec->execute();

    $delDesp = $con->prepare("DELETE FROM orcamento_despesa WHERE id_usuario = ? AND competencia = ?");
    $delDesp->bind_param("is", $idUsuario, $competencia);
    $delDesp->execute();

    // 2) insere receita
    $insRec = $con->prepare("
        INSERT INTO orcamento_receita (id_usuario, competencia, renda)
        VALUES (?, ?, ?)
    ");
    $insRec->bind_param("isd", $idUsuario, $competencia, $renda);
    $insRec->execute();

    // 3) insere despesas
    $insDesp = $con->prepare("
        INSERT INTO orcamento_despesa (id_usuario, competencia, tipo, descricao, valor)
        VALUES (?, ?, ?, ?, ?)
    ");

    $countLinhas = min(count($tipos), count($descricoes), count($valores));

    for ($i = 0; $i < $countLinhas; $i++) {
        $tipo = trim($tipos[$i] ?? '');
        $desc = trim($descricoes[$i] ?? '');
        $val  = trim($valores[$i] ?? '');

        if ($desc === '' || $val === '') {
            continue; // pula linhas vazias
        }

        // converte "1.234,56" -> 1234.56
        $val = str_replace(['.', ','], ['', '.'], $val);
        $valFloat = (float) $val;

        if ($tipo !== 'Fixa' && $tipo !== 'Variável') {
            $tipo = 'Fixa'; // default
        }

        $insDesp->bind_param("isssd", $idUsuario, $competencia, $tipo, $desc, $valFloat);
        $insDesp->execute();
    }

    $con->commit();

    // volta para a tela já filtrando pela competência salva
    header("Location: financias.php?comp=" . urlencode($competencia));
    exit;

} catch (Throwable $e) {
    $con->rollback();
    // debug bruto – pra TCC em localhost está ok
    echo "Erro ao salvar orçamento: " . $e->getMessage();
    exit;
}
