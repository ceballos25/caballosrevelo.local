<?php
declare(strict_types=1);

/**
 * Respuestas JSON: no forzar display_errors aquí; lo define config/config.php (DISPLAY_ERRORS).
 * Buffer sobre includes para no mezclar notices con JSON.
 */
header('Content-Type: application/json; charset=utf-8');

$result = ['success' => false, 'message' => 'Solicitud inválida'];

try {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    ob_start();
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../controllers/clientes.controller.php';
    $noise = ob_get_clean();
    if ($noise !== '' && $noise !== false) {
        error_log('[clientes.ajax] Salida no JSON desde bootstrap (' . strlen((string)$noise) . ' bytes)');
    }

    $action = isset($_POST['action']) ? trim((string)$_POST['action']) : '';

    if ($action === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action requerida'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    switch ($action) {
        case 'obtener':
            $result = ClientesController::obtenerClientes();
            break;

        case 'crear':
            $result = ClientesController::crearCliente($_POST);
            break;

        case 'actualizar':
            $result = ClientesController::actualizarCliente($_POST);
            break;

        case 'eliminar':
            $result = ClientesController::eliminarCliente($_POST);
            break;

        default:
            http_response_code(400);
            $result = ['success' => false, 'message' => 'Action desconocida: ' . $action];
            break;
    }
} catch (Throwable $e) {
    error_log('clientes.ajax.php Exception: ' . $e->getMessage());
    http_response_code(500);
    $msg = (defined('DEBUG_MODE') && DEBUG_MODE)
        ? $e->getMessage()
        : 'Error interno del servidor';
    $result = ['success' => false, 'message' => $msg];
}

$flags = JSON_UNESCAPED_UNICODE;
if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
    $flags |= JSON_INVALID_UTF8_SUBSTITUTE;
}
echo json_encode($result, $flags);
exit;
