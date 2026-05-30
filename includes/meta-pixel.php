<?php
declare(strict_types=1);

use App\Application\Marketing\MetaConversionsApi;

function edts_meta_pixel_head(): void
{
    static $rendered = false;
    if ($rendered) {
        return;
    }
    $rendered = true;

    if (!MetaConversionsApi::isConfigured()) {
        return;
    }

    $metaPixelId = MetaConversionsApi::pixelId();
    $metaPageViewEventId = MetaConversionsApi::eventId(
        'PageView',
        (string)(session_id() ?: 'guest')
            . '-' . md5((string)($_SERVER['REQUEST_URI'] ?? '/'))
            . '-' . date('Ymd')
    );
    MetaConversionsApi::sendPageView($metaPageViewEventId);
    ?>
    <!-- Meta Pixel Code -->
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', <?= json_encode($metaPixelId, JSON_UNESCAPED_SLASHES) ?>);
    fbq('track', 'PageView', {}, {eventID: <?= json_encode($metaPageViewEventId, JSON_UNESCAPED_SLASHES) ?>});
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=<?= htmlspecialchars($metaPixelId, ENT_QUOTES, 'UTF-8') ?>&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Meta Pixel Code -->
    <script>
    window.META_EVENTS_CONFIG = {
        enabled: true,
        pixelId: <?= json_encode($metaPixelId, JSON_UNESCAPED_SLASHES) ?>,
        ajaxUrl: <?= json_encode(BASE_URL . '/front/ajax/meta.ajax.php', JSON_UNESCAPED_SLASHES) ?>,
        standardEvents: <?= json_encode(MetaConversionsApi::STANDARD_EVENTS, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
    };
    </script>
    <?php
}
