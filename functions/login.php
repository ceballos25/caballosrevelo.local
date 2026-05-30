<?php

require_once __DIR__ . '/../config/config.php';

if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

if (!defined('DB_NAME') || DB_NAME === '' || DB_NAME === null) {
    die('ERROR: config.php no cargó la base de datos (DB_NAME)');
}

try {
    $email = trim($_POST['email'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if ($email === '' || $pass === '') {
        header('Location: ../dash.php?error=missing');
        exit;
    }

    try {
        $admin = Db::fetchOne(
            'SELECT id_admin, email_admin, password_admin, rol_admin, token_admin, token_exp_admin, id_branch, status_admin
             FROM admins WHERE email_admin = :e LIMIT 1',
            [':e' => $email]
        );
    } catch (Throwable $e) {
        // Compatibilidad: algunas BD no tienen admins.id_branch
        $admin = Db::fetchOne(
            'SELECT id_admin, email_admin, password_admin, rol_admin, token_admin, token_exp_admin, status_admin
             FROM admins WHERE email_admin = :e LIMIT 1',
            [':e' => $email]
        );
    }

    if (!$admin) {
        header('Location: ../dash.php?error=bad_credentials');
        exit;
    }

    if (isset($admin->status_admin) && (int)$admin->status_admin === 0) {
        header('Location: ../dash.php?error=bad_credentials');
        exit;
    }

    $hash = (string)($admin->password_admin ?? '');
    $ok = $hash !== '' && password_verify($pass, $hash);
    if (!$ok && $hash !== '' && strlen($hash) < 60) {
        $ok = hash_equals($hash, $pass);
    }

    if (!$ok) {
        header('Location: ../dash.php?error=bad_credentials');
        exit;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['user_id'] = (int)$admin->id_admin;
    $_SESSION['user_role'] = $admin->rol_admin ?? 'vendedor';
    $_SESSION['token_admin'] = $admin->token_admin ?? bin2hex(random_bytes(16));
    unset($_SESSION['token_exp_admin']);
    $_SESSION['email_admin'] = $admin->email_admin ?? $email;
    $_SESSION['id_branch'] = $admin->id_branch ?? null;

    header('Location: ../front/dashboard.php');
    exit;
} catch (Throwable $e) {
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        $detail = rawurlencode($e->getMessage());
        header('Location: ../dash.php?error=bad_credentials&detail=' . $detail);
    } else {
        header('Location: ../dash.php?error=bad_credentials');
    }
    exit;
}
