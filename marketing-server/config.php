<?php
/**
 * Config SOLO para el hosting de marketing (Openpay autorizado + puente al principal).
 * .env: fuera de public_html ($VH_ROOT/.env). Ver config/envLoader.php.
 */
require_once __DIR__ . '/../config/envLoader.php';
require_once __DIR__ . '/../config/paths.php';

$publicRoot = dirname(__DIR__);
$envLoader = new EnvLoader(resolveEnvPath($publicRoot));
$envLoader->load();
bootstrapPrivateStorage($publicRoot);

/**
 * ===============================
 * RUTAS ABSOLUTAS
 * ===============================
 */
define('DS', DIRECTORY_SEPARATOR);
define('BASE_URL', rtrim((string)env('SITE_URL', ''), '/'));
define('ASSETS_URL', BASE_URL . '/assets');






/**
 * ===============================
 * OPENPAY (cobros que salen desde este dominio)
 * ===============================
 */
define('OPENPAY_MERCHANT_ID', env('OPENPAY_MERCHANT_ID', ''));
define('OPENPAY_PRIVATE_KEY', env('OPENPAY_PRIVATE_KEY', ''));
define('OPENPAY_PUBLIC_KEY', env('OPENPAY_PUBLIC_KEY', ''));

define(
    'OPENPAY_URL',
    env('OPENPAY_ENV') === 'production'
        ? 'https://api.openpay.co/v1/' . env('OPENPAY_MERCHANT_ID', '')
        : 'https://sandbox-api.openpay.co/v1/' . env('OPENPAY_MERCHANT_ID', '')
);

define('OPENPAY_RETURN_URL', env('OPENPAY_RETURN_URL', ''));

/**
 * Puente → servidor principal (mismo OPENPAY_BRIDGE_SECRET que en el principal)
 */
define('OPENPAY_WEBHOOK_FORWARD_URL', trim((string)env('OPENPAY_WEBHOOK_FORWARD_URL', '')));
define('OPENPAY_BRIDGE_SECRET', (string)env('OPENPAY_BRIDGE_SECRET', ''));

/**
 * Success en marketing consulta estado en el principal
 */
define('OPENPAY_STATUS_API_URL', trim((string)env('OPENPAY_STATUS_API_URL', '')));
define('OPENPAY_STATUS_TOKEN', (string)env('OPENPAY_STATUS_TOKEN', ''));

/**
 * ===============================
 * CONFIG SITIO
 * ===============================
 */
define('SITE_NAME', env('SITE_NAME', ''));


/**
 * ===============================
 * TIMEZONE
 * ===============================
 */
date_default_timezone_set(env('TIMEZONE') ?: 'America/Bogota');

/**
 * ===============================
 * MODO / ERRORES
 * ===============================
 */
define('APP_ENV', env('APP_ENV', 'production'));
define('DEBUG_MODE', filter_var(env('DEBUG_MODE', false), FILTER_VALIDATE_BOOLEAN));

if (filter_var(env('DISPLAY_ERRORS', false), FILTER_VALIDATE_BOOLEAN)) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);
    ini_set('log_errors', '1');
    if (is_dir(LOG_PATH) && is_writable(LOG_PATH)) {
        ini_set('error_log', LOG_PATH . '/php_errors.log');
    }
}




