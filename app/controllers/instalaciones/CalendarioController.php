<?php
/**
 * DigiSports Arena — Controlador de Calendario Visual
 * Vista semanal interactiva de canchas con disponibilidad en tiempo real
 * 
 * @package DigiSports\Controllers\Instalaciones
 * @version 1.0.0
 */

namespace App\Controllers\Instalaciones;

require_once BASE_PATH . '/app/controllers/ModuleController.php';

class CalendarioController extends \App\Controllers\ModuleController {

    protected $moduloNombre = 'DigiSports Arena';
    protected $moduloIcono = 'fas fa-building';
    protected $moduloColor = '#3B82F6';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'ARENA';
    }

    /**
     * Vista principal del calendario semanal
     */
    public function index() {
        try {
            $fecha = $this->get('fecha') ?? date('Y-m-d');
            $instalacionId = (int)($this->get('instalacion_id') ?? 0);

            // Instalaciones del tenant
            $stmt = $this->db->prepare("
                SELECT ins_instalacion_id AS instalacion_id, ins_nombre AS nombre
                FROM instalaciones
                WHERE ins_tenant_id = ? AND ins_estado = 'ACTIVO'
                ORDER BY ins_nombre
            ");
            $stmt->execute([$this->tenantId]);
            $instalaciones = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Si no se especifica instalación, usar la primera
            if ($instalacionId < 1 && !empty($instalaciones)) {
                $instalacionId = $instalaciones[0]['instalacion_id'];
            }

            // Canchas de la instalación seleccionada
            $canchas = [];
            if ($instalacionId > 0) {
                $stmt = $this->db->prepare("
                    SELECT cancha_id, nombre, tipo, capacidad_maxima, estado
                    FROM canchas
                    WHERE instalacion_id = ? AND tenant_id = ? AND estado = 'ACTIVO'
                    ORDER BY nombre
                ");
                $stmt->execute([$instalacionId, $this->tenantId]);
                $canchas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }

            // Calcular semana
            $fechaObj = new \DateTime($fecha);
            $diaSemana = (int)$fechaObj->format('w'); // 0=dom
            $inicioSemana = (clone $fechaObj)->modify("-{$diaSemana} days");
            $diasSemana = [];
            for ($i = 0; $i < 7; $i++) {
                $d = (clone $inicioSemana)->modify("+{$i} days");
                $diasSemana[] = [
                    'fecha'   => $d->format('Y-m-d'),
                    'dia'     => $d->format('d'),
                    'nombre'  => ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'][$i],
                    'esHoy'   => $d->format('Y-m-d') === date('Y-m-d'),
                    'diaSem'  => $i
                ];
            }

            // Horarios (rango general)
            $horaInicio = 7;
            $horaFin = 23;
            $horarios = [];
            for ($h = $horaInicio; $h < $horaFin; $h++) {
                $horarios[] = sprintf('%02d:00', $h);
            }

            $this->viewData['fecha']          = $fecha;
            $this->viewData['instalacion_id'] = $instalacionId;
            $this->viewData['instalaciones']  = $instalaciones;
            $this->viewData['canchas']        = $canchas;
            $this->viewData['dias_semana']    = $diasSemana;
            $this->viewData['horarios']       = $horarios;
            $this->viewData['inicio_semana']  = $inicioSemana->format('Y-m-d');
            $this->viewData['fin_semana']     = (clone $inicioSemana)->modify('+6 days')->format('Y-m-d');
            $this->viewData['csrf_token']     = \Security::generateCsrfToken();
            $this->viewData['title']          = 'Calendario de Reservas';
            $this->viewData['layout']         = 'main';

            $this->renderModule('instalaciones/calendario/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error calendario: " . $e->getMessage());
            $this->error('Error al cargar el calendario');
        }
    }

    /**
     * API JSON — obtener eventos para una semana y canchas
     */
    public function eventos() {
        try {
            $inicio = $this->get('inicio') ?? date('Y-m-d');
            $fin    = $this->get('fin') ?? date('Y-m-d', strtotime('+6 days'));
            $instalacionId = (int)($this->get('instalacion_id') ?? 0);

            if ($instalacionId < 1) {
                $this->success(['reservas' => [], 'mantenimientos' => [], 'tarifas' => []]);
                return;
            }

            // Obtener canchas de la instalación
            $stmt = $this->db->prepare("
                SELECT cancha_id, instalacion_id, nombre
                FROM canchas
                WHERE instalacion_id = ? AND tenant_id = ? AND estado = 'ACTIVO'
            ");
            $stmt->execute([$instalacionId, $this->tenantId]);
            $canchas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($canchas)) {
                $this->success(['reservas' => [], 'mantenimientos' => [], 'tarifas' => []]);
                return;
            }

            $canchaIds = array_column($canchas, 'cancha_id');
            $instIds   = array_column($canchas, 'instalacion_id');
            $placeholders = implode(',', array_fill(0, count($canchaIds), '?'));

            // Reservas del rango
            $instPlaceholders = implode(',', array_fill(0, count($instIds), '?'));
            $stmt = $this->db->prepare("
                SELECT r.reserva_id, r.instalacion_id, r.fecha_reserva, 
                       r.hora_inicio, r.hora_fin, r.estado, r.total,
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) AS cliente_nombre
                FROM reservas r
                LEFT JOIN clientes c ON r.cliente_id = c.cli_cliente_id
                WHERE r.instalacion_id IN ({$instPlaceholders})
                  AND r.tenant_id = ?
                  AND r.fecha_reserva BETWEEN ? AND ?
                  AND r.estado IN ('CONFIRMADA','PENDIENTE')
                ORDER BY r.fecha_reserva, r.hora_inicio
            ");
            $params = array_merge($instIds, [$this->tenantId, $inicio, $fin]);
            $stmt->execute($params);
            $reservas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Mantenimientos del rango
            $stmt = $this->db->prepare("
                SELECT mantenimiento_id, cancha_id, tipo, descripcion,
                       fecha_inicio, fecha_fin, estado
                FROM mantenimientos
                WHERE cancha_id IN ({$placeholders})
                  AND tenant_id = ?
                  AND DATE(fecha_inicio) <= ?
                  AND DATE(fecha_fin) >= ?
                  AND estado IN ('PROGRAMADO','EN_PROGRESO')
            ");
            $params2 = array_merge($canchaIds, [$this->tenantId, $fin, $inicio]);
            $stmt->execute($params2);
            $mantenimientos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Tarifas de las canchas (para colorear disponibilidad)
            $stmt = $this->db->prepare("
                SELECT tarifa_id, cancha_id, dia_semana, hora_inicio, hora_fin, precio
                FROM tarifas
                WHERE cancha_id IN ({$placeholders}) AND estado = 'ACTIVO'
                ORDER BY cancha_id, dia_semana, hora_inicio
            ");
            $stmt->execute($canchaIds);
            $tarifas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Mapear instalacion_id -> cancha para las reservas
            $instToCancha = [];
            foreach ($canchas as $c) {
                $instToCancha[$c['instalacion_id']] = $c['cancha_id'];
            }

            // Agregar cancha_id a las reservas
            foreach ($reservas as &$r) {
                $r['cancha_id'] = $instToCancha[$r['instalacion_id']] ?? 0;
            }
            unset($r);

            $this->success([
                'reservas'       => $reservas,
                'mantenimientos' => $mantenimientos,
                'tarifas'        => $tarifas,
                'canchas'        => $canchas
            ]);

        } catch (\Exception $e) {
            $this->logError("Error eventos calendario: " . $e->getMessage());
            $this->error('Error al obtener eventos');
        }
    }
}
