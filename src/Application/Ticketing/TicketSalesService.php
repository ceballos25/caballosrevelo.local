<?php
declare(strict_types=1);

namespace App\Application\Ticketing;

use App\Domain\Sales\Repository\SalesRepositoryInterface;
use InvalidArgumentException;
use VentasController;
use NumerosController;

final class TicketSalesService
{
    public function __construct(private readonly SalesRepositoryInterface $salesRepository)
    {
    }

    public function execute(string $action, array $payload): array
    {
        return match ($action) {
            'obtener' => $this->salesRepository->getSales(),
            'obtener_rifas' => VentasController::listarRifas(),
            'crear_venta' => $this->salesRepository->createSale($payload),
            'crear_venta_mixta' => $this->salesRepository->createMixedSale($payload),
            'obtener_por_codigo' => $this->salesRepository->getSaleByCode($this->stringOrFail($payload, 'code_sale')),
            'obtener_disponibles' => VentasController::obtenerTicketsDisponibles((int)($payload['id_raffle'] ?? 0)),
            'detalle_venta' => VentasController::obtenerDetalleVenta((int)($payload['id_sale'] ?? 0)),
            'obtener_por_celular' => VentasController::buscarTicketsPorCelular($this->stringOrFail($payload, 'phone_customer')),
            'numeros_vendidos' => NumerosController::obtenerNumerosVendidos(),
            'obtener_admins' => VentasController::obtenerAdmins(),
            'anular' => VentasController::anularVenta((int)($payload['id_sale'] ?? 0)),
            'obtener_origenes' => VentasController::obtenerOrigenesUnicos(),
            default => throw new InvalidArgumentException('Accion no valida'),
        };
    }

    private function stringOrFail(array $payload, string $key): string
    {
        $value = trim((string)($payload[$key] ?? ''));
        if ($value === '') {
            throw new InvalidArgumentException("Parametro requerido: {$key}");
        }

        return $value;
    }
}
