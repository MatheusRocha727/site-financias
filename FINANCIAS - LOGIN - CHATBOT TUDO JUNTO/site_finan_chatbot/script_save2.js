//   <script src="./Finanças Residenciais save2_files/bootstrap.bundle.min.js.baixados"></script>
//   <!-- libs para PDF e gráficos -->
//   <script src="./Finanças Residenciais save2_files/jspdf.umd.min.js.baixados"></script>
//   <script src="./Finanças Residenciais save2_files/jspdf.plugin.autotable.min.js.baixados"></script>
//   <script src="./Finanças Residenciais save2_files/chart.umd.min.js.baixados"></script>


  document.addEventListener('DOMContentLoaded', function init(){

    // ===== Utilidades =====
    const fmtBRL = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v || 0);
    const fmtPct  = (v) => (isFinite(v) ? (v*100).toFixed(1).replace('.', ',') + '%' : '—');
    const parseMoney = (str) => {
      if (!str) return 0;
      str = (''+str).trim().replace(/\s/g,'').replace(/\./g,'').replace(',', '.');
      const n = parseFloat(str);
      return isNaN(n) ? 0 : n;
    }
    const parsePercent = (str) => parseMoney(str) / 100;

    // debounce para evitar re-render frenético
    const debounce = (fn, ms=200) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

    // Tema
    const themeToggle = document.getElementById('themeToggle');
    const setTheme = (dark) => document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
    if (themeToggle) themeToggle.addEventListener('change', (e)=> setTheme(e.target.checked));

    // ===== Orçamento =====
    const tabelaWrap = document.getElementById('tabelaDespesas');
    if (!tabelaWrap) { console.error('Elemento tabelaDespesas não encontrado. Abortando script.'); return; }
    const tabela = tabelaWrap.querySelector('tbody');
    const totalDespesasEl = document.getElementById('totalDespesas');
    const rendaEl = document.getElementById('renda');
    const saldoEl = document.getElementById('saldo');
    const saldoHintEl = document.getElementById('saldoHint');
    const taxaPoupancaEl = document.getElementById('taxaPoupanca');
    const reservaEl = document.getElementById('reserva');

    const rendaResumo = document.getElementById('rendaResumo');
    const despesasResumo = document.getElementById('despesasResumo');
    const saldoResumo = document.getElementById('saldoResumo');
    const dtiResumo = document.getElementById('dtiResumo');

    const addRow = (tipo='Fixa', desc='', val='')=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>
          <select class="form-select tipo">
            <option ${tipo==='Fixa'?'selected':''}>Fixa</option>
            <option ${tipo==='Variável'?'selected':''}>Variável</option>
          </select>
        </td>
        <td><input type="text" class="form-control desc" placeholder="Ex.: Aluguel / Mercado" value="${desc}"></td>
        <td><input type="text" class="form-control money valor" placeholder="0,00" value="${val}"></td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-danger remover"><i class="bi bi-trash"></i></button>
        </td>
      `;
      tabela.appendChild(tr);
    };

    // Linhas iniciais
    addRow('Fixa','Aluguel','500,00');
    addRow('Variável','Luz','343,80');
    addRow('Variável','Água','478,26');
    addRow('Variável','Mercado','554,93');
    addRow('Fixa','Transporte','100,00');

    const calcTotais = ()=>{
      let total = 0;
      const linhas = [...tabela.querySelectorAll('tr')];
      linhas.forEach(tr=>{
        const val = parseMoney(tr.querySelector('.valor').value);
        total += val;
      });
      totalDespesasEl.textContent = fmtBRL(total);
      const renda = parseMoney(rendaEl.value);
      const saldo = renda - total;
      saldoEl.textContent = fmtBRL(saldo);
      saldoResumo.textContent = fmtBRL(saldo);
      rendaResumo.textContent = fmtBRL(renda);
      despesasResumo.textContent = fmtBRL(total);

      const dti = renda > 0 ? (total / renda) : 0;
      dtiResumo.textContent = fmtPct(dti);

      const taxaPoupa = renda > 0 ? (Math.max(0, saldo)/renda) : 0;
      taxaPoupancaEl.textContent = fmtPct(taxaPoupa);

      const reserva = total * 3;
      reservaEl.textContent = fmtBRL(reserva);

      saldoHintEl.textContent = saldo < 0 ? 'Atenção: saldo negativo.' :
        (saldo === 0 ? 'Saldo zerado.' : 'Bom! Há sobra para poupança.');
    };

    const elAddFixa = document.getElementById('addFixa'); if (elAddFixa) elAddFixa.addEventListener('click', ()=> addRow('Fixa','',''));
    const elAddVariavel = document.getElementById('addVariavel'); if (elAddVariavel) elAddVariavel.addEventListener('click', ()=> addRow('Variável','',''));
    const elBtnCalcOrc = document.getElementById('btnCalcularOrcamento'); if (elBtnCalcOrc) elBtnCalcOrc.addEventListener('click', calcTotais);
    const elBtnLimpar = document.getElementById('btnLimpar'); if (elBtnLimpar) elBtnLimpar.addEventListener('click', ()=>{
      rendaEl.value = '';
      tabela.innerHTML = '';
      addRow('Fixa','Aluguel','');
      addRow('Variável','Luz','');
      addRow('Variável','Água','');
      addRow('Variável','Mercado','');
      addRow('Fixa','Transporte','');
      calcTotais();
      savePeriodo(competenciaEl.value); // snapshot "limpo"
    });

    // Remover linha
    document.addEventListener('click', (e)=>{
      if (e.target.closest && e.target.closest('.remover')) {
        const tr = e.target.closest('tr'); if (tr) tr.remove();
        calcTotais();
        if (competenciaEl) savePeriodo(competenciaEl.value);
      }
    });

    // ===== Competência + persistência =====
    const competenciaEl = document.getElementById('competencia');
    const competenciaHintEl = document.getElementById('competenciaHint');

    const getCurrentYYYYMM = () => {
      const d = new Date();
      const m = String(d.getMonth()+1).padStart(2,'0');
      return `${d.getFullYear()}-${m}`;
    };
    const getYYYYMMPrev = (yyyyMM) => {
      const [y,m] = yyyyMM.split('-').map(Number);
      const d = new Date(y, m-2, 1);
      const mm = String(d.getMonth()+1).padStart(2,'0');
      return `${d.getFullYear()}-${mm}`;
    };
    const lsKey = (yyyyMM) => `fr:v2:${yyyyMM}`;

    const snapshotFromUI = () => {
      const despesas = [...tabela.querySelectorAll('tr')].map(tr => ({
        tipo: tr.querySelector('.tipo').value,
        desc: tr.querySelector('.desc').value,
        valor: tr.querySelector('.valor').value
      }));
      return { renda: rendaEl.value || '', despesas };
    };

    const loadSnapshotToUI = (snap) => {
      tabela.innerHTML = '';
      rendaEl.value = snap?.renda || '';
      (snap?.despesas?.length ? snap.despesas : [
        {tipo:'Fixa',desc:'Aluguel',valor:'500,00'},
        {tipo:'Variável',desc:'Luz',valor:'343,80'},
        {tipo:'Variável',desc:'Água',valor:'478,26'},
        {tipo:'Variável',desc:'Mercado',valor:'554,93'},
        {tipo:'Fixa',desc:'Transporte',valor:'100,00'},
      ]).forEach(r => addRow(r.tipo, r.desc, r.valor));
      calcTotais();
    };

    // ===== ADD-ONS: Relatórios (PDF) e Gráficos =====
    const allCompetencias = () => {
      const keys = Object.keys(localStorage).filter(k => k.startsWith('fr:v2:'));
      return keys.map(k => k.split(':').pop()).sort();
    };
    const getSnapshot = (yyyyMM) => {
      const raw = localStorage.getItem(lsKey(yyyyMM));
      return raw ? JSON.parse(raw) : null;
    };
    const resumoPeriodo = (yyyyMM) => {
      const snap = getSnapshot(yyyyMM);
      if (!snap) return { renda:0, despesas:0, saldo:0, fixas:0, variaveis:0 };
      const renda = parseMoney(snap.renda);
      let despesas = 0, fixas = 0, variaveis = 0;
      (snap.despesas||[]).forEach(d => {
        const v = parseMoney(d.valor);
        despesas += v;
        if ((d.tipo||'').toLowerCase().startsWith('fixa')) fixas += v; else variaveis += v;
      });
      const saldo = renda - despesas;
      return { renda, despesas, saldo, fixas, variaveis };
    };

    // Selects (PDF e Pizza) — preservando seleção do pizza
    const populatePeriodoSelects = () => {
      const meses = allCompetencias();
      const selPdf = document.getElementById('relatorioMesSelect');
      const selChart = document.getElementById('chartMesSelect');
      const currentChartVal = selChart?.value || competenciaEl.value;

      const makeOpts = (sel) => {
        if (!sel) return;
        sel.innerHTML = '';
        const base = meses.length ? meses : [competenciaEl.value];
        base.forEach(m => {
          const opt = document.createElement('option');
          opt.value = m; opt.textContent = m;
          sel.appendChild(opt);
        });
      };
      makeOpts(selPdf);
      makeOpts(selChart);

      if (selChart && [...selChart.options].some(o => o.value === currentChartVal)) {
        selChart.value = currentChartVal;
      }
    };

    // PDFs
    const { jsPDF } = window.jspdf || {};
    const pdfCabecalho = (doc, titulo) => {
      doc.setFontSize(14); doc.text('Finanças Residenciais', 14, 14);
      doc.setFontSize(11); doc.text(titulo, 14, 22);
    };
    const pdfTabelaDespesas = (doc, snap) => {
      const rows = (snap?.despesas||[]).map(d => [d.tipo||'', d.desc||'', (parseMoney(d.valor)||0).toFixed(2).replace('.',',')]);
      doc.autoTable({ startY: 30, head: [['Tipo','Descrição','Valor (R$)']], body: rows.length? rows : [['—','—','0,00']], styles:{fontSize:9}, headStyles:{fillColor:[13,110,253]} });
    };
    const gerarPdfMes = (yyyyMM) => {
      const snap = getSnapshot(yyyyMM) || { renda:'', despesas:[] };
      const r = resumoPeriodo(yyyyMM);
      const doc = new jsPDF();
      pdfCabecalho(doc, `Relatório do mês ${yyyyMM}`);
      pdfTabelaDespesas(doc, snap);
      let y = doc.lastAutoTable ? doc.lastAutoTable.finalY + 8 : 40;
      doc.setFontSize(11);
      doc.text(`Renda: ${fmtBRL(r.renda)}`, 14, y); y+=6;
      doc.text(`Despesas: ${fmtBRL(r.despesas)}`, 14, y); y+=6;
      doc.text(`Saldo: ${fmtBRL(r.saldo)}`, 14, y); y+=6;
      const dti = r.renda>0 ? (r.despesas/r.renda) : 0;
      doc.text(`DTI: ${fmtPct(dti)}`, 14, y);
      doc.save(`relatorio_${yyyyMM}.pdf`);
    };
    const gerarPdfGeral = () => {
      const meses = allCompetencias();
      const doc = new jsPDF();
      pdfCabecalho(doc, 'Relatório Geral por competência');
      const base = meses.length? meses : [competenciaEl.value];
      const body = base.map(m => { const r = resumoPeriodo(m); return [m, fmtBRL(r.renda), fmtBRL(r.despesas), fmtBRL(r.saldo), fmtPct(r.renda>0? r.despesas/r.renda : 0)]; });
      doc.autoTable({ startY: 30, head: [['Competência','Renda','Despesas','Saldo','DTI']], body, styles:{fontSize:9}, headStyles:{fillColor:[32,201,151]} });
      doc.save('relatorio_geral.pdf');
    };
    const btnPdfMes = document.getElementById('btnPdfMes'); if (btnPdfMes) btnPdfMes.addEventListener('click', ()=>{ const sel = document.getElementById('relatorioMesSelect'); gerarPdfMes(sel?.value || (competenciaEl?competenciaEl.value:'')); });
    const btnPdfGeral = document.getElementById('btnPdfGeral'); if (btnPdfGeral) btnPdfGeral.addEventListener('click', gerarPdfGeral);

    // Charts
    let barChart, pieChart;
    const renderBar = () => {
      const ctx = document.getElementById('barSaldo'); if (!ctx) return;
      const meses = allCompetencias();
      const axis = meses.length ? meses : [competenciaEl.value];
      const data = axis.map(m => {
        const v = resumoPeriodo(m).saldo;
        return Number.isFinite(v) ? v : 0;
      });
      if (barChart) barChart.destroy();
      // Define cores: verde para positivo, vermelho para negativo
      const bgColors = data.map(v => v < 0 ? 'rgba(220,53,69,0.85)' : 'rgba(40,167,69,0.85)');
      const borderColors = data.map(v => v < 0 ? 'rgba(220,53,69,1)' : 'rgba(40,167,69,1)');
      barChart = new Chart(ctx, {
        type:'bar',
        data:{ labels: axis, datasets:[{ label:'Saldo', data, backgroundColor: bgColors, borderColor: borderColors, borderWidth:1 }] },
        options:{
          responsive:true,
          plugins:{ legend:{ display:false } },
          onClick: (evt, elements) => {
            if (!elements || !elements.length) return;
            const el = elements[0];
            const idx = el.index;
            const label = barChart.data.labels[idx];
            if (label) loadPeriodo(label);
          },
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    };
    const renderPie = () => {
      const ctx = document.getElementById('pieTipo'); if (!ctx) return;
      const m = (document.getElementById('chartMesSelect')?.value) || competenciaEl.value;
      const r = resumoPeriodo(m);
      const fixas = Number.isFinite(r.fixas) ? r.fixas : 0;
      const variaveis = Number.isFinite(r.variaveis) ? r.variaveis : 0;
      if (pieChart) pieChart.destroy();
      pieChart = new Chart(ctx, {
        type:'pie',
        data:{ labels:['Fixas','Variáveis'], datasets:[{ data:[fixas, variaveis] }] },
        options:{ responsive:true }
      });
    };
    const refreshAnalyticsUI = () => { populatePeriodoSelects(); renderBar(); renderPie(); };
    const chartMesSel = document.getElementById('chartMesSelect'); if (chartMesSel) chartMesSel.addEventListener('change', renderPie);

    // ===== Poupança: calcula tempo para atingir meta com aportes mensais e juros compostos
    const btnCalcularPoupanca = document.getElementById('btnCalcularPoupanca');
    if (btnCalcularPoupanca) btnCalcularPoupanca.addEventListener('click', ()=>{
      const meta = parseMoney(document.getElementById('metaValor').value);
      const aporte = parseMoney(document.getElementById('aporteMensal').value);
      const i = parsePercent(document.getElementById('rendimentoMes').value); // decimal mensal

      const outEl = document.getElementById('poupancaResultado');
      if (meta <= 0) { outEl.textContent = 'Informe uma meta válida.'; return; }
      if (aporte <= 0) { outEl.textContent = 'Informe um aporte mensal válido.'; return; }

      if (i <= 0) {
        // sem rendimento: meses simples
        const meses = Math.ceil(meta / aporte);
        const anos = Math.floor(meses/12);
        const rest = meses % 12;
        outEl.textContent = `${meses} meses (${anos} anos e ${rest} meses)`;
        return;
      }

      // cálculo pelo valor futuro de uma série de pagamentos (depósito ao final do período)
      // FV = A * ( (1+i)^n - 1 ) / i
      let n = 0;
      let fv = 0;
      const maxN = 1000; // limite prático
      while (n < maxN) {
        n++;
        fv = aporte * ( (Math.pow(1+i, n) - 1) / i );
        if (fv >= meta) break;
      }
      if (n >= maxN) { outEl.textContent = 'Tempo muito longo (mais de 83 anos). Ajuste parâmetros.'; return; }
      const anos = Math.floor(n/12); const rest = n % 12;
      outEl.textContent = `${n} meses (${anos} anos e ${rest} meses). Valor aproximado final: ${fmtBRL(fv)}`;
    });

    // Persistência
    const savePeriodo = (yyyyMM) => {
      try {
        localStorage.setItem(lsKey(yyyyMM), JSON.stringify(snapshotFromUI()));
        competenciaHintEl.textContent = `Salvo em ${new Date().toLocaleString('pt-BR')}`;
        refreshAnalyticsUI(); // sincronia dos gráficos/selects
      } catch(e){}
    };

    // ===== Empréstimo (Tabela Price) =====
    const btnCalcularEmprestimo = document.getElementById('btnCalcularEmprestimo');
    if (btnCalcularEmprestimo) btnCalcularEmprestimo.addEventListener('click', ()=>{
      const PV = parseMoney(document.getElementById('loanValor').value);
      const i = parsePercent(document.getElementById('loanJurosMes').value); // monthly decimal
      const n = parseInt(document.getElementById('loanMeses').value) || 0;

      const outParcela = document.getElementById('loanParcela');
      const outTotal = document.getElementById('loanTotalPago');
      const outJuros = document.getElementById('loanJurosTotais');

      if (PV <= 0 || n <= 0) {
        outParcela.textContent = 'R$ 0,00'; outTotal.textContent = 'R$ 0,00'; outJuros.textContent = 'R$ 0,00';
        return;
      }

      let parcela = 0;
      if (i === 0) {
        parcela = PV / n;
      } else {
        const pow = Math.pow(1+i, n);
        parcela = PV * (i * pow) / (pow - 1);
      }
      const totalPago = parcela * n;
      const jurosTotais = totalPago - PV;

      outParcela.textContent = fmtBRL(parcela);
      outTotal.textContent = fmtBRL(totalPago);
      outJuros.textContent = fmtBRL(jurosTotais);
    });

    const loadPeriodo = (yyyyMM) => {
      const raw = localStorage.getItem(lsKey(yyyyMM));
      if (raw) {
        loadSnapshotToUI(JSON.parse(raw));
        // compat: atualiza hint
        competenciaHintEl.textContent = 'Dados carregados deste período.';
      } else {
        loadSnapshotToUI(null);
        competenciaHintEl.textContent = 'Novo período. Insira dados ou duplique do mês anterior.';
      }
      refreshAnalyticsUI(); // refaz dashboards ao trocar período
    };

    // Exportar CSV
    const btnExportar = document.getElementById('btnExportar'); if (btnExportar) btnExportar.addEventListener('click', ()=>{
      const renda = parseMoney(rendaEl.value);
      const comp = competenciaEl.value || getCurrentYYYYMM();
      let csv = `Competência,${comp}\n`;
      csv += 'Categoria,Descrição,Valor (R$)\n';
      [...tabela.querySelectorAll('tr')].forEach(tr=>{
        const tipo = tr.querySelector('.tipo').value;
        const desc = (tr.querySelector('.desc').value || '').replace(/,/g,';');
        const val  = parseMoney(tr.querySelector('.valor').value).toFixed(2).replace('.',',');
        csv += `${tipo},${desc},${val}\n`;
      });
      csv += `\nRenda,,${renda.toFixed(2).replace('.',',')}\n`;
      const total = parseMoney(totalDespesasEl.textContent.replace(/[^\d,.-]/g,''));
      csv += `Despesas Totais,,${total.toFixed(2).replace('.',',')}\n`;
      const saldo = renda - total;
      csv += `Saldo,,${saldo.toFixed(2).replace('.',',')}\n`;

      const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `orcamento_${comp}.csv`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    });

    // Ano footer
    const anoEl = document.getElementById('ano'); if (anoEl) anoEl.textContent = new Date().getFullYear();

    // Bootstrap inicial: competência atual
    if (competenciaEl) {
      competenciaEl.value = getCurrentYYYYMM();
      loadPeriodo(competenciaEl.value);
    }

    // Troca de competência
    if (competenciaEl) competenciaEl.addEventListener('change', () => {
      const prev = competenciaEl.dataset.last || competenciaEl.value;
      savePeriodo(prev);
      competenciaEl.dataset.last = competenciaEl.value;
      loadPeriodo(competenciaEl.value);
    });

    // Duplicar do mês anterior
    const btnDuplicarPeriodo = document.getElementById('btnDuplicarPeriodo'); if (btnDuplicarPeriodo) btnDuplicarPeriodo.addEventListener('click', () => {
      const atual = competenciaEl.value || getCurrentYYYYMM();
      const anterior = getYYYYMMPrev(atual);
      const raw = localStorage.getItem(lsKey(anterior));
      if (!raw) {
        competenciaHintEl.textContent = 'Sem dados no mês anterior para duplicar.';
        return;
      }
      const snap = JSON.parse(raw);
      loadSnapshotToUI(snap);
      savePeriodo(atual);
      competenciaHintEl.textContent = `Dados duplicados de ${anterior}.`;
      refreshAnalyticsUI();
    });

    // Recalcular + salvar quando valores mudarem (debounced)
    const onUiChange = debounce(() => {
      calcTotais();
      savePeriodo(competenciaEl.value);
    }, 200);

    document.addEventListener('input', (e)=>{
      if (e.target && e.target.matches && e.target.matches('#renda, .valor, .desc, .tipo')) {
        onUiChange();
      }
    });

    // bootstrap inicial dos dashboards
    refreshAnalyticsUI();

  });


