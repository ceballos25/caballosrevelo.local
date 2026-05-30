const TICKETS_AJAX_URL = 'front/ajax/ventas.ajax.php';
const PHONE_PATTERN = /^\d{10}$/;

const inputBuscarTickets = document.getElementById('inputBuscarTickets');
const resultadoBusqueda = document.getElementById('resultadoBusqueda');
const modalBuscarTickets = document.getElementById('modalBuscarTickets');

function renderAlert(type, message, icon = '') {
    const iconMarkup = icon ? `<i class="${icon}"></i> ` : '';
    resultadoBusqueda.innerHTML = `<div class="alert alert-${type} mb-0">${iconMarkup}${message}</div>`;
}

function buscarTickets() {
    if (!inputBuscarTickets || !resultadoBusqueda) {
        return;
    }

    const valor = inputBuscarTickets.value.trim();

    if (!valor) {
        renderAlert('danger', 'Ingresa un dato válido');
        return;
    }

    if (!PHONE_PATTERN.test(valor)) {
        renderAlert('danger', 'El celular debe contener exactamente 10 dígitos');
        return;
    }

    renderAlert('info', `Buscando tickets para: <strong>${valor}</strong>...`);

    if (typeof MetaEvents !== 'undefined') {
        MetaEvents.track(
            'Search',
            { search_string: valor },
            'search-' + valor + '-' + Date.now(),
            { phone_customer: valor },
            { allowRepeat: true }
        ).catch(function (err) {
            console.warn('[MetaEvents] Search', err);
        });
    }

    $.ajax({
        url: TICKETS_AJAX_URL,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'obtener_por_celular',
            phone_customer: valor
        },
        success: function(resp) {
            if (!resp.success) {
                renderAlert('warning', resp.message || 'No encontrado', 'bi bi-exclamation-triangle');
                return;
            }

            resultadoBusqueda.innerHTML = resp.html;
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            renderAlert('danger', 'Error consultando los tickets', 'bi bi-exclamation-circle');
        }
    });
}

if (modalBuscarTickets && inputBuscarTickets) {
    modalBuscarTickets.addEventListener('shown.bs.modal', () => {
        inputBuscarTickets.focus();
    });

    inputBuscarTickets.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            buscarTickets();
        }
    });
}