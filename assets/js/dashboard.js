/**
 * dashboard.js - Versión Final Nivel Dios
 */
let chartTendenciaInst = null;
let chartMediosDineroInst = null;
let chartMediosCantInst = null;
let chartMediosTransInst = null;
let chartTopCliInst = null;
let chartTopCiuInst = null;
let chartHeatmapInst = null; // Nuevo
let chartPaquetesInst = null; // Nuevo
const DASHBOARD_ENDPOINT = '/front/ajax/dashboard.ajax.php';

async function fetchDashboard(fd) {
    const response = await fetch(DASHBOARD_ENDPOINT, { method: 'POST', body: fd });
    if (!response.ok) {
        throw new Error(`Dashboard AJAX ${response.status}`);
    }

    return response.json();
}

document.addEventListener('DOMContentLoaded', () => {
    cargarRifas();
    cambiarPeriodo(); 

    document.getElementById('filterPeriodo').addEventListener('change', cambiarPeriodo);
    document.getElementById('filterDesde').addEventListener('change', () => document.getElementById('filterPeriodo').value = '');
    document.getElementById('filterHasta').addEventListener('change', () => document.getElementById('filterPeriodo').value = '');
});

function cambiarPeriodo() {
    const periodo = document.getElementById('filterPeriodo').value;
    const date = new Date();
    const formatDate = (d) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    let desde = '', hasta = '';

    if (periodo === 'hoy') { desde = hasta = formatDate(date); }
    else if (periodo === 'ayer') { date.setDate(date.getDate() - 1); desde = hasta = formatDate(date); }
    else if (periodo === 'semana') { const day = date.getDay() || 7; if(day!==1) date.setHours(-24 * (day-1)); desde = formatDate(date); hasta = formatDate(new Date()); }
    else if (periodo === 'mes') { desde = formatDate(new Date(date.getFullYear(), date.getMonth(), 1)); hasta = formatDate(new Date(date.getFullYear(), date.getMonth() + 1, 0)); }
    else if (periodo === 'ano') { desde = formatDate(new Date(date.getFullYear(), 0, 1)); hasta = formatDate(new Date(date.getFullYear(), 11, 31)); }

    if (desde && hasta) {
        document.getElementById('filterDesde').value = desde;
        document.getElementById('filterHasta').value = hasta;
        cargarDashboard();
    }
}

async function cargarRifas() {
    try {
        const fd = new FormData();
        fd.append('action', 'obtener_rifas');
        const j = await fetchDashboard(fd);
        const sel = document.getElementById('filterRifa');
        if (j.success && sel) {
            sel.innerHTML = '<option value="">🌐 Todas las Rifas</option>';
            j.data.forEach(x => sel.innerHTML += `<option value="${x.id_raffle}">${x.title_raffle}</option>`);
        }
    } catch (e) {
        console.error(e);
        if (typeof alertify !== 'undefined') alertify.error('No se pudieron cargar las rifas del dashboard');
    }
}

async function cargarDashboard() {
    const desde = document.getElementById('filterDesde').value;
    const hasta = document.getElementById('filterHasta').value;
    const rifa  = document.getElementById('filterRifa').value;

    try {
        const fd = new FormData();
        fd.append('action', 'obtener_dashboard');
        fd.append('fechaDesde', desde);
        fd.append('fechaHasta', hasta);
        fd.append('id_raffle', rifa);

        const data = await fetchDashboard(fd);

        if (data.success) {
            renderKPIs(data.data.kpis);
            renderCharts(data.data.graficas);
            renderTabla(data.data.ultimasVentas);
        }
    } catch (e) {
        console.error(e);
        if (typeof alertify !== 'undefined') alertify.error('No se pudo cargar el dashboard');
    }
}

function limpiarFiltrosDashboard() {
    document.getElementById('filterRifa').value = '';
    document.getElementById('filterPeriodo').value = 'mes';
    cambiarPeriodo();
}

function renderKPIs(kpis) {
    const fmtMoney = (n) => '$' + Number(n).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    const fmtNum = (n) => Number(n).toLocaleString('es-CO');

    document.getElementById('kpiVentas').innerText = fmtMoney(kpis.totalVentas);
    document.getElementById('kpiVendidos').innerText = fmtNum(kpis.numerosVendidos);
    document.getElementById('kpiClientes').innerText = fmtNum(kpis.totalClientes);
    document.getElementById('kpiDisponibles').innerText = fmtNum(kpis.numerosDisponibles);
}

// Configuración Base Donut
const commonDonutOptions = {
    chart: { type: 'donut', height: 320, fontFamily: 'inherit' },
    legend: { position: 'bottom' },
    plotOptions: { pie: { donut: { size: '70%', labels: { show: true, name: { show: true, fontSize: '14px' }, value: { show: true, fontSize: '22px', fontWeight: 700, offsetY: 5 }, total: { show: true, label: 'TOTAL', fontSize: '12px', fontWeight: 600, color: '#6c757d' } } } } },
    dataLabels: { enabled: false }
};

function renderCharts(graficas) {
    const fmtMoney = v => '$' + Number(v).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    const fmtNum = v => Number(v).toLocaleString('es-CO');

    // PALETA DE COLORES PROFESIONAL (Armonía Azul/Verde/Violeta)
    const colorsTicket = ['#4361ee', '#3a0ca3', '#7209b7', '#f72585']; // Gama Violeta/Rosa fuerte
    const colorsDinero = ['#2ec4b6', '#ff9f1c', '#e71d36', '#011627']; // Gama Contraste (Verde/Naranja)
    const colorsTrans  = ['#3f37c9', '#4cc9f0', '#4895ef', '#560bad']; // Gama Azul Profundo

    // 1. TENDENCIA
    const optTendencia = {
        series: [{ name: 'Ventas ($)', data: graficas.tendencia.map(x => x.total) }],
        chart: { type: 'area', height: 350, toolbar: { show: false }, fontFamily: 'inherit' },
        xaxis: { categories: graficas.tendencia.map(x => x.fecha) },
        yaxis: { labels: { formatter: (val) => fmtMoney(val) } },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        colors: ['#4361ee'], // Azul vibrante principal
        tooltip: { y: { formatter: (val) => fmtMoney(val) } },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.6, opacityTo: 0.1 } }
    };
    if(chartTendenciaInst) chartTendenciaInst.destroy();
    chartTendenciaInst = new ApexCharts(document.querySelector("#chartTendencia"), optTendencia);
    chartTendenciaInst.render();

    // 2. DONUT 1: VENTAS (TRANSACCIONES)
    const optTrans = JSON.parse(JSON.stringify(commonDonutOptions));
    optTrans.series = graficas.mediosPagoTransacciones.length ? graficas.mediosPagoTransacciones : [1];
    optTrans.labels = graficas.mediosPagoLabels.length ? graficas.mediosPagoLabels : ['Sin datos'];
    optTrans.colors = colorsTrans; // Paleta Azul
    optTrans.plotOptions.pie.donut.labels.value.formatter = val => fmtNum(val);
    optTrans.plotOptions.pie.donut.labels.total.formatter = w => fmtNum(w.globals.seriesTotals.reduce((a, b) => a + b, 0));
    optTrans.tooltip = { y: { formatter: v => fmtNum(v) + ' Ventas' } };
    
    if(chartMediosTransInst) chartMediosTransInst.destroy();
    chartMediosTransInst = new ApexCharts(document.querySelector("#chartMediosTransacciones"), optTrans);
    chartMediosTransInst.render();

    // 3. DONUT 2: NÚMEROS (TICKETS)
    const optTick = JSON.parse(JSON.stringify(commonDonutOptions));
    optTick.series = graficas.mediosPagoTickets.length ? graficas.mediosPagoTickets : [1];
    optTick.labels = graficas.mediosPagoLabels.length ? graficas.mediosPagoLabels : ['Sin datos'];
    optTick.colors = colorsTicket; // Paleta Violeta
    optTick.plotOptions.pie.donut.labels.value.formatter = val => fmtNum(val);
    optTick.plotOptions.pie.donut.labels.total.formatter = w => fmtNum(w.globals.seriesTotals.reduce((a, b) => a + b, 0));
    optTick.tooltip = { y: { formatter: v => fmtNum(v) + ' Números' } };
    
    if(chartMediosCantInst) chartMediosCantInst.destroy();
    chartMediosCantInst = new ApexCharts(document.querySelector("#chartMediosTickets"), optTick);
    chartMediosCantInst.render();

    // 4. DONUT 3: DINERO ($)
    const optDin = JSON.parse(JSON.stringify(commonDonutOptions));
    optDin.series = graficas.mediosPagoDinero.length ? graficas.mediosPagoDinero : [1];
    optDin.labels = graficas.mediosPagoLabels.length ? graficas.mediosPagoLabels : ['Sin datos'];
    optDin.colors = colorsDinero; // Paleta Contraste (Dinero)
    optDin.plotOptions.pie.donut.labels.value.formatter = val => fmtMoney(val);
    optDin.plotOptions.pie.donut.labels.total.formatter = w => fmtMoney(w.globals.seriesTotals.reduce((a, b) => a + b, 0));
    optDin.tooltip = { y: { formatter: v => fmtMoney(v) } };
    
    if(chartMediosDineroInst) chartMediosDineroInst.destroy();
    chartMediosDineroInst = new ApexCharts(document.querySelector("#chartMediosDinero"), optDin);
    chartMediosDineroInst.render();

    // 5. TOP CLIENTES
    const optTopCli = {
        series: [{ name: 'Compras', data: graficas.topClientes.map(x => x.total) }],
        chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
        plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '65%' } },
        dataLabels: { enabled: false },
        xaxis: { categories: graficas.topClientes.map(x => x.name), labels: { style: { fontSize: '11px' } } },
        colors: ['#212529'],
        grid: { show: false },
        tooltip: {
            custom: function({ series, seriesIndex, dataPointIndex, w }) {
                const c = graficas.topClientes[dataPointIndex];
                return `<div class="px-3 py-2 border rounded shadow bg-white text-dark text-start" style="font-size: 0.85rem; min-width: 180px;"><div class="fw-bold mb-2 border-bottom pb-1 text-uppercase text-primary">${c.name}</div><div class="d-flex justify-content-between mb-1"><span>💰 Total:</span><span class="fw-bold">${fmtMoney(c.total)}</span></div><div class="d-flex justify-content-between mb-2"><span>🎟️ Números:</span><span class="fw-bold">${fmtNum(c.cantidad)}</span></div><div class="bg-light p-1 rounded small text-muted"><div><i class="ti ti-phone me-1"></i> ${c.telefono}</div><div><i class="ti ti-map-pin me-1"></i> ${c.ciudad}</div></div></div>`;
            }
        }
    };
    if(chartTopCliInst) chartTopCliInst.destroy();
    chartTopCliInst = new ApexCharts(document.querySelector("#chartTopClientes"), optTopCli);
    chartTopCliInst.render();

    // 6. TOP CIUDADES
    const optTopCiu = {
        series: [{ name: 'Números', data: graficas.topCiudades.map(x => x.data) }],
        chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
        plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '65%', distributed: true } },
        dataLabels: { enabled: true, formatter: (val) => fmtNum(val) },
        xaxis: { categories: graficas.topCiudades.map(x => x.name) },
        colors: ['#4361ee', '#3a0ca3', '#7209b7', '#f72585', '#4cc9f0'], // Misma gama violeta/azul
        legend: { show: false },
        grid: { show: false },
        tooltip: { y: { formatter: v => fmtNum(v) + ' Números' } }
    };
    if(chartTopCiuInst) chartTopCiuInst.destroy();
    chartTopCiuInst = new ApexCharts(document.querySelector("#chartTopCiudades"), optTopCiu);
    chartTopCiuInst.render();

    // 7. HEATMAP
    const optHeat = {
        series: graficas.heatmap,
        chart: { type: 'heatmap', height: 350, toolbar: { show: false }, fontFamily: 'inherit' },
        dataLabels: { enabled: false },
        colors: ["#dd1313"],
        title: { text: '' },
        plotOptions: { heatmap: { shadeIntensity: 0.5, colorScale: { ranges: [{ from: 0, to: 0, color: '#f8f9fa', name: 'Sin Ventas' }] } } },
        tooltip: { y: { formatter: v => v + ' Ventas' } }
    };
    if(chartHeatmapInst) chartHeatmapInst.destroy();
    chartHeatmapInst = new ApexCharts(document.querySelector("#chartHeatmap"), optHeat);
    chartHeatmapInst.render();

    // 8. PAQUETES
    const optPaq = {
        series: [{ name: 'Ventas', data: graficas.paquetes.map(x => x.data) }],
        chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
        dataLabels: { enabled: true },
        xaxis: { categories: graficas.paquetes.map(x => x.name) },
        colors: ['#10b981'], // Verde Esmeralda (Éxito)
        grid: { show: false },
        tooltip: { y: { formatter: v => v + ' veces comprado' } }
    };
    if(chartPaquetesInst) chartPaquetesInst.destroy();
    chartPaquetesInst = new ApexCharts(document.querySelector("#chartPaquetes"), optPaq);
    chartPaquetesInst.render();
}

function renderTabla(ventas) {
    const tbody = document.getElementById('tablaUltimasVentas');
    if (!ventas || ventas.length === 0) { tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No hay ventas registradas.</td></tr>'; return; }
    const fmtMoney = v => '$' + Number(v).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    tbody.innerHTML = ventas.map(v => `<tr style="font-size: 0.9rem;"><td class="ps-4"><span class="font-monospace bg-light border px-2 py-1 rounded small">${v.code_sale}</span></td><td class="fw-bold text-dark text-capitalize">${v.name_customer} ${v.lastname_customer}</td><td class="text-muted small text-truncate" style="max-width: 120px;">${v.title_raffle}</td><td class="text-success fw-bold">${fmtMoney(v.total_sale)}</td><td class="text-end pe-4 text-muted small">${v.date_created_sale.split(' ')[0]}</td></tr>`).join('');
}