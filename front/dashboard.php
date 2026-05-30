<?php

require_once "../config/config.php";
$page_title = "Dashboard Principal";
include_once ROOT_PATH . "/includes/head.php";
?>

<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-sidebartype="full">
    <?php include_once ROOT_PATH . "/includes/sidebar.php"; ?>

    <div class="body-wrapper">
        <?php include_once ROOT_PATH . "/includes/header.php"; ?>

        <div class="body-wrapper-inner">
            <div class="container-fluid" style="padding-top: 20px;">

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-3">
                        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-center gap-3">
                            <div class="w-100 w-xl-auto text-center text-xl-start">
                                <h3 class="mb-0 fw-bold text-dark text-nowrap"><i class="ti ti-chart-pie-filled text-primary me-2"></i>Dashboard</h3>
                            </div>
                            
                            <div class="row g-2 w-100 w-xl-auto align-items-center justify-content-end">
                                <div class="col-12 col-md-auto">
                                    <select id="filterRifa" class="form-select form-select-sm fw-bold border-secondary-subtle text-dark" style="min-width: 200px;"></select>
                                </div>
                                <div class="col-6 col-md-auto">
                                    <select id="filterPeriodo" class="form-select form-select-sm bg-light text-dark fw-medium" style="min-width: 130px;">
                                        <option value="mes" selected>📅 Este Mes</option>
                                        <option value="semana">📅 Esta Semana</option>
                                        <option value="hoy">📅 Hoy</option>
                                        <option value="ayer">📅 Ayer</option>
                                        <option value="ano">📅 Este Año</option>
                                        <option value="">⚙️ Rango</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-auto d-flex gap-2">
                                    <button class="btn btn-dark btn-sm flex-grow-1 px-3" onclick="cargarDashboard()" title="Filtrar">
                                        <i class="ti ti-filter d-md-none me-1"></i> <span class="d-none d-md-inline">Filtrar</span>
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm px-3" onclick="limpiarFiltrosDashboard()" title="Limpiar">
                                        <i class="ti ti-refresh"></i>
                                    </button>
                                </div>
                                <div class="col-12 col-md-auto">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white text-muted border-end-0"><i class="ti ti-calendar"></i></span>
                                        <input type="date" id="filterDesde" class="form-control" title="Desde">
                                        <span class="input-group-text bg-light border-start-0 border-end-0 text-muted px-1">-</span>
                                        <input type="date" id="filterHasta" class="form-control" title="Hasta">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4 g-3">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center p-3">
                                <div class="bg-primary-subtle text-primary rounded-circle p-3 me-3"><i class="ti ti-currency-dollar fs-2"></i></div>
                                <div><h6 class="text-muted small text-uppercase fw-bold mb-1">Ventas Totales</h6><h3 class="mb-0 fw-bolder text-dark" id="kpiVentas">$0</h3></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center p-3">
                                <div class="bg-success-subtle text-success rounded-circle p-3 me-3"><i class="ti ti-ticket fs-2"></i></div>
                                <div><h6 class="text-muted small text-uppercase fw-bold mb-1">Números Vendidos</h6><h3 class="mb-0 fw-bolder text-dark" id="kpiVendidos">0</h3></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center p-3">
                                <div class="bg-warning-subtle text-warning rounded-circle p-3 me-3"><i class="ti ti-users fs-2"></i></div>
                                <div><h6 class="text-muted small text-uppercase fw-bold mb-1">Total Clientes</h6><h3 class="mb-0 fw-bolder text-dark" id="kpiClientes">0</h3></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center p-3">
                                <div class="bg-info-subtle text-info rounded-circle p-3 me-3"><i class="ti ti-box fs-2"></i></div>
                                <div><h6 class="text-muted small text-uppercase fw-bold mb-1">Stock Disponible</h6><h3 class="mb-0 fw-bolder text-dark" id="kpiDisponibles">0</h3></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="card-title fw-bold mb-0">📈 Comportamiento de Ventas</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div id="chartTendencia" style="height: 350px;"></div>
                    </div>
                </div>

                <div class="row mb-4 g-3">
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3 border-0 text-center">
                                <h6 class="fw-bold mb-0 text-uppercase text-muted small">CANTIDAD DE VENTAS</h6>
                            </div>
                            <div class="card-body d-flex justify-content-center align-items-center">
                                <div id="chartMediosTransacciones" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3 border-0 text-center">
                                <h6 class="fw-bold mb-0 text-uppercase text-muted small">CANTIDAD DE NÚMEROS</h6>
                            </div>
                            <div class="card-body d-flex justify-content-center align-items-center">
                                <div id="chartMediosTickets" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3 border-0 text-center">
                                <h6 class="fw-bold mb-0 text-uppercase text-muted small">DINERO RECAUDADO ($)</h6>
                            </div>
                            <div class="card-body d-flex justify-content-center align-items-center">
                                <div id="chartMediosDinero" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4 g-3">
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="card-title fw-bold mb-0">🏆 Top 5 Clientes VIP</h5>
                            </div>
                            <div class="card-body pt-0">
                                <div id="chartTopClientes" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="card-title fw-bold mb-0">📍 Top Ciudades</h5>
                            </div>
                            <div class="card-body">
                                <div id="chartTopCiudades" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                
                
                <div class="row mb-4 g-3">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="card-title fw-bold mb-0">🔥 Intensidad de Ventas (Día vs Hora)</h5>
                            </div>
                            <div class="card-body pt-0">
                                <div id="chartHeatmap" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="card-title fw-bold mb-0">📦 Preferencia de Compra</h5>
                            </div>
                            <div class="card-body pt-0">
                                <div id="chartPaquetes" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title fw-bold mb-0">🚀 Últimas Transacciones</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Código</th>
                                    <th>Cliente</th>
                                    <th>Rifa</th>
                                    <th>Total</th>
                                    <th class="text-end pe-4">Fecha</th>
                                </tr>
                            </thead>
                            <tbody id="tablaUltimasVentas">
                                <tr><td colspan="5" class="text-center py-4 text-muted">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<?php
$extra_js = '<script src="' . ASSETS_URL . '/js/dashboard.js?v=2"></script>';
include_once ROOT_PATH . "/includes/footer.php";
?>