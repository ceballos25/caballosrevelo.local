<?php
/**
 * STATUS API (SERVIDOR PRINCIPAL)
 * Consulta por order_id para que success.php de marketing solo pinte HTML.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/ventas.controller.php';

header('Content-Type: application/json; charset=utf-8');

function statusOut(array $payload, int $http = 200): never
{
    http_response_code($http);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function statusUnauthorized(string $reason): never
{
    statusOut(['success' => false, 'status' => 'error', 'message' => 'unauthorized: ' . $reason], 401);
}

function statusHeadersLower(): array
{
    $h = function_exists('getallheaders') ? getallheaders() : [];
    $out = [];
    foreach ($h as $k => $v) {
        $out[strtolower((string)$k)] = (string)$v;
    }
    return $out;
}

function requireStatusToken(): void
{
    $token = (string)env('OPENPAY_STATUS_TOKEN', '');
    if ($token === '') {
        // fallback a bridge secret para no duplicar secretos si no quieren
        $token = (string)env('OPENPAY_BRIDGE_SECRET', '');
    }
    if ($token === '') {
        statusUnauthorized('token no configurado');
    }

    $h = statusHeadersLower();
    $got = $h['x-status-token'] ?? '';
    if ($got === '' || !hash_equals($token, $got)) {
        statusUnauthorized('token invalido');
    }
}

requireStatusToken();

$orderId = trim((string)($_GET['order_id'] ?? ''));
if ($orderId === '') {
    statusOut(['success' => false, 'status' => 'error', 'message' => 'order_id requerido'], 422);
}

$sale = Db::fetchOne(
    'SELECT id_sale FROM sales WHERE code_sale = :c LIMIT 1',
    [':c' => $orderId]
);
if ($sale) {
    $detalle = VentasController::obtenerDetalleVenta((int)$sale->id_sale);
    if (!empty($detalle['success'])) {
        statusOut([
            'success' => true,
            'status' => 'ok',
            'id_sale' => (int)$sale->id_sale,
            'html_recibo' => (string)($detalle['html_recibo'] ?? ''),
        ]);
    }
}

$backup = Db::fetchOne(
    'SELECT status_payment_backup FROM payment_backups WHERE code_payment_backup = :c LIMIT 1',
    [':c' => $orderId]
);

if (!$backup) {
    statusOut(['success' => false, 'status' => 'error', 'message' => 'Codigo no valido o expirado'], 404);
}

$st = (int)($backup->status_payment_backup ?? 0);
if ($st === 1 || $st === 2 || $st === 4) {
    statusOut(['success' => true, 'status' => 'pending', 'message' => 'Confirmacion en curso']);
}

if ($st === 3) {
    statusOut(['success' => false, 'status' => 'error', 'message' => 'Pago rechazado o cancelado'], 200);
}

statusOut(['success' => true, 'status' => 'pending', 'message' => 'Estado en verificacion'], 200);

