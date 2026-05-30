/**
 * ventas.js - Gestión Blindada y Completa
 */

// 1. DECLARACIÓN DE VARIABLES GLOBALES (VITAL)
let paginaActual = 1;
const registrosPorPagina = 10;
const VENTAS_ENDPOINT = '/front/ajax/ventas.ajax.php';

document.addEventListener('DOMContentLoaded', function() {
    
    // Cargas iniciales de selects
    //cargarRifasSelect();
    cargarAdminsSelect();
    cargarOrigenesSelect();
    
    // Cargar Ventas Iniciales
    cargarVentas();

    // Eventos de filtros
    const filtrosIds = ['filterMetodoPago', 'filterAdmin', 'filterRifa', 'filterOrigen', 'filterPeriodo'];
    filtrosIds.forEach(id => {
        document.getElementById(id)?.addEventListener('change', () => {
            paginaActual = 1;
            cargarVentas();
        });
    });

    // Buscador
    document.getElementById('searchVentas')?.addEventListener('input', debounce(() => {
        paginaActual = 1;
        cargarVentas();
    }, 600));

    // Fechas manuales
    ['fecha_inicio', 'fecha_fin'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', function() {
            if (this.value !== "") document.getElementById('filterPeriodo').value = '';
            if (document.getElementById('fecha_inicio').value && document.getElementById('fecha_fin').value) {
                paginaActual = 1;
                cargarVentas();
            }
        });
    });
});

async function cargarVentas() {
    // Activar Preloader
    if (typeof showPreloader === 'function') showPreloader();

    const tbody = document.getElementById('bodyTabla');
    if (tbody) tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5">Cargando datos...</td></tr>`;

    try {
        const fd = new FormData();
        fd.append('action', 'obtener');
        fd.append('page', paginaActual);
        fd.append('limit', registrosPorPagina);
        
        // Captura de filtros
        fd.append('search', document.getElementById('searchVentas')?.value || '');
        fd.append('id_raffle', document.getElementById('filterRifa')?.value || '');
        fd.append('id_admin', document.getElementById('filterAdmin')?.value || '');
        fd.append('payment_method', document.getElementById('filterMetodoPago')?.value || '');
        fd.append('periodo', document.getElementById('filterPeriodo')?.value || '');
        fd.append('fecha_inicio', document.getElementById('fecha_inicio')?.value || '');
        fd.append('fecha_fin', document.getElementById('fecha_fin')?.value || '');
        fd.append('source_sale', document.getElementById('filterOrigen')?.value || '');

        const res = await fetch(VENTAS_ENDPOINT, { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            const ventas = Array.isArray(data.data) ? data.data : (data.data ? [data.data] : []);
            renderTabla(ventas);
            actualizarPaginacion(data.total); 
        } else {
            renderTabla([]);
        }
    } catch (e) {
        console.error("Error en cargarVentas:", e);
        renderTabla([]);
    } finally {
        if (typeof hidePreloader === 'function') hidePreloader();
    }
}

function renderTabla(ventas) {
    const tbody = document.getElementById('bodyTabla');
    if (!tbody) return;

    if (ventas.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-muted">No se encontraron registros</td></tr>`;
        return;
    }

tbody.innerHTML = ventas.map(v => {


    // Fecha guardada como hora local Colombia (naive); mostrar siempre en America/Bogota (no en la zona del navegador).
    const f = new Date(String(v.date_created_sale).replace(' ', 'T') + '-05:00');
    const opts = { timeZone: 'America/Bogota' };
    const fecha = f.toLocaleDateString('es-CO', opts);
    const hora = f.toLocaleTimeString('es-CO', {
        ...opts,
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
    });
        
        const inicial = v.name_customer ? v.name_customer.charAt(0).toUpperCase() : 'C';
        const badgeClass = v.payment_method_sale === 'Venta Manual' 
            ? 'bg-success-subtle text-success border-success-subtle' 
            : 'bg-primary-subtle text-primary border-primary-subtle';
        const source = (v.source_sale ?? '').toString().trim().toLowerCase();
        
        // DESPUÉS ✓
        const sourceText = (source && source !== 'null')
            ? source
            : 'N/A';
    
        return `
        <tr class="align-middle border-bottom hover-shadow">
            <td class="py-3 ps-3">
                <div class="d-flex">
                    <div class="rounded-circle bg-light border d-flex justify-content-center align-items-center text-secondary fw-bold me-3 flex-shrink-0" 
                         style="width: 42px; height: 42px; font-size: 1.1rem;">
                        ${inicial}
                    </div>
                    <div class="d-flex flex-column" style="line-height: 1.3;">
                        <span class="fw-bold text-dark text-capitalize">${v.name_customer} ${v.lastname_customer}</span>
                        <div class="text-muted small mt-1">
                            <span class="me-2"><i class="ti ti-phone"></i> ${v.phone_customer || '--'}</span>
                        </div>
                        <small class="text-muted fst-italic" style="font-size: 0.75rem;">
                            <i class="ti ti-user"></i> ${v.email_admin || 'Sistema'}
                            &nbsp;·&nbsp;<i class="ti ti-world"></i> ${sourceText}
                        </small>
                    </div>
                </div>
            </td>
            <td><span class="font-monospace bg-light text-primary px-2 py-1 rounded border" style="font-size: 0.85rem;">${v.code_sale}</span></td>
            <td><span class="fw-medium text-dark d-block">${v.quantity_sale} Núms</span><small class="text-muted text-truncate d-block" style="max-width: 150px;">${v.title_raffle}</small></td>
            <td><span class="fw-bold text-dark">$${Number(v.total_sale).toLocaleString('es-CO')}</span></td>
            <td><span class="badge ${badgeClass} border px-3 py-2 rounded-pill">${v.payment_method_sale}</span></td>
            <td><div class="d-flex flex-column text-muted"><span class="text-dark fw-medium">${fecha}</span><span style="font-size: 0.85rem;">${hora}</span></div></td>
            <td class="py-3 text-end pe-3">
                <button class="btn btn-icon btn-sm btn-outline-primary border-0 rounded-circle shadow-sm" onclick="verRecibo(${v.id_sale})" title="Ver Detalle" style="width: 32px; height: 32px;"><i class="ti ti-eye fs-7"></i></button>
                <button class="btn btn-icon btn-sm btn-outline-danger border-0 rounded-circle shadow-sm ms-1" onclick="anularVenta(${v.id_sale})" title="Anular venta" style="width: 32px; height: 32px;"><i class="ti ti-trash fs-7"></i></button>
            </td>
        </tr>`;
    }).join('');
}

function actualizarPaginacion(totalItems) {
    const totalPaginas = Math.ceil(totalItems / registrosPorPagina);
    const contenedor = document.getElementById('contenedorPaginacion');
    const info = document.getElementById('infoPaginacion');

    if (info) {
        const desde = totalItems === 0 ? 0 : (paginaActual - 1) * registrosPorPagina + 1;
        const hasta = Math.min(paginaActual * registrosPorPagina, totalItems);
        info.innerText = `Mostrando ${desde} a ${hasta} de ${totalItems.toLocaleString()} registros`;
    }

    if (!contenedor) return;

    let html = '';
    html += `<li class="page-item ${paginaActual === 1 ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(${paginaActual - 1})">Anterior</a></li>`;

    let inicio = Math.max(1, paginaActual - 2);
    let fin = Math.min(totalPaginas, inicio + 4);
    if (fin - inicio < 4) inicio = Math.max(1, fin - 4);

    for (let i = inicio; i <= fin; i++) {
        if (i <= 0) continue;
        html += `<li class="page-item ${i === paginaActual ? 'active' : ''}"><a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(${i})">${i}</a></li>`;
    }

    html += `<li class="page-item ${paginaActual >= totalPaginas || totalPaginas === 0 ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(${paginaActual + 1})">Siguiente</a></li>`;
    contenedor.innerHTML = html;
}

function cambiarPagina(p) {
    paginaActual = p;
    cargarVentas();
    window.scrollTo(0, 0);
}

// --- AUXILIARES ---

async function cargarRifasSelect() {
    const fd = new FormData(); fd.append('action', 'obtener_rifas');
    const res = await fetch(VENTAS_ENDPOINT, { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
        const sel = document.getElementById('filterRifa');
        if (sel) sel.innerHTML = '<option value="">Todas las rifas</option>' + data.data.map(r => `<option value="${r.id_raffle}">${r.title_raffle}</option>`).join('');
    }
}

async function cargarAdminsSelect() {
    const fd = new FormData(); fd.append('action', 'obtener_admins');
    const res = await fetch(VENTAS_ENDPOINT, { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
        const sel = document.getElementById('filterAdmin');
        if (sel) sel.innerHTML = '<option value="">Todos los admins</option>' + data.data.map(a => `<option value="${a.id_admin}">${a.email_admin}</option>`).join('');
    }
}

async function cargarOrigenesSelect() {
    const fd = new FormData(); fd.append('action', 'obtener_origenes');
    const res = await fetch(VENTAS_ENDPOINT, { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success && data.data) {
        const sel = document.getElementById('filterOrigen');
        if (sel) sel.innerHTML = '<option value="">Todos los orígenes</option>' + data.data.map(o => `<option value="${o}">${o}</option>`).join('');
    }
}

function debounce(f, w) {
    let t;
    return (...a) => { clearTimeout(t); t = setTimeout(() => f(...a), w); };
}

function verRecibo(id) {
    const fd = new FormData();
    fd.append('action', 'detalle_venta');
    fd.append('id_sale', id);
    fetch(VENTAS_ENDPOINT, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                document.getElementById('cuerpoRecibo').innerHTML = res.html_recibo;
                new bootstrap.Modal(document.getElementById('modalRecibo')).show();
            }
        });
}

function anularVenta(id) {
    Swal.fire({
        title: '¿Anular venta?',
        text: 'Esta acción no se puede revertir',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar',
        showLoaderOnConfirm: true,
        allowOutsideClick: () => !Swal.isLoading(),

        preConfirm: async () => {
            try {
                const fd = new FormData();
                fd.append('action', 'anular');
                fd.append('id_sale', id);
                const csrfToken = window.APP_CSRF_TOKEN || '';
                fd.append('csrf_token', csrfToken);

                const res = await fetch(VENTAS_ENDPOINT, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': csrfToken
                    },
                    body: fd
                });

                const data = await res.json();

                if (!data.success) {
                    throw new Error(data.message || 'Error al anular');
                }

                return data;

            } catch (error) {
                Swal.showValidationMessage(error.message);
            }
        }
    }).then(result => {
        if (result.isConfirmed) {
            Swal.fire('OK', 'Venta anulada correctamente', 'success');
            cargarVentas();
        }
    });
}

