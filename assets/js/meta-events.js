/**
 * Meta Pixel + Conversions API (deduplicación con eventID).
 * Payloads neutros: sin referencias a stickers, rifas u otros términos de negocio.
 */
(function (window) {
    'use strict';

    const cfg = window.META_EVENTS_CONFIG || {};
    const AJAX_URL = cfg.ajaxUrl || 'front/ajax/meta.ajax.php';
    const sentKeys = new Set();
    const STORAGE_PREFIX = 'edts_meta_once_';
    const BLOCKED_TERMS = /sticker|stickers|rifa|rifas|raffle|raffles|ticket|tickets|suerte|sorteo|boleta|boletas|paquete|paquetes|transferencia|compra[\s\-]?web/i;

    function containsBlockedTerms(value) {
        return BLOCKED_TERMS.test(String(value || ''));
    }

    function sanitizeCustomData(customData) {
        const data = Object.assign({}, customData || {});
        delete data.content_name;

        ['content_category', 'search_string'].forEach(function (key) {
            if (data[key] === undefined) {
                return;
            }
            const value = String(data[key]).trim();
            if (value === '' || containsBlockedTerms(value)) {
                delete data[key];
            }
        });

        if (Array.isArray(data.content_ids)) {
            const ids = data.content_ids
                .map(function (id) {
                    return String(id).replace(/[^0-9\-]/g, '');
                })
                .filter(Boolean);
            if (ids.length) {
                data.content_ids = ids;
            } else {
                delete data.content_ids;
            }
        }

        return data;
    }

    function sanitizeEventRef(eventRef) {
        if (eventRef === undefined || eventRef === null) {
            return null;
        }

        let value = String(eventRef).trim();
        if (value === '') {
            return null;
        }

        if (containsBlockedTerms(value)) {
            value = value.replace(BLOCKED_TERMS, '');
        }

        value = value.replace(/[^a-z0-9\-]+/gi, '-').replace(/^-+|-+$/g, '');
        return value || null;
    }

    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/[.$?*|{}()[\]\\/+^]/g, '\\$&') + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : '';
    }

    function getFbp() {
        return getCookie('_fbp');
    }

    function getFbc() {
        return getCookie('_fbc');
    }

    function isEnabled() {
        return !!(cfg.enabled && cfg.pixelId && typeof window.fbq === 'function');
    }

    function commerceData(quantity, value, contentId) {
        const data = {
            currency: 'COP',
            value: Number(value) || 0,
            content_type: 'product',
            num_items: Math.max(1, Number(quantity) || 1)
        };

        if (contentId) {
            data.content_ids = [String(contentId)];
        }

        return data;
    }

    function dedupeKey(eventName, eventRef) {
        return eventName + '::' + (eventRef || 'auto');
    }

    function wasSentOnce(storageKey) {
        try {
            return sessionStorage.getItem(STORAGE_PREFIX + storageKey) === '1';
        } catch (e) {
            return sentKeys.has('once::' + storageKey);
        }
    }

    function markSentOnce(storageKey) {
        sentKeys.add('once::' + storageKey);
        try {
            sessionStorage.setItem(STORAGE_PREFIX + storageKey, '1');
        } catch (e) {
            /* ignore */
        }
    }

    function firePixel(eventName, customData, eventId) {
        if (!isEnabled() || !eventId) {
            return;
        }

        window.fbq('track', eventName, customData || {}, { eventID: eventId });
    }

    async function track(eventName, customData, eventRef, userData, options) {
        options = options || {};

        if (!cfg.enabled) {
            return null;
        }

        customData = sanitizeCustomData(customData);
        eventRef = sanitizeEventRef(eventRef);

        const key = dedupeKey(eventName, eventRef);
        if (!options.allowRepeat && sentKeys.has(key)) {
            return null;
        }

        const payload = new URLSearchParams();
        payload.append('action', 'track_event');
        payload.append('event_name', eventName);
        payload.append('custom_data', JSON.stringify(customData || {}));
        if (eventRef) {
            payload.append('event_ref', String(eventRef));
        }
        if (userData && typeof userData === 'object') {
            payload.append('user_data', JSON.stringify(userData));
        }

        const fbp = getFbp();
        const fbc = getFbc();
        if (fbp) payload.append('fbp', fbp);
        if (fbc) payload.append('fbc', fbc);

        try {
            const res = await fetch(AJAX_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: payload.toString(),
                credentials: 'same-origin'
            });

            const json = await res.json();
            if (!json.success || !json.event_id) {
                console.warn('[MetaEvents]', eventName, json.message || 'No event_id');
                return null;
            }

            const pixelData = sanitizeCustomData(json.custom_data || customData);
            firePixel(eventName, pixelData, json.event_id);
            sentKeys.add(key);

            return json;
        } catch (err) {
            console.warn('[MetaEvents]', eventName, err);
            return null;
        }
    }

    async function trackOnce(storageKey, eventName, customData, eventRef, userData) {
        if (wasSentOnce(storageKey)) {
            return null;
        }

        const result = await track(eventName, customData, eventRef, userData);
        if (result) {
            markSentOnce(storageKey);
        }

        return result;
    }

    function fireOnce(storageKey, eventName, customData, eventId) {
        if (wasSentOnce(storageKey)) {
            return;
        }

        firePixel(eventName, sanitizeCustomData(customData), eventId);
        markSentOnce(storageKey);
    }

    window.MetaEvents = {
        track: track,
        trackOnce: trackOnce,
        fireOnly: firePixel,
        fireOnce: fireOnce,
        commerceData: commerceData,
        getFbp: getFbp,
        getFbc: getFbc,
        isEnabled: isEnabled,
        STANDARD_EVENTS: cfg.standardEvents || []
    };
})(window);
