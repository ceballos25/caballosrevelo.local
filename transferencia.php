<?php
/**
 * Status de Transferencia
 * Página de confirmación de pagos
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/transfersController.php';
require_once __DIR__ . '/includes/meta-pixel.php';

use App\Application\Marketing\MetaConversionsApi;

// 1. CONFIGURACIÓN (Teléfono como string para evitar errores de entero)
$settings = TransfersController::obtenerSettings();
$whatsappUrl = $settings['whatsapp_chat_url'] ?? 'https://api.whatsapp.com/send/?phone=57';
$code = $_GET['code'] ?? null;


// 2. OBTENER DATOS
$transfer = $code ? TransfersController::obtenerPorCode($code) : null;
$estado = 'error';

if ($transfer && isset($transfer['status_transfer'])) {
    $status = (int)$transfer['status_transfer'];
    $estado = match($status) {
        1 => 'pending',
        2 => 'approved',
        3 => 'rejected',
        default => 'error'
    };
}

$codigo = $transfer['code_transfer'] ?? $code ?? '---';
$purchaseEvent = null;
$transferMeta = [
    'quantity' => (int)($transfer['quantity_transfer'] ?? 0),
    'amount' => (float)($transfer['amount_transfer'] ?? 0),
];

if ($estado === 'approved' && $codigo !== '---') {
    $sale = \Db::fetchOne(
        'SELECT s.id_sale, s.code_sale, s.total_sale, s.quantity_sale
         FROM sales s
         WHERE s.code_sale = :c
         ORDER BY s.id_sale DESC
         LIMIT 1',
        [':c' => (string)$codigo]
    );
    if ($sale) {
        $purchaseEvent = [
            'event_id' => MetaConversionsApi::eventId('Purchase', (string)$sale->id_sale),
            'value' => (float)$sale->total_sale,
            'quantity' => (int)$sale->quantity_sale,
            'order_id' => (string)$sale->code_sale,
        ];
    }
}

// 3. HEADERS UTF-8
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de tu compra</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f5f5f5; font-family: 'Segoe UI', Roboto, sans-serif; }
        .card-custom { border-radius: 1.5rem; max-width:420px; width:100%; border: none; }
        .btn-brand { background:#1b1b1b; color:#fff; border-radius:50px; font-weight:bold; transition: 0.3s; }
        .btn-brand:hover { background:#000; color:#fff; transform: translateY(-2px); }
        .btn-whatsapp {
            background: linear-gradient(45deg, #25D366, #128C7E);
            color: white !important;
            border-radius: 50px;
            font-weight: 600;
            border: none;
            transition: 0.3s;
        }
        .btn-whatsapp:hover {
            background: linear-gradient(45deg, #128C7E, #25D366) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
        }
        .status-icon { font-size: 3rem; margin-bottom: 1rem; }
    </style>
    
    <?php edts_meta_pixel_head(); ?>
</head>
<body>
    <div class="container mt-5 d-flex justify-content-center">
        <div class="card card-custom shadow-lg p-4 text-center">
            
            <?php if ($estado === 'pending'): ?>
                <div class="mb-3">
                    <div class="spinner-border text-warning" style="width: 3rem; height: 3rem;"></div>
                </div>
                <h4 class="fw-bold">⏳ Pago en validación</h4>
                <p class="text-muted mb-3">
                    Hemos recibido tu comprobante.<br>
                    Estamos validando tu pago en el sistema.
                </p>
                <div class="alert alert-warning py-2">
                    <small class="d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Código de compra:</small>
                    <strong><?= htmlspecialchars($codigo) ?></strong>
                </div>

                <button onclick="enviarValidacion()" class="btn btn-whatsapp w-100 mb-3 py-2">
                    📱 Obtener mis tickets
                </button>

                <button onclick="location.reload()" class="btn btn-brand w-100 py-2">
                    Actualizar estado 🔄
                </button>

            <?php elseif ($estado === 'approved'): ?>
                <div class="status-icon">✅</div>
                <h4 class="fw-bold text-success">¡Pago aprobado!</h4>
                <p class="text-muted">
                    Tu compra fue confirmada con éxito.<br>
                    Ya puedes revisar tus números.
                </p>
                <a href="/" class="btn btn-brand w-100 mt-3">Volver al inicio</a>

            <?php elseif ($estado === 'rejected'): ?>
                <div class="status-icon">❌</div>
                <h4 class="fw-bold text-danger">Pago rechazado</h4>
                <p class="text-muted">
                    No pudimos validar tu comprobante.<br>
                    Escríbenos para revisar qué sucedió.
                </p>
                <button onclick="enviarSoporte()" class="btn btn-whatsapp w-100 mt-3 py-2">
                    📲 Contactar soporte
                </button>

            <?php else: ?>
                <div class="status-icon">❓</div>
                <h4 class="fw-bold">Código inválido</h4>
                <p class="text-muted">El enlace no es válido o ya expiró.</p>
                <a href="/" class="btn btn-brand w-100 mt-3">Volver al inicio</a>
            <?php endif; ?>

            <p class="small text-muted mt-4 mb-0" style="font-size: 0.75rem;">
                © <?= date('Y') ?> El dia de TU SUERTE 🍀
            </p>
        </div>
    </div>

    <script src="<?= htmlspecialchars(BASE_URL . '/assets/js/meta-events.js?v=3', ENT_QUOTES, 'UTF-8') ?>"></script>
    <script>
    const DATA = {
        whatsappUrl: "<?= $whatsappUrl ?>",
        code: "<?= htmlspecialchars($codigo) ?>",
        estado: "<?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>",
        quantity: <?= (int)$transferMeta['quantity'] ?>,
        amount: <?= json_encode($transferMeta['amount'], JSON_UNESCAPED_UNICODE) ?>
    };

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof MetaEvents === 'undefined') {
            return;
        }

        <?php if ($purchaseEvent !== null): ?>
        MetaEvents.fireOnce(
            'purchase-' + <?= json_encode($purchaseEvent['order_id'], JSON_UNESCAPED_SLASHES) ?>,
            'Purchase',
            {
                currency: 'COP',
                value: <?= json_encode($purchaseEvent['value'], JSON_UNESCAPED_UNICODE) ?>,
                content_type: 'product',
                num_items: <?= (int)$purchaseEvent['quantity'] ?>,
                order_id: <?= json_encode($purchaseEvent['order_id'], JSON_UNESCAPED_SLASHES) ?>
            },
            <?= json_encode($purchaseEvent['event_id'], JSON_UNESCAPED_SLASHES) ?>
        );
        <?php endif; ?>
    });

    function enviarValidacion() {
        const texto = `¡Hola! 👋 Acabo de realizar una compra en su página.\n\n📋 *Código:* ${DATA.code}\n\nPor favor, confirmen y aprueben mi compra. 🙂`;
        abrirWA(texto);
    }

    function enviarSoporte() {
        const texto = `Hola 👋\n\nMi código *${DATA.code}* fue rechazado. Necesito ayuda con mi pago 🙏`;
        abrirWA(texto);
    }

    function abrirWA(texto) {
        const mensaje = encodeURIComponent(texto);
        const url = `${DATA.whatsappUrl}&text=${mensaje}`;
        window.open(url, '_blank');
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>