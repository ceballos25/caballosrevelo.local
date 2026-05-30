let transferencias = [];

const TRANSFERENCIAS_ENDPOINT = '/front/ajax/transferencias.ajax.php';
const VENTAS_ENDPOINT = '/front/ajax/ventas.ajax.php';
const TZ_COLOMBIA = 'America/Bogota';
const LOCALE_CO = 'es-CO';

/** `date_created_transfer` viene como hora local CO (naive `Y-m-d H:i:s`). */
function parseTransferDateTime(raw) {
    const s = String(raw ?? '').trim();
    if (!s) {
        return null;
    }
    const d = new Date(s.replace(' ', 'T') + '-05:00');
    return Number.isFinite(d.getTime()) ? d : null;
}

/** `YYYY-MM-DD` en Bogotá (para comparar con `<input type="date">`). */
function transferCalendarDateBogota(raw) {
    const d = parseTransferDateTime(raw);
    if (!d) {
        return '';
    }
    return d.toLocaleDateString('en-CA', { timeZone: TZ_COLOMBIA });
}

function formatTransferFechaHora(raw) {
    const d = parseTransferDateTime(raw);
    if (!d) {
        return { fecha: '—', hora: '' };
    }
    const opts = { timeZone: TZ_COLOMBIA };
    const fecha = d.toLocaleDateString(LOCALE_CO, opts);
    const hora = d.toLocaleTimeString(LOCALE_CO, {
        ...opts,
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
    });
    return { fecha, hora };
}

document.addEventListener("DOMContentLoaded", () => {
    cargarTransferencias();

    // 🔎 BUSCADOR
    const inputSearch = document.getElementById('searchTransfer');
    if (inputSearch) {
        inputSearch.addEventListener('input', debounce(() => {
            renderTabla();
        }, 400));
    }

    // 🔥 FILTROS
    ['filterEstado', 'fecha_inicio', 'fecha_fin'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', () => {
                renderTabla();
            });
        }
    });
});

function cargarTransferencias() {

    fetch(TRANSFERENCIAS_ENDPOINT, {
        method: "POST",
        body: new URLSearchParams({ action: "obtener" })
    })
    .then(res => res.json())
    .then(data => {

        if (data.success) {
            transferencias = Array.isArray(data.data)
                ? data.data
                : (data.data ? [data.data] : []);
        } else {
            transferencias = [];
        }

        renderTabla();
    })
    .catch(err => {
        console.error("Error cargando transferencias:", err);
        transferencias = [];
        renderTabla();
    });
}

function renderTabla() {

    const tbody = document.getElementById("bodyTabla");

    // 🔎 VALORES DE FILTRO
    const search = document.getElementById('searchTransfer').value.toLowerCase();
    const estado = document.getElementById('filterEstado').value;
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;

    // 🔥 FILTRADO COMPLETO
    const filtradas = transferencias.filter(t => {

        // 🔎 BUSCADOR
        const texto = `
            ${t.name_customer || ''}
            ${t.lastname_customer || ''}
            ${t.phone_customer || ''}
            ${t.email_customer || ''}
            ${t.code_transfer || ''}
        `.toLowerCase();

        const coincideBusqueda = texto.includes(search);

        // 📌 ESTADO
        const coincideEstado = estado ? t.status_transfer == estado : true;

        // 📅 FECHA
        let coincideFecha = true;

        if (fechaInicio && fechaFin) {
            const fecha = transferCalendarDateBogota(t.date_created_transfer);
            coincideFecha = fecha !== '' && fecha >= fechaInicio && fecha <= fechaFin;
        }

        return coincideBusqueda && coincideEstado && coincideFecha;
    });

    if (!filtradas.length) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center">Sin datos</td></tr>`;
        return;
    }

    tbody.innerHTML = filtradas.map(t => {

        const { fecha: fechaReg, hora: horaReg } = formatTransferFechaHora(t.date_created_transfer);

        let estadoBadge = {
            1: `<span class="badge bg-warning">Pendiente</span>`,
            2: `<span class="badge bg-success">Aprobado</span>`,
            3: `<span class="badge bg-danger">Rechazado</span>`,
            4: `<span class="badge bg-dark">Error</span>`
        }[t.status_transfer] || '';

        return `
        <tr class="align-middle border-bottom">

            <td class="py-3 ps-3">
                <div class="d-flex">
                    <div class="rounded-circle bg-light border d-flex justify-content-center align-items-center text-secondary fw-bold me-3"
                        style="width: 42px; height: 42px;">
                        ${t.name_customer ? t.name_customer.charAt(0).toUpperCase() : 'C'}
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-dark text-capitalize">
                            ${t.name_customer || ''} ${t.lastname_customer || ''}
                        </span>
                        <small class="text-muted">
                            <i class="ti ti-phone small"></i> ${t.phone_customer || '--'}
                        </small>
                    </div>
                </div>
            </td>

            <td>
                <div class="d-flex flex-column">
                    <span class="fw-bold text-secondary">${t.code_transfer}</span>
                    <div class="mt-1">
                        <span class="badge border text-primary bg-light" style="font-size: 0.75rem;">
                            <i class="ti ti-building-bank"></i> 
                            ${t.source_transfer ? t.source_transfer.toUpperCase() : 'N/A'}
                        </span>
                    </div>
                </div>
            </td>

            <td class="fw-bold">${t.quantity_transfer}</td>

            <td class="fw-bold text-dark">$${Number(t.amount_transfer).toLocaleString('es-CO')}</td>

            <td>
                ${t.url_transfer
                    ? `<button class="btn btn-sm btn-outline-primary" onclick="verComprobante('${t.url_transfer}')">Ver</button>`
                    : '—'
                }
            </td>

            <td>${estadoBadge}</td>

            <td>
                <div class="d-flex flex-column text-muted">
                    <span class="text-dark fw-medium">${fechaReg}</span>
                    <span style="font-size: 0.85rem;">${horaReg || '—'}</span>
                </div>
            </td>

            <td>
                ${t.status_transfer == 1
                    ? `
                    <button class="btn btn-success btn-sm" onclick="aprobar(${t.id_transfer})">✔</button>
                    <button class="btn btn-danger btn-sm" onclick="rechazar(${t.id_transfer})">✖</button>
                    `
                    : '—'
                }
            </td>
        </tr>
        `;
    }).join('');
    }

function limpiarFiltrosTransfer() {

    document.getElementById('searchTransfer').value = '';
    document.getElementById('filterEstado').value = '';
    document.getElementById('fecha_inicio').value = '';
    document.getElementById('fecha_fin').value = '';

    renderTabla();
}

function verComprobante(url) {
    document.getElementById("cuerpoComprobante").innerHTML =
        `<img src="${url}" class="img-fluid rounded">`;

    new bootstrap.Modal(document.getElementById("modalComprobante")).show();
}

/** Solo los campos que usa PHP al aprobar (evita URL largas, & en datos y basura del listado). */
function paramsAprobarTransferencia(t) {
    const p = new URLSearchParams();
    p.set('action', 'aprobar');
    p.set('id_transfer', String(t.id_transfer ?? ''));
    p.set('status_transfer', String(t.status_transfer ?? ''));
    p.set('quantity_transfer', String(t.quantity_transfer ?? ''));
    p.set('id_raffle_transfer', String(t.id_raffle_transfer ?? ''));
    p.set('amount_transfer', String(t.amount_transfer ?? ''));
    p.set('id_customer_transfer', String(t.id_customer_transfer ?? ''));
    p.set('code_transfer', String(t.code_transfer ?? ''));
    if (t.source_transfer != null && String(t.source_transfer).trim() !== '') {
        p.set('source_transfer', String(t.source_transfer));
    }
    return p;
}

function parseJsonTransferenciasAjax(res) {
    return res.text().then((text) => {
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('transferencias.ajax no devolvió JSON:', text.substring(0, 500));
            throw e;
        }
    });
}

function aprobar(id) {

    const t = transferencias.find(x => x.id_transfer == id);
    if (!t) return;

    Swal.fire({
        title: '¿Aprobar transferencia?',
        text: `Código: ${t.code_transfer}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, aprobar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754'
    }).then(result => {

        if (!result.isConfirmed) return;

        Swal.fire({
            title: 'Procesando...',
            text: 'Creando venta y asignando números',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch(TRANSFERENCIAS_ENDPOINT, {
            method: "POST",
            body: paramsAprobarTransferencia(t)
        })
        .then(parseJsonTransferenciasAjax)
        .then(res => {

        const idSale = Number(res.id_sale);

        if (res.success && idSale > 0) {

            Swal.fire({
                icon: 'success',
                title: 'Venta creada',
                text: 'Abriendo recibo…',
                timer: 1200,
                showConfirmButton: false
            });

            setTimeout(() => {
                verRecibo(idSale);
            }, 1200);

        } else if (!res.success && idSale > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Venta registrada',
                text: res.message || 'Revise el estado de la transferencia en la base de datos.'
            });
            verRecibo(idSale);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: res.message || 'No se pudo aprobar'
            });
        }

            cargarTransferencias();
        })
        .catch(() => {
            Swal.fire('Error', 'Fallo en la solicitud', 'error');
        });

    });
}

function rechazar(id) {

    const t = transferencias.find(x => x.id_transfer == id);
    if (!t) return;

    Swal.fire({
        title: '¿Rechazar transferencia?',
        text: `Código: ${t.code_transfer}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, rechazar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then(result => {

        if (!result.isConfirmed) return;

        Swal.fire({
            title: 'Procesando...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch(TRANSFERENCIAS_ENDPOINT, {
            method: "POST",
            body: new URLSearchParams({
                action: "rechazar",
                id_transfer: String(t.id_transfer ?? '')
            })
        })
        .then(parseJsonTransferenciasAjax)
        .then(res => {

            if (res.success !== false) {
                Swal.fire({
                    icon: 'success',
                    title: 'Rechazado',
                    text: 'La transferencia fue rechazada'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: res.message || 'No se pudo rechazar'
                });
            }

            cargarTransferencias();
        })
        .catch(() => {
            Swal.fire('Error', 'Fallo en la solicitud', 'error');
        });

    });
}

// 🔥 DEBOUNCE
function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

function verRecibo(id) {
    const fd = new FormData();
    fd.append('action', 'detalle_venta');
    fd.append('id_sale', id);

    fetch(VENTAS_ENDPOINT, { method: 'POST', body: fd })
        .then(parseJsonTransferenciasAjax)
        .then(res => {
            if (res.success) {
                document.getElementById('cuerpoRecibo').innerHTML = res.html_recibo;
                new bootstrap.Modal(document.getElementById('modalRecibo')).show();
            } else {
                Swal.fire('Error', 'No se pudo cargar el recibo', 'error');
            }
        })
        .catch(() => {
            Swal.fire('Error', 'No se pudo cargar el recibo', 'error');
        });
}