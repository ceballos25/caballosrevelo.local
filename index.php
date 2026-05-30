<?php
require_once "config/config.php";
require_once __DIR__ . '/includes/bendecidos_public.php';
require_once __DIR__ . '/includes/meta-pixel.php';

$bendecidosCards = edts_bendecidos_cards();
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>El dia de TU SUERTE 🍀</title>
    <meta name="description"
        content="El dia de TU SUERTE, comienza aquí. Accede a motos, carros, casas y mucho más. Participa fácil, rápido y seguro desde cualquier lugar de Colombia.">
    <link rel="shortcut icon" href="https://cdn.eldiadetusuerte.com/images/logos/logo.ico" type="image/x-icon">

    <!-- CSS: CDN (Bootstrap, iconos, carrusel, toasts) + hoja local vía SITE_URL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= htmlspecialchars(ASSETS_URL . '/css/styles-v21.css', ENT_QUOTES, 'UTF-8') ?>?v=3">



    <?php edts_meta_pixel_head(); ?>
</head>

<body>
    <div id="webComprasBloqueadasBanner" class="alert alert-danger text-center mb-0 rounded-0 border-0 py-3 d-none fw-semibold" role="alert"></div>

    <!-- Redes: mismas clases que settings.js → aplicarRedSocial (URLs dinámicas) -->
    <aside class="social-float-rail" aria-label="Contacto por redes sociales">
        <!-- Botón Instagram -->
        <div class="btn-instagram btn-social">
            <a href="#" target="_blank" rel="noopener noreferrer" class="social-instagram d-none" aria-label="Abrir Instagram">
                <div class="notification-bubble">1</div>
                <img src="https://public-assets-hv.herveleventos.com/img/instagram.png" alt="Instagram">
            </a>
        </div>

        <!-- Botón WhatsApp -->
        <div class="btn-whatsapp btn-social">
            <a href="#" target="_blank" rel="noopener noreferrer" class="social-whatsapp d-none" aria-label="Abrir WhatsApp">
                <div class="notification-bubble">1</div>
                <svg xmlns="http://www.w3.org/2000/svg" width="39" height="39" viewBox="0 0 39 39" aria-hidden="true" focusable="false">
                    <path fill="#00E676"
                        d="M10.7 32.8l.6.3c2.5 1.5 5.3 2.2 8.1 2.2 8.8 0 16-7.2 16-16 0-4.2-1.7-8.3-4.7-11.3s-7-4.7-11.3-4.7c-8.8 0-16 7.2-15.9 16.1 0 3 .9 5.9 2.4 8.4l.4.6-1.6 5.9 6-1.5z">
                    </path>
                    <path fill="#FFF"
                        d="M32.4 6.4C29 2.9 24.3 1 19.5 1 9.3 1 1.1 9.3 1.2 19.4c0 3.2.9 6.3 2.4 9.1L1 38l9.7-2.5c2.7 1.5 5.7 2.2 8.7 2.2 10.1 0 18.3-8.3 18.3-18.4 0-4.9-1.9-9.5-5.3-12.9zM19.5 34.6c-2.7 0-5.4-.7-7.7-2.1l-.6-.3-5.8 1.5L6.9 28l-.4-.6c-4.4-7.1-2.3-16.5 4.9-20.9s16.5-2.3 20.9 4.9 2.3 16.5-4.9 20.9c-2.3 1.5-5.1 2.3-7.9 2.3zm8.8-11.1l-1.1-.5s-1.6-.7-2.6-1.2c-.1 0-.2-.1-.3-.1-.3 0-.5.1-.7.2 0 0-.1.1-1.5 1.7-.1.2-.3.3-.5.3h-.1c-.1 0-.3-.1-.4-.2l-.5-.2c-1.1-.5-2.1-1.1-2.9-1.9-.2-.2-.5-.4-.7-.6-.7-.7-1.4-1.5-1.9-2.4l-.1-.2c-.1-.1-.1-.2-.2-.4 0-.2 0-.4.1-.5 0 0 .4-.5.7-.8.2-.2.3-.5.5-.7.2-.3.3-.7.2-1-.1-.5-1.3-3.2-1.6-3.8-.2-.3-.4-.4-.7-.5h-1.1c-.2 0-.4.1-.6.1l-.1.1c-.2.1-.4.3-.6.4-.2.2-.3.4-.5.6-.7.9-1.1 2-1.1 3.1 0 .8.2 1.6.5 2.3l.1.3c.9 1.9 2.1 3.6 3.7 5.1l.4.4c.3.3.6.5.8.8 2.1 1.8 4.5 3.1 7.2 3.8.3.1.7.1 1 .2h1c.5 0 1.1-.2 1.5-.4.3-.2.5-.2.7-.4l.2-.2c.2-.2.4-.3.6-.5s.4-.4.5-.6c.2-.4.3-.9.4-1.4v-.7s-.1-.1-.3-.2z">
                    </path>
                </svg>
            </a>
        </div>

        <!-- Botón Facebook -->
        <div class="btn-facebook btn-social">
            <a href="#" target="_blank" rel="noopener noreferrer" class="social-facebook d-none" aria-label="Abrir Facebook">
                <div class="notification-bubble">1</div>
                <img src="https://public-assets-hv.herveleventos.com/img/facebook.png" alt="Facebook">
            </a>
        </div>
    </aside>

    <!-- ===================== NAVBAR ===================== -->
    <nav class="navbar navbar-expand-lg navbar-custom shadow-sm">
        <div class="container justify-content-left justify-content-lg-between">
            <a class="navbar-brand d-flex align-items-center gap-2 logo-principal" href="#">
                <img src="https://cdn.eldiadetusuerte.com/images/logos/logo.jpg">
                <div>
                    <span class="fw-bold text-light lh-1 text-center"></span><br>
                </div>
            </a>
            <span class="badge bg-dark text-light px-3 py-2 d-none d-lg-inline">
                <i class="ti ti-calendar-event me-1"></i> Juega este 30 de Mayo por la de de Boyacá 🎫 
            </span>
        </div>
    </nav>

    <!-- ===================== HERO ===================== -->
    <section class="py-3">
        <div class="container">
            <div class="row g-4">

                <!-- Columna izquierda: carrusel hero -->
                <div class="col-lg-6">

                    <h2 class="hero-title mb-3">
                        Evento <span class="millonario">Flash</span>!
                    </h2>

                    <p class="text-muted fw-semibold mb-4 d-none"></p>

                    <div class="card border-0 bg-transparent shadow-none rounded-4 overflow-hidden" style="text-align: center;">

                        <!-- Carrusel principal hero -->
                        <section id="main-carousel" class="splide">
                            <div class="splide__track">
                                <ul class="splide__list">
                                    <li class="splide__slide">
                                        <img class="premios-primer-sorteo" src="https://cdn.eldiadetusuerte.com/images/profile/v3.png" alt="Premio Mayor" loading="lazy">
                                    </li>
                                </ul>
                            </div>
                        </section>

                        <!-- Miniaturas hero -->
                        <section id="thumbnail-carousel" class="splide mt-3" style="max-width: 200px; margin-left: auto; margin-right: auto;">
                            <div class="splide__track">
                                <ul class="splide__list">
                                    <li class="splide__slide">
                                        <img src="https://cdn.eldiadetusuerte.com/images/profile/v3.png" alt="Miniatura Premio Mayor" loading="lazy">
                                    </li>
                                </ul>
                            </div>
                        </section>

                    </div>
                </div>

                <!-- Columna derecha: info premios -->
                <div class="col-lg-6">

                    <div class="row g-3 mb-4">

                        <!-- Premio mayor -->
                        <div class="col-12">
                            <div class="">
                                <div class="card-body d-flex align-items-start gap-3 premio-mayor">
                                    <div class="bg-warning bg-opacity-25 rounded-circle p-3">
                                        <i class="ti ti-trophy fs-4 text-warning"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1 title-premio-mayor">Premio Mayor</h5>
                                        <p class="fs-6 text-muted fw-bold mb-0">
                                            NMAX V3 2027 PARA EL STICKER PRINCIPAL 💰💰
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Número invertido -->
                        <div class="col-md-12 d-flex">
                            <div class="card border-0 shadow-sm text-center w-100">
                                <div class="card-body">
                                    <h3 class="fw-bold mb-1">
                                        <span class="color-dinero-premio"> $2.000.000</span>
                                    </h3>
                                    <small class="fw-bold text-muted">Para el sticker invertido 💶</small>
                                </div>
                            </div>
                        </div>

                        <!-- Anticipados -->
                        <div class="col-md-12 d-flex d-none">
                            <div class="card border-0 shadow-sm text-center w-100">
                                <div class="card-body">
                                    <h3 class="fw-bold mb-2">
                                        5 Anticipados de <span class="color-dinero-premio"> $500.000</span>
                                    </h3>
                                    <small class="fw-bold text-muted">Todos los días</small>
                                </div>
                            </div>
                        </div>

                        <!-- Stickers bendecidos -->
                        <div class="col-12 d-flex">
                            <div class="card border-0 shadow-sm text-center w-100">
                                <div class="card-body">
                                    <h3 class="fw-bold text-dark mb-4">
                                        5 Sticker Bendecidos de <span class="color-dinero-premio">$500.000</span>
                                    </h3>
                                    <div id="bendecidosCardsContainer">
                                        <?php foreach ($bendecidosCards as $c): ?>
                                            <div class="bendecidos-numeros<?= !empty($c['premiado_vendido']) ? ' premiado' : '' ?>"><?= htmlspecialchars((string)$c['number'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="display-flex m-1">
                                        <small class="fw-bold text-muted m-5 justify-content-center">¡Pago Inmediato!</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Barra de progreso -->
                    <div class="card border-0 shadow-sm text-center mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between fw-bold">
                                <span>🔥 Total Stickers vendidos</span>
                                <span id="porcentajeTexto">0%</span>
                            </div>
                            <div class="progress my-2">
                                <div id="barraProgreso" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Precio boleta (oculto) -->
                    <div class="card bg-dark text-center mb-3 d-none">
                        <div class="card-body">
                            <h2 class="fw-bold text-warning display-6 mb-2" id="precioBoletaDisplay">
                                <div class="spinner-border spinner-border-sm"></div>
                            </h2>
                            <small class="fw-bold text-center text-white mt-2">Mínimo 3 stickers para participar</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- ===================== SECCIÓN PREMIOS ===================== -->
    <section class="section-premios py-4 border-top d-none">
        <div class="container-premio-loteria">
            <div>
                <div class="title-sticker-premiados">
                    <h2 class="text-center fw-bold mb-3 mt-3">5 Sticker bendecidos en premios 🎁</h2>
                </div>

                <!-- Slider premios principal -->
                <div class="container container-premios-dos">

                    <div id="slider-premios" class="splide slider-premios">
                        <div class="splide__track">
                            <ul class="splide__list">
                                <li class="splide__slide">
                                    <img src="https://cdn.eldiadetusuerte.com/images/profile/tv-premio.png" alt="TV Premio">
                                </li>
                                <li class="splide__slide">
                                    <img src="https://cdn.eldiadetusuerte.com/images/profile/play-five.png" alt="Play Five">
                                </li>
                                <li class="splide__slide">
                                    <img src="https://cdn.eldiadetusuerte.com/images/profile/cel-moto-g.png" alt="Celular Moto G">
                                </li>
                                <li class="splide__slide">
                                    <img src="https://cdn.eldiadetusuerte.com/images/profile/bicicleta-azul.png" alt="Bicicleta Azul">
                                </li>
                                <li class="splide__slide">
                                    <img src="https://cdn.eldiadetusuerte.com/images/profile/bicicleta-negra.png" alt="Bicicleta Negra">
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Miniaturas premios -->
                    <div id="slider-thumbnails" class="splide slider-thumbnails">
                        <div class="splide__track">
                            <ul class="splide__list">
                                <li class="splide__slide">
                                    <img src="https://cdn.eldiadetusuerte.com/images/profile/tv-premio.png" alt="Miniatura TV">
                                </li>
                                <li class="splide__slide">
                                    <img src="https://cdn.eldiadetusuerte.com/images/profile/play-five.png" alt="Miniatura Play Five">
                                </li>
                                <li class="splide__slide">
                                    <img src="https://cdn.eldiadetusuerte.com/images/profile/cel-moto-g.png" alt="Miniatura Celular">
                                </li>
                                <li class="splide__slide">
                                    <img src="https://cdn.eldiadetusuerte.com/images/profile/bicicleta-azul.png" alt="Miniatura Bici Azul">
                                </li>
                                <li class="splide__slide">
                                    <img src="https://cdn.eldiadetusuerte.com/images/profile/bicicleta-negra.png" alt="Miniatura Bici Negra">
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>

                <!-- Cronograma anticipados -->
                <div>
                    <div class="container-premiados-number">
                        <div class="container-section-dos mt-5 mb-5">
                            <div class="title-descrption">
                                <p class="title-info">
                                    📅 Todos los días, anticipados de <strong>$500.000</strong>
                                </p>
                            </div>
                            <div class="schedule">
                                <div class="row-days">
                                    <div class="day">CUNDINAMARCA</div>
                                    <div class="lottery">LUNES</div>
                                </div>
                                <div class="row-days">
                                    <div class="day">HUILA</div>
                                    <div class="lottery">MARTES</div>
                                </div>
                                <div class="row-days">
                                    <div class="day">VALLE</div>
                                    <div class="lottery">MIÉRCOLES</div>
                                </div>
                                <div class="row-days">
                                    <div class="day">BOGOTÁ</div>
                                    <div class="lottery">JUEVES</div>
                                </div>
                                <div class="row-days">
                                    <div class="day">MEDELLÍN</div>
                                    <div class="lottery">VIERNES</div>
                                </div>
                                <div class="row-days">
                                    <div class="day">BOYACA</div>
                                    <div class="lottery">SÁBADO</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ===================== SECCIÓN COMPRA ===================== -->
    <section id="compra" class="py-2 bg-white border-top">
        <div class="container">
            <h2 class="text-center fw-bold mb-3 mt-3">🎟️ Paquetes</h2>

            <div class="row g-4">

                <!-- Paquetes -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white py-3 px-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <p class="fw-bold mb-0 text-dark">
                                    <i class="ti ti-grid-dots me-2 text-warning"></i>Selecciona la cantidad
                                </p>
                            </div>
                            <p>
                                <small class="text-muted py-3 px-3">Mínimo 3 stickers para participar</small>
                            </p>
                        </div>

                        <div class="card-body bg-light">
                            <div class="row g-4" id="paquetesNumeros">

                                <!-- 3 stickers -->
                                <div class="col-6 col-md-4 paquetes">
                                    <input type="radio" class="btn-check paquete-radio" name="paqueteNumeros" id="paq3" value="3">
                                    <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center paquete-card" for="paq3">
                                        <div class="fw-bold">3 stickers</div>
                                        <div class="fs-5 fw-bold">$15.000</div>
                                    </label>
                                </div>

                                <!-- 5 stickers -->
                                <div class="col-6 col-md-4">
                                    <input type="radio" class="btn-check paquete-radio" name="paqueteNumeros" id="paq5" value="5">
                                    <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center paquete-card" for="paq5">
                                        <div class="fw-bold">5 stickers</div>
                                        <div class="fs-5 fw-bold">$25.000</div>
                                    </label>
                                </div>

                                <!-- 7 stickers -->
                                <div class="col-6 col-md-4">
                                    <input type="radio" class="btn-check paquete-radio" name="paqueteNumeros" id="paq7" value="7">
                                    <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center paquete-card popular" for="paq7">
                                        <span class="badge-paquete">🎯 Popular</span>
                                        <div class="fw-bold">7 stickers</div>
                                        <div class="fs-5 fw-bold">$35.000</div>
                                    </label>
                                </div>

                                <!-- 10 stickers -->
                                <div class="col-6 col-md-4">
                                    <input type="radio" class="btn-check paquete-radio" name="paqueteNumeros" id="paq10" value="10">
                                    <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center paquete-card recomendado" for="paq10">
                                        <span class="badge-paquete">⭐ Recomendado</span>
                                        <div class="fw-bold">10 stickers</div>
                                        <div class="fs-5 fw-bold">$50.000</div>
                                    </label>
                                </div>

                                <!-- 20 stickers -->
                                <div class="col-6 col-md-4">
                                    <input type="radio" class="btn-check paquete-radio" name="paqueteNumeros" id="paq20" value="20">
                                    <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center paquete-card mas-vendido" for="paq20">
                                        <span class="badge-paquete mas-vendido-cintilla">🔥 Más vendido</span>
                                        <div class="fw-bold">20 stickers</div>
                                        <div class="fs-5 fw-bold">$100.000</div>
                                    </label>
                                </div>

                                <!-- 50 stickers -->
                                <div class="col-6 col-md-4">
                                    <input type="radio" class="btn-check paquete-radio" name="paqueteNumeros" id="paq50" value="50">
                                    <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center paquete-card mejor-valor" for="paq50">
                                        <span class="badge-paquete">💰 VIP</span>
                                        <div class="fw-bold">50 stickers</div>
                                        <div class="fs-5 fw-bold">$250.000</div>
                                    </label>
                                </div>

                                <!-- Personalizado -->
                                <div class="col-6 col-md-4">
                                    <input type="radio" class="btn-check paquete-radio" name="paqueteNumeros" id="paqCustom" value="custom">
                                    <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center paquete-card custom" for="paqCustom">
                                        <span class="badge-paquete">🎯 Personalizado</span>
                                        <div class="fw-bold">Otro</div>
                                    </label>
                                    <input type="tel" id="cantidadManual" class="form-control form-control-sm text-center mt-1" min="3" placeholder="Cantidad (mín. 3)" style="display:none;">
                                </div>

                            </div>

                            <div class="alert alert-warning text-center small fw-bold mt-3">
                                🎯 Más stickers = más oportunidades de ganar
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Sidebar desktop -->
                <div class="col-lg-4 d-none d-lg-block">
                    <div class="card border-0 shadow sticky-top">
                        <div class="card-body">
                            <h4 class="fw-bold mb-3">Tu Compra</h4>

                            <div id="listaTicketsDesktop" class="mb-3"></div>

                            <ul class="list-group mb-3">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Cantidad</span>
                                    <strong id="cantTicketsDesktop">0</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between fw-bold">
                                    <span>Total</span>
                                    <strong class="text-success" id="totalDineroDesktop">$0</strong>
                                </li>
                            </ul>

                            <div class="alert alert-warning small text-center fw-bold">
                                🔥 Estás a un paso de participar
                            </div>

                            <button class="btn btn-dark w-100 py-3 fw-bold" onclick="abrirCheckout()" id="btnPagarDesktop" disabled>
                                Pagar ahora →
                            </button>

                            <div class="mt-3 pt-3 border-top text-center">
                                <p class="small text-muted mb-2 d-flex align-items-center justify-content-center gap-1">
                                    <i class="ti ti-lock-square-rounded text-success fs-5"></i>
                                    Pagos 100% seguros y confirmación inmediata
                                </p>
                                <div class="d-flex justify-content-center align-items-center gap-3 grayscale-hover">
                                    <img src="https://cdn.eldiadetusuerte.com/images/logos/pse.png" alt="PSE" style="height: 40px; width: auto;">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>


<!-- ===================== GANADORES ===================== -->
<section class="texto-ganadores">
    <div>
        <h2 class="title-ganadores text-center title-premios">¡Últimos ganadores! 🥳</h2>
    </div>
</section>

<section class="container-ganadores position-relative overflow-hidden mb-5">

    <canvas id="confetti-canvas"></canvas>

    <div style="max-width: 600px; margin: 0 auto;">

    <!-- PRINCIPAL -->
    <div id="ganadores-carousel" class="splide">
        <div class="splide__track">
            <ul class="splide__list">
                
                <li class="splide__slide text-center">
                    <p class="ganador-one">Evento Todo Terreno XTZ + 3 Palitos en Bogotá ️</p>
                    <img src="https://cdn.eldiadetusuerte.com/images/profile/bogota.png">
                </li>                
                <!-- 1 -->
                <li class="splide__slide text-center">
                    <p class="ganador-one">Ganador MT15 + 2 Palitos en Girardota🏍️</p>
                    <img src="https://cdn.eldiadetusuerte.com/images/profile/ganador-girardota.png">
                </li>
                <!-- 1 -->
                <li class="splide__slide text-center">
                    <p class="ganador-one">Combo navideño premio #1 🏍️</p>
                    <img src="https://cdn.eldiadetusuerte.com/images/profile/ganador-combo-navideno-V3.png">
                </li>

                <!-- 2 -->
                <li class="splide__slide text-center">
                    <p class="ganador-one">Combo navideño premio #2 🏍️</p>
                    <img src="https://cdn.eldiadetusuerte.com/images/profile/dinamica-twoo-combo.png">
                </li>

                <!-- 3 -->
                <li class="splide__slide text-center">
                    <p class="ganador-one">Noviembre MT-15 🏍️</p>
                    <img src="https://cdn.eldiadetusuerte.com/images/profile/mt-15-nov2.png">
                </li>

                <!-- 4 -->
                <li class="splide__slide text-center">
                    <p class="ganador-one">Noviembre MT-15 🏍️</p>
                    <img src="https://cdn.eldiadetusuerte.com/images/profile/ganadora_mt15.png">
                </li>

                <!-- 5 -->
                <li class="splide__slide text-center">
                    <p class="ganador-one">Octubre NMAX V3 🏍️</p>
                    <img src="https://cdn.eldiadetusuerte.com/images/profile/nmx-oct3.png">
                </li>

                <!-- 6 -->
                <li class="splide__slide text-center">
                    <p class="ganador-one">Agosto Mazda 🏎️</p>
                    <img src="https://cdn.eldiadetusuerte.com/images/profile/ganador-mazda.png">
                </li>

                <!-- 7 -->
                <li class="splide__slide text-center">
                    <p class="ganador-one">Mayo MT-15 🏍️</p>
                    <img src="https://cdn.eldiadetusuerte.com/images/profile/ganadora-mt-2025-mayo.jpg">
                </li>

                <!-- 8 -->
                <li class="splide__slide text-center">
                    <p class="ganador-one">Octubre FZ 3.0 🏍️</p>
                    <img src="https://cdn.eldiadetusuerte.com/images/profile/ganadorfz.png">
                </li>

                <!-- 9 -->
                <li class="splide__slide text-center">
                    <p class="ganador-one">Crypton FI 🏍️</p>
                    <img src="https://cdn.eldiadetusuerte.com/images/profile/ganador-tres.jpg">
                </li>

                <!-- 10 -->
                <li class="splide__slide text-center">
                    <p class="ganador-one">Julio MT-15 🏍️</p>
                    <img src="https://cdn.eldiadetusuerte.com/images/profile/ganadormt.png">
                </li>

                <!-- 11 -->
                <li class="splide__slide text-center">
                    <p class="ganador-one">Agosto NMAX 2025 🏍️</p>
                    <img src="https://cdn.eldiadetusuerte.com/images/profile/ganadornmax.png">
                </li>

                <!-- 12 -->
                <li class="splide__slide text-center">
                    <p class="ganador-one">Enero NS200 🏍️</p>
                    <img src="https://cdn.eldiadetusuerte.com/images/profile/ganadora-pulsar-f1.jpg">
                </li>

            </ul>
        </div>
    </div>

    <!-- MINIATURAS (MISMO ORDEN EXACTO) -->
    <div id="ganadores-thumbnails" class="splide mt-2">
        <div class="splide__track">
            <ul class="splide__list">
                <li class="splide__slide"><img src="https://cdn.eldiadetusuerte.com/images/profile/bogota.png"></li>
                <li class="splide__slide"><img src="https://cdn.eldiadetusuerte.com/images/profile/ganador-girardota.png"></li>
                <li class="splide__slide"><img src="https://cdn.eldiadetusuerte.com/images/profile/ganador-combo-navideno-V3.png"></li>
                <li class="splide__slide"><img src="https://cdn.eldiadetusuerte.com/images/profile/dinamica-twoo-combo.png"></li>
                <li class="splide__slide"><img src="https://cdn.eldiadetusuerte.com/images/profile/mt-15-nov2.png"></li>
                <li class="splide__slide"><img src="https://cdn.eldiadetusuerte.com/images/profile/ganadora_mt15.png"></li>
                <li class="splide__slide"><img src="https://cdn.eldiadetusuerte.com/images/profile/nmx-oct3.png"></li>
                <li class="splide__slide"><img src="https://cdn.eldiadetusuerte.com/images/profile/ganador-mazda.png"></li>
                <li class="splide__slide"><img src="https://cdn.eldiadetusuerte.com/images/profile/ganadora-mt-2025-mayo.jpg"></li>
                <li class="splide__slide"><img src="https://cdn.eldiadetusuerte.com/images/profile/ganadorfz.png"></li>
                <li class="splide__slide"><img src="https://cdn.eldiadetusuerte.com/images/profile/ganador-tres.jpg"></li>
                <li class="splide__slide"><img src="https://cdn.eldiadetusuerte.com/images/profile/ganadormt.png"></li>
                <li class="splide__slide"><img src="https://cdn.eldiadetusuerte.com/images/profile/ganadornmax.png"></li>
                <li class="splide__slide"><img src="https://cdn.eldiadetusuerte.com/images/profile/ganadora-pulsar-f1.jpg"></li>

            </ul>
        </div>
    </div>

    </div>

</section>

    <!-- ===================== FOOTER ===================== -->
    <footer class="site-footer">
        <div class="container py-5">

            <div class="row g-4 text-center text-md-start">

                <div class="col-md-4">
                    <h5 class="fw-bold site-footer-heading">El dia de TU SUERTE 🍀</h5>
                    <p class="small site-footer-text mb-0">
                        Emocionantes oportunidades para ganar motos, carros y más. Transparencia, respaldo y seguridad en cada dinámica.
                    </p>
                </div>

                <div class="col-md-4">
                    <h6 class="fw-bold text-uppercase mb-3 site-footer-heading">Enlaces de interés</h6>
                    <ul class="list-unstyled site-footer-list small mb-0">
                        <li>
                            <a href="#compra" class="site-footer-link">
                                <i class="ti ti-shopping-cart" aria-hidden="true"></i>
                                <span>Comprar stickers</span>
                            </a>
                        </li>
                        <li>
                            <a href="assets/doc/politica de proteccion de datos personale.pdf" class="site-footer-link" target="_blank" rel="noopener">
                                <i class="ti ti-shield-lock" aria-hidden="true"></i>
                                <span>Política de privacidad</span>
                            </a>
                        </li>
                        <li>
                            <a href="assets/doc/tyc-v4.pdf" class="site-footer-link" target="_blank" rel="noopener">
                                <i class="ti ti-file-text" aria-hidden="true"></i>
                                <span>Términos y condiciones</span>
                            </a>
                        </li>
                        <li id="consultarNumeros">
                            <button type="button" class="btn btn-warning btn-sm fw-bold site-footer-cta" data-bs-toggle="modal" data-bs-target="#modalBuscarTickets">
                                <i class="ti ti-ticket me-1" aria-hidden="true"></i>
                                Buscar mis stickers
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="col-md-4">
                    <h6 class="fw-bold text-uppercase mb-3 site-footer-heading">Contacto</h6>
                    <ul class="list-unstyled site-footer-list small mb-0">
                        <li>
                            <i class="ti ti-phone site-footer-icon" aria-hidden="true"></i>
                            <span>(+57) 317 168 4127</span>
                        </li>
                        <li>
                            <i class="ti ti-mail site-footer-icon" aria-hidden="true"></i>
                            <a href="mailto:info@eldiadetusuerte.com" class="site-footer-link">info@eldiadetusuerte.com</a>
                        </li>
                        <li>
                            <i class="ti ti-map-pin site-footer-icon" aria-hidden="true"></i>
                            <span>Colombia</span>
                        </li>
                        <li>
                            <i class="ti ti-credit-card site-footer-icon" aria-hidden="true"></i>
                            <span>Pagos procesados vía PSE</span>
                        </li>
                    </ul>
                    <div class="mt-3">
                        <img src="https://cdn.eldiadetusuerte.com/images/logos/pse.png" height="44" class="site-footer-pse" alt="PSE">
                    </div>
                </div>

            </div>

            <hr class="site-footer-hr my-4">

            <div class="text-center small site-footer-copy pb-2">
                © <?= date('Y'); ?> El dia de TU SUERTE · Todos los derechos reservados V6.0.0
                <br>
                Desarrollado por
                <strong>
                    <a href="https://wa.me/573245894268?text=Hola%20vi%20la%20p%C3%A1gina%20de%20EDTS%20y%20quiero%20obtener%20m%C3%A1s%20informaci%C3%B3n%20sobre%20el%20sistema%20"
                        target="_blank" rel="noopener noreferrer" class="site-footer-dev">
                        Cristian Ceballos
                        <i class="ti ti-brand-whatsapp" aria-hidden="true"></i>
                        <i class="ti ti-link" aria-hidden="true"></i>
                    </a>
                </strong>
            </div>

        </div>
    </footer>

    <!-- ===================== MOBILE CART ===================== -->
    <div class="mobile-cart-bar d-lg-none" id="mobileCart" style="display:none!important">
        <div class="d-flex align-items-center gap-3">
            <div class="mobile-cart-info">
                <span class="mobile-cart-label">Total a Pagar</span>
                <div>
                    <span class="mobile-cart-price" id="lblTotalMobile">$0</span>
                </div>
                <div>
                    <span class="mobile-cart-count"><span id="lblCantidadMobile">0</span> Núms</span>
                </div>
            </div>
        </div>
        <button class="btn btn-warning rounded-pill px-4 fw-bold shadow" onclick="abrirCheckout()" id="btnPagarMobile">
            PAGAR 🔥
        </button>
    </div>

    <!-- ===================== MODAL BUSCAR TICKETS ===================== -->
    <div class="modal fade" id="modalBuscarTickets" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">🔍 Buscar mis stickers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <input type="tel" id="inputBuscarTickets" class="form-control text-center mb-3" placeholder="Ej: 3001234567">
                    <button class="btn btn-warning w-100 fw-bold" onclick="buscarTickets()">Buscar</button>
                    <div id="resultadoBusqueda" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== MODAL CHECKOUT ===================== -->
    <div class="modal fade" id="modalCheckout" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

                <div class="modal-header bg-dark text-white border-bottom border-warning">
                    <h5 class="modal-title fw-bold">🚀 Finalizar Compra</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4 bg-light">
                    <form id="formCheckout">
                        <input type="hidden" id="totalPagarInput" name="totalPagar">

                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-uppercase fw-bold text-muted">Tus Números</small><br>
                                    <span class="fw-bold text-dark" id="resumenNumeros">...</span>
                                </div>
                                <div class="text-end">
                                    <small class="text-uppercase fw-bold text-muted">Total</small><br>
                                    <span class="fw-bold text-success fs-5" id="resumenTotal">$0</span>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3">Datos del Comprador</h6>

                        <div class="form-floating mb-3">
                            <input type="tel" class="form-control" id="celularCliente" required placeholder="Celular">
                            <label>Celular</label>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="nombreCliente" required placeholder="Nombre">
                                    <label>Nombre</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="apellidoCliente" required placeholder="Apellido">
                                    <label>Apellido</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="emailCliente" required placeholder="Correo">
                            <label>Correo Electrónico</label>
                        </div>

                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <div class="select-floating-label-group">
                                    <select class="form-select select2-ubicacion" id="departamento" required>
                                        <option value="">Departamento...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="select-floating-label-group">
                                    <select class="form-select select2-ubicacion" id="ciudad" required>
                                        <option value="">Ciudad...</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h6 class="fw-bold mb-3">Método de Pago</h6>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-dark w-100 fw-bold btn-sm" data-metodo="pse" onclick="seleccionarMetodo('pse')">
                                    💳 PSE
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-success w-100 fw-bold btn-sm" data-metodo="transferencia" onclick="seleccionarMetodo('transferencia')">
                                    🏦 Transferencia
                                </button>
                            </div>
                        </div>

                        <div id="contenedorMetodoPago">

                            <!-- PSE -->
                            <div id="metodoPSE" class="metodo-pago d-none btn-sm">
                                <button type="button" class="btn btn-warning w-100 py-3 fw-bold text-uppercase shadow-sm" onclick="iniciarPagoPSE()">
                                    Ir a pagar con PSE 💳
                                </button>
                            </div>

                            <!-- Transferencia -->
                            <div id="metodoTransferencia" class="metodo-pago d-none">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">

                                        <h6 class="fw-bold text-success mb-3">💸 Datos para Transferencia</h6>

                                        <!-- Bancolombia -->
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <strong>Llave Bre-B 🔑</strong><br>
                                                <span id="llave">@jorge5448</span><br>
                                                <small class="text-muted">Jorge Herrera</small>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-dark" onclick="copiarTexto('llave')">
                                                Copiar
                                            </button>
                                        </div>

                                        <!-- Llave breve -->
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <strong>Ahorros Bancolombia</strong><br>
                                                <span id="bancolombia">43800000923</span><br>
                                                <small class="text-muted">Jorge Herrera</small>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-dark" onclick="copiarTexto('bancolombia')">
                                                Copiar
                                            </button>
                                        </div>
                                        
                                        <!-- LlaveNequi Davi -->
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <strong>Nequi / Daviplata:</strong><br>
                                                <span id="nequi">3105888748</span><br>
                                                <small class="text-muted">Jorge Herrera</small>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-dark" onclick="copiarTexto('nequi')">
                                                Copiar
                                            </button>
                                        </div>                                        

                                        <hr>

                                        <label class="fw-bold mb-2">📤 Subir comprobante</label>
                                        <input type="file" class="form-control mb-3" id="comprobantePago" accept="image/*,application/pdf">

                                        <button type="button" class="btn btn-success w-100 fw-bold" onclick="procesarTransferencia(event)">
                                            Confirmar pago 🚀
                                        </button>

                                    </div>
                                </div>
                            </div>

                        </div>

                    </form>

                    <div class="mt-3 pt-3 border-top text-center">
                        <p class="small text-muted mb-2 d-flex align-items-center justify-content-center gap-1">
                            <i class="ti ti-lock-square-rounded text-success fs-5"></i>
                            Pagos 100% seguros y confirmación inmediata
                        </p>
                        <div class="d-flex justify-content-center align-items-center gap-3 grayscale-hover">
                            <img src="https://cdn.eldiadetusuerte.com/images/logos/pse.png" alt="PSE" style="height: 40px; width: auto;">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <?php
    include "includes/preloader.php";
    include "includes/btn-share.php";
    ?>

    <!-- ===================== SCRIPTS ===================== -->
    <script src="<?= ASSETS_URL ?>/libs/jquery/dist/jquery.min.js"></script>
    <script src="<?= ASSETS_URL ?>/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
    <script src="assets/js/departamentos-ciudades.js"></script>
    <script src="https://t.contentsquare.net/uxa/8c88e0bc219df.js"></script>
    <script src="<?= htmlspecialchars(ASSETS_URL . '/js/meta-events.js', ENT_QUOTES, 'UTF-8') ?>?v=3"></script>
    <script src="assets/js/settings.js?v=11"></script>
    <script src="assets/js/frontend.js?v=11"></script>
    <script src="<?= htmlspecialchars(ASSETS_URL . '/js/buscarTickets.js', ENT_QUOTES, 'UTF-8') ?>?v=2"></script>

</body>
</html>