<?php
require_once "../config/config.php";
$page_title = "Nueva Venta";
include_once ROOT_PATH . "/includes/head.php";
?>

<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <?php include_once ROOT_PATH . "/includes/sidebar.php" ?>
    
    <div class="body-wrapper bg-light min-vh-100">
        <?php include_once ROOT_PATH . "/includes/header.php" ?>
        
        <div class="body-wrapper-inner">
            <div class="container-xxl p-2 p-lg-4 pb-5 mb-5"> 

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0 fw-bold text-dark">Registrar Venta</h4>
                    <button class="btn btn-light border shadow-sm px-3 text-danger fw-bold rounded-pill" onclick="location.reload()">
                        <i class="ti ti-refresh"></i>
                    </button>
                </div>

                <div class="row g-3">
                    
                    <div class="col-lg-8">
                        
                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-header bg-white border-bottom py-3">
                                <h6 class="mb-0 fw-bold text-primary"><span class="badge bg-primary rounded-pill me-2">1</span>Cliente</h6>
                            </div>
                            <div class="card-body p-3 p-lg-4">
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">BUSCAR (Opcional)</label>
                                    <select id="buscadorCliente" class="form-control w-100"></select>
                                </div>

                                <div class="bg-white p-1 rounded-3">
                                    <form id="formClienteVenta">
                                        <input type="hidden" id="idCliente" name="id_customer">
                                        
                                        <div class="row g-3">
                                            <div class="col-12 col-md-4">
                                                <label class="small fw-bold text-dark mb-1">Celular <span class="text-danger">*</span></label>
                                                <input type="tel" class="form-control shadow-sm" id="celularCliente" required>
                                            </div>
                                            <div class="col-6 col-md-4">
                                                <label class="small fw-bold text-dark mb-1">Nombre <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control shadow-sm text-capitalize" id="nombreCliente" required>
                                            </div>
                                            <div class="col-6 col-md-4">
                                                <label class="small fw-bold text-dark mb-1">Apellido <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control shadow-sm text-capitalize" id="apellidoCliente" required>
                                            </div>

                                            <div class="col-12 col-md-4">
                                                <label class="small fw-bold text-dark mb-1">Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control shadow-sm text-lowercase" id="emailCliente">
                                            </div>
                                            <div class="col-6 col-md-4">
                                                <label class="small fw-bold text-dark mb-1">Depto <span class="text-danger">*</span></label>
                                                <select class="form-select shadow-sm select2-ubicacion" id="departamento"></select>
                                            </div>
                                            <div class="col-6 col-md-4">
                                                <label class="small fw-bold text-dark mb-1">Ciudad <span class="text-danger">*</span></label>
                                                <select class="form-select shadow-sm select2-ubicacion" id="ciudad" disabled></select>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end mt-3">
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill fw-bold" id="btnLimpiarCliente" onclick="resetClienteForm()">
                                                <i class="ti ti-eraser me-1"></i> Limpiar campos
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold text-primary"><span class="badge bg-primary rounded-pill me-2">2</span>Números</h6>                                
                            </div>
                            
                            <div class="card-body p-3 p-lg-4">
                                
                                <div class="mb-3">
                                    <select class="form-select form-select fw-bold shadow-sm text-dark border-secondary" id="selectRifa">
                                    </select>
                                </div>
                                

                                <div class="row g-2" id="paquetesNumeros">

                                    <div class="col-6 col-md-4">
                                        <input type="radio" class="btn-check paquete-radio" name="paqueteNumeros" id="paq3" value="3">
                                        <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center" for="paq3">
                                            <div class="fw-semibold">3 stickers</div>
                                            <div class="small">$15.000</div>
                                        </label>
                                    </div>

                                    <div class="col-6 col-md-4">
                                        <input type="radio" class="btn-check paquete-radio" name="paqueteNumeros" id="paq4" value="4">
                                        <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center" for="paq4">
                                            <div class="fw-semibold">4 stickers</div>
                                            <div class="small">$20.000</div>
                                        </label>
                                    </div>

                                    <div class="col-6 col-md-4">
                                        <input type="radio" class="btn-check paquete-radio" name="paqueteNumeros" id="paq5" value="5">
                                        <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center" for="paq5">
                                            <div class="fw-semibold">5 stickers</div>
                                            <div class="small">$25.000</div>
                                        </label>
                                    </div>

                                    <div class="col-6 col-md-4">
                                        <input type="radio" class="btn-check paquete-radio" name="paqueteNumeros" id="paq7" value="7">
                                        <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center" for="paq7">
                                            <div class="fw-semibold">7 stickers</div>
                                            <div class="small">$35.000</div>
                                        </label>
                                    </div>

                                    <div class="col-6 col-md-4">
                                        <input type="radio" class="btn-check paquete-radio" name="paqueteNumeros" id="paq10" value="10">
                                        <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center" for="paq10">
                                            <div class="fw-semibold">10 stickers</div>
                                            <div class="small">$50.000</div>
                                        </label>
                                    </div>

                                    <div class="col-6 col-md-4">
                                        <input type="radio" class="btn-check paquete-radio" name="paqueteNumeros" id="paq20" value="20">
                                        <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center" for="paq20">
                                            <div class="fw-semibold">20 stickers</div>
                                            <div class="small">$100.000</div>
                                        </label>
                                    </div>

                                    <div class="col-6 col-md-4">
                                        <input type="radio" class="btn-check paquete-radio" name="paqueteNumeros" id="paq50" value="50">
                                        <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center" for="paq50">
                                            <div class="fw-semibold">50 stickers</div>
                                            <div class="small">$250.000</div>
                                        </label>
                                    </div>


                                    <div class="col-6 col-md-4">

                                        <input type="radio"
                                            class="btn-check paquete-radio"
                                            name="paqueteNumeros"
                                            id="paqCustom"
                                            value="custom">

                                        <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center"
                                            for="paqCustom">

                                            <div class="fw-semibold">Otro</div>

                                        </label>

                                        <input
                                            type="tel"
                                            id="cantidadManual"
                                            class="form-control form-control-sm text-center mt-1"
                                            min="3"
                                            placeholder="Mín. 3"
                                            style="display:none;"
                                        >

                                    </div>   
                                    
                                    <div class="mt-3">
                                        <label class="form-label fw-bold small text-muted">
                                            Escoger número (opcional)
                                        </label>
                                        <input 
                                            type="tel" 
                                            class="form-control shadow-sm" 
                                            id="numeroPremiado" 
                                            placeholder="Ej: 12345"
                                            maxlength="5"
                                        >
                                        <small class="text-muted">
                                            Si lo defines, se asigna dentro de la venta
                                        </small>
                                    </div>


                                </div>                  

                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 d-none d-lg-block">
                        <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 90px;">
                            <div class="card-header text-white py-3 rounded-top-4">
                                <h6 class="mb-0 fw-bold"><i class="ti ti-receipt-2 me-2"></i>Resumen</h6>
                            </div>
                            <div class="card-body p-0 bg-white">
                                <ul class="list-group list-group-flush" id="listaCarritoDesktop" style="max-height: 300px; overflow-y: auto;">
                                    <li class="list-group-item text-center text-muted py-5 border-0"><small>Sin selección</small></li>
                                </ul>
                            </div>
                                <div class="card-footer bg-light p-4 border-top">
                                    <div class="d-flex justify-content-between align-items-end mb-3">
                                        <span class="h6 mb-0 text-muted">
                                            Total a Pagar 
                                            <small class="ms-1 text-muted">(<span id="lblCantidadDesktop">0</span> nums)</small>
                                        </span>
                                        <span class="h2 mb-0 fw-bolder text-primary" id="lblTotalDesktop">$0</span>
                                    </div>
                                    
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <input type="radio" class="btn-check d-none" checked name="metodoPago" cheked id="pagoEfecDesk" value="Venta Manual">
                                            <label class="btn btn-outline-primary w-100 fw-bold py-2 d-none" for="pagoEfecDesk"></label>
                                        </div>
                                    </div>
                                    <button class="btn btn-success w-100 py-2 fw-bold rounded-3 shadow" id="btnCompletarVenta" onclick="procesarVenta()">CONFIRMAR VENTA</button>
                                </div>
                        </div>
                    </div>

                </div> 
                
                <div class="d-lg-none" style="height:20px"></div>

            </div>
        </div>
    </div>
</div>

<div class="fixed-bottom bg-white border-top shadow-lg p-3 d-lg-none">
    <div class="d-flex justify-content-between align-items-center mb-2">
        
        <div class="cursor-pointer">
            <span class="d-block small text-muted fw-bold lh-1">
                TOTAL <i class="ti ti-chevron-up ms-1 text-primary"></i>
            </span>
            <span class="h3 fw-bolder text-primary" id="lblTotalMobile">$0</span>
        </div>
        
        <div class="btn-group" role="group">
            <input type="radio" class="btn-check de-none" checked name="metodoPagoMobile" id="pagoEfecMob" value="Venta Manual">
            <label class="btn btn-outline-primary btn-sm px-3 d-none" for="pagoEfecMob"></label>
            
        </div>
    </div>
    
    <button class="btn btn-success w-100 py-3 fw-bold rounded-pill shadow" id="btnCompletarVentaMobile" onclick="procesarVentaMobile()">
        CONFIRMAR VENTA <i class="ti ti-check ms-1"></i>
    </button>
</div>


<?php
$extra_js = '
<link href="' . ASSETS_URL . '/libs/select2/css/select2.min.css" rel="stylesheet" />
<link href="' . ASSETS_URL . '/libs/select2/css/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<script src="' . ASSETS_URL . '/libs/select2/js/select2.min.js"></script>
<script src="' . ASSETS_URL . '/js/departamentos-ciudades.js"></script>
<script src="' . ASSETS_URL . '/js/vender.js?v=6"></script> 
';
include_once ROOT_PATH . "/includes/footer.php";
?>