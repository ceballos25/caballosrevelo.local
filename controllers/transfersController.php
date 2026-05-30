<?php

require_once __DIR__ . '/clientes.controller.php';
require_once __DIR__ . '/ventas.controller.php';
require_once __DIR__ . '/mail.controller.php';

class TransfersController
{
    public const TABLE = 'transfers';

    public static function crearTransferencia(array $data)
    {
        if (
            !isset($data['id_raffle']) || trim((string)$data['id_raffle']) === ''
            || !isset($data['quantity']) || trim((string)$data['quantity']) === ''
        ) {
            return ['success' => false, 'message' => 'Datos incompletos'];
        }

        $cantidad = (int)$data['quantity'];

        if ($cantidad < 3) {
            return ['success' => false, 'message' => 'La compra mínima es de 3 números'];
        }

        $countRow = Db::fetchOne(
            'SELECT COUNT(*) AS c FROM tickets WHERE id_raffle_ticket = :r AND status_ticket = 0',
            [':r' => (int)$data['id_raffle']]
        );
        $disponibles = (int)($countRow->c ?? 0);

        if ($disponibles < $cantidad) {
            return [
                'success' => false,
                'message' => $disponibles < 1 ? 'No hay números disponibles' : 'No hay suficientes números disponibles',
            ];
        }

        $calc = VentasController::calcularTotalPorPrecioRifa((int)$data['id_raffle'], $cantidad);
        if (!$calc['success']) {
            return $calc;
        }
        $montoEsperado = $calc['total'];
        if (isset($data['amount']) && trim((string)$data['amount']) !== '') {
            $montoCliente = (float)$data['amount'];
            if (!VentasController::montosEquivalentesCOP($montoCliente, $montoEsperado)) {
                return [
                    'success' => false,
                    'message' => 'El monto no coincide con el precio vigente de la rifa. Recarga la página e intenta de nuevo.',
                ];
            }
        }

        $code = str_pad((string)random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);

        $idCustomer = ClientesController::obtenerOCrearCliente([
            'name_customer' => $data['name_customer'],
            'lastname_customer' => $data['lastname_customer'],
            'phone_customer' => $data['phone_customer'],
            'email_customer' => $data['email_customer'],
            'department_customer' => $data['department_customer'],
            'city_customer' => $data['city_customer'],
        ]);

        if (!$idCustomer) {
            return ['success' => false, 'message' => 'Error con cliente'];
        }

        $idTransfer = Db::insert(self::TABLE, [
            'code_transfer' => $code,
            'id_raffle_transfer' => (int)$data['id_raffle'],
            'id_customer_transfer' => $idCustomer,
            'quantity_transfer' => $cantidad,
            'amount_transfer' => $montoEsperado,
            'currency_transfer' => 'COP',
            'status_transfer' => 1,
            'source_transfer' => $data['source_transfer'] ?? null,
            'date_created_transfer' => date('Y-m-d H:i:s'),
        ]);

        if ($idTransfer <= 0) {
            return ['success' => false, 'message' => 'Error creando transferencia'];
        }

        return [
            'success' => true,
            'id_transfer' => $idTransfer,
            'code_transfer' => $code,
        ];
    }

    public static function obtenerPorCode(string $code)
    {
        $code = trim($code);
        $row = Db::fetchOne(
            'SELECT * FROM transfers WHERE code_transfer = :c LIMIT 1',
            [':c' => $code]
        );

        return $row ? (array)$row : null;
    }

    public static function aprobarTransferencia(array $transfer)
    {
        if ((int)$transfer['status_transfer'] !== 1) {
            return ['success' => false, 'message' => 'Ya procesado'];
        }

        $cantidad = (int)$transfer['quantity_transfer'];

        if ($cantidad <= 0) {
            return ['success' => false, 'message' => 'Cantidad inválida'];
        }

        $countRow = Db::fetchOne(
            'SELECT COUNT(*) AS c FROM tickets WHERE id_raffle_ticket = :r AND status_ticket = 0',
            [':r' => (int)$transfer['id_raffle_transfer']]
        );
        $disponibles = (int)($countRow->c ?? 0);

        if ($disponibles < $cantidad) {
            return ['success' => false, 'message' => 'No hay suficientes números'];
        }

        $calc = VentasController::calcularTotalPorPrecioRifa((int)$transfer['id_raffle_transfer'], $cantidad);
        if (!$calc['success']) {
            return $calc;
        }
        $totalEsperado = $calc['total'];
        $montoReg = (float)($transfer['amount_transfer'] ?? 0);
        if (!VentasController::montosEquivalentesCOP($montoReg, $totalEsperado)) {
            return [
                'success' => false,
                'message' => 'El monto guardado en la transferencia no coincide con el precio vigente de la rifa. Rechace la solicitud o actualice el precio y vuelva a intentar.',
            ];
        }

        $resVenta = VentasController::crearVenta([
            'id_customer' => $transfer['id_customer_transfer'],
            'id_raffle' => $transfer['id_raffle_transfer'],
            'quantity_sale' => $cantidad,
            'total_sale' => $totalEsperado,
            'code_sale' => $transfer['code_transfer'],
            'payment_method_sale' => 'Transferencia',
            'id_admin' => $_SESSION['user_id'] ?? null,
            'source_sale' => $transfer['source_transfer'] ?? null,
        ], null, true);

        if (empty($resVenta['success']) || empty($resVenta['id_sale'])) {
            return ['success' => false, 'message' => $resVenta['message'] ?? 'Error creando la venta'];
        }

        $n = Db::update(
            self::TABLE,
            ['status_transfer' => 2],
            'id_transfer = :id AND status_transfer = 1',
            [':id' => (int)$transfer['id_transfer']]
        );

        if ($n < 1) {
            return [
                'success' => false,
                'message' => 'La venta se creó pero no se pudo marcar la transferencia como aprobada. Revisar manualmente.',
                'id_sale' => (int)$resVenta['id_sale'],
            ];
        }

        return [
            'success' => true,
            'id_sale' => (int)$resVenta['id_sale'],
            'message' => 'Venta creada correctamente',
        ];
    }

    public static function rechazarTransferencia(array $transfer)
    {
        $n = Db::update(
            self::TABLE,
            ['status_transfer' => 3],
            'id_transfer = :id',
            [':id' => (int)$transfer['id_transfer']]
        );

        if ($n < 1) {
            return ['success' => false, 'message' => 'Error al rechazar'];
        }

        return ['success' => true, 'message' => 'Transferencia rechazada'];
    }

    public static function obtenerTransferencias()
    {
        $sql = 'SELECT t.id_transfer,t.code_transfer,t.quantity_transfer,t.amount_transfer,t.status_transfer,
            t.date_created_transfer,t.source_transfer,t.url_transfer,t.id_raffle_transfer,t.id_customer_transfer,
            c.name_customer,c.lastname_customer,c.phone_customer,c.email_customer,c.city_customer
            FROM transfers t
            INNER JOIN customers c ON c.id_customer = t.id_customer_transfer
            WHERE t.status_transfer = 1
            ORDER BY t.id_transfer DESC';

        $lista = Db::fetchAll($sql);

        return ['success' => true, 'data' => $lista];
    }

    public static function obtenerSettings()
    {
        $rows = Db::fetchAll('SELECT key_setting, value_setting FROM settings');

        $map = [];
        foreach ($rows as $item) {
            $map[$item->key_setting] = $item->value_setting;
        }

        return $map;
    }
}
