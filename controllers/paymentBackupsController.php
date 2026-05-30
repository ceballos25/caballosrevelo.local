<?php

require_once __DIR__ . '/clientes.controller.php';
require_once __DIR__ . '/ventas.controller.php';
require_once __DIR__ . '/mail.controller.php';

/**
 * Respaldo de pagos (OpenPay) — MySQL directo.
 */
class PaymentBackupsController
{
    public const TABLE_BACKUP = 'payment_backups';

    public static function crearRespaldo(array $data)
    {
        if (
            empty($data['id_raffle']) ||
            empty($data['quantity']) ||
            empty($data['amount'])
        ) {
            return ['success' => false, 'message' => 'Datos incompletos para crear respaldo'];
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
                'message' => $disponibles < 1
                    ? 'No hay números disponibles'
                    : 'No hay suficientes números disponibles',
            ];
        }

        $code = 'PB-' . date('YmdHis') . rand(100, 999);

        $idCustomer = ClientesController::obtenerOCrearCliente([
            'name_customer' => $data['name_customer'],
            'lastname_customer' => $data['lastname_customer'],
            'phone_customer' => $data['phone_customer'],
            'email_customer' => $data['email_customer'],
            'department_customer' => $data['department_customer'],
            'city_customer' => $data['city_customer'],
        ]);

        if (!$idCustomer) {
            return ['success' => false, 'message' => 'No se pudo crear u obtener el cliente'];
        }

        $insert = [
            'code_payment_backup' => $code,
            'id_raffle_payment_backup' => (int)$data['id_raffle'],
            'id_customer_payment_backup' => $idCustomer,
            'quantity_payment_backup' => $cantidad,
            'amount_payment_backup' => $data['amount'],
            'currency_payment_backup' => 'COP',
            'status_payment_backup' => 1,
            'source_payment_backup' => $data['source_payment_backup'],
            'date_created_payment_backup' => date('Y-m-d H:i:s'),
        ];

        $metaContext = array_filter([
            'fbp' => trim((string)($data['meta_fbp'] ?? '')),
            'fbc' => trim((string)($data['meta_fbc'] ?? '')),
        ]);
        if ($metaContext !== []) {
            $insert['openpay_response_payment_backup'] = json_encode(
                ['meta' => $metaContext],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }

        $idBackup = Db::insert(self::TABLE_BACKUP, $insert);

        if ($idBackup <= 0) {
            return ['success' => false, 'message' => 'Error creando respaldo'];
        }

        return [
            'success' => true,
            'id_payment_backup' => $idBackup,
            'code_payment_backup' => $code,
        ];
    }

    public static function obtenerPorCode(string $code)
    {
        $row = Db::fetchOne(
            'SELECT * FROM payment_backups WHERE code_payment_backup = :c LIMIT 1',
            [':c' => $code]
        );

        return $row ? (array)$row : null;
    }

    private static function log(string $message): void
    {
        writeAppLog('openpay.log', $message);
    }

    public static function aprobarPago(array $backup, array $tx)
    {
        $txStatus = strtolower(trim($tx['status'] ?? ''));

        self::log('========== INICIO APROBACIÓN ==========');
        self::log('Status TX: ' . $txStatus);
        self::log('ID Backup: ' . $backup['id_payment_backup']);

        if ((int)$backup['status_payment_backup'] === 2) {
            self::log('⚠️ Este pago ya fue procesado anteriormente');

            return;
        }

        if ((int)$backup['status_payment_backup'] !== 1) {
            self::log('⚠️ El backup no está en estado pendiente');

            return;
        }

        if (!in_array($txStatus, ['completed', 'paid', 'in_progress', 'charge_pending'], true)) {
            self::log('❌ APROBACIÓN BLOQUEADA - Status no es válido: ' . $txStatus);

            return;
        }

        self::log('✓ Status válido para aprobación');

        Db::update(
            self::TABLE_BACKUP,
            [
                'status_payment_backup' => 2,
                'openpay_status_payment_backup' => $tx['status'] ?? '',
                'openpay_response_payment_backup' => json_encode($tx, JSON_UNESCAPED_UNICODE),
            ],
            'id_payment_backup = :id',
            [':id' => (int)$backup['id_payment_backup']]
        );

        self::log('✓ Respaldo actualizado a APROBADO');

        $cantidad = (int)$backup['quantity_payment_backup'];

        if ($cantidad <= 0) {
            self::log('❌ Cantidad inválida');

            return;
        }

        self::log('Cantidad comprada: ' . $cantidad);

        $countRow = Db::fetchOne(
            'SELECT COUNT(*) AS c FROM tickets WHERE id_raffle_ticket = :r AND status_ticket = 0',
            [':r' => (int)$backup['id_raffle_payment_backup']]
        );
        $disponibles = (int)($countRow->c ?? 0);

        if ($disponibles < $cantidad) {
            self::log('❌ No hay suficientes números disponibles');

            return;
        }

        self::log('Tickets disponibles encontrados: ' . $disponibles);

        $resVenta = VentasController::crearVenta([
            'id_customer' => $backup['id_customer_payment_backup'],
            'id_raffle' => $backup['id_raffle_payment_backup'],
            'quantity_sale' => $cantidad,
            'total_sale' => $backup['amount_payment_backup'],
            'code_sale' => $backup['code_payment_backup'],
            'payment_method_sale' => 'Página Web',
            'source_sale' => $backup['source_payment_backup'] ?? null,
            'id_admin_sale' => 99,
            'meta_user_data' => \App\Application\Marketing\MetaConversionsApi::userDataFromPaymentBackup($backup),
        ], null, false);

        if (!empty($resVenta['success']) && !empty($resVenta['id_sale'])) {
            self::log('✓ Venta creada correctamente');
            self::log('ID Venta: ' . $resVenta['id_sale']);

            MailController::enviarCorreoVenta((int)$resVenta['id_sale']);
            self::log('✓ Correo enviado al cliente');

            self::limpiarRespaldo((int)$backup['id_payment_backup']);
            self::log('✓ Respaldo eliminado');
        } else {
            self::log('❌ Error creando venta');

            Db::update(
                self::TABLE_BACKUP,
                ['status_payment_backup' => 4],
                'id_payment_backup = :id',
                [':id' => (int)$backup['id_payment_backup']]
            );

            self::log('⚠️ Backup marcado como ERROR');
        }

        self::log('========== FIN APROBACIÓN ==========');
    }

    public static function rechazarPago(array $backup, array $tx)
    {
        try {
            $idBackup = (int)$backup['id_payment_backup'];

            self::log('========== INICIO RECHAZO ==========');
            self::log('ID Backup: ' . $idBackup);

            Db::update(
                self::TABLE_BACKUP,
                [
                    'status_payment_backup' => 3,
                    'openpay_status_payment_backup' => $tx['status'] ?? 'failed',
                    'openpay_response_payment_backup' => json_encode($tx, JSON_UNESCAPED_UNICODE),
                ],
                'id_payment_backup = :id',
                [':id' => $idBackup]
            );

            self::log('✓ Respaldo marcado como RECHAZADO');
            self::log('========== FIN RECHAZO ==========');
        } catch (Exception $e) {
            self::log('❌ ERROR: ' . $e->getMessage());
        }
    }

    private static function limpiarRespaldo(int $idBackup): void
    {
        try {
            self::log('========== LIMPIANDO RESPALDO ==========');
            self::log('ID Backup: ' . $idBackup);

            $n = Db::delete(self::TABLE_BACKUP, 'id_payment_backup = :id', [':id' => $idBackup]);

            self::log($n > 0 ? '✓ Respaldo eliminado correctamente' : '⚠️ No se pudo eliminar el respaldo');
            self::log('========== FIN LIMPIEZA RESPALDO ==========');
        } catch (Exception $e) {
            self::log('❌ ERROR LIMPIANDO RESPALDO: ' . $e->getMessage());
        }
    }
}
