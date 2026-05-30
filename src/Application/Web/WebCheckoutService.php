<?php
declare(strict_types=1);

namespace App\Application\Web;

use App\Shared\Config\DynamicConfig;
use OpenPayController;
use PaymentBackupsController;
use TransfersController;

final class WebCheckoutService
{
    private const SETTING_WEB_COMPRAS = 'web_compras_habilitadas';

    public function __construct(private readonly DynamicConfig $dynamicConfig)
    {
    }

    public function execute(string $action, array $payload, array $files): array
    {
        if (!$this->areWebPurchasesEnabled()) {
            return [
                'success' => false,
                'message' => 'Las compras en línea están temporalmente deshabilitadas.',
            ];
        }

        return match ($action) {
            'crear_respaldo' => PaymentBackupsController::crearRespaldo($payload),
            'ir_openpay' => OpenPayController::irAOpenPay($payload),
            'crear_transferencia_completa' => $this->createTransfer($payload, $files),
            default => ['success' => false, 'message' => 'Accion no valida'],
        };
    }

    private function createTransfer(array $payload, array $files): array
    {
        if (empty($files['comprobante']) || !is_array($files['comprobante'])) {
            return ['success' => false, 'message' => 'Comprobante requerido'];
        }

        $file = $files['comprobante'];
        $err = (int)($file['error'] ?? \UPLOAD_ERR_OK);
        if ($err !== \UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => self::uploadErrorMessage($err),
            ];
        }

        $tmp = (string)($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return ['success' => false, 'message' => 'Archivo temporal inválido. Intenta de nuevo o usa otra imagen.'];
        }

        if (!self::isAllowedComprobanteMime($tmp, (string)($file['type'] ?? ''))) {
            return ['success' => false, 'message' => 'Formato no permitido (usa JPG, PNG o WebP).'];
        }

        if (((int)($file['size'] ?? 0)) > (5 * 1024 * 1024)) {
            return ['success' => false, 'message' => 'Archivo muy pesado (max 5MB)'];
        }

        // Primero validar negocio (precio BD, stock); si falla, no se consume el archivo subido.
        $transfer = TransfersController::crearTransferencia($payload);
        if (empty($transfer['success']) || empty($transfer['id_transfer'])) {
            return $transfer;
        }
        $idTransfer = (int)$transfer['id_transfer'];

        $name = time() . '_' . preg_replace('/[^A-Za-z0-9.\-_]/', '', (string)($file['name'] ?? 'comprobante'));
        $relativePath = 'uploads/comprobantes/' . $name;
        $absolutePath = self::resolveComprobanteAbsolutePath($relativePath);
        $dir = dirname($absolutePath);

        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            \Db::delete('transfers', 'id_transfer = :id', [':id' => $idTransfer]);

            return ['success' => false, 'message' => 'No se pudo crear la carpeta de comprobantes: ' . $dir];
        }

        if (!is_writable($dir)) {
            \Db::delete('transfers', 'id_transfer = :id', [':id' => $idTransfer]);

            return [
                'success' => false,
                'message' => 'Sin permiso de escritura en ' . $dir . ' (chmod 775 o propietario del servidor web).',
            ];
        }

        if (!move_uploaded_file($tmp, $absolutePath)) {
            \Db::delete('transfers', 'id_transfer = :id', [':id' => $idTransfer]);
            $last = error_get_last();

            return [
                'success' => false,
                'message' => 'No se pudo guardar el archivo en ' . $absolutePath
                    . ($last && isset($last['message']) ? ' — ' . $last['message'] : ''),
            ];
        }

        $base = defined('BASE_URL') ? rtrim((string) \BASE_URL, '/') : '';
        $fileUrl = ($base !== '' ? $base . '/' : '/') . $relativePath;

        $n = \Db::update(
            'transfers',
            ['url_transfer' => $fileUrl],
            'id_transfer = :id',
            [':id' => $idTransfer]
        );

        if ($n < 1) {
            @unlink($absolutePath);
            \Db::delete('transfers', 'id_transfer = :id', [':id' => $idTransfer]);

            return ['success' => false, 'message' => 'Error actualizando transferencia'];
        }

        return ['success' => true, 'code_transfer' => $transfer['code_transfer'] ?? null];
    }

    private function areWebPurchasesEnabled(): bool
    {
        $raw = strtolower(trim((string)$this->dynamicConfig->get(self::SETTING_WEB_COMPRAS, '1')));

        return !in_array($raw, ['0', 'false', 'no', 'off'], true);
    }

    private static function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            \UPLOAD_ERR_INI_SIZE, \UPLOAD_ERR_FORM_SIZE => 'El archivo supera el tamaño máximo permitido por el servidor.',
            \UPLOAD_ERR_PARTIAL => 'La subida quedó incompleta. Intenta de nuevo.',
            \UPLOAD_ERR_NO_FILE => 'No se recibió ningún archivo.',
            \UPLOAD_ERR_NO_TMP_DIR => 'El servidor no tiene carpeta temporal para subidas (tmp).',
            \UPLOAD_ERR_CANT_WRITE => 'El servidor no pudo escribir el archivo temporal.',
            \UPLOAD_ERR_EXTENSION => 'Una extensión de PHP bloqueó la subida.',
            default => 'Error al recibir el archivo (código ' . $code . ').',
        };
    }

    private static function isAllowedComprobanteMime(string $tmp, string $browserType): bool
    {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg', 'image/pjpeg'];
        $browserType = strtolower(trim($browserType));
        if ($browserType !== '' && in_array($browserType, $allowed, true)) {
            return true;
        }
        if (!is_file($tmp) || !is_readable($tmp)) {
            return false;
        }
        if (!function_exists('finfo_open')) {
            return false;
        }
        $f = finfo_open(\FILEINFO_MIME_TYPE);
        if ($f === false) {
            return false;
        }
        $mime = strtolower((string) finfo_file($f, $tmp));
        finfo_close($f);

        return in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true);
    }

    /** Ruta absoluta donde guardar; prioriza ROOT_PATH y cae a DOCUMENT_ROOT. */
    private static function resolveComprobanteAbsolutePath(string $relativePath): string
    {
        $bases = [];
        if (defined('ROOT_PATH') && \ROOT_PATH !== false && \ROOT_PATH !== '') {
            $bases[] = rtrim((string) \ROOT_PATH, '/');
        }
        $doc = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
        if ($doc !== '') {
            $bases[] = $doc;
        }
        $bases = array_values(array_unique(array_filter($bases)));

        foreach ($bases as $base) {
            $full = $base . '/' . $relativePath;
            $parent = dirname($full);
            if (is_dir($parent) && is_writable($parent)) {
                return $full;
            }
            if (is_writable($base)) {
                return $full;
            }
        }

        return ($bases[0] ?? '') . '/' . $relativePath;
    }
}
