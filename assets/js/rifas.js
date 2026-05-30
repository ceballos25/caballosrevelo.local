/**
 * rifas.js - Gestión Completa Restaurada (Talla Mundial)
 */
let rifasCache = [], idRifaEliminar = null, modalRifa = null, modalConfirm = null;
let paginaActual = 1;
const registrosPorPagina = 10;
const RIFAS_ENDPOINT = '/front/ajax/rifas.ajax.php';

document.addEventListener('DOMContentLoaded', function() {
    const elModalRifa = document.getElementById('modalRifa');
    const elModalConfirm = document.getElementById('modalConfirm');

    if (elModalRifa) modalRifa = new bootstrap.Modal(elModalRifa);
    if (elModalConfirm) modalConfirm = new bootstrap.Modal(elModalConfirm);

    if (document.getElementById('bodyTabla')) cargarRifas();

    const inputSearch = document.getElementById('searchRifas');
    if (inputSearch) inputSearch.addEventListener('input', debounce(cargarRifas, 500));

    const selectStatus = document.getElementById('filterStatus');
    if (selectStatus) selectStatus.addEventListener('change', cargarRifas);
});

// Versión corregida que permite el valor 0
function setVal(id, value) {
    const el = document.getElementById(id);
    if (el) {
        // Si el valor es null o undefined, ponemos vacío. Si es 0 o cualquier otra cosa, ponemos el valor.
        el.value = (value !== null && value !== undefined) ? value : '';
    }
}

function setTxt(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}

async function cargarRifas() {
    if (typeof showPreloader === 'function') showPreloader(); 
    try {
        const searchInput = document.getElementById('searchRifas');
        const statusSelect = document.getElementById('filterStatus');

        const formData = new FormData();
        formData.append('action', 'obtener');
        formData.append('search', searchInput ? searchInput.value.trim() : '');
        formData.append('status', statusSelect ? statusSelect.value : '');

        const response = await fetch(RIFAS_ENDPOINT, { method: 'POST', body: formData });
        const data = await response.json();

        if (data.success) {
            rifasCache = data.data || [];
            renderizarTodo();
        }
    } catch (error) {
        console.error('Error:', error);
    } finally {
        if (typeof hidePreloader === 'function') hidePreloader();
    }
}

function renderizarTodo() {
    if (typeof PaginationHelper === 'undefined') return;
    const segmento = PaginationHelper.getSegment(rifasCache, paginaActual, registrosPorPagina);
    renderTabla(segmento);
    PaginationHelper.render({
        totalItems: rifasCache.length,
        currentPage: paginaActual,
        limit: registrosPorPagina,
        containerId: 'contenedorPaginacion',
        infoId: 'infoPaginacion',
        callbackName: 'cambiarPagina'
    });
}

function renderTabla(rifas) {
    const tbody = document.getElementById('bodyTabla');
    if (!tbody) return;
    if (!rifas || rifas.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center py-5 text-muted">No hay rifas registradas</td></tr>`;
        return;
    }

    tbody.innerHTML = rifas.map(r => {
        const activo = parseInt(r.status_raffle) === 1;
        return `
            <tr>
                <td>${r.id_raffle}</td>
                <td>${r.title_raffle}</td>
                <td>${r.description_raffle || '-'}</td>
                <td>${r.digits_raffle} cifras</td>
                <td>$${parseFloat(r.price_raffle).toLocaleString()}</td>
                <td>${r.date_raffle || '-'}</td>
                <td>${r.promotions_raffle || '-'}</td>
                <td><span class="badge ${activo ? 'bg-success' : 'bg-danger'}">${activo ? 'Activa' : 'Inactiva'}</span></td>
                <td>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-primary" onclick="editarRifa(${r.id_raffle})"><i class="ti ti-edit"></i></button>
                    </div>
                </td>
            </tr>`;
    }).join('');
}

function abrirModal() {
    const form = document.getElementById('formRifa');
    if (form) form.reset();
    setVal('rifaId', '');
    setVal('promociones', ''); 
    setTxt('modalTitle', 'Nueva Rifa');
    if(modalRifa) modalRifa.show();
}

function editarRifa(id) {
    const r = rifasCache.find(x => parseInt(x.id_raffle) === parseInt(id));
    if (!r) return;

    setVal('rifaId', r.id_raffle);
    setVal('titulo', r.title_raffle);
    setVal('descripcion', r.description_raffle);
    setVal('promociones', r.promotions_raffle);
    setVal('precio', r.price_raffle);
    setVal('cifras', r.digits_raffle);
    setVal('fecha', r.date_raffle ? r.date_raffle.replace(" ", "T") : '');
    setVal('estado', r.status_raffle);
    
    setTxt('modalTitle', 'Editar Rifa');
    if(modalRifa) modalRifa.show();
}

async function guardarRifa() {
    const id = document.getElementById('rifaId')?.value;
    const formData = new FormData();
    formData.append('action', id ? 'actualizar' : 'crear');
    formData.append('id_raffle', id || '');
    formData.append('title_raffle', document.getElementById('titulo')?.value.trim() || '');
    formData.append('description_raffle', document.getElementById('descripcion')?.value.trim() || '');
    formData.append('promotions_raffle', document.getElementById('promociones')?.value.trim() || ''); 
    formData.append('price_raffle', document.getElementById('precio')?.value || '0');
    formData.append('digits_raffle', document.getElementById('cifras')?.value || '4');
    formData.append('date_raffle', document.getElementById('fecha')?.value || '');
    formData.append('status_raffle', document.getElementById('estado')?.value || '1');

    if (typeof showPreloader === 'function') showPreloader();
    try {
        const res = await fetch(RIFAS_ENDPOINT, { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            alertify.success(data.message);
            if(modalRifa) modalRifa.hide();
            cargarRifas();
        } else {
            alertify.error(data.message);
        }
    } catch (e) { alertify.error("Error en la solicitud"); }
    finally { if (typeof hidePreloader === 'function') hidePreloader(); }
}

function eliminarRifa(id) { 
    idRifaEliminar = id; 
    if(modalConfirm) modalConfirm.show(); 
}

async function confirmarEliminar() {
    const fd = new FormData();
    fd.append('action', 'eliminar');
    fd.append('id_raffle', idRifaEliminar);
    if (typeof showPreloader === 'function') showPreloader();
    try {
        const res = await fetch(RIFAS_ENDPOINT, { method: 'POST', body: fd });
        const data = await res.json();
        if(data.success) {
            alertify.success('Eliminado');
            if(modalConfirm) modalConfirm.hide();
            cargarRifas();
        }
    } finally { if (typeof hidePreloader === 'function') hidePreloader(); }
}

function cambiarPagina(p) { paginaActual = p; renderizarTodo(); }
function debounce(f, w) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => f(...a), w); }; }

function limpiarFiltros() {
    setVal('searchRifas', '');
    setVal('filterStatus', '');
    paginaActual = 1;
    cargarRifas();
}