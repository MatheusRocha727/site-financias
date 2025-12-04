<!-- Querido programador, quando escrevi este código, só Deus e eu sabíamos como ele funcionava.
Agora, só Deus sabe! Portanto, se você está tentando otimizar esta função e ela falhar
(o que é bem provável)
por favor aumente este contador como um aviso para a próxima pessoa:
total_horas_perdidas_aqui = 254 -->

<?php
session_start();

if (empty($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

require "conexao.php";

$idUsuario = $_SESSION['id_usuario'] ?? null;
if (!$idUsuario) {
  // Se o login não estiver jogando o id na sessão, arruma o login.php
  header("Location: login.php");
  exit;
}

// competência selecionada: GET ?comp=YYYY-MM ou padrão inicial (jan/2025)
$competenciaSel = $_GET['comp'] ?? '2025-01';

// função para formatar decimal para BR
function decimalToBr($valor)
{
  return number_format((float)$valor, 2, ',', '.');
}

// ===== CARREGAR RECEITA =====
$rendaNum = 0.0;
$rendaBr  = "";
$stmtR = $con->prepare("
    SELECT renda
    FROM orcamento_receita
    WHERE id_usuario = ? AND competencia = ?
    ORDER BY id DESC
    LIMIT 1
");
$stmtR->bind_param("is", $idUsuario, $competenciaSel);
$stmtR->execute();
$resR = $stmtR->get_result();
if ($rowR = $resR->fetch_assoc()) {
  $rendaNum = (float)$rowR['renda'];
  $rendaBr  = decimalToBr($rendaNum);
}

// ===== CARREGAR DESPESAS =====
$despesas = [];
$totalDespesasNum = 0.0;

$stmtD = $con->prepare("
    SELECT tipo, descricao, valor
    FROM orcamento_despesa
    WHERE id_usuario = ? AND competencia = ?
    ORDER BY id
");
$stmtD->bind_param("is", $idUsuario, $competenciaSel);
$stmtD->execute();
$resD = $stmtD->get_result();
while ($rowD = $resD->fetch_assoc()) {
  $despesas[] = $rowD;
  $totalDespesasNum += (float)$rowD['valor'];
}

$saldoNum  = $rendaNum - $totalDespesasNum;
$dti       = $rendaNum > 0 ? ($totalDespesasNum / $rendaNum * 100) : 0;
$reserva3m = $totalDespesasNum * 3;

$totalDespesasBr = decimalToBr($totalDespesasNum);
$saldoBr         = decimalToBr($saldoNum);
$dtiFmt          = number_format($dti, 1, ',', '');
$reservaBr       = decimalToBr($reserva3m);
$rendaResumoBr   = $rendaBr !== "" ? $rendaBr : "0,00";

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Finanças Residenciais</title>
  <link href="bootstrap.min.css" rel="stylesheet">
  <link href="bootstrap-icons.css" rel="stylesheet">
  <!-- Inline styles from style_save2.css inserted below -->

  <style>
    :root {
      --brand-primary: #03223f;
      --brand-accent: #9dc34a;
      --ink: #0e1a1e;
      --muted: #64707a;
      --surface: #ffffff;
      --bg: #f6f8f7;
    }

    #chat-widget {
      position: fixed;
      bottom: 20px;
      right: 20px;
      width: 320px;
      height: 450px;
      background: var(--surface);
      border: 1px solid #e3e7e5;
      border-radius: 16px;
      box-shadow: 0 8px 25px rgba(3, 34, 63, .25);
      z-index: 9999;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      transition: height 0.2s ease;
    }

    #chat-widget.collapsed {
      height: 64px;
    }

    #chat-widget.collapsed .body,
    #chat-widget.collapsed .footer {
      display: none;
    }

    .shell {
      width: 100%;
      height: 100%;
      background: var(--surface);
      border-radius: 18px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }

    .hero {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 12px 12px 8px 12px;
      border-bottom: 1px solid #eef1f0;
    }

    .hero .avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: grid;
      place-items: center;
      background: #eef6ea;
      border: 2px solid var(--brand-accent);
      overflow: hidden;
      flex-shrink: 0;
    }

    .hero img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .hero .title {
      font-weight: 700;
      color: var(--brand-primary);
      margin: 0;
      font-size: 0.95rem;
    }

    .hero .subtitle {
      margin: 2px 0 0 0;
      color: var(--muted);
      font-size: .75rem;
    }

    .body {
      padding: 8px 12px 0 12px;
      flex: 1 1 auto;
      min-height: 0;
    }

    .chat {
      height: 100%;
      max-height: 280px;
      overflow: auto;
      padding: 4px 0 8px 0;
    }

    .msg {
      display: flex;
      gap: 8px;
      margin-bottom: 10px;
      font-size: .85rem;
    }

    .msg .bubble {
      padding: 8px 10px;
      border-radius: 14px;
      background: #f2f6f3;
      border: 1px solid #e3eee5;
      max-width: 85%;
      white-space: pre-wrap;
    }

    .msg.me .bubble {
      background: #e7f2d3;
      border-color: #d4e7b3;
    }

    .msg .who {
      width: 24px;
      height: 24px;
      flex: 0 0 24px;
      border-radius: 50%;
      background: #eaf4e0;
      display: grid;
      place-items: center;
      border: 1px solid #d9ead1;
      font-size: .65rem;
      color: #3a5f1d;
    }

    .footer {
      padding: 8px 12px 12px 12px;
      border-top: 1px solid #eef1f0;
      background: linear-gradient(to top, #fafcfb, #ffffff);
    }

    .btn-primary {
      --bs-btn-bg: var(--brand-primary);
      --bs-btn-border-color: var(--brand-primary);
      --bs-btn-hover-bg: #021c34;
      --bs-btn-hover-border-color: #021c34;
    }

    .small-muted {
      color: var(--muted);
      font-size: .75rem;
    }

    .topic {
      padding: 8px 10px;
      border-left: 4px solid var(--brand-accent);
      background: #eef8e8;
      color: var(--brand-primary);
      border-radius: 6px;
      margin: 6px 0;
      font-weight: 700;
    }

    /* (modo escuro removido) */

    #toggle-window {
      border-radius: 999px;
      padding: 2px 8px;
      font-size: 0.75rem;
      line-height: 1;
    }

    /* Begin styles moved from style_save2.css */
    body { padding-bottom: 3rem; }
    .currency::before { content: "R$ "; color: var(--bs-secondary); }
    .table input { min-width: 120px; }
    .badge-dot { height: .6rem; width: .6rem; display: inline-block; border-radius: 50%; margin-right: .35rem; }
    /* ajuste visual dos gráficos */
    #barSaldo, #pieTipo { max-height: 320px; }
    /* End styles moved from style_save2.css */
  </style>
</head>

<body>

  <nav class="navbar navbar-expand-lg bg-body border-bottom sticky-top"> <!-- NAV -->
    <div class="container">
      <a class="navbar-brand fw-bold" onclick="location.href='financias.php'">
        <i class="bi bi-wallet2 me-2"></i>Finanças Residenciais
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div id="nav" class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><button class="nav-link" onclick="location.href='financias.php#orcamento'">Orçamento</button></li>
          <li class="nav-item"><button class="nav-link" onclick="location.href='financias.php#poupanca'">Poupança</button></li>
          <li class="nav-item"><button class="nav-link" onclick="location.href='financias.php#emprestimo'">Empréstimo</button></li>
          <li class="nav-item"><button class="nav-link" onclick="location.href='financias.php#resumo'">Resumo</button></li>
          <li class="nav-item"><button class="nav-link" onclick="location.href='financias.php#relatorios'">Relatórios</button></li>
          <li class="nav-item"><button class="nav-link" onclick="location.href='financias.php#graficos'">Gráficos</button></li>
        </ul>

        <!-- Usuário logado -->
        <span class="navbar-text ms-2 small text-secondary">
          Logado como: <?php echo strtolower(htmlspecialchars($_SESSION['usuario'])); ?>
        </span>

        <!-- Link sair ao lado do usuário logado -->
        <a class="nav-link text-danger ms-3" href="logout.php">Sair</a>
        
      </div>
    </div>
  </nav>
  <!-- /NAV -->

  <main class="container mt-4">


    <div class="alert alert-info d-flex align-items-start" role="alert"> <!-- Aviso -->
      <i class="bi bi-info-circle fs-4 me-2"></i>
      <div>
        Preencha sua renda mensal e adicione despesas fixas/variáveis. <br>
        <strong>Todos os valores são considerados em Real (R$).</strong>
      </div>
    </div>
    <!-- Aviso -->


    <section id="orcamento" class="mb-5"> <!-- Orçamento -->
      <form action="salvar_orcamento.php" method="POST">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
          <div class="input-group" style="max-width:420px">
            <span class="input-group-text"><i class="bi bi-calendar3"></i> Competência</span>
            <input type="month"
              class="form-control"
              id="competencia"
              name="competencia"
              value="<?php echo htmlspecialchars($competenciaSel); ?>">
          </div>
          <!-- Botão para duplicar o mês anterior -->
          <button id="btnDuplicarPeriodo" class="btn btn-outline-secondary btn-sm ms-2">
            <i class="bi bi-arrow-repeat"></i> Duplicar do mês anterior
          </button>

          <span class="text-secondary small ms-1" id="competenciaHint">
            Competência atual: <?php echo htmlspecialchars($competenciaSel); ?>
          </span>
        </div>

        <div class="row g-4">
          <div class="col-lg-5">
            <div class="card shadow-sm">
              <div class="card-header d-flex align-items-center">
                <span class="badge-dot bg-success"></span>
                <strong>Receitas</strong>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <label class="form-label">Renda mensal líquida (R$)</label>
                  <input type="text"
                    inputmode="decimal"
                    class="form-control money"
                    id="renda"
                    name="renda"
                    placeholder="Ex.: 4500,00"
                    value="<?php echo htmlspecialchars($rendaBr); ?>">
                </div>
                <div class="mb-2 d-flex gap-2">
                  <button type="button" class="btn btn-outline-primary" id="btnCalcularOrcamento">
                    <i class="bi bi-calculator"></i> Calcular orçamento
                  </button>
                  <button type="button" class="btn btn-outline-secondary" id="btnLimpar">
                    <i class="bi bi-eraser"></i> Limpar
                  </button>
                </div>
                <small class="text-secondary">Obs: Use vírgula para centavos (ex.: 1.234,56).</small>
              </div>
            </div>
          </div>

          <div class="col-lg-7">
            <div class="card shadow-sm">
              <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                  <span class="badge-dot bg-danger"></span><strong>Despesas</strong>
                </div>
                <div class="btn-group">
                  <button type="button" class="btn btn-sm btn-outline-success" id="addFixa">
                    <i class="bi bi-plus-circle"></i> Fixa
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-warning" id="addVariavel">
                    <i class="bi bi-plus-circle"></i> Variável
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table align-middle" id="tabelaDespesas">
                    <thead>
                      <tr>
                        <th style="width:25%">Tipo</th>
                        <th>Descrição</th>
                        <th style="width:25%">Valor (R$)</th>
                        <th style="width:1%"></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($despesas)): ?>
                        <?php foreach ($despesas as $d): ?>
                          <tr>
                            <td>
                              <select class="form-select tipo" name="tipo[]">
                                <option value="Fixa" <?php echo ($d['tipo'] === 'Fixa') ? 'selected' : ''; ?>>Fixa</option>
                                <option value="Variável" <?php echo ($d['tipo'] === 'Variável') ? 'selected' : ''; ?>>Variável</option>
                              </select>
                            </td>
                            <td>
                              <input type="text"
                                class="form-control desc"
                                name="descricao[]"
                                value="<?php echo htmlspecialchars($d['descricao']); ?>"
                                placeholder="Ex.: Aluguel / Mercado">
                            </td>
                            <td>
                              <input type="text"
                                class="form-control money valor"
                                name="valor[]"
                                value="<?php echo htmlspecialchars(decimalToBr($d['valor'])); ?>"
                                placeholder="0,00">
                            </td>
                            <td class="text-end">
                              <button type="button" class="btn btn-sm btn-outline-danger remover">
                                <i class="bi bi-trash"></i>
                              </button>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <!-- fallback se não tiver nada no banco -->
                        <tr>
                          <td>
                            <select class="form-select tipo" name="tipo[]">
                              <option selected>Fixa</option>
                              <option>Variável</option>
                            </select>
                          </td>
                          <td>
                            <input type="text"
                              class="form-control desc"
                              name="descricao[]"
                              placeholder="Ex.: Aluguel / Mercado"
                              value="Aluguel">
                          </td>
                          <td>
                            <input type="text"
                              class="form-control money valor"
                              name="valor[]"
                              placeholder="0,00"
                              value="500,00">
                          </td>
                          <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger remover">
                              <i class="bi bi-trash"></i>
                            </button>
                          </td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <th colspan="2" class="text-end">Total Despesas:</th>
                        <th><span id="totalDespesas" class="currency">R$ <?php echo $totalDespesasBr; ?></span></th>
                        <th></th>
                      </tr>
                    </tfoot>
                  </table>
                </div>

                <div class="row g-3">
                  <div class="col-md-4">
                    <div class="card border-0 bg-light">
                      <div class="card-body">
                        <div class="fw-semibold text-secondary">Saldo mensal</div>
                        <div id="saldo" class="fs-4">R$ <?php echo $saldoBr; ?></div>
                        <small id="saldoHint" class="text-secondary">
                          <?php echo ($saldoNum >= 0) ? 'Bom! Há sobra para poupança.' : 'Atenção: despesas maiores que renda.'; ?>
                        </small>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="card border-0 bg-light">
                      <div class="card-body">
                        <div class="fw-semibold text-secondary">Taxa de poupança</div>
                        <div id="taxaPoupanca" class="fs-5">
                          <?php echo ($rendaNum > 0) ? (100 - $dtiFmt) . '%' : '0%'; ?>
                        </div>
                        <small class="text-secondary">Meta sugerida: 10–20%</small>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="card border-0 bg-light">
                      <div class="card-body">
                        <div class="fw-semibold text-secondary">Reserva de emergência</div>
                        <div id="reserva" class="fs-6">R$ <?php echo $reservaBr; ?></div>
                        <small class="text-secondary">Sugestão: 3–6 meses de despesas</small>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Botão que salva no banco -->
                <div class="mt-3 d-flex justify-content-end">
                  <button type="submit" class="btn btn-success" name="salvar_orcamento">
                    <i class="bi bi-save"></i> Salvar orçamento
                  </button>
                </div>

              </div>
            </div>
          </div>
        </div>
      </form>
    </section>
    <!-- /Orçamento -->

    <section id="poupanca" class="mb-5"> <!-- Poupança -->
      <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center">
          <span class="badge-dot bg-primary"></span>
          <strong>Meta de Poupança</strong>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Meta (R$)</label>
              <input type="text" class="form-control money" id="metaValor" placeholder="Ex.: 10000,00">
            </div>
            <div class="col-md-3">
              <label class="form-label">Aporte mensal (R$)</label>
              <input type="text" class="form-control money" id="aporteMensal" placeholder="Ex.: 500,00">
            </div>
            <div class="col-md-3">
              <label class="form-label">Rendimento ao mês (%)</label>
              <input type="text" class="form-control percent" id="rendimentoMes" placeholder="Ex.: 0,7">
            </div>
            <div class="col-md-3 d-grid">
              <label class="form-label invisible">.</label>
              <button class="btn btn-primary" id="btnCalcularPoupanca">
                <i class="bi bi-hourglass-split"></i> Calcular tempo
              </button>
            </div>
          </div>
          <div class="mt-3">
            <div><strong>Resultado:</strong> <span id="poupancaResultado">—</span></div>
            <small class="text-secondary">Fórmula: soma de série com juros compostos mensais.</small>
          </div>
        </div>
      </div>
    </section>
    <!-- /Poupança -->


    <section id="emprestimo" class="mb-5"> <!-- Empréstimo -->
      <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center">
          <span class="badge-dot bg-warning"></span>
          <strong>Simulador de Empréstimo (Tabela Price)</strong>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Valor (R$)</label>
              <input type="text" class="form-control money" id="loanValor" placeholder="Ex.: 20000,00">
            </div>
            <div class="col-md-3">
              <label class="form-label">Taxa ao mês (%)</label>
              <input type="text" class="form-control percent" id="loanJurosMes" placeholder="Ex.: 2,0">
            </div>
            <div class="col-md-3">
              <label class="form-label">Prazo (meses)</label>
              <input type="number" class="form-control" id="loanMeses" placeholder="Ex.: 36" min="1">
            </div>
            <div class="col-md-3 d-grid">
              <label class="form-label invisible">.</label>
              <button class="btn btn-warning" id="btnCalcularEmprestimo">
                <i class="bi bi-cash-coin"></i> Calcular parcelas
              </button>
            </div>
          </div>
          <div class="row g-3 mt-3">
            <div class="col-md-4">
              <div class="card border-0 bg-light">
                <div class="card-body">
                  <div class="fw-semibold text-secondary">Parcela</div>
                  <div id="loanParcela" class="fs-4">R$ 0,00</div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card border-0 bg-light">
                <div class="card-body">
                  <div class="fw-semibold text-secondary">Total pago</div>
                  <div id="loanTotalPago" class="fs-5">R$ 0,00</div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card border-0 bg-light">
                <div class="card-body">
                  <div class="fw-semibold text-secondary">Juros totais</div>
                  <div id="loanJurosTotais" class="fs-6">R$ 0,00</div>
                </div>
              </div>
            </div>
          </div>
          <small class="text-secondary d-block mt-2">
            Observação: simulação educativa. Consulte custos adicionais (IOF, seguros, tarifas) com sua instituição.
          </small>
        </div>
      </div>
    </section>
    <!-- /Empréstimo -->


    <section id="resumo"> <!-- Resumo -->
      <div class="row g-4">
        <div class="col-lg-6">
          <div class="card shadow-sm">
            <div class="card-header"><strong>Resumo financeiro</strong></div>
            <div class="card-body">
              <ul class="list-group list-group-flush" id="listaResumo">
                <li class="list-group-item d-flex justify-content-between">
                  <span>Renda mensal</span>
                  <span id="rendaResumo">R$ <?php echo $rendaResumoBr; ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  <span>Total de despesas</span>
                  <span id="despesasResumo">R$ <?php echo $totalDespesasBr; ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  <span>Saldo</span>
                  <span id="saldoResumo">R$ <?php echo $saldoBr; ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  <span>DTI (Despesas / Renda)</span>
                  <span id="dtiResumo"><?php echo $rendaNum > 0 ? $dtiFmt . '%' : '0%'; ?></span>
                </li>
              </ul>
              <div class="mt-3">
                <button class="btn btn-outline-secondary btn-sm" id="btnExportar">
                  <i class="bi bi-download"></i> Exportar .CSV
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card shadow-sm">
            <div class="card-header"><strong>Dicas rápidas</strong></div>
            <div class="card-body">
              <ul class="mb-0">
                <li>Mire guardar <strong>10–20% da renda</strong> todo mês.</li>
                <li>Monte uma <strong>reserva de emergência</strong> de 3–6 meses das despesas.</li>
                <li>Se o <strong>DTI</strong> passar de 50–60%, reavalie despesas e dívidas.</li>
                <li>Revise assinaturas e gastos variáveis com frequência.</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- /Resumo -->


    <section id="relatorios" class="mt-5 mb-5"> <!-- Relatórios -->
      <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center">
          <span class="badge-dot bg-secondary"></span><strong>Relatórios em PDF</strong>
        </div>
        <div class="card-body">
          <div class="row g-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label">Competência para PDF do mês</label>
              <select id="relatorioMesSelect" class="form-select">
                <?php for ($m = 1; $m <= 12; $m++):
                  $val = date('Y') . '-' . str_pad($m, 2, '0', STR_PAD_LEFT); ?>
                  <option value="<?php echo $val; ?>"><?php echo $val; ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="col-md-8 d-flex gap-2 flex-wrap">
              <button id="btnPdfMes" class="btn btn-outline-primary">
                <i class="bi bi-filetype-pdf"></i> Gerar PDF do mês
              </button>
              <button id="btnPdfGeral" class="btn btn-primary">
                <i class="bi bi-files"></i> Gerar PDF geral (todos os meses)
              </button>
            </div>
          </div>
          <small class="text-secondary d-block mt-2">
            Os PDFs são gerados localmente (navegador) com base nos dados salvos por competência.
          </small>
        </div>
      </div>
    </section>
    <!-- /Relatórios -->


    <section id="graficos" class="mb-5"> <!-- Gráficos -->
      <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center">
          <span class="badge-dot bg-info"></span><strong>Dashboards</strong>
        </div>
        <div class="card-body">
          <div class="row g-3 align-items-end mb-3">
            <div class="col-md-4">
              <label class="form-label">Competência (pizza)</label>
              <select id="chartMesSelect" class="form-select">
                <?php for ($m = 1; $m <= 12; $m++):
                  $val = date('Y') . '-' . str_pad($m, 2, '0', STR_PAD_LEFT); ?>
                  <option value="<?php echo $val; ?>"><?php echo $val; ?></option>
                <?php endfor; ?>
              </select>
            </div>
          </div>
          <div class="row g-4">
            <div class="col-lg-7">
              <div class="card border-0 bg-light">
                <div class="card-body">
                  <div class="fw-semibold text-secondary mb-2">
                    Escala mensal — Saldo por competência
                  </div>
                  <canvas id="barSaldo" width="694" height="320"></canvas>
                </div>
              </div>
            </div>
            <div class="col-lg-5">
              <div class="card border-0 bg-light">
                <div class="card-body">
                  <div class="fw-semibold text-secondary mb-2">
                    Pizza — Despesas por tipo (Fixa x Variável)
                  </div>
                  <canvas id="pieTipo" width="479" height="320"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- /Gráficos -->
  </main>

  <footer class="container mt-5 pt-3 border-top small text-secondary">
    Feito pela 352 Faetec ❤️.
    <span class="ms-2">© <span id="ano"><?php echo date('Y'); ?></span></span>
  </footer>


  <div id="chat-widget" class="chat-widget collapsed"> <!-- Chatbot (Gaston) -->
    <section class="shell" role="region" aria-label="FINC">
      <div class="hero">
        <div class="avatar">
          <img src="WhatsApp Image 2025-11-13 at 21.04.32.jpeg" alt="Agente de Locução">
        </div>
        <div class="flex-grow-1">
          <h1 class="title">Mini PiggMoneta</h1>
          <p class="subtitle">Seu assistente financeiro.</p>
        </div>
        <button id="toggle-window" class="btn btn-sm btn-light ms-auto" title="Minimizar/Maximizar">–</button>
      </div>
      <div class="body">
        <div id="chat" class="chat" aria-live="polite" aria-atomic="false">
          <div class="msg">
            <div class="who">IA</div>
            <div class="bubble">Estou aqui para tirar suas dúvidas, por onde começamos?</div>
          </div>
        </div>
      </div>
      <div class="footer">
        <div class="input-group input-group-sm">
          <input id="pergunta" type="text" class="form-control" placeholder="Digite aqui sua pergunta">
          <button id="btn" class="btn btn-primary">Enviar</button>
        </div>
        <div id="status" class="small-muted mt-1"></div>
      </div>
    </section>
  </div>
  <!-- /Chatbot (Gaston) -->

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <!-- jsPDF + AutoTable (para geração de PDF) -->
  <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.5.28/dist/jspdf.plugin.autotable.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script src="script_save2.js"></script>

  <script>
      // Intercepta o submit do formulário de orçamento para não recarregar a página
      document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form[action="salvar_orcamento.php"]');
        if (form) {
          form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = form.querySelector('button[name="salvar_orcamento"]');
            if (btn) btn.disabled = true;
            const formData = new FormData(form);
            try {
              const resp = await fetch('salvar_orcamento.php', {
                method: 'POST',
                body: formData
              });
              if (resp.ok) {
                alert('Orçamento salvo com sucesso!');
              } else {
                alert('Erro ao salvar orçamento.');
              }
            } catch (err) {
              alert('Erro de conexão ao salvar orçamento.');
            } finally {
              if (btn) btn.disabled = false;
            }
          });
        }
      });

    // Chatbot
    const chatEl = document.getElementById('chat');
    const inputEl = document.getElementById('pergunta');
    const btnEl = document.getElementById('btn');
    const statusEl = document.getElementById('status');

    // guarda id do registro de pergunta para atualizar com a resposta
    let lastQuestionId = null;

    btnEl.addEventListener('click', onSubmit);
    inputEl.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') onSubmit();
    });

    // >>> NÃO TIRA ISSO <<<
    function lock(state, msg = '') {
      btnEl.disabled = state;
      inputEl.disabled = state;
      statusEl.textContent = msg;
    }

    async function onSubmit() {
      const text = inputEl.value.trim();
      if (!text) {
        inputEl.focus();
        return;
      }
      pushMessage('me', text);
      inputEl.value = '';
      lock(true, 'Processando…');
      try {
        // salva imediatamente a pergunta e guarda o id retornado
        try {
          const resp = await fetch('salva_chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ pergunta: text })
          });
          if (resp.ok) {
            const jd = await resp.json().catch(() => ({}));
            lastQuestionId = jd.id || null;
          }
        } catch (e) {
          console.error('Erro ao salvar pergunta:', e);
        }

        const out = await callPoeAPI(text);
        const resposta = out || 'Sem conteúdo retornado.';

        pushMessage('ai', resposta);

        // atualiza registro anterior com a resposta (se tivermos id), caso contrário insere par completo
        try {
          if (lastQuestionId) {
            await fetch('salva_chat.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ update_id: lastQuestionId, resposta: resposta })
            });
            lastQuestionId = null;
          } else {
            await fetch('salva_chat.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ pergunta: text, resposta: resposta })
            });
          }
        } catch (e) {
          console.error('Erro ao salvar chat no banco:', e);
        }

      } catch (err) {
        console.error(err);
        pushMessage('ai', 'Falha na solicitação. Consulte o console.');
      } finally {
        lock(false, '');
      }
    }

    function renderWithBold(text) {
      if (text == null) return '';

      let safe = String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");

      // transforma linhas que começam com '###' em um bloco de tópico
      // exemplo: '### Meu Tópico' -> <div class="topic"><strong>Meu Tópico</strong></div>
      safe = safe.replace(/(^|\n)###\s*(.+?)(?=\n|$)/gm, (m, p1, title) => {
        return p1 + `<div class="topic"><strong>${title}</strong></div>`;
      });

      // negrito com **texto**
      safe = safe.replace(/\*\*(.+?)\*\*/g, "<strong>$1</strong>");

      // quebras de linha para <br>
      safe = safe.replace(/\n/g, '<br>');

      return safe;
    }

    function pushMessage(who, text) {
      const wrap = document.createElement('div');
      wrap.className = 'msg' + (who === 'me' ? ' me' : '');
      wrap.innerHTML = `
      <div class="who">${who === 'me' ? 'Você' : 'IA'}</div>
      <div class="bubble"></div>
    `;

      const bubble = wrap.querySelector('.bubble');

      if (who === 'ai') {
        bubble.innerHTML = renderWithBold(text);
      } else {
        bubble.textContent = text;
      }

      chatEl.appendChild(wrap);
      chatEl.scrollTop = chatEl.scrollHeight;
    }

    //A partezinha que faz o sobe e desce do chat
    async function callPoeAPI(prompt, model = 'GPT-4o') {
      const POE_API_KEY = 'vj2kWqra9hpkDV3Oy-u-P_ShLVA4JiKY7OTOPuuzwDY'; //chave do POE
      const POE_API_URL = 'https://api.poe.com/v1/chat/completions';
      const resp = await fetch(POE_API_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${POE_API_KEY}`
        },
        body: JSON.stringify({
          model,
          messages: [{
            role: 'user',
            content: prompt
          }]
        })
      });
      if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
      const data = await resp.json();
      return data?.choices?.[0]?.message?.content ?? '';
    }

    const widget = document.getElementById('chat-widget');
    const toggleWindow = document.getElementById('toggle-window');
    toggleWindow.addEventListener('click', () => {
      widget.classList.toggle('collapsed');
      if (widget.classList.contains('collapsed')) {
        toggleWindow.textContent = '▲';
      } else {
        toggleWindow.textContent = '▼';
      }
    });
  </script>


</body>

</html>