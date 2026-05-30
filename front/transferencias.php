<?php
require_once "../config/config.php";
$page_title = "Gestión de Transferencias";
include_once ROOT_PATH . "/includes/head.php";
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-sidebartype="full">
    <?php include_once ROOT_PATH . "/includes/sidebar.php" ?>

    <div class="body-wrapper">
        <?php include_once ROOT_PATH . "/includes/header.php" ?>

        <div class="body-wrapper-inner">
            <div class="container-fluid" style="padding: 0.5rem;">

                <!-- FILTROS -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body py-3">
                        <div class="row g-2 align-items-end">

                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Buscador</label>
                                <input type="text" id="searchTransfer" class="form-control form-control-sm"
                                    placeholder="Código, cliente...">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Fechas</label>
                                <div class="input-group input-group-sm">
                                    <input type="date" id="fecha_inicio" class="form-control">
                                    <input type="date" id="fecha_fin" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Estado</label>
                                <select id="filterEstado" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="1">Pendiente</option>
                                    <option value="2">Aprobado</option>
                                    <option value="3">Rechazado</option>
                                    <option value="4">Error</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Rifa</label>
                                <select id="filterRifa" class="form-select form-select-sm">
                                    <option value="">Todas</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <button class="btn btn-outline-secondary btn-sm w-100"
                                    onclick="limpiarFiltrosTransfer()">
                                    <i class="ti ti-refresh"></i>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- TABLA -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 600px;">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Código</th>
                                        <th>Cantidad</th>
                                        <th>Total</th>
                                        <th>Comprobante</th>
                                        <th>Estado</th>
                                        <th>Fecha y hora</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>

                                <tbody id="bodyTabla">
                                    <tr>
                                        <td colspan="8" class="text-center py-5">Cargando...</td>
                                    </tr>
                                </tbody>

                            </table>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <small id="infoPaginacion"></small>
                            <ul class="pagination pagination-sm mb-0" id="contenedorPaginacion"></ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- MODAL COMPROBANTE -->
<div class="modal fade" id="modalComprobante" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Comprobante</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="cuerpoComprobante"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRecibo" tabindex="-1">
    <div class="modal-dialog modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Recibo</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="cuerpoRecibo"></div>
        </div>
    </div>
</div>

<?php
$extra_js = '<script src="' . ASSETS_URL . '/js/transferencias.js?v=2"></script>';
include_once ROOT_PATH . "/includes/footer.php";
?>