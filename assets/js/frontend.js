/**
 * frontend-v3.js – SISTEMA POR PAQUETES
 * Caballos Revelo
 */

/* ================== ESTADO GLOBAL ================== */

const estado = {
    rifa: { id: null, precio: 0 },
    inventarioCompleto: [],
    cantidadSeleccionada: 0,
    rutas: {
        numeros: 'front/ajax/numeros.ajax.php',
        ventas: 'front/ajax/ventas.ajax.php',
        clientes: 'front/ajax/clientes.ajax.php'
    }
};

    document.addEventListener('DOMContentLoaded', function () {
    var main = new Splide('#main-carousel', {
        type: 'fade',
        rewind: true,
        pagination: false,
        arrows: true,
    });

    var thumbnails = new Splide('#thumbnail-carousel', {
        fixedWidth: 90,
        fixedHeight: 60,
        gap: 10,
        rewind: true,
        pagination: false,
        isNavigation: true,
        focus: 'center',
        cover: true,
        breakpoints: {
        600: {
            fixedWidth: 60,
            fixedHeight: 44,
        },
        },
    });

    main.sync(thumbnails);
    main.mount();
    thumbnails.mount();
    });

/* ================== META EVENTS (IDs neutros, sin texto de negocio) ================== */

function metaCatalogId(quantity) {
    const catalog = String(estado.rifa.id || '0');
    const qty = Number(quantity) || 0;
    return qty > 0 ? catalog + '-' + qty : catalog;
}

function metaEventRef(prefix, extra) {
    const parts = [prefix, String(estado.rifa.id || '0')];
    if (extra !== undefined && extra !== null && String(extra) !== '') {
        parts.push(String(extra).replace(/\D+/g, '') || String(extra));
    }
    return parts.join('-');
}

function metaCommercePayload(quantity, value) {
    if (typeof MetaEvents === 'undefined') {
        return null;
    }
    return MetaEvents.commerceData(
        quantity,
        value,
        metaCatalogId(quantity)
    );
}

function metaTrack(eventName, customData, eventRef, userData) {
    if (typeof MetaEvents === 'undefined') {
        return Promise.resolve(null);
    }
    return MetaEvents.track(eventName, customData || {}, eventRef || null, userData || null);
}

function metaTrackOnce(storageKey, eventName, customData, eventRef, userData) {
    if (typeof MetaEvents === 'undefined') {
        return Promise.resolve(null);
    }
    return MetaEvents.trackOnce(storageKey, eventName, customData || {}, eventRef || null, userData || null);
}

function metaCheckoutUserData() {
    return {
        name_customer: $('#nombreCliente').val().trim(),
        lastname_customer: $('#apellidoCliente').val().trim(),
        phone_customer: $('#celularCliente').val().replace(/\D/g, ''),
        email_customer: $('#emailCliente').val().trim(),
        department_customer: $('#departamento').val(),
        city_customer: $('#ciudad').val()
    };
}

function metaAfterInventoryLoaded() {
    const value = precioUnitarioBoleta();
    if (!(value > 0)) {
        return;
    }

    metaTrackOnce(
        'viewcontent-' + (estado.rifa.id || '0'),
        'ViewContent',
        metaCommercePayload(1, value),
        metaEventRef('vc')
    );
}

function metaOnPackageSelected(sourceId) {
    const cant = estado.cantidadSeleccionada;
    const total = totalAPagarCOP();
    if (cant <= 0 || total <= 0) {
        return;
    }

    const payload = metaCommercePayload(cant, total);
    metaTrack(
        'AddToCart',
        payload,
        metaEventRef('atc', cant),
        null
    );

    if (sourceId === 'paqCustom') {
        metaTrack(
            'CustomizeProduct',
            payload,
            metaEventRef('cp', cant)
        );
    }
}


/* ================== INIT ================== */
let ORIGEN = null;
$(document).ready(function () {
        
    ORIGEN = obtenerOrigenURL();

    console.log('🔥 ORIGEN DETECTADO:', ORIGEN);

    inicializarSistema();


    $('#celularCliente').on('input paste', function () {

        let val = $(this).val().replace(/\D/g, '');

        if (val.startsWith('57') && val.length > 10)
            val = val.substring(2);

        $(this).val(val);

        if (val.length === 10)
            buscarClientePorCelular(val);

    });

    if (typeof datosColombia !== 'undefined') {

        cargarDepartamentos();

        $('#departamento').on('change', function () {
            cargarCiudades(this.value);
        });

    }

});


/* ================== SISTEMA ================== */

async function inicializarSistema() {

    await cargarSettingsGlobal();

    const idCfg = parseInt(String(typeof getSetting === 'function' ? getSetting('web_id_raffle', '') : '').trim(), 10);
    if (Number.isFinite(idCfg) && idCfg > 0) {
        estado.rifa.id = idCfg;
    } else {
        estado.rifa.id = 1;
    }

    await cargarInventario();
    actualizarPrecioVisual();

    // 🔥 PROGRESO (una sola consulta al cargar la página; suma ajuste `barra` desde settings)
    const porcentajeBackend = await cargarPorcentajeBackend();

    aplicarBloqueoComprasWeb();
    actualizarBarraProgreso(porcentajeBackend);
    metaAfterInventoryLoaded();
}

/* ================== COMPRAS WEB (SETTINGS) ================== */

function webComprasHabilitadas() {
    const raw =
        typeof getSetting === 'function'
            ? getSetting('web_compras_habilitadas', '1')
            : '1';
    const v = String(raw ?? '1').trim().toLowerCase();
    return !['0', 'false', 'no', 'off'].includes(v);
}

function aplicarBloqueoComprasWeb() {
    const ok = webComprasHabilitadas();
    const banner = document.getElementById('webComprasBloqueadasBanner');
    if (banner) {
        if (!ok) {
            let msg =
                'Las compras en línea están temporalmente deshabilitadas. Puedes seguir navegando y consultar tus números.';
            if (typeof getSetting === 'function') {
                const custom = String(getSetting('web_mensaje_compras_bloqueadas', '') || '').trim();
                if (custom !== '') {
                    msg = custom;
                }
            }
            banner.textContent = msg;
            banner.classList.remove('d-none');
        } else {
            banner.classList.add('d-none');
            banner.textContent = '';
        }
    }

    if (!ok) {
        $('#btnPagarDesktop, #btnPagarMobile').prop('disabled', true);
        $('#comprobantePago').prop('disabled', true);
        document.querySelectorAll('[data-metodo]').forEach((el) => {
            el.disabled = true;
        });
        const finalPay = document.getElementById('btnPagarFinal');
        if (finalPay) finalPay.disabled = true;
    } else {
        $('#comprobantePago').prop('disabled', false);
        document.querySelectorAll('[data-metodo]').forEach((el) => {
            el.disabled = false;
        });
        const finalPay = document.getElementById('btnPagarFinal');
        if (finalPay) finalPay.disabled = false;
        actualizarUI();
    }
}


/* ================== INVENTARIO ================== */

async function cargarInventario() {

    showPreloader();

    const fd = new FormData();
    fd.append('action', 'obtener_inventario');
    fd.append('id_raffle', estado.rifa.id);

    const res = await fetch(estado.rutas.numeros, {
        method: 'POST',
        body: fd
    });

    const json = await res.json();

    if (json.success) {

        estado.inventarioCompleto = json.data.filter(t => t.status_ticket == 0);

        const p = Number(json.price_raffle);
        if (Number.isFinite(p) && p > 0) {
            estado.rifa.precio = p;
        }
    }

    hidePreloader();

}


/* ================== PAQUETES ================== */

$(document).on('change', '.paquete-radio', function () {

    if (this.value === 'custom') {
        $('#cantidadManual').show().focus();
        return;
    }

    $('#cantidadManual').hide();

    estado.cantidadSeleccionada = parseInt(this.value);

    actualizarUI();
    metaOnPackageSelected(this.id);

});

$('#cantidadManual').on('blur', function () {

    let cant = parseInt(this.value);

    if (!cant) return;

    if (cant < 3) {

        toastError("Recuerda mínimo 3 stickers para participar");

        this.value = 3;
        cant = 3;
    }

    estado.cantidadSeleccionada = cant;

    actualizarUI();
    metaOnPackageSelected('paqCustom');

});


/* ================== PRECIOS (solo BD: price_raffle vía obtener_inventario) ================== */

function precioUnitarioBoleta() {
    return Number(estado.rifa.precio) || 0;
}

/** Total COP a pagar = cantidad × precio BD (redondeado como en el servidor). */
function totalAPagarCOP() {
    const c = Number(estado.cantidadSeleccionada) || 0;
    const u = precioUnitarioBoleta();
    if (c <= 0 || !(u > 0)) {
        return 0;
    }
    return Math.round(c * u);
}

function actualizarPrecioVisual() {

    const u = precioUnitarioBoleta();
    if (u > 0) {
        $('#precioBoletaDisplay').text('$' + u.toLocaleString('es-CO'));
    } else {
        $('#precioBoletaDisplay').text('—');
    }

}


/* ================== UI ================== */

function actualizarUI() {

    const cant = estado.cantidadSeleccionada;

    const total = totalAPagarCOP();

    actualizarPrecioVisual();

    const fmt = n =>
        new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            maximumFractionDigits: 0
        }).format(n);


    $('#cantTicketsDesktop, #lblCantidadMobile').text(cant);

    $('#totalDineroDesktop, #lblTotalMobile, #resumenTotal').text(fmt(total));


    $('#resumenNumeros').html(

        cant
            ? `<span class="fw-bold">${cant}</span>`
            : '<span class="text-muted">Sin selección</span>'

    );


    const comprasOk = webComprasHabilitadas();

    $('#btnPagarDesktop, #btnPagarMobile').prop('disabled', !comprasOk || !cant);


    const bar = document.getElementById('mobileCart');

    if (bar)
        bar.style.display = cant ? '' : 'none';

}


/* ================== UBICACIÓN ================== */

function cargarDepartamentos() {

    const $d = $('#departamento');

    $d.empty().append('<option value="">Seleccione...</option>');

    Object.keys(datosColombia)
        .sort()
        .forEach(d => $d.append(new Option(d, d)));

}

function cargarCiudades(dep) {

    const $c = $('#ciudad');

    $c.empty().append('<option value="">Seleccione...</option>');

    if (dep && datosColombia[dep]) {

        datosColombia[dep].forEach(c =>
            $c.append(new Option(c.display || c, c.value || c))
        );

    }

}


/* ================== CLIENTE ================== */

async function buscarClientePorCelular(tel) {

    const fd = new FormData();

    fd.append('action', 'obtener');
    fd.append('search', tel);

    const r = await fetch(estado.rutas.clientes, {
        method: 'POST',
        body: fd
    });

    const j = await r.json();

    if (j.success && j.data.length) {

        const c = j.data[0];

        $('#nombreCliente').val(c.name_customer);
        $('#apellidoCliente').val(c.lastname_customer);
        $('#emailCliente').val(c.email_customer);

        $('#departamento')
            .val(c.department_customer)
            .trigger('change');

        setTimeout(() => {
            $('#ciudad').val(c.city_customer);
        }, 200);

    }

}


/* ================== UTILIDADES ================== */

function toastError(msg) {

    Toastify({
        text: msg,
        backgroundColor: '#dc3545',
        duration: 2500
    }).showToast();

}

function esEmailValido(email) {

    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

}

function setLoadingBtn(btnId, loading = true) {

    const btn = document.getElementById(btnId);

    if (!btn) return;

    btn.disabled = loading;

    btn.querySelector('.spinner-border')
        ?.classList.toggle('d-none', !loading);

}


/* ================== CHECKOUT ================== */

function abrirCheckout() {

    if (!webComprasHabilitadas()) {
        toastError('Las compras en línea no están disponibles en este momento.');
        return;
    }

if (!estado.cantidadSeleccionada || estado.cantidadSeleccionada < 3) {

    toastError('La compra mínima es de 3 números');

    return;

}

    if (estado.cantidadSeleccionada > estado.inventarioCompleto.length) {

        toastError('No hay suficientes números disponibles');

        return;

    }

    const total = totalAPagarCOP();
    if (total <= 0) {
        toastError('No se pudo calcular el total desde la rifa. Recarga la página e intenta de nuevo.');
        return;
    }

    $('#totalPagarInput').val(total);

    metaTrackOnce(
        'checkout-' + estado.cantidadSeleccionada + '-' + total,
        'InitiateCheckout',
        metaCommercePayload(estado.cantidadSeleccionada, total),
        metaEventRef('ic', estado.cantidadSeleccionada + '-' + total)
    );

    const modal = new bootstrap.Modal(
        document.getElementById('modalCheckout')
    );

    modal.show();

}


/* ================== PAGO ================== */

async function iniciarPagoPSE() {

    if (!webComprasHabilitadas()) {
        toastError('Las compras en línea no están disponibles en este momento.');
        return;
    }

    const datos = validarFormularioCheckout();

    if (!datos) return;


    if (estado.cantidadSeleccionada > estado.inventarioCompleto.length) {

        toastError('No hay suficientes números disponibles');

        return;

    }

    if (totalAPagarCOP() <= 0) {
        toastError('No se pudo calcular el total desde la rifa. Recarga la página e intenta de nuevo.');
        return;
    }


    setLoadingBtn('btnPagarFinal', true);

    showPreloader();


    const payload = {

        action: 'crear_respaldo',

        id_raffle: estado.rifa.id,

        quantity: estado.cantidadSeleccionada,

        amount: totalAPagarCOP(),

        name_customer: datos.nombre,
        lastname_customer: datos.apellido,
        phone_customer: datos.celular,
        email_customer: datos.email,
        department_customer: datos.departamento,
        city_customer: datos.ciudad,
        source_payment_backup: ORIGEN,
        meta_fbp: typeof MetaEvents !== 'undefined' ? MetaEvents.getFbp() : '',
        meta_fbc: typeof MetaEvents !== 'undefined' ? MetaEvents.getFbc() : '',

    };


    try {

        const res = await fetch('front/ajax/web.ajax.php', {
            method: 'POST',
            body: new URLSearchParams(payload)
        });

        const json = await res.json();

        if (!json.success)
            throw new Error(json.message || 'No se pudo crear el respaldo');


        window.PAYMENT_BACKUP_ID = json.id_payment_backup;

        await irAOpenPay();

    }

    catch (e) {

        toastError(e.message);

        setLoadingBtn('btnPagarFinal', false);

        hidePreloader();

    }

}


/* ================== OPENPAY ================== */

async function irAOpenPay() {

    if (!window.PAYMENT_BACKUP_ID) {

        toastError("No hay respaldo de pago");

        return;

    }

    const data = {

        action: 'ir_openpay',

        id_payment_backup: window.PAYMENT_BACKUP_ID,

        name_customer: $('#nombreCliente').val(),
        lastname_customer: $('#apellidoCliente').val(),
        phone_customer: $('#celularCliente').val(),
        email_customer: $('#emailCliente').val()

    };


    try {

        const res = await fetch('front/ajax/web.ajax.php', {
            method: 'POST',
            body: new URLSearchParams(data)
        });

        const json = await res.json();

        if (!json.success)
            throw new Error(json.message || 'Error al ir a OpenPay');


        window.location.href = json.redirect_url;

    }

    catch (e) {

        toastError(e.message);

        setLoadingBtn('btnPagarFinal', false);

        hidePreloader();

    }

}


/* ================== VALIDACIÓN ================== */

function validarFormularioCheckout() {

if (!estado.cantidadSeleccionada || estado.cantidadSeleccionada < 3) {

    toastError("La compra mínima es de 3 números");

    return false;

}

    const datos = {

        nombre: $('#nombreCliente').val().trim(),
        apellido: $('#apellidoCliente').val().trim(),
        celular: $('#celularCliente').val().replace(/\D/g, ''),
        email: $('#emailCliente').val().trim(),
        departamento: $('#departamento').val(),
        ciudad: $('#ciudad').val()

    };


    if (datos.celular.length !== 10)
        return toastError("Celular inválido"), false;

    if (!datos.nombre)
        return toastError("Ingresa tu nombre"), false;

    if (!datos.apellido)
        return toastError("Ingresa tu apellido"), false;

    if (!esEmailValido(datos.email))
        return toastError("Correo inválido"), false;

    if (!datos.departamento)
        return toastError("Selecciona departamento"), false;

    if (!datos.ciudad)
        return toastError("Selecciona ciudad"), false;

    metaTrackOnce(
        'lead-' + datos.celular,
        'Lead',
        metaCommercePayload(estado.cantidadSeleccionada, totalAPagarCOP()),
        metaEventRef('ld', datos.celular),
        metaCheckoutUserData()
    );

    return datos;

}

async function seleccionarMetodo(tipo) {

    if (!webComprasHabilitadas()) {
        toastError('Las compras en línea no están disponibles en este momento.');
        return;
    }

    const pse = document.getElementById('metodoPSE');
    const transferencia = document.getElementById('metodoTransferencia');

    const metodos = [pse, transferencia];

    // ocultar
    metodos.forEach(el => {
        el.classList.remove('show');
        el.classList.add('d-none');
    });

    // botones activos
    document.querySelectorAll('[data-metodo]').forEach(btn => {
        btn.classList.remove('active');
    });

    const btnActivo = document.querySelector(`[data-metodo="${tipo}"]`);
    if (btnActivo) btnActivo.classList.add('active');

    // 🔥 SOLO TRANSFERENCIA CREA RESPALDO
    if (tipo === 'transferencia') {

        const ok = await crearRespaldoTransferencia();
        if (!ok) return;

    }

    const target = tipo === 'pse' ? pse : transferencia;

    target.classList.remove('d-none');

    requestAnimationFrame(() => {
        target.classList.add('show');
    });

    metaTrackOnce(
        'payment-' + tipo + '-' + estado.cantidadSeleccionada,
        'AddPaymentInfo',
        metaCommercePayload(estado.cantidadSeleccionada, totalAPagarCOP()),
        metaEventRef('pi', tipo === 'pse' ? '1' : '2'),
        metaCheckoutUserData()
    );
}

async function procesarTransferencia(e) {

    e.preventDefault();

    if (!webComprasHabilitadas()) {
        toastError('Las compras en línea no están disponibles en este momento.');
        return;
    }

    const datos = validarFormularioCheckout();
    if (!datos) return;

    const file = document.getElementById('comprobantePago').files[0];

    if (!file) {
        toastError("Debes subir el comprobante");
        return;
    }

    if (estado.cantidadSeleccionada < 3) {
        toastError("Mínimo 3 números");
        return;
    }

    const total = totalAPagarCOP();
    if (total <= 0) {
        toastError('No se pudo calcular el total desde la rifa. Recarga la página e intenta de nuevo.');
        return;
    }

    const formData = new FormData();

    formData.append('action', 'crear_transferencia_completa');

    // 🔥 datos compra
    formData.append('id_raffle', estado.rifa.id);
    formData.append('quantity', estado.cantidadSeleccionada);
    formData.append('amount', total);

    // 🔥 cliente
    formData.append('name_customer', datos.nombre);
    formData.append('lastname_customer', datos.apellido);
    formData.append('phone_customer', datos.celular);
    formData.append('email_customer', datos.email);
    formData.append('department_customer', datos.departamento);
    formData.append('city_customer', datos.ciudad);

    // 🔥 archivo
    formData.append('comprobante', file);
    //Origen de venta en el campo CP para detectar origen de venta el parametro debe ser CP=cualquier cosa
    formData.append('source_transfer', ORIGEN);

    showPreloader();

    try {

        const res = await fetch('front/ajax/web.ajax.php', {
            method: 'POST',
            body: formData
        });

        const json = await res.json();

        if (!json.success)
            throw new Error(json.message);

        metaTrackOnce(
            'transfer-' + (json.code_transfer || 'pending'),
            'SubmitApplication',
            metaCommercePayload(estado.cantidadSeleccionada, total),
            metaEventRef('sa', json.code_transfer || 'pending'),
            metaCheckoutUserData()
        );

        // 🚀 REDIRECCIÓN FINAL
        window.location.href = `transferencia.php?code=${json.code_transfer}`;

    } catch (e) {

        toastError(e.message);

    }

    hidePreloader();
}

function copiarTexto(id) {

    const el = document.getElementById(id);

    if (!el) {
        console.error("No existe el elemento:", id);
        return;
    }

    const texto = el.innerText;

    // ✔️ MÉTODO MODERNO
    if (navigator.clipboard && window.isSecureContext) {

        navigator.clipboard.writeText(texto)
            .then(() => mostrarToastCopiado(texto))
            .catch(() => copiarFallback(texto));

    } else {
        // ❗ fallback para http o navegadores viejos
        copiarFallback(texto);
    }
}


// 🔁 FALLBACK UNIVERSAL
function copiarFallback(texto) {

    const textarea = document.createElement("textarea");
    textarea.value = texto;
    textarea.style.position = "fixed";
    textarea.style.opacity = "0";

    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();

    try {
        document.execCommand("copy");
        mostrarToastCopiado(texto);
    } catch (err) {
        alert("No se pudo copiar automáticamente 😢");
    }

    document.body.removeChild(textarea);
}


// 🎯 TOAST BONITO
function mostrarToastCopiado(texto) {
    Toastify({
        text: "Copiado: " + texto,
        duration: 2000,
        gravity: "top",
        position: "center",
        backgroundColor: "#28a745"
    }).showToast();
}

async function crearRespaldoTransferencia() {
    return true; // TEMPORAL para probar
}


function aplicarRedSocial(selector, url) {

    if (!url) return;

    document.querySelectorAll(selector).forEach(el => {
        el.href = url;
        el.classList.remove('d-none');
    });

}

async function cargarPorcentajeBackend() {

    const fd = new FormData();
    fd.append('action', 'obtener_progreso');
    fd.append('id_raffle', estado.rifa.id);

    const res = await fetch(estado.rutas.numeros, {
        method: 'POST',
        body: fd
    });

    const json = await res.json();

    if (!json.success) {
        return 0;
    }

    return Number(json.porcentaje) || 0;
}

function obtenerOrigenURL() {
    const params = new URLSearchParams(window.location.search);

    return params.get('cp') || null;
}

document.addEventListener('DOMContentLoaded', function () {

    // 🔧 CONFIG BASE reutilizable
    function crearSlider(mainId, thumbsId, interval = 3000) {

        const thumbs = new Splide(thumbsId, {
            fixedWidth  : 100,
            fixedHeight : 60,
            gap         : 10,
            rewind      : true,
            pagination  : false,
            isNavigation: true,
            arrows      : true,   // ✅ flechas SOLO aquí
            focus       : 'center',
        });

        const main = new Splide(mainId, {
            type       : 'slide',
            autoplay   : true,
            interval   : interval,
            rewind     : true,
            pagination : false,
            arrows     : false, // ❌ sin flechas arriba
            speed      : 800,
        });

        main.sync(thumbs);

        main.mount();
        thumbs.mount();
    }

    // ── HERO ─────────────────────────────
    crearSlider('#main-carousel', '#thumbnail-carousel', 3000);

    // ── PREMIOS ──────────────────────────
    crearSlider('#slider-premios', '#slider-thumbnails', 2500);

    // ── GANADORES ────────────────────────
    crearSlider('#ganadores-carousel', '#ganadores-thumbnails', 3500);



    // ── CONFETI ─────────────────────────
    let confetiInterval = null;
    let myConfetti      = null;

    const canvas  = document.getElementById('confetti-canvas');
    const seccion = document.querySelector('.container-ganadores');

    if (canvas && seccion) {

        myConfetti = confetti.create(canvas, {
            resize: true,
            useWorker: true
        });

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                entry.isIntersecting ? iniciarConfeti() : detenerConfeti();
            });
        }, { threshold: 0.3 });

        observer.observe(seccion);
    }

    function iniciarConfeti() {
        if (confetiInterval) return;

        confetiInterval = setInterval(() => {

            myConfetti({
                particleCount: 10,
                angle: 60,
                spread: 80,
                origin: { x: 0, y: 0 },
                colors: ['#FFD700', '#00C853', '#FFFFFF']
            });

            myConfetti({
                particleCount: 10,
                angle: 120,
                spread: 80,
                origin: { x: 1, y: 0 },
                colors: ['#FFD700', '#00C853', '#FFFFFF']
            });

        }, 180);
    }

    function detenerConfeti() {
        clearInterval(confetiInterval);
        confetiInterval = null;
    }

});

