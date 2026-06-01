<?php
declare(strict_types=1);

/**
 * Grilla de paquetes (landing + vender). Mismas clases = mismos estilos (.cr-theme).
 *
 * @var string $crPaquetesContainerId
 * @var string $crPaquetesName
 * @var string $crCantidadManualId
 * @var string $crPaquetesRowClass
 * @var bool   $crPaquetesShowFour
 */
$crPaquetesContainerId = $crPaquetesContainerId ?? 'paquetesNumeros';
$crPaquetesName = $crPaquetesName ?? 'paqueteNumeros';
$crCantidadManualId = $crCantidadManualId ?? 'cantidadManual';
$crPaquetesRowClass = $crPaquetesRowClass ?? 'row g-4';
$crPaquetesShowFour = $crPaquetesShowFour ?? false;
?>
<div class="<?= htmlspecialchars($crPaquetesRowClass, ENT_QUOTES, 'UTF-8') ?>" id="<?= htmlspecialchars($crPaquetesContainerId, ENT_QUOTES, 'UTF-8') ?>">

    <div class="col-6 col-md-4">
        <input type="radio" class="btn-check paquete-radio" name="<?= htmlspecialchars($crPaquetesName, ENT_QUOTES, 'UTF-8') ?>" id="paq1" value="1">
        <label class="w-100 py-2 d-flex flex-column align-items-center justify-content-center paquete-card" for="paq1">
            <div class="fw-bold">1 sticker</div>
            <div class="fs-5 fw-bold">$5.000</div>
        </label>
    </div>

    <div class="col-6 col-md-4">
        <input type="radio" class="btn-check paquete-radio" name="<?= htmlspecialchars($crPaquetesName, ENT_QUOTES, 'UTF-8') ?>" id="paq3" value="3">
        <label class="w-100 py-2 d-flex flex-column align-items-center justify-content-center paquete-card" for="paq3">
            <div class="fw-bold">3 stickers</div>
            <div class="fs-5 fw-bold">$15.000</div>
        </label>
    </div>

    <?php if ($crPaquetesShowFour): ?>
    <div class="col-6 col-md-4">
        <input type="radio" class="btn-check paquete-radio" name="<?= htmlspecialchars($crPaquetesName, ENT_QUOTES, 'UTF-8') ?>" id="paq4" value="4">
        <label class="w-100 py-2 d-flex flex-column align-items-center justify-content-center paquete-card" for="paq4">
            <div class="fw-bold">4 stickers</div>
            <div class="fs-5 fw-bold">$20.000</div>
        </label>
    </div>
    <?php endif; ?>

    <div class="col-6 col-md-4">
        <input type="radio" class="btn-check paquete-radio" name="<?= htmlspecialchars($crPaquetesName, ENT_QUOTES, 'UTF-8') ?>" id="paq5" value="5">
        <label class="w-100 py-2 d-flex flex-column align-items-center justify-content-center paquete-card" for="paq5">
            <div class="fw-bold">5 stickers</div>
            <div class="fs-5 fw-bold">$25.000</div>
        </label>
    </div>

    <div class="col-6 col-md-4">
        <input type="radio" class="btn-check paquete-radio" name="<?= htmlspecialchars($crPaquetesName, ENT_QUOTES, 'UTF-8') ?>" id="paq7" value="7">
        <label class="w-100 py-2 d-flex flex-column align-items-center justify-content-center paquete-card popular" for="paq7">
            <span class="badge-paquete">Popular</span>
            <div class="fw-bold">7 stickers</div>
            <div class="fs-5 fw-bold">$35.000</div>
        </label>
    </div>

    <div class="col-6 col-md-4">
        <input type="radio" class="btn-check paquete-radio" name="<?= htmlspecialchars($crPaquetesName, ENT_QUOTES, 'UTF-8') ?>" id="paq10" value="10">
        <label class="w-100 py-2 d-flex flex-column align-items-center justify-content-center paquete-card recomendado" for="paq10">
            <span class="badge-paquete">Recomendado</span>
            <div class="fw-bold">10 stickers</div>
            <div class="fs-5 fw-bold">$50.000</div>
        </label>
    </div>

    <div class="col-6 col-md-4">
        <input type="radio" class="btn-check paquete-radio" name="<?= htmlspecialchars($crPaquetesName, ENT_QUOTES, 'UTF-8') ?>" id="paq20" value="20">
        <label class="w-100 py-2 d-flex flex-column align-items-center justify-content-center paquete-card mas-vendido" for="paq20">
            <span class="badge-paquete mas-vendido-cintilla">Más vendido</span>
            <div class="fw-bold">20 stickers</div>
            <div class="fs-5 fw-bold">$100.000</div>
        </label>
    </div>

    <div class="col-6 col-md-4">
        <input type="radio" class="btn-check paquete-radio" name="<?= htmlspecialchars($crPaquetesName, ENT_QUOTES, 'UTF-8') ?>" id="paq50" value="50">
        <label class="w-100 py-2 d-flex flex-column align-items-center justify-content-center paquete-card mejor-valor" for="paq50">
            <span class="badge-paquete">VIP</span>
            <div class="fw-bold">50 stickers</div>
            <div class="fs-5 fw-bold">$250.000</div>
        </label>
    </div>

    <div class="col-6 col-md-4">
        <input type="radio" class="btn-check paquete-radio" name="<?= htmlspecialchars($crPaquetesName, ENT_QUOTES, 'UTF-8') ?>" id="paqCustom" value="custom">
        <label class="w-100 py-2 d-flex flex-column align-items-center justify-content-center paquete-card custom" for="paqCustom">
            <span class="badge-paquete">Personalizado</span>
            <div class="fw-bold">Otro</div>
        </label>
        <input type="tel" id="<?= htmlspecialchars($crCantidadManualId, ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm text-center mt-1" min="1" placeholder="Cantidad (mín. 1)" style="display:none;">
    </div>

</div>
