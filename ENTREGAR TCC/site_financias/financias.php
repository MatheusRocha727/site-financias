<!DOCTYPE html>
<!-- Mesclado: site financeiro + chatbot -->
<html lang="pt-br" data-bs-theme="light">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Finanças Residenciais</title>
  <link href="bootstrap.min.css" rel="stylesheet">
  <link href="bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="style_save2.css">

  <!-- CSS do chatbot (copiado de cha_boo.html) -->
  <style>
    :root{
      --brand-primary:#03223f;
      --brand-accent:#9dc34a;
      --ink:#0e1a1e;
      --muted:#64707a;
      --surface:#ffffff;
      --bg:#f6f8f7;
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
      box-shadow: 0 8px 25px rgba(3,34,63,.25);
      z-index: 9999;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      transition: height 0.2s ease;
    }

    #chat-widget.collapsed { height: 64px; }
    #chat-widget.collapsed .body,
    #chat-widget.collapsed .footer { display: none; }

    .shell{ width:100%; height:100%; background:var(--surface); border-radius:18px; overflow:hidden; display:flex; flex-direction:column; }
    .hero{ display:flex; align-items:center; gap:16px; padding:12px 12px 8px 12px; border-bottom:1px solid #eef1f0; }
    .hero .avatar{ width:40px; height:40px; border-radius:50%; display:grid; place-items:center; background:#eef6ea; border:2px solid var(--brand-accent); overflow:hidden; flex-shrink:0; }
    .hero img{ width:100%; height:100%; object-fit:cover; }
    .hero .title{ font-weight:700; color:var(--brand-primary); margin:0; font-size:0.95rem; }
    .hero .subtitle{ margin:2px 0 0 0; color:var(--muted); font-size:.75rem; }
    .body{ padding:8px 12px 0 12px; flex:1 1 auto; min-height:0; }
    .chat{ height:100%; max-height:280px; overflow:auto; padding:4px 0 8px 0; }
    .msg{ display:flex; gap:8px; margin-bottom:10px; font-size:.85rem; }
    .msg .bubble{ padding:8px 10px; border-radius:14px; background:#f2f6f3; border:1px solid #e3eee5; max-width:85%; white-space:pre-wrap; }
    .msg.me .bubble{ background:#e7f2d3; border-color:#d4e7b3; }
    .msg .who{ width:24px; height:24px; flex:0 0 24px; border-radius:50%; background:#eaf4e0; display:grid; place-items:center; border:1px solid #d9ead1; font-size:.65rem; color:#3a5f1d; }
    .footer{ padding:8px 12px 12px 12px; border-top:1px solid #eef1f0; background:linear-gradient(to top, #fafcfb, #ffffff); }
    .btn-primary{ --bs-btn-bg:var(--brand-primary); --bs-btn-border-color:var(--brand-primary); --bs-btn-hover-bg:#021c34; --bs-btn-hover-border-color:#021c34; }
    .small-muted{ color:var(--muted); font-size:.75rem; }
    #toggle-window { border-radius:999px; padding:2px 8px; font-size:0.75rem; line-height:1; }
  </style>

</head>

<body>
  <!-- NAV -->
  <nav class="navbar navbar-expand-lg bg-body border-bottom sticky-top">
    <div class="container">
      <a class="navbar-brand fw-bold" onclick="location.href='financias.php#'"><i class="bi bi-wallet2 me-2"></i>Finanças Residenciais</a>
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
          <div class="ms-3 form-check form-switch">
            <input class="form-check-input" type="checkbox" id="themeToggle">
            <label class="form-check-label" for="themeToggle"><i class="bi bi-moon-stars"></i></label>
          </div>
        </div>
      </div>
  </nav>
  <!-- /NAV -->

  <main class="container mt-4">
    <!-- Conteúdo do FR_save2.html (site financeiro) -->

    <!-- Competência -->
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
      <div class="input-group" style="max-width:420px">
        <span class="input-group-text"><i class="bi bi-calendar3"></i> Competência</span>
        <input type="month" class="form-control" id="competencia" data-last="2025-05">
      </div>
      <button class="btn btn-outline-secondary" id="btnDuplicarPeriodo">
        <i class="bi bi-files"></i> Duplicar do mês anterior
      </button>
      <span class="text-secondary small ms-1" id="competenciaHint">Salvo em 05/11/2025, 19:14:44</span>
    </div>

    <!-- Aviso -->
    <div class="alert alert-info d-flex align-items-start" role="alert">
      <i class="bi bi-info-circle fs-4 me-2"></i>
      <div>
        Preencha sua renda mensal e adicione despesas fixas/variáveis. <br> <strong>Todos os valores são considerados em Real (R$).</strong>
      </div>
    </div>

    <!-- Orçamento -->
    <section id="orcamento" class="mb-5">
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
                <input type="text" inputmode="decimal" class="form-control money" id="renda" placeholder="Ex.: 4500,00" value="">
              </div>
              <div class="mb-2 d-flex gap-2">
                <button class="btn btn-outline-primary" id="btnCalcularOrcamento"><i class="bi bi-calculator"></i> Calcular orçamento</button>
                <button class="btn btn-outline-secondary" id="btnLimpar"><i class="bi bi-eraser"></i> Limpar</button>
              </div>
              <small class="text-secondary">Obs: Use vírgula para centavos (ex.: 1.234,56).</small>
            </div>
          </div>
        </div>

        <div class="col-lg-7">
          <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
              <div><span class="badge-dot bg-danger"></span><strong>Despesas</strong></div>
              <div class="btn-group">
                <button class="btn btn-sm btn-outline-success" id="addFixa"><i class="bi bi-plus-circle"></i> Fixa</button>
                <button class="btn btn-sm btn-outline-warning" id="addVariavel"><i class="bi bi-plus-circle"></i> Variável</button>
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
                    <tr>
                      <td>
                        <select class="form-select tipo">
                          <option selected>Fixa</option>
                          <option>Variável</option>
                        </select>
                      </td>
                      <td><input type="text" class="form-control desc" placeholder="Ex.: Aluguel / Mercado" value="Aluguel"></td>
                      <td><input type="text" class="form-control money valor" placeholder="0,00" value="500,00"></td>
                      <td class="text-end"><button class="btn btn-sm btn-outline-danger remover"><i class="bi bi-trash"></i></button></td>
                    </tr>
                    <tr>
                      <td>
                        <select class="form-select tipo">
                          <option>Fixa</option>
                          <option selected>Variável</option>
                        </select>
                      </td>
                      <td><input type="text" class="form-control desc" placeholder="Ex.: Aluguel / Mercado" value="Luz"></td>
                      <td><input type="text" class="form-control money valor" placeholder="0,00" value="343,80"></td>
                      <td class="text-end"><button class="btn btn-sm btn-outline-danger remover"><i class="bi bi-trash"></i></button></td>
                    </tr>
                    <tr>
                      <td>
                        <select class="form-select tipo">
                          <option>Fixa</option>
                          <option selected>Variável</option>
                        </select>
                      </td>
                      <td><input type="text" class="form-control desc" placeholder="Ex.: Aluguel / Mercado" value="Água"></td>
                      <td><input type="text" class="form-control money valor" placeholder="0,00" value="478,26"></td>
                      <td class="text-end"><button class="btn btn-sm btn-outline-danger remover"><i class="bi bi-trash"></i></button></td>
                    </tr>
                    <tr>
                      <td>
                        <select class="form-select tipo">
                          <option>Fixa</option>
                          <option selected>Variável</option>
                        </select>
                      </td>
                      <td><input type="text" class="form-control desc" placeholder="Ex.: Aluguel / Mercado" value="Mercado"></td>
                      <td><input type="text" class="form-control money valor" placeholder="0,00" value="554,93"></td>
                      <td class="text-end"><button class="btn btn-sm btn-outline-danger remover"><i class="bi bi-trash"></i></button></td>
                    </tr>
                    <tr>
                      <td>
                        <select class="form-select tipo">
                          <option selected>Fixa</option>
                          <option>Variável</option>
                        </select>
                      </td>
                      <td><input type="text" class="form-control desc" placeholder="Ex.: Aluguel / Mercado" value="Transporte"></td>
                      <td><input type="text" class="form-control money valor" placeholder="0,00" value="100,00"></td>
                      <td class="text-end"><button class="btn btn-sm btn-outline-danger remover"><i class="bi bi-trash"></i></button></td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr>
                      <th colspan="2" class="text-end">Total Despesas:</th>
                      <th><span id="totalDespesas" class="currency">R$&nbsp;1.976,99</span></th>
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
                      <div id="saldo" class="fs-4">R$&nbsp;423,01</div>
                      <small id="saldoHint" class="text-secondary">Bom! Há sobra para poupança.</small>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="card border-0 bg-light">
                    <div class="card-body">
                      <div class="fw-semibold text-secondary">Taxa de poupança</div>
                      <div id="taxaPoupanca" class="fs-5">17,6%</div>
                      <small class="text-secondary">Meta sugerida: 10–20%</small>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="card border-0 bg-light">
                    <div class="card-body">
                      <div class="fw-semibold text-secondary">Reserva de emergência</div>
                      <div id="reserva" class="fs-6">R$&nbsp;5.930,97</div>
                      <small class="text-secondary">Sugestão: 3–6 meses de despesas</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Poupança -->
    <section id="poupanca" class="mb-5">
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
              <button class="btn btn-primary" id="btnCalcularPoupanca"><i class="bi bi-hourglass-split"></i> Calcular
                tempo</button>
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

      <!-- Empréstimo -->
      <section id="emprestimo" class="mb-5">
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
                <button class="btn btn-warning" id="btnCalcularEmprestimo"><i class="bi bi-cash-coin"></i> Calcular
                  parcelas</button>
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
              
              <small class="text-secondary d-block mt-2">Observação: simulação educativa. Consulte custos adicionais (IOF,
                seguros, tarifas) com sua instituição.</small>
              </div>
            </div>
          </section>
          <!-- /Empréstimo -->

    <!-- Poupança, Empréstimo, Resumo, Relatórios, Gráficos continuam abaixo -->

    <!-- Resumo -->
    <section id="resumo">
      <div class="row g-4">
        <div class="col-lg-6">
          <div class="card shadow-sm">
            <div class="card-header"><strong>Resumo financeiro</strong></div>
            <div class="card-body">
              <ul class="list-group list-group-flush" id="listaResumo">
                <li class="list-group-item d-flex justify-content-between"><span>Renda mensal</span><span id="rendaResumo">R$&nbsp;2.400,00</span></li>
                <li class="list-group-item d-flex justify-content-between"><span>Total de despesas</span><span id="despesasResumo">R$&nbsp;1.976,99</span></li>
                <li class="list-group-item d-flex justify-content-between"><span>Saldo</span><span id="saldoResumo">R$&nbsp;423,01</span></li>
                <li class="list-group-item d-flex justify-content-between"><span>DTI (Despesas / Renda)</span><span id="dtiResumo">82,4%</span></li>
              </ul>
              <div class="mt-3"><button class="btn btn-outline-secondary btn-sm" id="btnExportar"><i class="bi bi-download"></i> Exportar .CSV</button></div>
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

    <!-- Relatórios (exemplo resumido) -->
    <section id="relatorios" class="mt-5 mb-5">
      <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center"><span class="badge-dot bg-secondary"></span><strong>Relatórios em PDF</strong></div>
        <div class="card-body">
          <div class="row g-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label">Competência para PDF do mês</label>
              <select id="relatorioMesSelect" class="form-select">
                <option value="2025-01">2025-01</option>
                <option value="2025-02">2025-02</option>
                <option value="2025-03">2025-03</option>
                <option value="2025-04">2025-04</option>
                <option value="2025-05">2025-05</option>
                <option value="2025-06">2025-06</option>
                <option value="2025-07">2025-07</option>
                <option value="2025-08">2025-08</option>
                <option value="2025-09">2025-09</option>
                <option value="2025-10">2025-10</option>
                <option value="2025-11">2025-11</option>
                <option value="2025-12">2025-12</option>
              </select>
            </div>
            <div class="col-md-8 d-flex gap-2 flex-wrap"><button id="btnPdfMes" class="btn btn-outline-primary"><i class="bi bi-filetype-pdf"></i> Gerar PDF do mês</button>
              <button id="btnPdfGeral" class="btn btn-primary"><i class="bi bi-files"></i> Gerar PDF geral (todos os meses)</button>
            </div>
          </div>
          <small class="text-secondary d-block mt-2">Os PDFs são gerados localmente (navegador) com base nos dados salvos por competência.</small>
        </div>
      </div>
    </section>

    <!-- Gráficos (resumido) -->
    <section id="graficos" class="mb-5">
      <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center"><span class="badge-dot bg-info"></span><strong>Dashboards</strong></div>
        <div class="card-body">
          <div class="row g-3 align-items-end mb-3">
            <div class="col-md-4">
              <label class="form-label">Competência (pizza)</label>
              <select id="chartMesSelect" class="form-select">
                <option value="2025-01">2025-01</option>
                <option value="2025-02">2025-02</option>
                <option value="2025-03">2025-03</option>
                <option value="2025-04">2025-04</option>
                <option value="2025-05">2025-05</option>
                <option value="2025-06">2025-06</option>
                <option value="2025-07">2025-07</option>
                <option value="2025-08">2025-08</option>
                <option value="2025-09">2025-09</option>
                <option value="2025-10">2025-10</option>
                <option value="2025-11">2025-11</option>
                <option value="2025-12">2025-12</option>
              </select>
            </div>
          </div>
          <div class="row g-4">
            <div class="col-lg-7">
              <div class="card border-0 bg-light">
                <div class="card-body">
                  <div class="fw-semibold text-secondary mb-2">Escala mensal — Saldo por competência</div>
                  <canvas id="barSaldo" width="694" height="320"></canvas>
                </div>
              </div>
            </div>
            <div class="col-lg-5">
              <div class="card border-0 bg-light">
                <div class="card-body">
                  <div class="fw-semibold text-secondary mb-2">Pizza — Despesas por tipo (Fixa x Variável)</div>
                  <canvas id="pieTipo" width="479" height="320"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

  </main>

  <footer class="container mt-5 pt-3 border-top small text-secondary">Feito pela 352 Faetec ❤️. <span class="ms-2">© <span id="ano">2025</span></span></footer>

  <!-- Chatbot widget (inserido do cha_boo.html) -->
  <div id="chat-widget">
    <section class="shell" role="region" aria-label="FINC">
      <div class="hero">
        <div class="avatar"><img src="WhatsApp Image 2025-11-13 at 21.04.32.jpeg" alt="Agente de Locução"></div>
        <div class="flex-grow-1">
          <h1 class="title">Mini PiggMoneta</h1>
          <p class="subtitle">Seu assistente financeiro.</p>
        </div>
        <button id="toggle-window" class="btn btn-sm btn-light ms-auto" title="Minimizar/Maximizar">–</button>
      </div>
      <div class="body">
        <div id="chat" class="chat" aria-live="polite" aria-atomic="false">
          <div class="msg"><div class="who">IA</div><div class="bubble">Estou aqui para tirar suas dúvidas, por onde começamos?</div></div>
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

  <!-- Scripts: adiciona o Bootstrap bundle (JS) e mantém o script principal do site -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script src="script_save2.js"></script>

  <script>
    // Código do chatbot (copiado de cha_boo.html, sem o bootstrap bundle)
    const chatEl = document.getElementById('chat');
    const inputEl = document.getElementById('pergunta');
    const btnEl = document.getElementById('btn');
    const statusEl = document.getElementById('status');

    btnEl.addEventListener('click', onSubmit);
    inputEl.addEventListener('keydown', (e)=>{ if(e.key==='Enter') onSubmit(); });

    async function onSubmit(){
      const text = inputEl.value.trim();
      if(!text){ inputEl.focus(); return; }
      pushMessage('me', text);
      inputEl.value = '';
      lock(true, 'Processando…');
      try{
        const out = await callPoeAPI(text);
        pushMessage('ai', out || 'Sem conteúdo retornado.');
      }catch(err){
        console.error(err);
        pushMessage('ai', 'Falha na solicitação. Consulte o console.');
      }finally{ lock(false, ''); }
    }

    function pushMessage(who, text){
      const wrap = document.createElement('div');
      wrap.className = 'msg' + (who==='me' ? ' me' : '');
      wrap.innerHTML = `\n        <div class="who">${who==='me'?'Você':'IA'}</div>\n        <div class="bubble"></div>\n      `;
      wrap.querySelector('.bubble').textContent = text;
      chatEl.appendChild(wrap);
      chatEl.scrollTop = chatEl.scrollHeight;
    }

    function lock(state, msg=''){ btnEl.disabled = state; inputEl.disabled = state; statusEl.textContent = msg; }

    async function callPoeAPI(prompt, model='GPT-4o'){
      const POE_API_KEY = 'vj2kWqra9hpkDV3Oy-u-P_ShLVA4JiKY7OTOPuuzwDY'; // chave no front-end: não usar em produção
      const POE_API_URL = 'https://api.poe.com/v1/chat/completions';
      const resp = await fetch(POE_API_URL, {
        method:'POST',
        headers:{ 'Content-Type':'application/json', 'Authorization':`Bearer ${POE_API_KEY}` },
        body: JSON.stringify({ model, messages:[{ role:'user', content: prompt }] })
      });
      if(!resp.ok) throw new Error(`HTTP ${resp.status}`);
      const data = await resp.json();
      return data?.choices?.[0]?.message?.content ?? '';
    }

    const widget = document.getElementById('chat-widget');
    const toggleWindow = document.getElementById('toggle-window');
    toggleWindow.addEventListener('click', () => {
      widget.classList.toggle('collapsed');
      if (widget.classList.contains('collapsed')) { toggleWindow.textContent = '▲'; } else { toggleWindow.textContent = '–'; }
    });
  </script>

</body>
</html>
