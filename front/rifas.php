<?php
require_once "../config/config.php";
$page_title = "Gestión de Rifas";
include_once ROOT_PATH . "/includes/head.php";
?>
<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-sidebartype="full">
    <?php include_once ROOT_PATH . "/includes/sidebar.php" ?>
    <div class="body-wrapper">
        <?php include_once ROOT_PATH . "/includes/header.php" ?>
        <div class="body-wrapper-inner">
            <div class="container-fluid pt-3">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0 fw-bold"><i class="ti ti-ticket me-1"></i> Rifas</h2>
                    <button class="btn btn-primary" onclick="abrirModal()"><i class="ti ti-plus"></i> Nueva Rifa</button>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body py-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label class="small fw-bold">Buscador</label>
                                <input type="text" id="searchRifas" class="form-control form-control-sm" placeholder="Buscar título o descripción...">
                            </div>
                            <div class="col-md-4">
                                <label class="small fw-bold">Estado</label>
                                <select id="filterStatus" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="1">Activas</option>
                                    <option value="0">Inactivas</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-outline-primary btn-sm w-100" onclick="limpiarFiltros()">
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
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="ps-3">ID</th>
                                        <th>Título</th>
                                        <th>Descripción</th>
                                        <th>Cifras</th>
                                        <th>Precio</th>
                                        <th>Fecha Sorteo</th>
                                        <th>Promociones</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyTabla">
                                    <tr><td colspan="9" class="text-center py-5">Cargando rifas...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted" id="infoPaginacion"></small>
                            <nav><ul class="pagination pagination-sm mb-0" id="contenedorPaginacion"></ul></nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRifa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Nueva Rifa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formRifa">
                    <input type="hidden" id="rifaId">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Título del Sorteo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="titulo" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Descripción / Premio <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="descripcion" rows="2" required></textarea>
                    </div>
                    <div class="mb-3 d-none">
                        <label class="form-label small fw-bold">Promociones</label>
                        <input type="text" class="form-control" id="promociones">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Precio Boleta <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="precio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Cifras <span class="text-danger">*</span></label>
                            <select class="form-select" id="cifras" required>
                                <option value="2">2 Cifras (100 Núnms)</option>
                                <option value="3">3 Cifras (1.000 Núnms)</option>
                                <option value="4">4 Cifras (10.000 Núnms)</option>
                                <option value="5">5 Cifras (100.000 Núnms)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Fecha y Hora Sorteo <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="fecha" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Estado</label>
                        <select class="form-select" id="estado">
                            <option value="1">Activa</option>
                            <option value="0">Inactiva</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarRifa()">GUARDAR RIFA</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <i class="ti ti-alert-triangle text-warning fs-1 mb-2"></i>
                <h5 class="fw-bold">¿Eliminar Rifa?</h5>
                <p class="text-muted small">Borrará también todos los números.</p>
                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-light flex-grow-1" data-bs-dismiss="modal">No</button>
                    <button class="btn btn-danger flex-grow-1" onclick="confirmarEliminar()">Sí, borrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$extra_js = '<script src="' . ASSETS_URL . '/js/rifas.js?v=2"></script>';
include_once ROOT_PATH . "/includes/footer.php"; 
?>