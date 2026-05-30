<?php
declare(strict_types=1);

namespace App\Presentation\Http\Middleware;

use App\Shared\Http\Request;
use App\Shared\Http\Response;

final class CsrfMiddleware
{
    public function handle(Request $request, array $protectedActions): void
    {
        $action = (string)$request->input('action', '');
        if (!in_array($action, $protectedActions, true)) {
            return;
        }

        $token = (string)$request->input('csrf_token', '');
        if ($token === '') {
            $token = (string)$request->input('_csrf', '');
        }
        if ($token === '') {
            $token = (string)$request->header('X-CSRF-Token', '');
        }
        $sessionToken = (string)($_SESSION['csrf_token'] ?? '');

        if ($sessionToken === '' || $token === '' || !hash_equals($sessionToken, $token)) {
            Response::json(['success' => false, 'message' => 'CSRF token invalido'], 403);
        }
    }
}
