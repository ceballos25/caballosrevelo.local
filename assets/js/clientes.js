/**
 * clientes.js - Gestión Total Blindada (Sin Omisiones)
 */
let clientesCache = [], idClienteEliminar = null, modalCliente = null, modalConfirm = null;
let paginaActual = 1;
const registrosPorPagina = 10;
const CLIENTES_ENDPOINT = '/front/ajax/clientes.ajax.php';

// ==========================================
// FUNCIONES GLOBALES (ACCESIBLES DESDE EL HTML)
// ==========================================

/**
 * Abre el modal para crear un nuevo cliente
 */
function abrirModal() {
    const form = document.getElementById('formCliente');
    if (form) form.reset();
    
    setVal('clienteId', '');
    document.getElementById('modalTitle').textContent = 'Nuevo Cliente';

    // Resetear Select2 y disparar evento para sincronizar departamentos-ciudades.js
    $('#departamento').val('').trigger('change');
    $('#ciudad').val('').trigger('change').prop('disabled', true);
    
    if(modalCliente) modalCliente.show();
}

/**
 * Cambia la página actual del listado
 */
function cambiarPagina(p) { 
    paginaActual = p; 
    renderizarTodo(); 
}

/**
 * Helper para asignar valores a inputs, permitiendo el valor 0 (Inactivo)
 */
function setVal(id, value) {
    const el = document.getElementById(id);
    if (el) {
        el.value = (value !== null && value !== undefined) ? value : '';
    }
}

// ==========================================
// INICIALIZACIÓN Y CARGA
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    const elModalCliente = document.getElementById('modalCliente');
    const elModalConfirm = document.getElementById('modalConfirm');

    if (elModalCliente) modalCliente = bootstrap.Modal.getOrCreateInstance(elModalCliente);
    if (elModalConfirm) modalConfirm = bootstrap.Modal.getOrCreateInstance(elModalConfirm);

    // Inicializar Select2 con temas originales
    if ($('.select2-departamento').length) {
        $('.select2-departamento').select2({
            theme: 'bootstrap-5', dropdownParent: $('#modalCliente'), width: '100%', placeholder: 'Departamento'
        });
    }
    if ($('.select2-ciudad').length) {
        $('.select2-ciudad').select2({
            theme: 'bootstrap-5', dropdownParent: $('#modalCliente'), width: '100%', placeholder: 'Ciudad'
        });
    }

    // Activar lógica de departamentos-ciudades.js si existe
    if (typeof inicializarUbicacion === 'function') inicializarUbicacion();

    if (document.getElementById('bodyTabla')) cargarClientes();

    const inputSearch = document.getElementById('searchClientes');
    if (inputSearch) inputSearch.addEventListener('input', debounce(cargarClientes, 500));

    const selectStatus = document.getElementById('filterStatus');
    if (selectStatus) selectStatus.addEventListener('change', cargarClientes);
});

async function cargarClientes() {
    if (typeof showPreloader === 'function') showPreloader();
    try {
        const formData = new FormData();
        formData.append('action', 'obtener');
        formData.append('search', document.getElementById('searchClientes')?.value.trim() || '');
        formData.append('status', document.getElementById('filterStatus')?.value || '');

        const response = await fetch(CLIENTES_ENDPOINT, { method: 'POST', body: formData });
        const data = await response.json();

        if (data.success) {
            clientesCache = data.data || [];
            renderizarTodo();
        }
    } catch (e) { console.error("Error al cargar:", e); }
    finally { if (typeof hidePreloader === 'function') hidePreloader(); }
}

function renderizarTodo() {
    if (typeof PaginationHelper === 'undefined') return;
    const segmento = PaginationHelper.getSegment(clientesCache, paginaActual, registrosPorPagina);
    renderTabla(segmento);
    PaginationHelper.render({
        totalItems: clientesCache.length, currentPage: paginaActual, limit: registrosPorPagina,
        containerId: 'contenedorPaginacion', infoId: 'infoPaginacion', callbackName: 'cambiarPagina'
    });
}

function renderTabla(clientes) {
    const tbody = document.getElementById('bodyTabla');
    if (!tbody) return;
    if (!clientes || clientes.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-muted">No se encontraron registros</td></tr>`;
        return;
    }
    tbody.innerHTML = clientes.map(c => {
        const activo = parseInt(c.status_customer) === 1;
        return `
            <tr>
                <td>${c.id_customer}</td>
                <td>${c.name_customer} ${c.lastname_customer}</td>
                <td>${c.phone_customer || '-'}</td>
                <td>${c.email_customer || '-'}</td>
                <td>${c.department_customer || '-'}</td>
                <td>${c.city_customer || '-'}</td>
                <td><span class="badge ${activo ? 'bg-success' : 'bg-danger'}">${activo ? 'Activo' : 'Inactivo'}</span></td>
                <td>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-primary" onclick="editarCliente(${c.id_customer})"><i class="ti ti-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarCliente(${c.id_customer})"><i class="ti ti-trash"></i></button>
                    </div>
                </td>
            </tr>`;
    }).join('');
}

function editarCliente(id) {
    const c = clientesCache.find(x => parseInt(x.id_customer) === parseInt(id));
    if (!c) return;

    setVal('clienteId', c.id_customer);
    setVal('nombre', c.name_customer);
    setVal('apellido', c.lastname_customer);
    setVal('telefono', c.phone_customer);
    setVal('email', c.email_customer);
    setVal('estado', c.status_customer); // Soporta el 0 perfectamente

    // LÓGICA DE UBICACIÓN SINCRONIZADA
    if (c.department_customer) {
        $('#departamento').val(c.department_customer).trigger('change');
        if (c.city_customer) {
            // Espera a que el script cargue las ciudades del departamento
            setTimeout(() => {
                $('#ciudad').val(c.city_customer).trigger('change');
            }, 350);
        }
    }

    document.getElementById('modalTitle').textContent = 'Editar Cliente';
    if(modalCliente) modalCliente.show();
}

async function guardarCliente() {
    const id = document.getElementById('clienteId').value;
    const formData = new FormData();
    formData.append('action', id ? 'actualizar' : 'crear');
    formData.append('id_customer', id);
    formData.append('name_customer', document.getElementById('nombre').value.trim());
    formData.append('lastname_customer', document.getElementById('apellido').value.trim());
    formData.append('phone_customer', document.getElementById('telefono').value.trim());
    formData.append('email_customer', document.getElementById('email').value.trim());
    formData.append('department_customer', $('#departamento').val() || '');
    formData.append('city_customer', $('#ciudad').val() || '');
    formData.append('status_customer', document.getElementById('estado').value);

    if (typeof showPreloader === 'function') showPreloader();
    try {
        const res = await fetch(CLIENTES_ENDPOINT, { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            alertify.success(data.message);
            if(modalCliente) modalCliente.hide();
            cargarClientes();
        } else { alertify.error(data.message); }
    } catch (e) { alertify.error("Error en la solicitud"); }
    finally { if (typeof hidePreloader === 'function') hidePreloader(); }
}

function eliminarCliente(id) { 
    idClienteEliminar = id; 
    if(modalConfirm) modalConfirm.show(); 
}

async function confirmarEliminar() {
    if (typeof showPreloader === 'function') showPreloader();
    try {
        const fd = new FormData();
        fd.append('action', 'eliminar');
        fd.append('id_customer', idClienteEliminar);
        const res = await fetch(CLIENTES_ENDPOINT, { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            alertify.success('Eliminado');
            if(modalConfirm) modalConfirm.hide();
            cargarClientes();
        }
    } finally { if (typeof hidePreloader === 'function') hidePreloader(); }
}

function limpiarFiltros() {
    document.getElementById('searchClientes').value = '';
    document.getElementById('filterStatus').value = '';
    paginaActual = 1;
    cargarClientes();
}

function debounce(f, w) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => f(...a), w); }; }