<?php
declare(strict_types=1);

namespace App\Application\Marketing;

final class MetaEventsService
{
    /** @var list<string> */
    public const ALLOWED_ACTIONS = ['track_event', 'list_events'];

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function execute(string $action, array $payload): array
    {
        return match ($action) {
            'track_event' => $this->trackEvent($payload),
            'list_events' => [
                'success' => true,
                'events' => MetaConversionsApi::STANDARD_EVENTS,
                'pixel_id' => MetaConversionsApi::pixelId(),
                'capi_enabled' => defined('META_CAPI_ENABLED') ? (bool)\META_CAPI_ENABLED : true,
            ],
            default => ['success' => false, 'message' => 'Acción no válida'],
        };
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function trackEvent(array $payload): array
    {
        if (!MetaConversionsApi::isConfigured()) {
            return ['success' => false, 'message' => 'Meta Pixel no configurado'];
        }

        $eventName = trim((string)($payload['event_name'] ?? ''));
        if (!MetaConversionsApi::isStandardEvent($eventName)) {
            return ['success' => false, 'message' => 'Evento Meta no válido: ' . $eventName];
        }

        $customData = $this->decodeJsonField($payload['custom_data'] ?? '{}');
        $userInput = $this->decodeJsonField($payload['user_data'] ?? '{}');
        $customData = MetaConversionsApi::sanitizeCustomData($customData);

        $eventRef = trim((string)($payload['event_ref'] ?? ''));
        if ($eventRef === '') {
            $orderId = trim((string)($customData['order_id'] ?? ''));
            if ($orderId !== '') {
                $eventRef = $orderId;
            } elseif (!empty($customData['content_ids']) && is_array($customData['content_ids'])) {
                $eventRef = implode('-', $customData['content_ids']);
            }
        }
        $eventRef = MetaConversionsApi::sanitizeEventReference($eventRef !== '' ? $eventRef : null);

        $userData = MetaConversionsApi::userDataFromInput($userInput);

        if ($eventName === 'Search') {
            $searchPhone = preg_replace('/\D+/', '', (string)($customData['search_string'] ?? ''));
            if (strlen($searchPhone) >= 10) {
                $userData = array_merge(
                    $userData,
                    MetaConversionsApi::userDataFromInput(['phone_customer' => $searchPhone])
                );
            }
        }

        if (!empty($payload['fbp'])) {
            $userData['fbp'] = trim((string)$payload['fbp']);
        }
        if (!empty($payload['fbc'])) {
            $userData['fbc'] = trim((string)$payload['fbc']);
        }

        $result = MetaConversionsApi::trackStandardEvent(
            $eventName,
            $customData,
            $eventRef,
            $userData,
            true
        );

        return [
            'success' => true,
            'event_name' => $eventName,
            'event_id' => $result['event_id'],
            'capi_sent' => $result['sent'],
            'custom_data' => $customData,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonField(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $raw = trim((string)$value);
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
