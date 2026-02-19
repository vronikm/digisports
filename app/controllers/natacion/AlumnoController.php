<?php
/**
 * DigiSports Natación — Controlador de Alumnos
 * 
 * CRUD completo: tabla compartida `alumnos` + extensión `natacion_ficha_alumno`
 * con campos JSON personalizables por tenant. Usa DataProtection para cifrar
 * la identificación del alumno y ValidadorEcuador para cédulas.
 * 
 * @package DigiSports\Controllers\Natacion
 */

namespace App\Controllers\Natacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';
require_once BASE_PATH . '/app/helpers/ValidadorEcuador.php';

class AlumnoController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'NATACION';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'NATACION';
    }

    // ═══════════════════════════════════════
    // LISTADO
    // ═══════════════════════════════════════
    public function index() {
        try {
            $this->setupModule();
            $buscar   = trim($this->get('buscar') ?? '');
            $nivel    = $this->get('nivel') ?? '';
            $estado   = $this->get('estado') ?? '';
            $pagina   = max(1, (int)($this->get('pagina') ?? 1));
            $porPagina = 25;
            $sedeId   = $_SESSION['natacion_sede_id'] ?? null;

            $where = " WHERE a.alu_tenant_id = ? AND nf.nfa_tenant_id = ?";
            $params = [$this->tenantId, $this->tenantId];

            if ($sedeId) {
                $where .= " AND a.alu_sede_id = ?";
                $params[] = (int)$sedeId;
            }

            if (!empty($buscar)) {
                $idHash = \DataProtection::blindIndex($buscar);
                $where .= " AND (a.alu_nombres LIKE ? OR a.alu_apellidos LIKE ? OR a.alu_identificacion_hash = ?)";
                $like = "%{$buscar}%";
                $params = array_merge($params, [$like, $like, $idHash]);
            }
            if (!empty($nivel)) {
                $where .= " AND nf.nfa_nivel_actual_id = ?";
                $params[] = (int)$nivel;
            }
            if (!empty($estado)) {
                $where .= " AND a.alu_estado = ?";
                $params[] = $estado;
            }

            $baseSQL = "FROM alumnos a
                        JOIN natacion_ficha_alumno nf ON nf.nfa_alumno_id = a.alu_alumno_id AND nf.nfa_tenant_id = a.alu_tenant_id
                        LEFT JOIN natacion_niveles n ON nf.nfa_nivel_actual_id = n.nnv_nivel_id
                        LEFT JOIN clientes rep ON a.alu_representante_id = rep.cli_cliente_id";

            // Count
            $stm = $this->db->prepare("SELECT COUNT(*) {$baseSQL} {$where}");
            $stm->execute($params);
            $total = (int)$stm->fetchColumn();
            $totalPaginas = max(1, ceil($total / $porPagina));
            $offset = ($pagina - 1) * $porPagina;

            $sql = "SELECT a.*, nf.nfa_ficha_id, nf.nfa_nivel_actual_id, nf.nfa_sabe_nadar, nf.nfa_objetivo,
                           nf.nfa_autorizacion_medica, nf.nfa_fecha_ingreso, nf.nfa_activo AS nfa_activo,
                           n.nnv_nombre AS nivel_nombre, n.nnv_color AS nivel_color, n.nnv_codigo AS nivel_codigo,
                           rep.cli_nombres AS rep_nombres, rep.cli_apellidos AS rep_apellidos
                    {$baseSQL} {$where}
                    ORDER BY a.alu_apellidos, a.alu_nombres
                    LIMIT {$porPagina} OFFSET {$offset}";
            $stm = $this->db->prepare($sql);
            $stm->execute($params);
            $alumnos = \DataProtection::decryptRows('alumnos', $stm->fetchAll(\PDO::FETCH_ASSOC));

            // Niveles para filtro
            $stm = $this->db->prepare("SELECT nnv_nivel_id, nnv_nombre, nnv_color FROM natacion_niveles WHERE nnv_tenant_id = ? AND nnv_activo = 1 ORDER BY nnv_orden");
            $stm->execute([$this->tenantId]);
            $niveles = $stm->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['alumnos']       = $alumnos;
            $this->viewData['niveles']       = $niveles;
            $this->viewData['buscar']        = $buscar;
            $this->viewData['nivelFiltro']   = $nivel;
            $this->viewData['estadoFiltro']  = $estado;
            $this->viewData['pagina']        = $pagina;
            $this->viewData['totalPaginas']  = $totalPaginas;
            $this->viewData['total']         = $total;
            $this->viewData['sede_activa']   = $sedeId;
            $this->viewData['csrf_token']    = \Security::generateCsrfToken();
            $this->viewData['title']         = 'Alumnos';

            $this->renderModule('natacion/alumnos/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando alumnos: " . $e->getMessage());
            $this->error('Error al cargar alumnos');
        }
    }

    // ═══════════════════════════════════════
    // FORMULARIO CREAR (GET) / GUARDAR (POST)
    // ═══════════════════════════════════════
    public function crear() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $this->setupModule();
                $this->viewData['csrf_token'] = \Security::generateCsrfToken();
                $this->viewData['title']      = 'Nuevo Alumno';
                $this->viewData['niveles']    = $this->getNivelesActivos();
                $this->viewData['campos_custom'] = $this->getCamposCustom();
                $this->viewData['clientes']   = $this->getClientesBusqueda();
                $this->viewData['sedes']      = $this->getSedesActivas();
                $this->viewData['sede_activa'] = $_SESSION['natacion_sede_id'] ?? null;
                return $this->renderModule('natacion/alumnos/formulario', $this->viewData);
            }

            if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            // Validar datos obligatorios
            $nombres = trim($this->post('nombres') ?? '');
            $apellidos = trim($this->post('apellidos') ?? '');
            if (empty($nombres) || empty($apellidos)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Nombres y apellidos son obligatorios']);
            }

            // Validar identificación
            $identificacion = trim($this->post('identificacion') ?? '');
            $tipoId = $this->post('tipo_identificacion') ?? 'CED';
            if (!empty($identificacion)) {
                $validacion = \ValidadorEcuador::validar($identificacion, $tipoId);
                if (!$validacion['valido']) {
                    return $this->jsonResponse(['success' => false, 'message' => $validacion['mensaje']]);
                }
                // Verificar duplicado por blind index
                $hash = \DataProtection::blindIndex($identificacion);
                $stm = $this->db->prepare("SELECT alu_alumno_id FROM alumnos WHERE alu_identificacion_hash = ? AND alu_tenant_id = ?");
                $stm->execute([$hash, $this->tenantId]);
                if ($stm->fetchColumn()) {
                    return $this->jsonResponse(['success' => false, 'message' => 'Ya existe un alumno con esa identificación']);
                }
            }

            $this->db->beginTransaction();

            // 1. Crear en tabla compartida alumnos
            $protectedData = ['alu_identificacion' => $identificacion ?: null];
            $encrypted = \DataProtection::encryptRow('alumnos', $protectedData);

            $stm = $this->db->prepare("INSERT INTO alumnos (
                alu_tenant_id, alu_sede_id, alu_representante_id, alu_parentesco,
                alu_tipo_identificacion, alu_identificacion, alu_identificacion_hash,
                alu_nombres, alu_apellidos, alu_fecha_nacimiento, alu_genero,
                alu_tipo_sangre, alu_alergias, alu_condiciones_medicas, alu_medicamentos,
                alu_contacto_emergencia, alu_telefono_emergencia, alu_estado, alu_notas
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

            $sedeIdAlumno = (int)($this->post('sede_id') ?? 0) ?: ($_SESSION['natacion_sede_id'] ?? null);
            $stm->execute([
                $this->tenantId,
                $sedeIdAlumno,
                $this->post('representante_id') ?: null,
                $this->post('parentesco') ?: 'PADRE',
                $tipoId,
                $encrypted['alu_identificacion'],
                $encrypted['alu_identificacion_hash'] ?? null,
                $nombres,
                $apellidos,
                $this->post('fecha_nacimiento') ?: null,
                $this->post('genero') ?: null,
                $this->post('tipo_sangre') ?: null,
                $this->post('alergias') ?: null,
                $this->post('condiciones_medicas') ?: null,
                $this->post('medicamentos') ?: null,
                $this->post('contacto_emergencia') ?: null,
                $this->post('telefono_emergencia') ?: null,
                'ACTIVO',
                $this->post('notas') ?: null,
            ]);
            $alumnoId = (int)$this->db->lastInsertId();

            // 2. Crear ficha natación (extensión)
            $datosCustom = $this->procesarDatosCustom();

            $stm = $this->db->prepare("INSERT INTO natacion_ficha_alumno (
                nfa_tenant_id, nfa_alumno_id, nfa_nivel_actual_id, nfa_sabe_nadar,
                nfa_experiencia_previa, nfa_objetivo, nfa_autorizacion_medica,
                nfa_seguro_medico, nfa_fecha_ingreso, nfa_datos_custom, nfa_notas
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?)");

            $stm->execute([
                $this->tenantId,
                $alumnoId,
                $this->post('nivel_id') ?: null,
                (int)($this->post('sabe_nadar') ?? 0),
                $this->post('experiencia_previa') ?: null,
                $this->post('objetivo') ?: 'RECREATIVO',
                (int)($this->post('autorizacion_medica') ?? 0),
                $this->post('seguro_medico') ?: null,
                date('Y-m-d'),
                !empty($datosCustom) ? json_encode($datosCustom) : null,
                $this->post('notas_natacion') ?: null,
            ]);

            $this->db->commit();
            return $this->jsonResponse(['success' => true, 'message' => 'Alumno registrado exitosamente', 'id' => $alumnoId]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error creando alumno: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al registrar alumno']);
        }
    }

    // ═══════════════════════════════════════
    // VER FICHA
    // ═══════════════════════════════════════
    public function ver() {
        try {
            $this->setupModule();
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->error('ID requerido');

            $stm = $this->db->prepare("
                SELECT a.*, nf.*,
                       n.nnv_nombre AS nivel_nombre, n.nnv_color AS nivel_color,
                       rep.cli_nombres AS rep_nombres, rep.cli_apellidos AS rep_apellidos,
                       rep.cli_telefono AS rep_telefono, rep.cli_email AS rep_email
                FROM alumnos a
                JOIN natacion_ficha_alumno nf ON nf.nfa_alumno_id = a.alu_alumno_id AND nf.nfa_tenant_id = a.alu_tenant_id
                LEFT JOIN natacion_niveles n ON nf.nfa_nivel_actual_id = n.nnv_nivel_id
                LEFT JOIN clientes rep ON a.alu_representante_id = rep.cli_cliente_id
                WHERE a.alu_alumno_id = ? AND a.alu_tenant_id = ?
            ");
            $stm->execute([$id, $this->tenantId]);
            $alumno = $stm->fetch(\PDO::FETCH_ASSOC);
            if (!$alumno) return $this->error('Alumno no encontrado');

            $alumno = \DataProtection::decryptRow('alumnos', $alumno);
            if (!empty($alumno['rep_email'])) {
                $row = \DataProtection::decryptRow('clientes', ['cli_email' => $alumno['rep_email'], 'cli_telefono' => $alumno['rep_telefono'] ?? '']);
                $alumno['rep_email'] = $row['cli_email'];
                $alumno['rep_telefono'] = $row['cli_telefono'];
            }

            // Inscripciones
            $stm = $this->db->prepare("
                SELECT i.*, g.ngr_nombre AS grupo, g.ngr_color,
                       CONCAT(ins.nin_nombres, ' ', ins.nin_apellidos) AS instructor
                FROM natacion_inscripciones i
                JOIN natacion_grupos g ON i.nis_grupo_id = g.ngr_grupo_id
                LEFT JOIN natacion_instructores ins ON g.ngr_instructor_id = ins.nin_instructor_id
                WHERE i.nis_alumno_id = ? AND i.nis_tenant_id = ?
                ORDER BY i.nis_fecha_inscripcion DESC
            ");
            $stm->execute([$id, $this->tenantId]);

            // Evaluaciones
            $stm2 = $this->db->prepare("
                SELECT e.*, h.nnh_nombre AS habilidad, n.nnv_nombre AS nivel_evaluado
                FROM natacion_evaluaciones e
                JOIN natacion_nivel_habilidades h ON e.nev_habilidad_id = h.nnh_habilidad_id
                JOIN natacion_niveles n ON e.nev_nivel_id = n.nnv_nivel_id
                WHERE e.nev_alumno_id = ? AND e.nev_tenant_id = ?
                ORDER BY e.nev_fecha DESC LIMIT 20
            ");
            $stm2->execute([$id, $this->tenantId]);

            // Asistencia resumen
            $stm3 = $this->db->prepare("
                SELECT nas_estado, COUNT(*) AS total
                FROM natacion_asistencia WHERE nas_alumno_id = ? AND nas_tenant_id = ?
                GROUP BY nas_estado
            ");
            $stm3->execute([$id, $this->tenantId]);

            $this->viewData['alumno']        = $alumno;
            $this->viewData['inscripciones'] = $stm->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['evaluaciones']  = $stm2->fetchAll(\PDO::FETCH_ASSOC);
            $this->viewData['asistencia']    = $stm3->fetchAll(\PDO::FETCH_KEY_PAIR);
            $this->viewData['campos_custom'] = $this->getCamposCustom();
            $this->viewData['csrf_token']    = \Security::generateCsrfToken();
            $this->viewData['title']         = $alumno['alu_nombres'] . ' ' . $alumno['alu_apellidos'];

            $this->renderModule('natacion/alumnos/ver', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error viendo alumno: " . $e->getMessage());
            $this->error('Error al cargar ficha del alumno');
        }
    }

    // ═══════════════════════════════════════
    // EDITAR
    // ═══════════════════════════════════════
    public function editar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $this->setupModule();
                $id = (int)($this->get('id') ?? 0);
                if (!$id) return $this->error('ID requerido');

                $stm = $this->db->prepare("
                    SELECT a.*, nf.* FROM alumnos a
                    JOIN natacion_ficha_alumno nf ON nf.nfa_alumno_id = a.alu_alumno_id AND nf.nfa_tenant_id = a.alu_tenant_id
                    WHERE a.alu_alumno_id = ? AND a.alu_tenant_id = ?
                ");
                $stm->execute([$id, $this->tenantId]);
                $alumno = $stm->fetch(\PDO::FETCH_ASSOC);
                if (!$alumno) return $this->error('Alumno no encontrado');

                $alumno = \DataProtection::decryptRow('alumnos', $alumno);

                $this->viewData['alumno']        = $alumno;
                $this->viewData['niveles']       = $this->getNivelesActivos();
                $this->viewData['campos_custom'] = $this->getCamposCustom();
                $this->viewData['clientes']      = $this->getClientesBusqueda();
                $this->viewData['sedes']         = $this->getSedesActivas();
                $this->viewData['sede_activa']   = $_SESSION['natacion_sede_id'] ?? null;
                $this->viewData['csrf_token']    = \Security::generateCsrfToken();
                $this->viewData['title']         = 'Editar Alumno';
                return $this->renderModule('natacion/alumnos/formulario', $this->viewData);
            }

            if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            $id = (int)($this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $nombres   = trim($this->post('nombres') ?? '');
            $apellidos = trim($this->post('apellidos') ?? '');
            if (empty($nombres) || empty($apellidos)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Nombres y apellidos son obligatorios']);
            }

            $identificacion = trim($this->post('identificacion') ?? '');
            $tipoId = $this->post('tipo_identificacion') ?? 'CED';
            if (!empty($identificacion)) {
                $validacion = \ValidadorEcuador::validar($identificacion, $tipoId);
                if (!$validacion['valido']) {
                    return $this->jsonResponse(['success' => false, 'message' => $validacion['mensaje']]);
                }
                // Verificar duplicado (excluyendo al propio)
                $hash = \DataProtection::blindIndex($identificacion);
                $stm = $this->db->prepare("SELECT alu_alumno_id FROM alumnos WHERE alu_identificacion_hash = ? AND alu_tenant_id = ? AND alu_alumno_id != ?");
                $stm->execute([$hash, $this->tenantId, $id]);
                if ($stm->fetchColumn()) {
                    return $this->jsonResponse(['success' => false, 'message' => 'Ya existe otro alumno con esa identificación']);
                }
            }

            $this->db->beginTransaction();

            // Actualizar tabla compartida alumnos
            $protectedData = ['alu_identificacion' => $identificacion ?: null];
            $encrypted = \DataProtection::encryptRow('alumnos', $protectedData);

            $stm = $this->db->prepare("UPDATE alumnos SET
                alu_sede_id = ?, alu_representante_id = ?, alu_parentesco = ?,
                alu_tipo_identificacion = ?, alu_identificacion = ?, alu_identificacion_hash = ?,
                alu_nombres = ?, alu_apellidos = ?, alu_fecha_nacimiento = ?, alu_genero = ?,
                alu_tipo_sangre = ?, alu_alergias = ?, alu_condiciones_medicas = ?, alu_medicamentos = ?,
                alu_contacto_emergencia = ?, alu_telefono_emergencia = ?, alu_estado = ?, alu_notas = ?
                WHERE alu_alumno_id = ? AND alu_tenant_id = ?");

            $stm->execute([
                (int)($this->post('sede_id') ?? 0) ?: null,
                $this->post('representante_id') ?: null,
                $this->post('parentesco') ?: 'PADRE',
                $tipoId,
                $encrypted['alu_identificacion'],
                $encrypted['alu_identificacion_hash'] ?? null,
                $nombres, $apellidos,
                $this->post('fecha_nacimiento') ?: null,
                $this->post('genero') ?: null,
                $this->post('tipo_sangre') ?: null,
                $this->post('alergias') ?: null,
                $this->post('condiciones_medicas') ?: null,
                $this->post('medicamentos') ?: null,
                $this->post('contacto_emergencia') ?: null,
                $this->post('telefono_emergencia') ?: null,
                $this->post('estado') ?? 'ACTIVO',
                $this->post('notas') ?: null,
                $id, $this->tenantId,
            ]);

            // Actualizar ficha natación
            $datosCustom = $this->procesarDatosCustom();

            $stm = $this->db->prepare("UPDATE natacion_ficha_alumno SET
                nfa_nivel_actual_id = ?, nfa_sabe_nadar = ?, nfa_experiencia_previa = ?,
                nfa_objetivo = ?, nfa_autorizacion_medica = ?, nfa_seguro_medico = ?,
                nfa_datos_custom = ?, nfa_notas = ?
                WHERE nfa_alumno_id = ? AND nfa_tenant_id = ?");

            $stm->execute([
                $this->post('nivel_id') ?: null,
                (int)($this->post('sabe_nadar') ?? 0),
                $this->post('experiencia_previa') ?: null,
                $this->post('objetivo') ?: 'RECREATIVO',
                (int)($this->post('autorizacion_medica') ?? 0),
                $this->post('seguro_medico') ?: null,
                !empty($datosCustom) ? json_encode($datosCustom) : null,
                $this->post('notas_natacion') ?: null,
                $id, $this->tenantId,
            ]);

            $this->db->commit();
            return $this->jsonResponse(['success' => true, 'message' => 'Alumno actualizado exitosamente']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error editando alumno: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar alumno']);
        }
    }

    // ═══════════════════════════════════════
    // ELIMINAR (Soft)
    // ═══════════════════════════════════════
    public function eliminar() {
        try {
            $id = (int)($this->get('id') ?? $this->post('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->beginTransaction();
            $this->db->prepare("UPDATE alumnos SET alu_estado = 'INACTIVO' WHERE alu_alumno_id = ? AND alu_tenant_id = ?")->execute([$id, $this->tenantId]);
            $this->db->prepare("UPDATE natacion_ficha_alumno SET nfa_activo = 0 WHERE nfa_alumno_id = ? AND nfa_tenant_id = ?")->execute([$id, $this->tenantId]);
            $this->db->commit();

            return $this->jsonResponse(['success' => true, 'message' => 'Alumno desactivado']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error eliminando alumno: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar alumno']);
        }
    }

    // ═══════════════════════════════════════
    // API: Buscar representante (AJAX)
    // ═══════════════════════════════════════
    public function buscarRepresentante() {
        $q = trim($this->get('q') ?? '');
        if (strlen($q) < 2) return $this->jsonResponse(['results' => []]);

        $idHash = \DataProtection::blindIndex($q);
        $stm = $this->db->prepare("
            SELECT cli_cliente_id AS id, cli_nombres, cli_apellidos, cli_identificacion, cli_telefono
            FROM clientes
            WHERE cli_tenant_id = ? AND cli_estado = 'A'
              AND (cli_nombres LIKE ? OR cli_apellidos LIKE ? OR cli_identificacion_hash = ?)
            LIMIT 10
        ");
        $stm->execute([$this->tenantId, "%{$q}%", "%{$q}%", $idHash]);
        $rows = \DataProtection::decryptRows('clientes', $stm->fetchAll(\PDO::FETCH_ASSOC));

        $results = [];
        foreach ($rows as $r) {
            $results[] = [
                'id'   => $r['id'],
                'text' => $r['cli_nombres'] . ' ' . $r['cli_apellidos'] . ($r['cli_identificacion'] ? ' — ' . $r['cli_identificacion'] : ''),
            ];
        }
        return $this->jsonResponse(['results' => $results]);
    }

    // ═══════════════════════════════════════
    // HELPERS PRIVADOS
    // ═══════════════════════════════════════

    private function getNivelesActivos() {
        $stm = $this->db->prepare("SELECT nnv_nivel_id, nnv_nombre, nnv_codigo, nnv_color FROM natacion_niveles WHERE nnv_tenant_id = ? AND nnv_activo = 1 ORDER BY nnv_orden");
        $stm->execute([$this->tenantId]);
        return $stm->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getCamposCustom() {
        $stm = $this->db->prepare("SELECT * FROM natacion_campos_ficha WHERE ncf_tenant_id = ? AND ncf_activo = 1 ORDER BY ncf_grupo, ncf_orden");
        $stm->execute([$this->tenantId]);
        return $stm->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getClientesBusqueda() {
        $stm = $this->db->prepare("SELECT cli_cliente_id, cli_nombres, cli_apellidos FROM clientes WHERE cli_tenant_id = ? AND cli_estado = 'A' ORDER BY cli_nombres LIMIT 100");
        $stm->execute([$this->tenantId]);
        return $stm->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getSedesActivas() {
        $stm = $this->db->prepare("SELECT sed_sede_id, sed_nombre, sed_ciudad FROM instalaciones_sedes WHERE sed_tenant_id = ? AND sed_estado = 'A' ORDER BY sed_es_principal DESC, sed_nombre");
        $stm->execute([$this->tenantId]);
        return $stm->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Procesar campos custom del POST → array para JSON
     */
    private function procesarDatosCustom(): array {
        $custom = [];
        $campos = $this->getCamposCustom();
        foreach ($campos as $campo) {
            $key = $campo['ncf_clave'];
            $valor = $this->post("custom_{$key}");
            if ($valor !== null && $valor !== '') {
                $custom[$key] = $valor;
            }
        }
        return $custom;
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
