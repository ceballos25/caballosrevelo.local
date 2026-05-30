<?php
require_once "../config/config.php";
$page_title = "Configuración del Sistema";
$extra_css = '
<style>
  .settings-main-card { border-radius: 12px; overflow: hidden; }
  .settings-list .settings-row { padding: 1rem 1.25rem; border-bottom: 1px solid var(--bs-border-color, #dee2e6); }
  .settings-list .settings-row:last-child { border-bottom: none; }
  .settings-list .settings-title { font-size: 0.95rem; font-weight: 600; }
  .settings-list .settings-help { font-size: 0.8rem; color: #6c757d; max-width: 42rem; }
  .settings-list code { font-size: 0.78rem; }
  .settings-list .form-switch .form-check-input { width: 2.75em; height: 1.35rem; cursor: pointer; }
  .settings-list .form-switch .form-check-label { cursor: pointer; user-select: none; padding-top: 0.15rem; }
</style>';
include_once ROOT_PATH . "/includes/head.php";
?>

<div class="page-wrapper" id="main-wrapper" data-layout="vertical">

    <?php include_once ROOT_PATH . "/includes/sidebar.php" ?>

    <div class="body-wrapper">
        <?php include_once ROOT_PATH . "/includes/header.php" ?>

        <div class="body-wrapper-inner">
            <div class="container-fluid py-3">

                <div class="row mb-4 align-items-center flex-wrap gap-3">
                    <div class="col">
                        <h2 class="mb-1 fw-bold d-flex align-items-center gap-2">
                            <i class="ti ti-settings text-primary"></i> Configuración del sistema
                        </h2>
                        <p class="text-muted small mb-0">
                            Ajustes que afectan la web pública y el negocio. Los cambios se aplican al guardar; la caché de opciones se actualiza sola.
                        </p>
                    </div>
                    <div class="col-auto d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-success" onclick="guardarSettings()">
                            <i class="ti ti-device-floppy me-1"></i> Guardar todo
                        </button>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4 settings-main-card">
                    <div class="card-header bg-white border-bottom py-3">
                        <span class="fw-bold"><i class="ti ti-adjustments me-1 text-primary"></i> Opciones</span>
                        <span class="text-muted small ms-2">Interruptores para encender/apagar; el resto son campos de texto.</span>
                    </div>
                    <div class="card-body p-0">
                        <div id="settingsContainer" class="settings-list">
                            <div class="text-center py-5 text-muted">
                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                <p class="small mt-2 mb-0">Cargando configuración...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-1">
                            <i class="ti ti-plus me-1 text-primary"></i> Nueva clave
                        </h5>
                        <p class="text-muted small mb-3">
                            Solo si necesitas un parámetro técnico nuevo. Usa minúsculas y guiones bajos (ej. <code>mi_clave</code>). Para compras web ya existe <strong>web_compras_habilitadas</strong>.
                        </p>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">Nombre interno (key)</label>
                                <input type="text" id="newKey" class="form-control form-control-sm"
                                    placeholder="ej: mi_parametro">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small text-muted mb-1">Valor inicial</label>
                                <input type="text" id="newValue" class="form-control form-control-sm"
                                    placeholder="texto o número">
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-primary w-100" onclick="crearSetting()">
                                    <i class="ti ti-plus me-1"></i> Crear clave
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
$extra_js = '<script src="' . ASSETS_URL . '/js/settings.js?v=10"></script>';
include_once ROOT_PATH . "/includes/footer.php";
?>