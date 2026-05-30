<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Reporting\ReportSchemaRegistry;
use App\Application\Reporting\SafeReportExecutor;
use App\Application\Reporting\SavedReportRepository;
use App\Presentation\Http\Middleware\RbacMiddleware;
use App\Shared\Audit\AuditLogger;
use App\Shared\Http\Request;
use App\Shared\Http\Response;
use Throwable;

final class ReportHttpController
{
    private const ROLE = [
        'schema' => ['admin', 'administrador', 'superadmin', 'vendedor'],
        'run' => ['admin', 'administrador', 'superadmin', 'vendedor'],
        'presets' => ['admin', 'administrador', 'superadmin', 'vendedor'],
        'saved_list' => ['admin', 'administrador', 'superadmin', 'vendedor'],
        'saved_get' => ['admin', 'administrador', 'superadmin', 'vendedor'],
        'saved_save' => ['admin', 'administrador', 'superadmin', 'vendedor'],
        'saved_delete' => ['admin', 'administrador', 'superadmin'],
    ];

    public function __construct(
        private readonly ReportSchemaRegistry $registry,
        private readonly SafeReportExecutor $executor,
        private readonly SavedReportRepository $saved,
        private readonly RbacMiddleware $rbac,
        private readonly AuditLogger $audit
    ) {
    }

    public function __invoke(Request $request): never
    {
        try {
            $action = trim((string)$request->input('action', ''));
            if (!isset(self::ROLE[$action])) {
                Response::json(['success' => false, 'message' => 'Accion invalida'], 422);
            }
            $this->rbac->authorize($action, self::ROLE);

            match ($action) {
                'schema' => $this->schema(),
                'presets' => $this->presets(),
                'run' => $this->run($request),
                'saved_list' => $this->savedList(),
                'saved_get' => $this->savedGet($request),
                'saved_save' => $this->savedSave($request),
                'saved_delete' => $this->savedDelete($request),
                default => Response::json(['success' => false, 'message' => 'Accion invalida'], 422),
            };
        } catch (Throwable $e) {
            $this->audit->log('reports.error', ['error' => $e->getMessage()]);
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function schema(): never
    {
        $out = [];
        foreach ($this->registry->datasets() as $key => $meta) {
            $out[] = [
                'key' => $key,
                'label' => $meta['label'],
                'fields' => $this->registry->fieldDescriptors($key),
                'date_column' => $meta['date_column'],
            ];
        }
        Response::json(['success' => true, 'datasets' => $out, 'aggregates' => $this->registry->allowedAggregates()]);
    }

    private function presets(): never
    {
        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');
        Response::json([
            'success' => true,
            'presets' => [
                [
                    'name' => 'Ventas por día y medio de pago (recaudo)',
                    'spec' => [
                        'dataset' => 'sales_detail',
                        'date_from' => $monthStart,
                        'date_to' => $today,
                        'dimensions' => [
                            ['field' => 'date_sale_day', 'alias' => 'dia'],
                            ['field' => 'payment_method_sale', 'alias' => 'medio_pago'],
                        ],
                        'measures' => [
                            ['fn' => 'SUM', 'field' => 'total_sale', 'alias' => 'total_recaudado'],
                            ['fn' => 'COUNT', 'field' => '*', 'alias' => 'num_ventas'],
                            ['fn' => 'SUM', 'field' => 'quantity_sale', 'alias' => 'tickets_vendidos'],
                        ],
                        'filters' => [],
                        'order_by' => 'total_recaudado',
                        'order_dir' => 'DESC',
                        'limit' => 5000,
                    ],
                ],
                [
                    'name' => 'Ventas por ciudad (tickets)',
                    'spec' => [
                        'dataset' => 'sales_detail',
                        'date_from' => $monthStart,
                        'date_to' => $today,
                        'dimensions' => [['field' => 'city_customer', 'alias' => 'ciudad']],
                        'measures' => [
                            ['fn' => 'SUM', 'field' => 'quantity_sale', 'alias' => 'tickets'],
                            ['fn' => 'SUM', 'field' => 'total_sale', 'alias' => 'total'],
                        ],
                        'filters' => [],
                        'order_by' => 'tickets',
                        'order_dir' => 'DESC',
                        'limit' => 500,
                    ],
                ],
                [
                    'name' => 'Tickets premium / ganadores por rifa',
                    'spec' => [
                        'dataset' => 'tickets_detail',
                        'dimensions' => [
                            ['field' => 'title_raffle', 'alias' => 'rifa'],
                            ['field' => 'is_premium_ticket', 'alias' => 'premium'],
                            ['field' => 'is_winner_ticket', 'alias' => 'ganador'],
                        ],
                        'measures' => [['fn' => 'COUNT', 'field' => '*', 'alias' => 'cantidad']],
                        'filters' => [],
                        'order_by' => 'cantidad',
                        'order_dir' => 'DESC',
                        'limit' => 2000,
                    ],
                ],
            ],
        ]);
    }

    private function run(Request $request): never
    {
        $raw = trim((string)$request->input('spec', ''));
        if ($raw === '') {
            Response::json(['success' => false, 'message' => 'Spec JSON requerido'], 422);
        }
        $spec = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($spec)) {
            Response::json(['success' => false, 'message' => 'Spec invalido'], 422);
        }
        $result = $this->executor->run($spec);
        $this->audit->log('reports.run', ['dataset' => $spec['dataset'] ?? '']);
        Response::json(['success' => true] + $result);
    }

    private function savedList(): never
    {
        Response::json(['success' => true, 'data' => $this->saved->listAll()]);
    }

    private function savedGet(Request $request): never
    {
        $id = (int)$request->input('id', 0);
        if ($id <= 0) {
            Response::json(['success' => false, 'message' => 'ID requerido'], 422);
        }
        $row = $this->saved->get($id);
        if ($row === null) {
            Response::json(['success' => false, 'message' => 'No encontrado'], 404);
        }
        $spec = json_decode((string)$row['spec_report'], true);
        if (!is_array($spec)) {
            Response::json(['success' => false, 'message' => 'Reporte corrupto en base de datos'], 500);
        }
        Response::json(['success' => true, 'name' => $row['name_report'], 'spec' => $spec]);
    }

    private function savedSave(Request $request): never
    {
        $name = trim((string)$request->input('name', ''));
        $specRaw = (string)$request->input('spec', '');
        if ($name === '' || $specRaw === '') {
            Response::json(['success' => false, 'message' => 'Nombre y spec requeridos'], 422);
        }
        json_decode($specRaw, true, 512, JSON_THROW_ON_ERROR);
        $adminId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $id = $this->saved->save($name, $specRaw, $adminId);
        $this->audit->log('reports.saved', ['id' => $id]);
        Response::json(['success' => true, 'id_saved_report' => $id]);
    }

    private function savedDelete(Request $request): never
    {
        $id = (int)$request->input('id', 0);
        if ($id <= 0) {
            Response::json(['success' => false, 'message' => 'ID requerido'], 422);
        }
        $ok = $this->saved->delete($id);
        Response::json(['success' => $ok]);
    }
}
