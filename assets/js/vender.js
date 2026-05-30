/**
 * vender.js - Gestión de Ventas Caballos Revelo
 * Refactorización Pro: Búsqueda Inteligente, Limpieza de Datos y Soporte Multi-dispositivo.
 * NUEVA LÓGICA: selección por cantidad de números.
 */

// --- 1. ESTADO GLOBAL ---
const estado = {
    rifa: null,
    cantidadSeleccionada: 0,
    paginaActual: 1,
    itemsPorPagina: 40,
    config: {
        rutas: {
            clientes: '/front/ajax/clientes.ajax.php',
            rifas: '/front/ajax/rifas.ajax.php',
            ventas: '/front/ajax/ventas.ajax.php'
        }
    }
};

/** Evita que avisos PHP (antes del JSON) rompan silenciosamente res.json(). */
async function parseAjaxJson(res) {
    const text = await res.text();
    try {
        return JSON.parse(text.trim());
    } catch (e) {
        console.error('Respuesta no JSON (HTTP ' + res.status + '):', text.slice(0, 1500));
        throw e;
    }
}

// --- 2. INICIALIZACIÓN ---
document.addEventListener('DOMContentLoaded', () => {
    inyectarEstilosMarca();
    initComponentes();
    cargarRifasActivas();
    asignarEventos();
});

function inyectarEstilosMarca() {

    const css = `
    <style>
        .btn-ticket-revelo {
            background-color:#ffffff !important;
            border:2px solid #1a1a1a !important;
            color:#1a1a1a !important;
            border-radius:10px;
            font-weight:700 !important;
            width:55px;
            height:55px;
        }

        .btn-resumen-tickets{
            background:#fff !important;
            border:1px solid #1a1a1a !important;
            border-radius:50px !important;
        }

        .pulse-gold{
            animation:pulse-animation .5s ease-in-out;
        }

        @keyframes pulse-animation{
            0%{transform:scale(1);}
            50%{transform:scale(1.15);box-shadow:0 0 10px #d4af37;}
            100%{transform:scale(1);}
        }
    </style>`;

    document.head.insertAdjacentHTML('beforeend', css);
}

// --- 3. EVENTOS ---
function asignarEventos() {

    $('#selectRifa').on('change', cambiarRifa);

    $('#cantidadNumeros').on('input', function () {

        estado.cantidadSeleccionada = parseInt(this.value) || 0;

        actualizarCarritoUI();
    });

    $('#departamento').on('change', function () {
        cargarCiudadesVenta(this.value);
    });

    $('#btnLimpiarCliente').on('click', resetClienteForm);

    $('#celularCliente').on('input paste', function () {

        let val = $(this).val().replace(/\D/g, '');

        if (val.startsWith('57') && val.length > 10) val = val.substring(2);

        $(this).val(val);

        if (val.length === 10) buscarClientePorCelular(val);
    });
}

// --- CLIENTES ---
async function buscarClientePorCelular(numero) {

    const fd = new FormData();

    fd.append('action', 'obtener');
    fd.append('search', numero);
    fd.append('status', 1);

    try {

        const res = await fetch(estado.config.rutas.clientes, {
            method: 'POST',
            body: fd
        });

        const json = await res.json();

        if (json.success && json.data && json.data.length > 0) {

            const clienteEncontrado = json.data[0];

            if (clienteEncontrado.phone_customer === numero) {

                llenarFormulario(clienteEncontrado);
            }
        }

    } catch (e) {

        console.error("Error buscando cliente:", e);
    }
}

// --- COMPONENTES ---
function initComponentes() {

    $('#buscadorCliente').select2({
        theme: 'bootstrap-5',
        placeholder: 'Buscar cliente...',
        allowClear: true,
        minimumInputLength: 3,
        ajax: {
            url: estado.config.rutas.clientes,
            type: 'POST',
            dataType: 'json',
            delay: 300,
            data: params => {

                let term = params.term ? params.term.trim() : "";

                if (/^[0-9\s+]+$/.test(term)) {

                    let digits = term.replace(/\D/g, '');

                    if (digits.startsWith('57') && digits.length > 10)
                        term = digits.substring(2);
                    else
                        term = digits;
                }

                return {
                    action: 'obtener',
                    search: term,
                    status: 1
                };
            },
            processResults: res => ({
                results: (res.success && res.data)
                    ? res.data.map(c => ({
                        id: c.id_customer,
                        text: `${c.name_customer} ${c.lastname_customer} (${c.phone_customer})`,
                        cliente: c
                    }))
                    : []
            })
        }
    })
        .on('select2:select', e => llenarFormulario(e.params.data.cliente))
        .on('select2:unselecting', resetClienteForm);

    $('.select2-ubicacion').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    if (typeof datosColombia !== 'undefined') {

        const $depto = $('#departamento');

        $depto.empty().append('<option value="">Seleccione...</option>');

        Object.keys(datosColombia)
            .sort()
            .forEach(d => $depto.append(new Option(d, d)));
    }
}

// --- RIFAS ---
async function cargarRifasActivas() {

    const fd = new FormData();
    fd.append('action', 'obtener_activas');

    try {

        const res = await fetch(estado.config.rutas.rifas, {
            method: 'POST',
            body: fd
        });

        const json = await res.json();

        if (json.success && json.data.length > 0) {

            const select = document.getElementById('selectRifa');

            // limpiar opciones por seguridad
            select.innerHTML = '';

            json.data.forEach((r, index) => {

                const opt = new Option(r.title_raffle, r.id_raffle);
                opt.dataset.precio = r.price_raffle;

                select.add(opt);

                // seleccionar la primera automáticamente
                if (index === 0) {

                    estado.rifa = {
                        id: r.id_raffle,
                        precio: parseFloat(r.price_raffle || 0)
                    };

                }

            });

        }

    } catch (e) {

        console.error("Error rifas:", e);

    }

}

function cambiarRifa() {

    const idRifa = $('#selectRifa').val();

    if (!idRifa) return;

    const opt = document.getElementById('selectRifa').selectedOptions[0];

    estado.rifa = {
        id: idRifa,
        precio: parseFloat(opt.dataset.precio || 0)
    };

    actualizarCarritoUI();
}

// --- RESUMEN ---
function actualizarCarritoUI() {

    const cantidad = estado.cantidadSeleccionada;

    const total = cantidad * (estado.rifa?.precio || 0);

    const fmt = n => new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        maximumFractionDigits: 0
    }).format(n);

    $('#lblTotalDesktop, #lblTotalMobile').text(fmt(total));

    $('#lblCantidadMobileBadge, #lblCantidadDesktop').text(cantidad);

    const listaHtml = cantidad === 0
        ? '<li class="list-group-item text-center text-muted py-4 border-0 small">Selecciona cantidad de números</li>'
        : `<li class="list-group-item d-flex justify-content-between align-items-center px-0 border-light">
            <span class="badge bg-dark rounded-pill">${cantidad} números</span>
            <span class="fw-bold small text-primary">${fmt(total)}</span>
        </li>`;

    $('#listaCarritoDesktop, #listaCarritoMobile').html(listaHtml);
}

// --- PROCESAR VENTA ---
async function procesarVenta() {

    const btnD = document.getElementById('btnCompletarVenta');
    const btnM = document.getElementById('btnCompletarVentaMobile');
    const spinnerHtml = '<span class="spinner-border spinner-border-sm"></span> Procesando...';

    if ((btnD && btnD.disabled) || (btnM && btnM.disabled)) return;

    if (!estado.rifa || !estado.rifa.id) {
        console.warn("[VENTA] No hay sorteo seleccionado");
        return alertify.error("No hay sorteo seleccionado.");
    }

    const cliente = {
        id: $('#idCliente').val(),
        nombre: $('#nombreCliente').val().trim(),
        apellido: $('#apellidoCliente').val().trim(),
        celular: $('#celularCliente').val().trim(),
        email: $('#emailCliente').val().trim(),
        depto: $('#departamento').val(),
        ciudad: $('#ciudad').val()
    };

    const metodo =
        $('input[name="metodoPago"]:checked').val()
        || $('input[name="metodoPagoMobile"]:checked').val();

    if (estado.cantidadSeleccionada <= 0) {
        console.warn("[VENTA] Cantidad inválida:", estado.cantidadSeleccionada);
        return alertify.error("Debes indicar la cantidad de números.");
    }

    if (estado.cantidadSeleccionada < 3) {
        return alertify.error("La compra mínima es de 3 stickers.");
    }

    if (!cliente.nombre || !cliente.apellido || !cliente.celular || !cliente.email || !cliente.depto || !cliente.ciudad) {
        console.warn("[VENTA] Cliente incompleto:", cliente);
        return alertify.error("Todos los campos obligatorios (*) deben estar llenos.");
    }

    if (!metodo) {
        console.warn("[VENTA] Sin método de pago");
        return alertify.error("Debes seleccionar un método de pago.");
    }

    const total = estado.cantidadSeleccionada * (estado.rifa?.precio || 0);

    alertify.confirm(
        "Confirmar Venta",
        `¿Deseas registrar la venta de <b>${estado.cantidadSeleccionada}</b> números?`,
        async function () {

            const codigoVenta = "AP" + Date.now() + Math.floor(Math.random() * 100);

            const numeroForzadoRaw = $('#numeroPremiado').val();
            const numeroForzado = (numeroForzadoRaw ?? '').trim();

            const action = numeroForzado ? 'crear_venta_mixta' : 'crear_venta';

            // console.group("🧾 PROCESO VENTA DEBUG");
            // console.log("➡️ Número forzado RAW:", numeroForzadoRaw);
            // console.log("➡️ Número forzado TRIM:", numeroForzado);
            // console.log("➡️ Acción seleccionada:", action);
            // console.log("➡️ Cliente:", cliente);
            // console.log("➡️ Cantidad:", estado.cantidadSeleccionada);
            // console.log("➡️ Total:", total);
            // console.groupEnd();

            // debugger;

            if (btnD) {
                btnD.disabled = true;
                btnD.innerHTML = spinnerHtml;
            }
            if (btnM) {
                btnM.disabled = true;
                btnM.innerHTML = spinnerHtml;
            }

            const fd = new FormData();

            fd.append('action', action);
            fd.append('premiado_ticket', numeroForzado);

            fd.append('code_sale', codigoVenta);
            fd.append('quantity_sale', estado.cantidadSeleccionada);
            fd.append('id_customer', cliente.id);
            fd.append('id_raffle', estado.rifa.id);
            fd.append('total_sale', total);
            fd.append('payment_method_sale', metodo);

            fd.append('name_customer', cliente.nombre);
            fd.append('lastname_customer', cliente.apellido);
            fd.append('phone_customer', cliente.celular);
            fd.append('email_customer', cliente.email);
            fd.append('department_customer', cliente.depto);
            fd.append('city_customer', cliente.ciudad);


            try {

                const res = await fetch(estado.config.rutas.ventas, {
                    method: 'POST',
                    body: fd
                });

                const json = await parseAjaxJson(res);

                console.log("📩 RESPUESTA BACKEND:", json);

                if (json.success) {

                    alertify.success("Venta Exitosa");
                    generarReciboFinal(json.id_sale);

                } else {

                    console.error("❌ Error backend:", json.message);

                    alertify.error(json.message);

                    if (btnD) {
                        btnD.disabled = false;
                        btnD.innerHTML = 'CONFIRMAR VENTA';
                    }
                    if (btnM) {
                        btnM.disabled = false;
                        btnM.innerHTML = 'CONFIRMAR VENTA <i class="ti ti-check ms-1"></i>';
                    }
                }

            } catch (e) {

                console.error("🔥 ERROR FETCH:", e);

                const msg =
                    e instanceof SyntaxError
                        ? "Respuesta inválida del servidor (¿avisos PHP?). Abre la consola (F12). En .env pon DISPLAY_ERRORS=false."
                        : "Error en el servidor";
                alertify.error(msg);

                if (btnD) {
                    btnD.disabled = false;
                    btnD.innerHTML = 'CONFIRMAR VENTA';
                }
                if (btnM) {
                    btnM.disabled = false;
                    btnM.innerHTML = 'CONFIRMAR VENTA <i class="ti ti-check ms-1"></i>';
                }
            }
        },
        null
    ).set('labels', { ok: 'SÍ, VENDER', cancel: 'CANCELAR' });
}

// --- RECIBO ---
async function generarReciboFinal(idVenta) {

    const fd = new FormData();

    fd.append('action', 'detalle_venta');
    fd.append('id_sale', idVenta);

    try {

        const res = await fetch(estado.config.rutas.ventas, {
            method: 'POST',
            body: fd
        });

        const json = await parseAjaxJson(res);

        if (json.success) {

            $('.fixed-bottom').addClass('d-none');

            const container = document.querySelector('.body-wrapper-inner');

            container.innerHTML = `
                <div class="container py-5 animated fadeIn">
                    ${json.html_recibo}
                    <div class="mt-4 text-center no-print">
                        <button class="btn btn-dark fw-bold px-5 rounded-pill shadow" onclick="location.reload()">NUEVA VENTA</button>
                    </div>
                </div>`;

            window.scrollTo(0, 0);
        }

    } catch (e) {

        alertify.error("Error visual al cargar el recibo.");

        setTimeout(() => location.reload(), 3000);
    }
}

// --- HELPERS ---
window.procesarVentaMobile = () => procesarVenta();

function llenarFormulario(c) {

    $('#idCliente').val(c.id_customer);
    $('#nombreCliente').val(c.name_customer);
    $('#apellidoCliente').val(c.lastname_customer);
    $('#celularCliente').val(c.phone_customer);
    $('#emailCliente').val(c.email_customer);

    if (c.department_customer) {

        $('#departamento').val(c.department_customer).trigger('change');

        setTimeout(() =>
            $('#ciudad').val(c.city_customer).trigger('change'), 150);
    }

    $('#btnLimpiarCliente').removeClass('d-none');

    toggleInputs(true);
}

function resetClienteForm() {

    $('#idCliente').val('');

    document.getElementById('formClienteVenta').reset();

    $('#departamento, #ciudad, #buscadorCliente')
        .val(null)
        .trigger('change');

    $('#btnLimpiarCliente').addClass('d-none');

    toggleInputs(false);
}

function toggleInputs(bloquear) {

    $('#formClienteVenta input:not([type="hidden"])')
        .prop('readonly', bloquear);
}

function cargarCiudadesVenta(depto) {

    const $ciudad = $('#ciudad');

    $ciudad.empty().append('<option value="">Seleccione...</option>');

    if (depto && datosColombia[depto]) {

        $ciudad.prop('disabled', false);

        datosColombia[depto].forEach(c =>
            $ciudad.append(new Option(c.display, c.value)));

    } else {

        $ciudad.prop('disabled', true);
    }

    $ciudad.trigger('change');
}

$('.paquete-radio').on('change', function () {

    if (this.value === 'custom') {
        $('#cantidadManual').show().focus();
        estado.cantidadSeleccionada = 0;
        actualizarCarritoUI();
        return;
    }

    $('#cantidadManual').hide().val('');
    estado.cantidadSeleccionada = parseInt(this.value, 10) || 0;
    actualizarCarritoUI();
});

$('#cantidadManual').on('input', function(){

    const val = parseInt(this.value) || 0;

    estado.cantidadSeleccionada = val;

    actualizarCarritoUI();

});