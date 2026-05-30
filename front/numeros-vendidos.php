<?php
require_once "../config/config.php";
$page_title = "Números Vendidos";
include_once ROOT_PATH . "/includes/head.php";
?>

<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-sidebartype="full">
    <?php include_once ROOT_PATH . "/includes/sidebar.php"; ?>

    <div class="body-wrapper">
        <?php include_once ROOT_PATH . "/includes/header.php"; ?>

        <div class="body-wrapper-inner">
            <div class="container-fluid" style="padding-top: 20px;">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0 fw-bold"><i class="ti ti-ticket me-1"></i>Números Vendidos</h2>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body py-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Buscar Número / Cliente</label>
                                <input type="text" id="searchNumeros" class="form-control form-control-sm"
                                    placeholder="Ej: 15, Juan Pérez...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Rifa</label>
                                <select id="filterRifa" class="form-select form-select-sm">
                                    <option value="">Todas</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-secondary btn-sm w-100" onclick="limpiarFiltros()">
                                    <i class="ti ti-refresh"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="table-light sticky-top" style="z-index: 10;">
                                    <tr>
                                        <th class="ps-4">Cliente</th>

                                        <th>Código</th>

                                        <th class="text-center">Número</th>

                                        <th class="text-end pe-4">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyTabla">
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">Cargando...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted" id="infoPaginacion"></small>
                            <nav>
                                <ul class="pagination pagination-sm mb-0" id="contenedorPaginacion"></ul>
                            </nav>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>


<?php
$extra_js = '<script src="' . ASSETS_URL . '/js/numeros-vendidos.js?v=2"></script>';
include_once ROOT_PATH . "/includes/footer.php";
?>