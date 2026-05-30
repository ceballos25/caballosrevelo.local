<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Web\WebCheckoutService;
use App\Shared\Audit\AuditLogger;
use App\Shared\Http\Request;
use App\Shared\Http\Response;
use Throwable;

final class WebCheckoutController
{
    private const ALLOWED_ACTIONS = ['crear_respaldo', 'ir_openpay', 'crear_transferencia_completa'];

    public function __construct(
        private readonly WebCheckoutService $service,
        private readonly AuditLogger $audit
    ) {
    }

    public function __invoke(Request $request): never
    {
        try {
            $action = trim((string)$request->input('action', ''));
            if (!in_array($action, self::ALLOWED_ACTIONS, true)) {
                Response::json(['success' => false, 'message' => 'Accion no valida'], 422);
            }

            $payload = $this->sanitize($request->all());
            $result = $this->service->execute($action, $payload, $request->files());
            $this->audit->log('web_checkout.' . $action, ['success' => (bool)($result['success'] ?? false)]);
            Response::json($result);
        } catch (Throwable $exception) {
            $this->audit->log('web_checkout.error', ['error' => $exception->getMessage()]);
            Response::json(['success' => false, 'message' => $exception->getMessage()], 500);
        }
    }

    private function sanitize(array $payload): array
    {
        $clean = [];
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $clean[(string)$key] = trim((string)$value);
        }
        return $clean;
    }
}
