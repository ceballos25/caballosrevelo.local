/**
 * numeros.js
 */
let cache = [], paginaActual = 1;
const registrosPorPagina = 20;

document.addEventListener('DOMContentLoaded', () => {
    cargarRifasSelect();
    
    // Listeners para carga automática al filtrar
    const elRifa = document.getElementById('filterRifa');
    const elEstado = document.getElementById('filterEstado');
    const elSearch = document.getElementById('searchNumeros');

    if(elRifa) elRifa.addEventListener('change', () => { paginaActual = 1; cargarInventario(); });
    if(elEstado) elEstado.addEventListener('change', () => { paginaActual = 1; cargarInventario(); });
    if(elSearch) elSearch.addEventListener('input', debounce(() => { paginaActual = 1; cargarInventario(); }, 500));
});

async function cargarRifasSelect() {
    try {
        const fd = new FormData();
        fd.append('action', 'obtener_rifas');
        const r = await fetch('ajax/numeros.ajax.php', { method: 'POST', body: fd });
        const j = await r.json();
        const s = document.getElementById('filterRifa');
        if (j.success && s) {
            s.innerHTML = '<option value="" disabled selected>Seleccione...</option>';
            j.data.forEach(r => {
                s.innerHTML += `<option value="${r.id_raffle}">${r.title_raffle}</option>`;
            });
            if(j.data.length > 0) {
                s.value = j.data[0].id_raffle;
                cargarInventario();
            }
        }
    } catch (e) { console.error(e); }
}

async function cargarInventario() {
    const idRaffle = document.getElementById('filterRifa').value;
    if(!idRaffle) return;

    document.getElementById('bodyTablaNumeros').innerHTML = `<tr><td colspan="4" class="text-center py-5 text-muted">Cargando...</td></tr>`;

    try {
        const fd = new FormData();
        fd.append('action', 'obtener_inventario');
        fd.append('id_raffle', idRaffle);
        fd.append('search', document.getElementById('searchNumeros').value);
        fd.append('status', document.getElementById('filterEstado').value);

        const res = await fetch('ajax/numeros.ajax.php', { method: 'POST', body: fd });
        const data = await res.json();
        
        cache = data.success ? data.data : [];
        renderTodo();
    } catch (e) { console.error(e); }
}

function renderTodo() {
    if (typeof PaginationHelper !== 'undefined') {
        const segmento = PaginationHelper.getSegment(cache, paginaActual, registrosPorPagina);
        renderTabla(segmento);
        PaginationHelper.render({
            totalItems: cache.length, currentPage: paginaActual, limit: registrosPorPagina,
            containerId: 'contenedorPaginacion', infoId: 'infoPaginacion', callbackName: 'cambiarPagina'
        });
    } else {
        const inicio = (paginaActual - 1) * registrosPorPagina;
        const segmento = cache.slice(inicio, inicio + registrosPorPagina);
        renderTabla(segmento);
    }
}

function cambiarPagina(p) { paginaActual = p; renderTodo(); }

function renderTabla(datos) {
    const tbody = document.getElementById('bodyTablaNumeros');
    
    if (!datos || datos.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center py-5 text-muted">No se encontraron números</td></tr>`;
        return;
    }

    tbody.innerHTML = datos.map((t) => {
        const status = parseInt(t.status_ticket); // 0=Libre, 1=Vendido, 2=Reservado
        
        let badge = '';
        let boton = '';
        const premiumChecked = Number(t.is_premium_ticket || 0) === 1 ? 'checked' : '';
        const premiumControl = status === 1
            ? '<span class="text-muted small">No editable</span>'
            : `
            <div class="form-check form-switch d-inline-block" title="Marcar o desmarcar como premium">
                <input
                    class="form-check-input"
                    type="checkbox"
                    ${premiumChecked}
                    onchange="cambiarPremiumTicket(${t.id_ticket}, this.checked, this)"
                >
            </div>
        `;

        if (status === 1) {
            // VENDIDO
            badge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3">Vendido</span>';
            boton = `<button class="btn btn-sm btn-light border text-muted" disabled title="No se puede cambiar"><i class="ti ti-ban me-1"></i>Bloqueado</button>`;
        } else if (status === 2) {
            // RESERVADO
            badge = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3">Reservado</span>';
            // Botón para Liberar (Volver a 0)
            boton = `<button class="btn btn-sm btn-outline-success border px-3" onclick="cambiarEstadoTicket(${t.id_ticket}, 0)">
                        <i class="ti ti-lock-open me-1"></i>Liberar
                     </button>`;
        } else {
            // DISPONIBLE (0)
            badge = '<span class="badge bg-success-subtle text-success border border-success-subtle px-3">Disponible</span>';
            // Botón para Reservar/Bloquear (Volver a 2)
            boton = `<button class="btn btn-sm btn-outline-secondary border px-3" onclick="cambiarEstadoTicket(${t.id_ticket}, 2)">
                        <i class="ti ti-lock me-1"></i>Reservar
                     </button>`;
        }

        return `
        <tr class="align-middle border-bottom hover-shadow">
            <td class="py-3 ps-5 text-start">
                <div class="d-inline-flex align-items-center justify-content-center rounded shadow-sm" 
                     style="background-color: #f5f5f5; color: #000; border: 1px solid #000000; width: 50px; height: 38px;">
                    <span class="fw-bold" style="font-size: 1rem; letter-spacing: 0.5px;">${t.number_ticket}</span>
                </div>
            </td>

            <td class="py-3 text-center">${badge}</td>
            <td class="py-3 text-center">${premiumControl}</td>

            <td class="py-3 text-end pe-5">${boton}</td>
        </tr>`;
    }).join('');
}

async function cambiarEstadoTicket(id, nuevoEstado) {
    // Confirmación suave
    const accion = nuevoEstado === 0 ? 'Liberar' : 'Reservar';
    if(!confirm(`¿Deseas ${accion} este número?`)) return;

    try {
        const fd = new FormData();
        fd.append('action', 'cambiar_estado');
        fd.append('id_ticket', id);
        fd.append('status', nuevoEstado);

        const r = await fetch('ajax/numeros.ajax.php', { method: 'POST', body: fd });
        const j = await r.json();

        if (j.success) {
            // Recargar datos sin refrescar toda la página
            cargarInventario();
        } else {
            alert(j.message || 'Error al cambiar estado');
        }
    } catch (e) { console.error(e); alert('Error de conexión'); }
}

async function cambiarPremiumTicket(id, checked, controlEl) {
    if (!id) return;
    const previous = !checked;
    if (controlEl) controlEl.disabled = true;
    try {
        const fd = new FormData();
        fd.append('action', 'marcar_flags');
        fd.append('id_ticket', id);
        fd.append('is_premium_ticket', checked ? 1 : 0);

        const r = await fetch('ajax/numeros.ajax.php', { method: 'POST', body: fd });
        const j = await r.json();

        if (!j.success) {
            if (controlEl) controlEl.checked = previous;
            alert(j.message || 'No se pudo actualizar premium');
            return;
        }

        cache = cache.map((item) => {
            if (Number(item.id_ticket) !== Number(id)) return item;
            return { ...item, is_premium_ticket: checked ? 1 : 0 };
        });
    } catch (e) {
        console.error(e);
        if (controlEl) controlEl.checked = previous;
        alert('Error de conexión');
    } finally {
        if (controlEl) controlEl.disabled = false;
    }
}

function limpiarFiltrosNumeros() {
    document.getElementById('searchNumeros').value = '';
    document.getElementById('filterEstado').value = '';
    cargarInventario();
}


function debounce(f, t) { let e; return () => { clearTimeout(e); e = setTimeout(f, t); } }