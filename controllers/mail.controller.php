<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailController {

    public static function enviarCorreoVenta(int $idSale): bool {

        // 1️⃣ Obtener venta
        $venta = VentasController::consultarVenta($idSale);
        if (!$venta) {
            return false;
        }

        // 2️⃣ Obtener tickets
        $tickets = VentasController::consultarTicketsVenta($idSale);

        // 3️⃣ Reutilizar plantilla existente
        $html = VentasController::generarRecibo($venta, $tickets);
        if (!$html) {
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            // SMTP
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->Port       = SMTP_PORT;
            // Evita bloqueos largos de SMTP que disparan timeouts HTTP en hosting compartido.
            $mail->Timeout = 8;
            $mail->SMTPKeepAlive = false;

            $encryption = strtolower(trim((string)SMTP_ENCRYPTION));
            if ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPAutoTLS = false;
                $mail->SMTPSecure = '';
            }

            // cPanel: localhost presenta cert del host (host11...), no "localhost".
            $smtpHost = strtolower(trim((string)SMTP_HOST));
            if (in_array($smtpHost, ['localhost', '127.0.0.1'], true)) {
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ];
            }

            // Correo
            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            $mail->addAddress($venta->email_customer, trim($venta->name_customer . ' ' . $venta->lastname_customer));

            if (MAIL_BCC) {
                $mail->addBCC(MAIL_BCC);
            }

            $mail->isHTML(true);
            $mail->Subject = '🎟️ Confirmación de compra - ' . SITE_NAME . ' - ' . $idSale;
            $mail->Body    = $html;

            $mail->send();
            return true;

        } catch (Exception $e) {
            writeAppLog('mail.log', $e->getMessage());
            return false;
        }
    }
}
