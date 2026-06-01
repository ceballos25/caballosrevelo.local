<section class="cr-hero-section py-3">
    <div class="container">
        <div class="cr-hero-layout">

            <!-- Título -->
            <div class="cr-hero-title-wrap">
                <h2 class="hero-title mb-0" id="landingHeroTitle">
                    Hermoso Caballo <span class="millonario">¡Trote y Galope!</span>
                </h2>
            </div>

            <!-- Imágenes del premio -->
            <div class="cr-hero-photo">
                <div class="card border-0 bg-transparent shadow-none rounded-4 overflow-hidden text-center">
                    <section id="main-carousel" class="splide">
                        <div class="splide__track">
                            <ul class="splide__list" id="heroMainSlides">
                                <li class="splide__slide">
                                    <img class="premios-primer-sorteo" src="https://cdn.caballosrevelo.com/principal/1.jpg?v=1" alt="Primer puesto 1" loading="lazy">
                                </li>
                                <li class="splide__slide">
                                    <img class="premios-primer-sorteo" src="https://cdn.caballosrevelo.com/principal/2.jpg?v=1" alt="Primer puesto 2" loading="lazy">
                                </li>
                                <li class="splide__slide">
                                    <img class="premios-primer-sorteo" src="https://cdn.caballosrevelo.com/principal/4.jpg?v=1" alt="Primer puesto 3" loading="lazy">
                                </li>
                            </ul>
                        </div>
                    </section>

                    <section id="thumbnail-carousel" class="splide mt-3 mx-auto" style="max-width: 420px;">
                        <div class="splide__track">
                            <ul class="splide__list" id="heroThumbSlides">
                                <li class="splide__slide">
                                    <img src="https://cdn.caballosrevelo.com/principal/1.jpg?v=1" alt="Miniatura 1" loading="lazy">
                                </li>
                                <li class="splide__slide">
                                    <img src="https://cdn.caballosrevelo.com/principal/2.jpg?v=1" alt="Miniatura 2" loading="lazy">
                                </li>
                                <li class="splide__slide">
                                    <img src="https://cdn.caballosrevelo.com/principal/4.jpg?v=1" alt="Miniatura 4" loading="lazy">
                                </li>
                            </ul>
                        </div>
                    </section>
                </div>
            </div>

            <!-- Puestos 1, 2 y 3 -->
            <div class="cr-hero-puestos">
                <div class="cr-hero-premio">
                    <div class="card border-0 shadow-sm premio-mayor-card">
                        <div class="card-body d-flex align-items-start gap-3">
                            <div class="premio-mayor-icon">
                                <i class="ti ti-trophy fs-4"></i>
                            </div>
                            <div>
                                <p class="premio-mayor-label mb-1" id="landingPremioMayorTitulo">Primer puesto</p>
                                <p class="premio-mayor-desc mb-0" id="landingPremioMayorDesc">Caballo trote y galope totalmente aperado (incluye envío) · 3 últimas cifras de la lotería de Medellín</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cr-hero-extras">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm text-center w-100 cr-puesto-card">
                                <div class="card-body py-3">
                                    <p class="cr-puesto-label mb-1">Segundo puesto</p>
                                    <h3 class="fw-bold mb-1">
                                        <span class="color-dinero-premio">$3.000.000</span>
                                    </h3>
                                    <small class="fw-bold text-muted d-block">En efectivo · por las tres primeras de Medellín</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card border-0 shadow-sm text-center w-100 cr-puesto-card">
                                <div class="card-body py-3">
                                    <p class="cr-puesto-label mb-1">Tercer puesto</p>
                                    <h3 class="fw-bold mb-1">
                                        <span class="color-dinero-premio">$1.000.000</span>
                                    </h3>
                                    <small class="fw-bold text-muted d-block">Al cliente con más cupos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barra de progreso -->
            <div class="cr-hero-progress">
                <div class="card border-0 shadow-sm cr-progress-card">
                    <div class="card-body py-3">
                        <div class="cr-progress-label">
                            <span>Ventas realizadas</span>
                            <span id="porcentajeTexto">0%</span>
                        </div>
                        <div class="cr-progress-race my-1">
                            <div class="progress">
                                <div id="barraProgreso" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span id="caballoProgreso" class="cr-progress-caballo" aria-hidden="true">🐎</span>
                            <span class="cr-progress-meta" aria-hidden="true">🏁</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Precio -->
            <div class="cr-hero-precio">
                <div class="card border-0 shadow-sm text-center w-100 cr-hero-precio-card">
                    <div class="card-body py-3">
                        <p class="cr-hero-precio-label mb-2">Precios</p>
                        <div id="precioBoletaDisplay" class="cr-hero-precio-tiers">
                            <span class="spinner-border spinner-border-sm text-warning"></span>
                        </div>
                        <p class="cr-hero-promo-note mb-0 d-none" id="precioPromoNote"></p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
