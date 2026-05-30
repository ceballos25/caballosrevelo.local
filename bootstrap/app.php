<?php
declare(strict_types=1);


use App\Application\Analytics\DashboardDbService;
use App\Application\Marketing\MetaEventsService;
use App\Application\Reporting\ReportSchemaRegistry;
use App\Application\Reporting\SafeReportExecutor;
use App\Application\Reporting\SavedReportRepository;
use App\Application\Ticketing\TicketSalesService;
use App\Application\Settings\SettingsService;
use App\Application\Web\WebCheckoutService;
use App\Infrastructure\Repository\LegacySalesRepository;
use App\Shared\Audit\AuditLogger;
use App\Shared\Config\DynamicConfig;
use App\Presentation\Http\Controller\DashboardHttpController;
use App\Presentation\Http\Controller\MetaEventsController;
use App\Presentation\Http\Controller\ReportHttpController;
use App\Presentation\Http\Controller\SettingsHttpController;
use App\Presentation\Http\Controller\TicketSalesController;
use App\Presentation\Http\Controller\WebCheckoutController;
use App\Presentation\Http\Middleware\CsrfMiddleware;
use App\Presentation\Http\Middleware\RbacMiddleware;
use App\Shared\Routing\Router;


require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/ventas.controller.php';
require_once __DIR__ . '/../controllers/numeros.controller.php';
require_once __DIR__ . '/../controllers/settings.controller.php';
require_once __DIR__ . '/../controllers/paymentBackupsController.php';
require_once __DIR__ . '/../controllers/openpay.controller.php';
require_once __DIR__ . '/../controllers/transfersController.php';

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

$router = new Router();
$audit = new AuditLogger();
$rbac = new RbacMiddleware();
$config = new DynamicConfig();

$router->post('/front/ajax/ventas.ajax.php', new TicketSalesController(new TicketSalesService(new LegacySalesRepository()), new CsrfMiddleware(), $rbac, $audit));
$router->post('/front/ajax/settings.ajax.php', new SettingsHttpController(new SettingsService($config), $rbac, $audit));
$router->post('/front/ajax/web.ajax.php', new WebCheckoutController(new WebCheckoutService($config), $audit));
$router->post('/front/ajax/meta.ajax.php', new MetaEventsController(new MetaEventsService(), $audit));

$registry = new ReportSchemaRegistry();
$executor = new SafeReportExecutor($registry);
$savedReports = new SavedReportRepository();
$router->post('/front/ajax/dashboard.ajax.php', new DashboardHttpController(new DashboardDbService(), $rbac, $audit));
$router->post(
    '/front/ajax/reports.ajax.php',
    new ReportHttpController($registry, $executor, $savedReports, $rbac, $audit)
);

return $router;
