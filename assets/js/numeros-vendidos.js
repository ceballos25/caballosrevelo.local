/**
 * numeros-vendidos.js - Gestión Estilizada con Paginación
 */
let cache = [], paginaActual = 1;
const registrosPorPagina = 10;
const VENTAS_ENDPOINT = '/front/ajax/ventas.ajax.php';

document.addEventListener('DOMContentLoaded', () => {
    cargarRifas();
    cargarNumeros();

    document.getElementById('searchNumeros').addEventListener('input', debounce(() => { paginaActual = 1; cargarNumeros(); }, 500));
    document.getElementById('filterRifa').addEventListener('change', () => { paginaActual = 1; cargarNumeros(); });
});

async function cargarRifas() {
    try {
        const fd = new FormData();
        fd.append('action', 'obtener_rifas');
        const r = await fetch(VENTAS_ENDPOINT, { method: 'POST', body: fd });
        const j = await r.json();
        const s = document.getElementById('filterRifa');
        if (j.success && s) {
            j.data.forEach(r => {
                s.innerHTML += `<option value="${r.id_raffle}">${r.title_raffle}</option>`;
            });
        }
    } catch (e) { console.error(e); }
}

async function cargarNumeros() {
    // Spinner simple mientras carga
    document.getElementById('bodyTabla').innerHTML = `<tr><td colspan="5" class="text-center py-5">Cargando...</td></tr>`;
    
    try {
        const fd = new FormData();
        fd.append('action', 'numeros_vendidos'); // Asegúrate que esta acción exista en tu AJAX
        // Si no existe 'numeros_vendidos' usa 'obtener_disponibles' o crea la lógica similar a ventas
        // Asumo que tienes un endpoint que trae todos los vendidos
        // Si no, avísame para crear el endpoint en el controlador.
        
        // NOTA: Para este ejemplo usaré la lógica que tenías, asumiendo que el backend responde.
        // Si no tienes el backend para esto, dímelo y te paso el controlador.
        
        // Simulación de parámetros (ajusta según tu backend real)
        fd.append('search', document.getElementById('searchNumeros').value);
        fd.append('id_raffle', document.getElementById('filterRifa').value);

        const res = await fetch(VENTAS_ENDPOINT, { method: 'POST', body: fd });
        const data = await res.json();
        
        cache = data.success ? data.data : [];
        renderTodo(); // Llamamos al renderizador que incluye paginación
        
    } catch (e) { console.error(e); }
}

// --- LÓGICA DE PAGINACIÓN ---
function renderTodo() {
    // Si tienes el PaginationHelper global
    if (typeof PaginationHelper !== 'undefined') {
        const segmento = PaginationHelper.getSegment(cache, paginaActual, registrosPorPagina);
        renderTabla(segmento);
        PaginationHelper.render({
            totalItems: cache.length,
            currentPage: paginaActual,
            limit: registrosPorPagina,
            containerId: 'contenedorPaginacion',
            infoId: 'infoPaginacion',
            callbackName: 'cambiarPagina'
        });
    } else {
        // Fallback manual si no existe el Helper
        const inicio = (paginaActual - 1) * registrosPorPagina;
        const fin = inicio + registrosPorPagina;
        const segmento = cache.slice(inicio, fin);
        renderTabla(segmento);
        renderPaginadorManual();
    }
}

function cambiarPagina(p) {
    paginaActual = p;
    renderTodo();
}

// --- RENDERIZADO VISUAL PRO ---
function renderTabla(datos) {
    const tbody = document.getElementById('bodyTabla');
    
    if (!datos || datos.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center py-5 text-muted">No se encontraron números</td></tr>`;
        return;
    }

    tbody.innerHTML = datos.map((t) => {
        const raw = t.date_created_sale ? String(t.date_created_sale).replace(' ', 'T') + '-05:00' : null;
        const f = raw ? new Date(raw) : new Date();
        const opts = { timeZone: 'America/Bogota' };
        const fecha = f.toLocaleDateString('es-CO', opts);
        const hora = f.toLocaleTimeString('es-CO', {
            ...opts,
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
        });
        
        // Inicial para Avatar
        const inicial = t.name_customer ? t.name_customer.charAt(0).toUpperCase() : 'C';

        return `
        <tr class="align-middle border-bottom hover-shadow">
            
            <td class="py-3 ps-4">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-light border d-flex justify-content-center align-items-center text-secondary fw-bold me-3 flex-shrink-0" 
                         style="width: 40px; height: 40px; font-size: 1rem;">
                        ${inicial}
                    </div>
                    <div class="d-flex flex-column" style="line-height: 1.3;">
                        <span class="fw-bold text-dark text-capitalize">
                            ${t.name_customer} ${t.lastname_customer}
                        </span>
                        <div class="text-muted small mt-1 d-flex gap-2 flex-wrap">
                            <span><i class="ti ti-phone text-secondary"></i> ${t.phone_customer || '--'}</span>
                            <span class="border-start ps-2"><i class="ti ti-map-pin text-secondary"></i> ${t.city_customer || 'N/A'}</span>
                        </div>
                        <small class="text-muted fst-italic" style="font-size: 0.75rem;">
                            ${t.email_customer || ''}
                        </small>
                    </div>
                </div>
            </td>

            <td class="py-3">
                <div class="d-flex flex-column">
                    <span class="font-monospace bg-light text-secondary py-1 rounded border" 
                          style="font-size: 0.8rem; width: fit-content;">${t.code_sale}
                    </span>
                    <span class="text-muted small fw-medium text-truncate" style="max-width: 180px;">
                        ${t.title_raffle}
                    </span>
                </div>
            </td>

            <td class="py-3 text-center">
                <div class="d-inline-flex align-items-center justify-content-center rounded shadow-sm" 
                     style="background-color: #f5f5f5; color: #000; border: 1px solid #000000; width: 50px; height: 38px;"
                    <span class="fw-bold" style="font-size: 1rem; letter-spacing: 0.5px;">
                        ${t.number_ticket}
                    </span>
                </div>
            </td>

            <td class="py-3 text-end pe-4">
                <div class="d-flex flex-column">
                    <span class="text-dark fw-medium" style="font-size: 0.9rem;">${fecha}</span>
                    <span class="text-muted small">${hora}</span>
                </div>
            </td>
        </tr>`;
    }).join('');
}

// Fallback manual de paginación por si no usas el Helper externo
function renderPaginadorManual() {
    const totalPaginas = Math.ceil(cache.length / registrosPorPagina);
    const container = document.getElementById('contenedorPaginacion');
    const info = document.getElementById('infoPaginacion');
    
    if(info) info.textContent = `Total: ${cache.length} registros`;
    if(!container) return;

    let html = '';
    // Botón Anterior
    html += `<li class="page-item ${paginaActual === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="cambiarPagina(${paginaActual - 1})">Ant</a>
             </li>`;
             
    // Números (simplificado)
    for(let i=1; i<=totalPaginas; i++){
        if(i === 1 || i === totalPaginas || (i >= paginaActual - 1 && i <= paginaActual + 1)){
             html += `<li class="page-item ${i === paginaActual ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="cambiarPagina(${i})">${i}</a>
                      </li>`;
        } else if (i === paginaActual - 2 || i === paginaActual + 2) {
             html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    // Botón Siguiente
    html += `<li class="page-item ${paginaActual === totalPaginas ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="cambiarPagina(${paginaActual + 1})">Sig</a>
             </li>`;
             
    container.innerHTML = html;
}

function limpiarFiltros() {
    document.getElementById('searchNumeros').value = '';
    document.getElementById('filterRifa').value = '';
    paginaActual = 1;
    cargarNumeros();
}

function debounce(f, t) { let e; return () => { clearTimeout(e); e = setTimeout(f, t); } }