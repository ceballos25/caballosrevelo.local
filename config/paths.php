<?php

/**
 * Rutas privadas fuera de public_html (CyberPanel: $VH_ROOT).
 *
 * eldiadetusuerte/
 * ├── .env
 * ├── logs/          ← LOG_PATH (archivos .log, sessions/)
 * ├── data/          ← DATA_PATH (cache, locks)
 * └── public_html/   ← ROOT_PATH
 */

function resolveAppRoot(string $publicRoot): string
{
    return dirname(rtrim($publicRoot, '/\\'));
}

function resolveEnvPath(string $publicRoot): string
{
    $publicRoot = rtrim($publicRoot, '/\\');
    $candidates = [
        resolveAppRoot($publicRoot) . DIRECTORY_SEPARATOR . '.env-cr',
        $publicRoot . DIRECTORY_SEPARATOR . '.env-cr',
    ];

    foreach ($candidates as $path) {
        $real = realpath($path);
        if ($real !== false && is_readable($real)) {
            return $real;
        }
    }

    throw new Exception(
        'No se encontró .env legible. Probado: ' . implode(', ', $candidates)
    );
}

function resolveLogDir(string $publicRoot): string
{
    return resolveAppRoot($publicRoot) . DIRECTORY_SEPARATOR . 'logs';
}

function resolveDataDir(string $publicRoot): string
{
    return resolveAppRoot($publicRoot) . DIRECTORY_SEPARATOR . 'data';
}

/**
 * Define ROOT_PATH, APP_ROOT, LOG_PATH, DATA_PATH y crea carpetas necesarias.
 */
function bootstrapPrivateStorage(string $publicRoot): void
{
    $publicRoot = rtrim($publicRoot, '/\\');
    $rootReal = realpath($publicRoot);

    if (!defined('ROOT_PATH')) {
        define('ROOT_PATH', $rootReal !== false ? $rootReal : $publicRoot);
    }
    if (!defined('APP_ROOT')) {
        define('APP_ROOT', resolveAppRoot((string)ROOT_PATH));
    }
    if (!defined('LOG_PATH')) {
        define('LOG_PATH', APP_ROOT . DIRECTORY_SEPARATOR . 'logs');
    }
    if (!defined('DATA_PATH')) {
        define('DATA_PATH', APP_ROOT . DIRECTORY_SEPARATOR . 'data');
    }

    foreach ([
        LOG_PATH,
        LOG_PATH . DIRECTORY_SEPARATOR . 'sessions',
        DATA_PATH,
        DATA_PATH . DIRECTORY_SEPARATOR . 'cache',
        DATA_PATH . DIRECTORY_SEPARATOR . 'locks',
    ] as $dir) {
        ensureWritableDirectory($dir);
    }
}

function ensureWritableDirectory(string $path, int $mode = 0750): bool
{
    if (!is_dir($path)) {
        if (!@mkdir($path, $mode, true) && !is_dir($path)) {
            error_log('[paths] No se pudo crear: ' . $path);

            return false;
        }
    }

    if (!is_writable($path)) {
        error_log('[paths] No escribible: ' . $path);

        return false;
    }

    return true;
}

function appLogPath(string $filename): string
{
    $base = defined('LOG_PATH')
        ? LOG_PATH
        : resolveLogDir(defined('ROOT_PATH') ? (string)ROOT_PATH : dirname(__DIR__));

    ensureWritableDirectory($base);

    return $base . DIRECTORY_SEPARATOR . ltrim($filename, '/\\');
}

function appDataPath(string $relative = ''): string
{
    $base = defined('DATA_PATH')
        ? DATA_PATH
        : resolveDataDir(defined('ROOT_PATH') ? (string)ROOT_PATH : dirname(__DIR__));

    ensureWritableDirectory($base);

    $relative = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative), DIRECTORY_SEPARATOR);
    if ($relative === '') {
        return $base;
    }

    $dir = dirname($relative);
    if ($dir !== '.' && $dir !== '') {
        ensureWritableDirectory($base . DIRECTORY_SEPARATOR . $dir);
    }

    return $base . DIRECTORY_SEPARATOR . $relative;
}

/**
 * Escribe una línea en un log bajo LOG_PATH. Falla en silencio si no hay permisos.
 */
function writeAppLog(string $filename, string $message, bool $withTimestamp = true): bool
{
    $file = appLogPath($filename);
    $dir = dirname($file);

    if (!ensureWritableDirectory($dir)) {
        return false;
    }

    clearstatcache(true, $file);
    if (file_exists($file)) {
        if (!is_writable($file)) {
            return false;
        }
    } elseif (!is_writable($dir)) {
        return false;
    }

    $line = ($withTimestamp ? '[' . date('Y-m-d H:i:s') . '] ' : '') . $message . PHP_EOL;

    return @file_put_contents($file, $line, FILE_APPEND | LOCK_EX) !== false;
}
