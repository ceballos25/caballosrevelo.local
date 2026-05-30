<?php
/**
 * WEBHOOK PUENTE (SERVIDOR PRINCIPAL)
 * - Recibe JSON reenviado desde marketing
 * - Valida firma HMAC
 * - Procesa aprobacion/rechazo con PaymentBackupsController
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/paymentBackupsController.php';

function bridgeLog(string $message): void
{
    writeAppLog('openpay-bridge.log', $message);
}

function headersLower(): array
{
    $h = function_exists('getallheaders') ? getallheaders() : [];
    $out = [];
    foreach ($h as $k => $v) {
        $out[strtolower((string)$k)] = (string)$v;
    }
    return $out;
}

function unauthorized(string $reason): never
{
    bridgeLog('UNAUTHORIZED: ' . $reason);
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'unauthorized']);
    exit;
}

function validateSignature(string $raw): void
{
    $secret = (string)env('OPENPAY_BRIDGE_SECRET', '');
    if ($secret === '') {
        unauthorized('secret vacio');
    }

    $h = headersLower();
    $sig = $h['x-bridge-signature'] ?? '';
    $ts = $h['x-bridge-timestamp'] ?? '';

    if ($sig === '' || $ts === '') {
        unauthorized('faltan headers de firma');
    }

    if (!ctype_digit($ts)) {
        unauthorized('timestamp invalido');
    }

    $now = time();
    $tsInt = (int)$ts;
    if (abs($now - $tsInt) > 600) {
        unauthorized('timestamp fuera de ventana');
    }

    $expected = hash_hmac('sha256', $ts . '.' . $raw, $secret);
    if (!hash_equals($expected, $sig)) {
        unauthorized('firma invalida');
    }
}

function processEvent(array $data): void
{
    $type = $data['type'] ?? null;
    $tx = $data['transaction'] ?? null;
    if (!$type || !$tx || empty($tx['order_id'])) {
        bridgeLog('Datos incompletos');
        http_response_code(200);
        echo 'OK';
        return;
    }

    $code = (string)$tx['order_id'];
    $backup = PaymentBackupsController::obtenerPorCode($code);
    if (!$backup) {
        bridgeLog('Respaldo no encontrado: ' . $code);
        http_response_code(200);
        echo 'OK';
        return;
    }

    if ((int)$backup['status_payment_backup'] !== 1) {
        bridgeLog('YA PROCESADO status=' . $backup['status_payment_backup'] . ' code=' . $code . ' event=' . $type);
        http_response_code(200);
        echo 'OK';
        return;
    }

    $approvedEvents = [
        'charge.succeeded',
        'order.completed',
        'order.payment.received',
    ];
    $rejectedEvents = [
        'charge.failed',
        'charge.cancelled',
        'charge.refunded',
        'charge.rescored.to.decline',
        'order.expired',
        'order.cancelled',
        'order.payment.cancelled',
    ];

    if (in_array($type, $approvedEvents, true)) {
        bridgeLog('APROBANDO ' . $code . ' EVENT=' . $type);
        PaymentBackupsController::aprobarPago($backup, $tx);
    } elseif (in_array($type, $rejectedEvents, true)) {
        bridgeLog('RECHAZANDO ' . $code . ' EVENT=' . $type);
        PaymentBackupsController::rechazarPago($backup, $tx);
    } else {
        bridgeLog('IGNORADO ' . $code . ' EVENT=' . $type);
    }

    http_response_code(200);
    echo 'OK';
}

$raw = (string)file_get_contents('php://input');
bridgeLog('WEBHOOK BRIDGE RECIBIDO: ' . $raw);

$data = json_decode($raw, true);
if (!is_array($data)) {
    bridgeLog('JSON invalido');
    http_response_code(400);
    echo 'BAD_REQUEST';
    exit;
}

if (OPENPAY_REQUIRE_BRIDGE_SIGNATURE) {
    validateSignature($raw);
} else {
    bridgeLog('ADVERTENCIA: OPENPAY_REQUIRE_BRIDGE_SIGNATURE=false, firma HMAC no validada');
}

processEvent($data);

