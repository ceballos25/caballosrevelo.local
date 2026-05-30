<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

require_once "../../config/config.php";
require_once "../../controllers/numeros.controller.php";
require_once "../../controllers/rifas.controller.php";

const ALLOWED_ACTIONS = [
    'obtener_inventario' => ['NumerosController', 'obtenerInventario', []],
    'obtener_rifas'      => ['RifasController', 'obtenerRifas', []],
    'cambiar_estado'     => ['NumerosController', 'cambiarEstado', ['id_ticket' => 0, 'status' => 0]],
    'obtener_progreso'   => ['NumerosController', 'obtenerProgreso', ['id_raffle' => 0]],
    'marcar_flags'      => ['NumerosController', 'marcarTicketFlags', []],
];

try {
    $action = $_POST['action'] ?? '';
    if (!isset(ALLOWED_ACTIONS[$action])) throw new Exception('Acción no válida');
    
    [$class, $method, $paramsConfig] = ALLOWED_ACTIONS[$action];
    
    $args = [];
    foreach ($paramsConfig as $key => $default) {
        $args[] = $_POST[$key] ?? $default;
    }
    
    if (method_exists($class, $method)) {
        echo json_encode(call_user_func_array([$class, $method], $args));
    } else {
        throw new Exception("Método no encontrado");
    }
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}