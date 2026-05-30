<?php
declare(strict_types=1);

/**
 * Captura avisos/notices de includes para no romper el JSON en el navegador (res.json()).
 */
ob_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/transfersController.php';

$noiseFromBootstrap = ob_get_clean();

const ALLOWED_ACTIONS = [
    'obtener' => ['TransfersController', 'obtenerTransferencias', []],
    'aprobar' => ['TransfersController', 'aprobarTransferencia', ['transfer' => '$_POST']],
    'rechazar' => ['TransfersController', 'rechazarTransferencia', ['transfer' => '$_POST']],
];

if ($noiseFromBootstrap !== '' && $noiseFromBootstrap !== false) {
    error_log('[transferencias.ajax] Salida no JSON desde bootstrap (' . strlen((string)$noiseFromBootstrap) . ' bytes)');
}

header('Content-Type: application/json; charset=UTF-8');

try {
    $action = $_POST['action'] ?? '';

    if (!isset(ALLOWED_ACTIONS[$action])) {
        throw new RuntimeException('Acción no válida');
    }

    [$class, $method, $paramsConfig] = ALLOWED_ACTIONS[$action];

    $args = [];
    foreach ($paramsConfig as $key => $default) {
        if ($default === '$_POST') {
            $args[] = $_POST;
        } else {
            $args[] = $_POST[$key] ?? $default;
        }
    }

    $payload = call_user_func_array([$class, $method], $args);

    $flags = JSON_UNESCAPED_UNICODE;
    if (\defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
        $flags |= JSON_INVALID_UTF8_SUBSTITUTE;
    }

    $json = json_encode($payload, $flags);
    if ($json === false) {
        throw new RuntimeException('No se pudo codificar la respuesta JSON');
    }

    echo $json;
} catch (Throwable $e) {
    http_response_code(200);
    echo json_encode(
        ['success' => false, 'message' => $e->getMessage()],
        JSON_UNESCAPED_UNICODE | (\defined('JSON_INVALID_UTF8_SUBSTITUTE') ? JSON_INVALID_UTF8_SUBSTITUTE : 0)
    );
}