// ============================
// STATE GLOBAL
// ============================
let SETTINGS = {};
let SETTINGS_ROWS = [];
let SETTINGS_LOADED = false;
let SETTINGS_PROMISE = null;

/**
 * Claves conocidas: título, ayuda y tipo de control (toggle = encendido/apagado → 1/0 en BD).
 */
const SETTING_UI_META = {
    web_compras_habilitadas: {
        title: 'Compras en la página web',
        help: 'Encendido: los visitantes pueden pagar (PSE, OpenPay) y subir comprobante. Apagado: pueden navegar y consultar números, pero no comprar.',
        type: 'toggle',
    },
    web_mensaje_compras_bloqueadas: {
        title: 'Mensaje cuando las compras están apagadas',
        help: 'Texto que aparece en la franja roja de aviso en la web. Déjalo vacío para usar el mensaje por defecto.',
        type: 'textarea',
    },
    web_id_raffle: {
        title: 'Rifa mostrada en la web',
        help: 'ID de la rifa activa en la landing: inventario, progreso de ventas y bendecidos. Debe coincidir con la rifa que estás vendiendo.',
        type: 'text',
    },
    barra: {
        title: 'Barra de progreso — ajuste (%)',
        help: '0 = solo % real de ventas al cargar la página. Mayor que 0 = se suma a ese % (vuelve a cargar la web para ver el dato nuevo). Máximo 100%.',
        type: 'text',
    },
    whatsapp: {
        title: 'WhatsApp (número)',
        help: 'Solo dígitos, sin + ni espacios. Se usa para armar el enlace wa.me si no hay URL completa.',
        type: 'text',
    },
    whatsapp_chat_url: {
        title: 'WhatsApp (URL completa)',
        help: 'Si está llena, tiene prioridad sobre el número. Ejemplo: https://wa.me/57…',
        type: 'text',
    },
    social_instagram_url: {
        title: 'Instagram',
        help: 'URL del perfil o enlace público de Instagram.',
        type: 'text',
    },
    social_facebook_url: {
        title: 'Facebook',
        help: 'URL de la página o perfil de Facebook.',
        type: 'text',
    },
    numeros_bendecidos: {
        title: 'Números bendecidos (legacy)',
        help: 'Lista separada por comas. Si ya usas solo premium en tickets, puede quedar vacía.',
        type: 'text',
    },
};

const SETTING_UI_ORDER = [
    'web_compras_habilitadas',
    'web_mensaje_compras_bloqueadas',
    'web_id_raffle',
    'barra',
    'whatsapp',
    'whatsapp_chat_url',
    'social_instagram_url',
    'social_facebook_url',
    'numeros_bendecidos',
];

// ============================
// INIT GLOBAL
// ============================
document.addEventListener('DOMContentLoaded', async () => {

    // 🔥 Cargar settings UNA sola vez
    await cargarSettingsGlobal();

    // 🔥 Solo renderizar si existe la vista settings
    if (document.getElementById('settingsContainer')) {
        renderSettingsFromGlobal();
    }

});

// ============================
// HELPERS
// ============================
function escapeHtml(text) {
    return String(text ?? '')
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function getSetting(key, defaultValue = null) {
    return SETTINGS[key] ?? defaultValue;
}

// ============================
// FETCH BASE
// ============================
async function fetchSettings(action, extra = {}) {

    const fd = new FormData();
    fd.append('action', action);

    Object.entries(extra).forEach(([k, v]) => fd.append(k, v));

    const res = await fetch('/front/ajax/settings.ajax.php', {
        method: 'POST',
        body: fd
    });

    return res.json();
}

// ============================
// CARGAR GLOBAL (SINGLE SOURCE)
// ============================
async function cargarSettingsGlobal(force = false) {

    if (force) {
        SETTINGS_LOADED = false;
        SETTINGS_PROMISE = null;
    }

    if (SETTINGS_LOADED) return SETTINGS;

    if (SETTINGS_PROMISE) return SETTINGS_PROMISE;

    SETTINGS_PROMISE = (async () => {

        try {

            const data = await fetchSettings('obtener');

            if (!data.success) throw new Error("Error obteniendo settings");

            SETTINGS = {};
            SETTINGS_ROWS = Array.isArray(data.data) ? data.data : [];

            SETTINGS_ROWS.forEach(s => {
                SETTINGS[s.key_setting] = s.value_setting;
            });

            SETTINGS_LOADED = true;

            return SETTINGS;

        } catch (e) {
            console.error("Error cargando settings:", e);
            return {};
        }

    })();

    return SETTINGS_PROMISE;
}

function isSettingTruthyValue(val) {
    const v = String(val ?? '').trim().toLowerCase();
    return !['0', 'false', 'no', 'off'].includes(v);
}

// ============================
// READY (SIN setInterval)
// ============================
function onSettingsReady(callback) {
    cargarSettingsGlobal().then(callback);
}

// ============================
// RENDER SETTINGS (ADMIN UI)
// ============================
function metaForKey(key) {
    return (
        SETTING_UI_META[key] || {
            title: key,
            help: 'Parámetro avanzado. El valor se guarda tal cual en la base de datos.',
            type: 'text',
        }
    );
}

function sortSettingsRows(rows) {
    const seen = new Set();
    const ordered = [];

    SETTING_UI_ORDER.forEach((k) => {
        const row = rows.find((r) => r.key_setting === k);
        if (row) {
            ordered.push(row);
            seen.add(k);
        }
    });

    const rest = rows
        .filter((r) => !seen.has(r.key_setting))
        .sort((a, b) => String(a.key_setting).localeCompare(String(b.key_setting)));

    return ordered.concat(rest);
}

function renderSettingsFromGlobal() {

    const container = document.getElementById('settingsContainer');
    if (!container) return;

    const rows = sortSettingsRows([...SETTINGS_ROWS]);

    if (!rows.length) {
        container.innerHTML = `<div class="text-center py-5 text-muted">Sin configuración. Crea la primera clave abajo.</div>`;
        return;
    }

    container.innerHTML = rows.map((s) => {
        const key = s.key_setting;
        const meta = metaForKey(key);
        const val = s.value_setting ?? '';
        const id = s.id_setting;

        let controlHtml = '';

        if (meta.type === 'toggle') {
            const checked = isSettingTruthyValue(val) ? 'checked' : '';
            controlHtml = `
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" role="switch" id="toggle-${escapeHtml(key)}"
                        data-key="${escapeHtml(key)}"
                        data-boolean-toggle="1"
                        ${checked}
                        onchange="guardarToggleSetting('${escapeHtml(key)}', this)">
                    <label class="form-check-label small" for="toggle-${escapeHtml(key)}">
                        <span class="text-muted toggle-state-label" data-for-key="${escapeHtml(key)}"></span>
                    </label>
                </div>
            `;
        } else if (meta.type === 'textarea') {
            controlHtml = `
                <textarea class="form-control form-control-sm" rows="2" data-key="${escapeHtml(key)}"
                    placeholder="Opcional">${escapeHtml(val)}</textarea>
            `;
        } else {
            controlHtml = `
                <input type="text" class="form-control form-control-sm" data-key="${escapeHtml(key)}"
                    value="${escapeHtml(val)}">
            `;
        }

        const showSave = meta.type !== 'toggle';

        return `
        <div class="settings-row row g-3 align-items-start" data-setting-key="${escapeHtml(key)}">
            <div class="col-lg-4">
                <div class="settings-title">${escapeHtml(meta.title)}</div>
                <div class="settings-code text-muted small mb-1"><code>${escapeHtml(key)}</code></div>
                <p class="settings-help mb-0">${escapeHtml(meta.help)}</p>
            </div>
            <div class="col-lg-5">
                ${controlHtml}
            </div>
            <div class="col-lg-3 text-lg-end">
                ${showSave ? `
                    <button type="button" class="btn btn-sm btn-success btn-save-row"
                        onclick="guardarIndividualDesdeUI('${escapeHtml(key)}', this)">
                        <i class="ti ti-device-floppy me-1"></i> Guardar
                    </button>
                ` : `
                    <span class="small text-success"><i class="ti ti-bolt me-1"></i>Se guarda al cambiar</span>
                `}
                <button type="button" class="btn btn-sm btn-outline-danger ms-lg-2 mt-2 mt-lg-0 d-inline-block"
                    title="Eliminar clave"
                    onclick="eliminarSetting(${Number(id)})"
                    ${['precio_ticket', 'max_tickets', 'min_tickets'].includes(key) ? 'disabled' : ''}>
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        </div>
        `;
    }).join('');

    rows.forEach((s) => {
        if (metaForKey(s.key_setting).type === 'toggle') {
            updateToggleLabel(s.key_setting);
        }
    });
}

function updateToggleLabel(key) {
    const el = document.querySelector(`.toggle-state-label[data-for-key="${key}"]`);
    const input = document.getElementById(`toggle-${key}`);
    if (!el || !input) return;
    el.textContent = input.checked ? 'Encendido (compras permitidas)' : 'Apagado (solo navegación, sin compras)';
}

async function guardarToggleSetting(key, inputEl) {
    const v = inputEl.checked ? '1' : '0';
    updateToggleLabel(key);
    try {
        await actualizarSettings({ [key]: v });
        alertify.success('Listo');
    } catch (e) {
        alertify.error(e.message || 'Error');
        inputEl.checked = !inputEl.checked;
        updateToggleLabel(key);
    }
}

// ============================
// ACTUALIZAR (GLOBAL)
// ============================
async function actualizarSettings(payload) {

    const data = await fetchSettings('actualizar', payload);

    if (!data.success) {
        throw new Error(data.message);
    }

    await cargarSettingsGlobal(true);

    return true;
}

// ============================
// GUARDAR INDIVIDUAL (UI)
// ============================
async function guardarIndividualDesdeUI(key, btn) {

    const row = btn.closest('.settings-row');
    const input = row && row.querySelector('[data-key]');
    if (!input) return;

    const value = input.type === 'checkbox'
        ? (input.checked ? '1' : '0')
        : input.value.trim();

    try {

        await actualizarSettings({ [key]: value });

        alertify.success("Actualizado");
        if (document.getElementById('settingsContainer')) {
            renderSettingsFromGlobal();
        }

    } catch (e) {
        alertify.error(e.message || "Error");
    }
}

// ============================
// GUARDAR MASIVO (UI)
// ============================
async function guardarSettings() {

    const inputs = document.querySelectorAll('[data-key]');
    const payload = {};

    inputs.forEach(i => {
        if (i.type === 'checkbox') {
            payload[i.dataset.key] = i.checked ? '1' : '0';
        } else {
            payload[i.dataset.key] = i.value.trim();
        }
    });

    try {

        await actualizarSettings(payload);

        alertify.success("Configuración actualizada");
        if (document.getElementById('settingsContainer')) {
            renderSettingsFromGlobal();
        }

    } catch (e) {
        alertify.error(e.message || "Error");
    }
}

// ============================
// CREAR
// ============================
async function crearSetting() {

    let key = document.getElementById('newKey').value.trim();
    const value = document.getElementById('newValue').value.trim();

    if (!key) {
        alertify.error("Indica el nombre de la clave");
        return;
    }

    key = key.toLowerCase().replace(/\s+/g, '_');

    try {

        const data = await fetchSettings('crear', {
            key_setting: key,
            value_setting: value
        });

        if (!data.success) throw new Error(data.message);

        alertify.success("Setting creado");

        document.getElementById('newKey').value = '';
        document.getElementById('newValue').value = '';

        await cargarSettingsGlobal(true);
        renderSettingsFromGlobal();

    } catch (e) {
        alertify.error(e.message || "Error");
    }
}

// ============================
// ELIMINAR
// ============================
async function eliminarSetting(id) {

    if (!confirm("¿Eliminar este setting?")) return;

    try {

        const data = await fetchSettings('eliminar', { id_setting: id });

        if (!data.success) throw new Error(data.message);

        alertify.success("Eliminado");

        await cargarSettingsGlobal(true);
        renderSettingsFromGlobal();

    } catch (e) {
        alertify.error(e.message || "Error al eliminar");
    }
}

// ============================
// EXPORT GLOBAL
// ============================
window.getSetting = getSetting;
window.onSettingsReady = onSettingsReady;
window.cargarSettingsGlobal = cargarSettingsGlobal;
window.guardarToggleSetting = guardarToggleSetting;

onSettingsReady(() => {

    // INSTAGRAM
    aplicarRedSocial(
        '.social-instagram',
        getSetting('social_instagram_url')
    );

    // FACEBOOK
    aplicarRedSocial(
        '.social-facebook',
        getSetting('social_facebook_url')
    );

    // WHATSAPP
    const whatsappUrl = getSetting('whatsapp_chat_url');
    const whatsappNum = getSetting('whatsapp');

    let finalWhatsappUrl = whatsappUrl || 
        (whatsappNum ? `https://wa.me/${whatsappNum}?text=Hola` : null);

    aplicarRedSocial('.social-whatsapp', finalWhatsappUrl);

});

/**
 * Evita artefactos IEEE 754 al sumar % backend + boost (ej. 40.760000000000005%).
 * Coincide con el redondeo a 2 decimales del backend (NumerosController).
 */
function redondearPorcentajeUIPct(n, decimales = 2) {
    if (!Number.isFinite(n)) {
        return 0;
    }
    return parseFloat(n.toFixed(decimales));
}

function obtenerPorcentajeFinal(porcentajeBackend) {

    const real = Number(porcentajeBackend);
    const base = Number.isFinite(real) ? real : 0;

    const raw = getBarraBoostRaw();

    if (raw === null || raw === undefined) {
        return base;
    }

    const trimmed = String(raw).trim();
    if (trimmed === '') {
        return base;
    }

    const barraSetting = parseFloat(trimmed.replace(',', '.'));

    if (!Number.isFinite(barraSetting) || barraSetting <= 0) {
        return base;
    }

    return base + barraSetting;
}

/** Valor numérico del ajuste `barra` en settings (case-insensitive por clave). */
function getBarraBoostRaw() {
    if (typeof getSetting !== 'function') {
        return null;
    }
    const direct = getSetting('barra');
    if (direct !== null && direct !== undefined && String(direct).trim() !== '') {
        return direct;
    }
    if (typeof SETTINGS === 'object' && SETTINGS !== null) {
        const keys = Object.keys(SETTINGS);
        const found = keys.find((k) => k.toLowerCase() === 'barra');
        if (found !== undefined && SETTINGS[found] !== undefined && SETTINGS[found] !== null) {
            return SETTINGS[found];
        }
    }
    return null;
}

function actualizarBarraProgreso(porcentajeBackend) {
    const porcentaje = obtenerPorcentajeFinal(porcentajeBackend);

    const clamped = Math.min(Math.max(porcentaje, 0), 100);
    const porcentajeFinal = redondearPorcentajeUIPct(clamped, 2);

    const barra = document.getElementById('barraProgreso');
    const texto = document.getElementById('porcentajeTexto');

    if (!barra || !texto) return;

    barra.style.width = porcentajeFinal + '%';
    texto.innerText = porcentajeFinal + '%';
}

let porcentajeBackendGlobal = 0;

// Cuando cargas la rifa (ej: desde API)
function cargarDatosRifa(data) {
    porcentajeBackendGlobal = Number(data?.porcentaje) || 0;

    // Esperar settings antes de pintar
    onSettingsReady(() => {
        actualizarBarraProgreso(porcentajeBackendGlobal);
    });
}

onSettingsReady(() => {

});

async function initBarraProgreso() {

    await cargarSettingsGlobal();

    // ⚠️ TEMPORAL (hasta que hagamos el endpoint)
    const porcentajeBackend = 25;

    actualizarBarraProgreso(porcentajeBackend);
}