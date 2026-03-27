<?php
/**
 * DigiSports Fútbol — Controlador de Alumnos
 * Gestión de datos de alumnos con ficha deportiva de fútbol
 * 
 * @package DigiSports\Controllers\Futbol
 */

namespace App\Controllers\Futbol;

require_once BASE_PATH . '/app/controllers/ModuleController.php';
require_once BASE_PATH . '/app/services/FileManager.php';

class AlumnoController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'FUTBOL';

    public function __construct() { parent::__construct(); $this->moduloCodigo = 'FUTBOL'; }

    /**
     * Listar alumnos con ficha de fútbol
     */
    public function index() {
        try {
            $this->setupModule();
            $sedeId = $_SESSION['futbol_sede_id'] ?? null;

            $where = 'a.alu_tenant_id = ?';
            $params = [$this->tenantId];

            if ($sedeId) { $where .= ' AND a.alu_sede_id = ?'; $params[] = (int)$sedeId; }

            // Filtro por búsqueda de texto
            $q = trim($this->post('q') ?? $this->get('q') ?? '');
            if ($q !== '') { $like = "%{$q}%"; $where .= ' AND (a.alu_nombres LIKE ? OR a.alu_apellidos LIKE ? OR a.alu_identificacion_hash = ?)'; $params[] = $like; $params[] = $like; $params[] = \DataProtection::blindIndex($q); }

            // Filtro por categoría
            $categoriaId = $this->post('categoria_id') ?? $this->get('categoria_id');
            if ($categoriaId) { $where .= ' AND ffa.ffa_categoria_id = ?'; $params[] = (int)$categoriaId; }

            // Filtro por grupo
            $grupoId = $this->post('grupo_id') ?? $this->get('grupo_id');
            if ($grupoId) { $where .= ' AND fin.fin_grupo_id = ?'; $params[] = (int)$grupoId; }

            // Filtro por estado
            $estado = $this->post('estado') ?? $this->get('estado');
            if ($estado) { $where .= ' AND a.alu_estado = ?'; $params[] = $estado; }

            $stm = $this->db->prepare("
                SELECT a.*, ffa.*,
                       fct.fct_nombre AS categoria_nombre,
                       fct.fct_edad_min, fct.fct_edad_max, fct.fct_color AS categoria_color,
                       s.sed_nombre AS sede_nombre,
                       fg.fgr_nombre AS grupo_nombre, fg.fgr_color AS grupo_color,
                       fin.fin_estado AS estado_inscripcion,
                       arc.arc_id AS foto_arc_id
                FROM alumnos a
                LEFT JOIN futbol_ficha_alumno ffa ON ffa.ffa_alumno_id = a.alu_alumno_id AND ffa.ffa_tenant_id = a.alu_tenant_id
                LEFT JOIN futbol_categorias fct ON ffa.ffa_categoria_id = fct.fct_categoria_id
                LEFT JOIN instalaciones_sedes s ON a.alu_sede_id = s.sed_sede_id
                LEFT JOIN futbol_inscripciones fin ON fin.fin_alumno_id = a.alu_alumno_id AND fin.fin_tenant_id = a.alu_tenant_id AND fin.fin_estado = 'ACTIVA'
                LEFT JOIN futbol_grupos fg ON fin.fin_grupo_id = fg.fgr_grupo_id AND fg.fgr_tenant_id = a.alu_tenant_id
                LEFT JOIN core_archivos arc ON arc.arc_entidad = 'alumnos'
                       AND arc.arc_entidad_id = a.alu_alumno_id
                       AND arc.arc_tenant_id  = a.alu_tenant_id
                       AND arc.arc_categoria  = 'fotos'
                       AND arc.arc_es_principal = 1
                       AND arc.arc_estado = 'activo'
                WHERE {$where}
                ORDER BY a.alu_apellidos, a.alu_nombres
                LIMIT 300
            ");
            $stm->execute($params);
            $this->viewData['alumnos'] = \DataProtection::decryptRows('alumnos', $stm->fetchAll(\PDO::FETCH_ASSOC));

            // Categorías para filtro/formulario
            $stmCat = $this->db->prepare("SELECT fct_categoria_id, fct_nombre, fct_edad_min, fct_edad_max, fct_color FROM futbol_categorias WHERE fct_tenant_id = ? AND fct_activo = 1 ORDER BY fct_orden");
            $stmCat->execute([$this->tenantId]);
            $this->viewData['categorias'] = $stmCat->fetchAll(\PDO::FETCH_ASSOC);

            // Sedes para filtro
            $stmSedes = $this->db->prepare("SELECT sed_sede_id, sed_nombre FROM instalaciones_sedes WHERE sed_tenant_id = ? AND sed_estado = 'A' ORDER BY sed_nombre");
            $stmSedes->execute([$this->tenantId]);
            $this->viewData['sedes'] = $stmSedes->fetchAll(\PDO::FETCH_ASSOC);

            // Grupos para filtro
            $sedeSQL3 = $sedeId ? ' AND fgr_sede_id = ?' : '';
            $stmGrupos = $this->db->prepare("SELECT fgr_grupo_id, fgr_nombre FROM futbol_grupos WHERE fgr_tenant_id = ? AND fgr_estado IN ('ABIERTO','EN_CURSO'){$sedeSQL3} ORDER BY fgr_nombre");
            $paramsGrupos = [$this->tenantId];
            if ($sedeId) $paramsGrupos[] = (int)$sedeId;
            $stmGrupos->execute($paramsGrupos);
            $this->viewData['grupos'] = $stmGrupos->fetchAll(\PDO::FETCH_ASSOC);

            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Alumnos';
            $this->viewData['sede_activa'] = $sedeId;
            $this->viewData['q'] = $q;
            $this->viewData['categoria_id'] = $categoriaId;
            $this->viewData['grupo_id'] = $grupoId;
            $this->viewData['estado'] = $estado;
            $this->renderModule('futbol/alumnos/index', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error listando alumnos: " . $e->getMessage());
            $this->error('Error al cargar alumnos');
        }
    }

    /**
     * Crear alumno con ficha de fútbol
     * GET: muestra formulario | POST: guarda vía AJAX
     */
    public function crear() {
        try {
            // GET: mostrar formulario vacío
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->setupModule();
                $this->viewData['alumno'] = [];
                $this->viewData['ficha'] = [];
                $this->viewData['representante'] = [];
                $this->viewData['hermanos'] = [];
                $this->viewData['categorias'] = $this->getCategoriasActivas();
                $this->viewData['sedes'] = $this->getSedesActivas();
                $this->viewData['campos_ficha'] = $this->getCamposActivos();
                $this->viewData['csrf_token'] = \Security::generateCsrfToken();
                $this->viewData['title'] = 'Nuevo Alumno';
                $this->viewData['sede_activa'] = $_SESSION['futbol_sede_id'] ?? null;
                $this->renderModule('futbol/alumnos/formulario', $this->viewData);
                return;
            }

            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $nombres = trim($this->post('nombres') ?? '');
            $apellidos = trim($this->post('apellidos') ?? '');
            if (empty($nombres) || empty($apellidos)) return $this->jsonResponse(['success' => false, 'message' => 'Nombres y apellidos son obligatorios']);

            $representanteId = (int)($this->post('representante_id') ?? 0) ?: null;
            $parentesco = $this->post('parentesco') ?: null;
            if (!$representanteId) return $this->jsonResponse(['success' => false, 'message' => 'Debe seleccionar un representante']);

            $sedeId = (int)($this->post('sede_id') ?? 0) ?: ($_SESSION['futbol_sede_id'] ?? null);

            $this->db->beginTransaction();

            // Registrar consentimiento si se marcó
            if ($this->post('consentimiento_datos')) {
                $this->registrarConsentimiento($representanteId);
            }

            $tipoId = $this->post('tipo_identificacion') === 'PAS' ? 'PAS' : 'CED';

            // Cifrar identificación del alumno (LOPDP Ecuador)
            $identPlain = $this->post('identificacion') ?: null;
            $identEnc   = \DataProtection::encryptRow('alumnos', ['alu_identificacion' => $identPlain]);

            // Insertar en tabla alumnos (usando FK a clientes)
            $stm = $this->db->prepare("
                INSERT INTO alumnos (alu_tenant_id, alu_sede_id, alu_nombres, alu_apellidos,
                    alu_tipo_identificacion, alu_identificacion, alu_identificacion_hash, alu_fecha_nacimiento,
                    alu_genero, alu_tipo_sangre, alu_alergias, alu_condiciones_medicas, alu_medicamentos,
                    alu_contacto_emergencia, alu_telefono_emergencia, alu_observaciones_medicas,
                    alu_representante_id, alu_parentesco, alu_notas, alu_estado)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ");
            $stm->execute([
                $this->tenantId,
                $sedeId,
                $nombres,
                $apellidos,
                $tipoId,
                $identEnc['alu_identificacion'],
                $identEnc['alu_identificacion_hash'] ?? null,
                $this->post('fecha_nacimiento') ?: null,
                $this->post('genero') ?: null,
                $this->post('tipo_sangre') ?: null,
                $this->post('alergias') ?: null,
                $this->post('condiciones_medicas') ?: null,
                $this->post('medicamentos') ?: null,
                $this->post('contacto_emergencia') ?: null,
                $this->post('telefono_emergencia') ?: null,
                $this->post('observaciones_medicas') ?: null,
                $representanteId,
                $parentesco,
                $this->post('notas') ?: null,
                'ACTIVO',
            ]);
            $alumnoId = $this->db->lastInsertId();

            // Insertar ficha de fútbol
            $objetivo = $this->post('objetivo') ?: 'RECREATIVO';
            if (!in_array($objetivo, ['RECREATIVO', 'FORMATIVO', 'COMPETITIVO'])) $objetivo = 'RECREATIVO';

            // Recopilar campos custom
            $camposCustom = [];
            foreach ($_POST['campo_custom'] ?? [] as $k => $v) {
                $safeKey = preg_replace('/[^a-zA-Z0-9_]/', '', $k);
                if ($safeKey) $camposCustom[$safeKey] = is_string($v) ? substr(trim($v), 0, 500) : '';
            }
            $datosCustomJson = !empty($camposCustom) ? json_encode($camposCustom, JSON_UNESCAPED_UNICODE) : null;

            $stm2 = $this->db->prepare("
                INSERT INTO futbol_ficha_alumno (ffa_tenant_id, ffa_alumno_id, ffa_categoria_id, ffa_posicion_preferida,
                    ffa_pie_dominante, ffa_experiencia_previa, ffa_club_anterior, ffa_objetivo, ffa_talla_camiseta,
                    ffa_numero_camiseta, ffa_autorizacion_medica, ffa_datos_custom, ffa_fecha_ingreso, ffa_activo)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,CURDATE(),1)
            ");
            $stm2->execute([
                $this->tenantId,
                $alumnoId,
                (int)($this->post('categoria_id') ?? 0) ?: null,
                $this->post('posicion_preferida') ?: null,
                $this->post('pie_dominante') ?: null,
                $this->post('experiencia_previa') ?: null,
                $this->post('club_anterior') ?: null,
                $objetivo,
                $this->post('talla_camiseta') ?: null,
                (int)($this->post('numero_camiseta') ?? 0) ?: null,
                (int)($this->post('autorizacion_medica') ?? 0),
                $datosCustomJson,
            ]);

            $this->db->commit();

            return $this->jsonResponse(['success' => true, 'message' => 'Alumno creado correctamente', 'alumno_id' => $alumnoId]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error creando alumno: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al crear alumno']);
        }
    }

    /**
     * Editar alumno y ficha de fútbol
     * GET: muestra formulario precargado | POST: actualiza vía AJAX
     */
    public function editar() {
        try {
            // GET: mostrar formulario con datos existentes
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->setupModule();
                $id = (int)($this->get('id') ?? 0);
                if (!$id) { header('Location: ' . url('futbol', 'alumno', 'index')); exit; }

                $stm = $this->db->prepare("
                    SELECT a.*, ffa.*
                    FROM alumnos a
                    LEFT JOIN futbol_ficha_alumno ffa ON ffa.ffa_alumno_id = a.alu_alumno_id AND ffa.ffa_tenant_id = a.alu_tenant_id
                    WHERE a.alu_alumno_id = ? AND a.alu_tenant_id = ?
                ");
                $stm->execute([$id, $this->tenantId]);
                $alumno = $stm->fetch(\PDO::FETCH_ASSOC);
                if (!$alumno) { header('Location: ' . url('futbol', 'alumno', 'index')); exit; }

                // Descifrar PII del alumno (LOPDP)
                $alumno = \DataProtection::decryptRow('alumnos', $alumno);

                // Cargar datos del representante desde clientes
                $representante = [];
                $hermanos = [];
                if (!empty($alumno['alu_representante_id'])) {
                    $representante = $this->getClienteById($alumno['alu_representante_id']);
                    $hermanos = $this->getHermanos($alumno['alu_representante_id'], $id);
                }

                // Verificar si el alumno ya tiene una beca activa asignada
                $stmBeca = $this->db->prepare("
                    SELECT COUNT(*) FROM futbol_beca_asignaciones
                    WHERE fba_alumno_id = ? AND fba_tenant_id = ? AND fba_estado = 'ACTIVA'
                ");
                $stmBeca->execute([$id, $this->tenantId]);
                $tieneBeca = (int)$stmBeca->fetchColumn() > 0;

                $this->viewData['alumno'] = $alumno;
                $this->viewData['ficha'] = $alumno; // ficha viene en el mismo JOIN
                $this->viewData['representante'] = $representante;
                $this->viewData['hermanos'] = $hermanos;
                $this->viewData['tiene_beca'] = $tieneBeca;
                $this->viewData['categorias'] = $this->getCategoriasActivas();
                $this->viewData['sedes'] = $this->getSedesActivas();
                $this->viewData['campos_ficha'] = $this->getCamposActivos();
                $this->viewData['foto_alumno'] = $this->getFotoAlumno($id);
                $this->viewData['csrf_token'] = \Security::generateCsrfToken();
                $this->viewData['title'] = 'Editar Alumno';
                $this->viewData['sede_activa'] = $_SESSION['futbol_sede_id'] ?? null;
                $this->renderModule('futbol/alumnos/formulario', $this->viewData);
                return;
            }

            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $alumnoId = (int)($this->post('id') ?? 0);
            if (!$alumnoId) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $nombres = trim($this->post('nombres') ?? '');
            $apellidos = trim($this->post('apellidos') ?? '');
            if (empty($nombres) || empty($apellidos)) return $this->jsonResponse(['success' => false, 'message' => 'Nombres y apellidos son obligatorios']);

            $representanteId = (int)($this->post('representante_id') ?? 0) ?: null;
            $parentesco = $this->post('parentesco') ?: null;
            if (!$representanteId) return $this->jsonResponse(['success' => false, 'message' => 'Debe seleccionar un representante']);

            // Leer estado actual antes de modificar (para cascade si pasa a INACTIVO)
            $stmEstAnt = $this->db->prepare("SELECT alu_estado FROM alumnos WHERE alu_alumno_id = ? AND alu_tenant_id = ?");
            $stmEstAnt->execute([$alumnoId, $this->tenantId]);
            $estadoAnterior = (string)$stmEstAnt->fetchColumn();

            $this->db->beginTransaction();

            // Registrar consentimiento si se marcó
            if ($this->post('consentimiento_datos')) {
                $this->registrarConsentimiento($representanteId);
            }

            $tipoId = $this->post('tipo_identificacion') === 'PAS' ? 'PAS' : 'CED';

            // Cifrar identificación del alumno (LOPDP Ecuador)
            $identPlain = $this->post('identificacion') ?: null;
            $identEnc   = \DataProtection::encryptRow('alumnos', ['alu_identificacion' => $identPlain]);

            // Actualizar datos base del alumno
            $stm = $this->db->prepare("
                UPDATE alumnos SET alu_sede_id=?, alu_nombres=?, alu_apellidos=?,
                    alu_tipo_identificacion=?, alu_identificacion=?, alu_identificacion_hash=?, alu_fecha_nacimiento=?,
                    alu_genero=?, alu_tipo_sangre=?, alu_alergias=?, alu_condiciones_medicas=?, alu_medicamentos=?,
                    alu_contacto_emergencia=?, alu_telefono_emergencia=?, alu_observaciones_medicas=?,
                    alu_representante_id=?, alu_parentesco=?, alu_notas=?, alu_estado=?
                WHERE alu_alumno_id=? AND alu_tenant_id=?
            ");
            $stm->execute([
                (int)($this->post('sede_id') ?? 0) ?: ($_SESSION['futbol_sede_id'] ?? null),
                $nombres,
                $apellidos,
                $tipoId,
                $identEnc['alu_identificacion'],
                $identEnc['alu_identificacion_hash'] ?? null,
                $this->post('fecha_nacimiento') ?: null,
                $this->post('genero') ?: null,
                $this->post('tipo_sangre') ?: null,
                $this->post('alergias') ?: null,
                $this->post('condiciones_medicas') ?: null,
                $this->post('medicamentos') ?: null,
                $this->post('contacto_emergencia') ?: null,
                $this->post('telefono_emergencia') ?: null,
                $this->post('observaciones_medicas') ?: null,
                $representanteId,
                $parentesco,
                $this->post('notas') ?: null,
                $this->post('estado') ?: 'ACTIVO',
                $alumnoId, $this->tenantId,
            ]);

            // Actualizar ficha de fútbol
            $objetivo = $this->post('objetivo') ?: 'RECREATIVO';
            if (!in_array($objetivo, ['RECREATIVO', 'FORMATIVO', 'COMPETITIVO'])) $objetivo = 'RECREATIVO';

            // Recopilar campos custom
            $camposCustom = [];
            foreach ($_POST['campo_custom'] ?? [] as $k => $v) {
                $safeKey = preg_replace('/[^a-zA-Z0-9_]/', '', $k);
                if ($safeKey) $camposCustom[$safeKey] = is_string($v) ? substr(trim($v), 0, 500) : '';
            }
            $datosCustomJson = !empty($camposCustom) ? json_encode($camposCustom, JSON_UNESCAPED_UNICODE) : null;

            // Verificar si ya existe ficha
            $stmCheck = $this->db->prepare("SELECT COUNT(*) FROM futbol_ficha_alumno WHERE ffa_alumno_id = ? AND ffa_tenant_id = ?");
            $stmCheck->execute([$alumnoId, $this->tenantId]);

            if ((int)$stmCheck->fetchColumn() > 0) {
                $stm2 = $this->db->prepare("
                    UPDATE futbol_ficha_alumno SET ffa_categoria_id=?, ffa_posicion_preferida=?, ffa_pie_dominante=?,
                        ffa_experiencia_previa=?, ffa_club_anterior=?, ffa_objetivo=?, ffa_talla_camiseta=?,
                        ffa_numero_camiseta=?, ffa_autorizacion_medica=?, ffa_datos_custom=?
                    WHERE ffa_alumno_id=? AND ffa_tenant_id=?
                ");
                $stm2->execute([
                    (int)($this->post('categoria_id') ?? 0) ?: null,
                    $this->post('posicion_preferida') ?: null,
                    $this->post('pie_dominante') ?: null,
                    $this->post('experiencia_previa') ?: null,
                    $this->post('club_anterior') ?: null,
                    $objetivo,
                    $this->post('talla_camiseta') ?: null,
                    (int)($this->post('numero_camiseta') ?? 0) ?: null,
                    (int)($this->post('autorizacion_medica') ?? 0),
                    $datosCustomJson,
                    $alumnoId, $this->tenantId,
                ]);
            } else {
                // Crear ficha si no existe
                $stm2 = $this->db->prepare("
                    INSERT INTO futbol_ficha_alumno (ffa_tenant_id, ffa_alumno_id, ffa_categoria_id, ffa_posicion_preferida,
                        ffa_pie_dominante, ffa_experiencia_previa, ffa_club_anterior, ffa_objetivo, ffa_talla_camiseta,
                        ffa_numero_camiseta, ffa_autorizacion_medica, ffa_datos_custom, ffa_fecha_ingreso, ffa_activo)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,CURDATE(),1)
                ");
                $stm2->execute([
                    $this->tenantId,
                    $alumnoId,
                    (int)($this->post('categoria_id') ?? 0) ?: null,
                    $this->post('posicion_preferida') ?: null,
                    $this->post('pie_dominante') ?: null,
                    $this->post('experiencia_previa') ?: null,
                    $this->post('club_anterior') ?: null,
                    $objetivo,
                    $this->post('talla_camiseta') ?: null,
                    (int)($this->post('numero_camiseta') ?? 0) ?: null,
                    (int)($this->post('autorizacion_medica') ?? 0),
                    $datosCustomJson,
                ]);
            }

            // Cascade: si el alumno pasó a INACTIVO o SUSPENDIDO, cancelar inscripciones ACTIVAS
            $nuevoEstado = $this->post('estado') ?: 'ACTIVO';
            if (in_array($nuevoEstado, ['INACTIVO', 'SUSPENDIDO']) && $estadoAnterior === 'ACTIVO') {
                $this->cancelarInscripcionesActivas($alumnoId);
            }

            $this->db->commit();

            return $this->jsonResponse(['success' => true, 'message' => 'Alumno actualizado']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error editando alumno: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar alumno']);
        }
    }

    /**
     * Eliminar alumno (soft delete)
     */
    public function eliminar() {
        try {
            $id = (int)($this->get('id') ?? 0);
            if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID requerido']);

            $this->db->beginTransaction();

            $this->db->prepare("UPDATE alumnos SET alu_estado = 'INACTIVO' WHERE alu_alumno_id = ? AND alu_tenant_id = ?")
                ->execute([$id, $this->tenantId]);

            $this->db->prepare("UPDATE futbol_ficha_alumno SET ffa_activo = 0 WHERE ffa_alumno_id = ? AND ffa_tenant_id = ?")
                ->execute([$id, $this->tenantId]);

            // Cascade: cancelar inscripciones ACTIVAS y liberar cupos
            $this->cancelarInscripcionesActivas($id);

            $this->db->commit();

            return $this->jsonResponse(['success' => true, 'message' => 'Alumno desactivado']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->logError("Error eliminando alumno: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al desactivar alumno']);
        }
    }

    /**
     * Buscar alumnos por nombre (AJAX autocomplete)
     */
    public function buscar() {
        try {
            $q = trim($this->get('q') ?? '');
            $stm = $this->db->prepare("
                SELECT a.alu_alumno_id, a.alu_nombres, a.alu_apellidos
                FROM alumnos a
                JOIN futbol_ficha_alumno ffa ON ffa.ffa_alumno_id = a.alu_alumno_id AND ffa.ffa_tenant_id = a.alu_tenant_id
                WHERE a.alu_tenant_id = ? AND a.alu_estado = 'ACTIVO' AND ffa.ffa_activo = 1
                  AND (a.alu_nombres LIKE ? OR a.alu_apellidos LIKE ?)
                ORDER BY a.alu_apellidos, a.alu_nombres
                LIMIT 20
            ");
            $like = "%{$q}%";
            $stm->execute([$this->tenantId, $like, $like]);

            return $this->jsonResponse(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_ASSOC)]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'data' => []]);
        }
    }

    private function jsonResponse($data) { header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }

    /**
     * Ver ficha completa del alumno
     */
    public function ver() {
        try {
            $this->setupModule();
            $id = (int)($this->get('id') ?? 0);
            if (!$id) { header('Location: ' . url('futbol', 'alumno', 'index')); exit; }

            // Datos del alumno + ficha + categoría + sede + representante
            $stm = $this->db->prepare("
                SELECT a.*, ffa.*,
                       fct.fct_nombre AS categoria_nombre,
                       fct.fct_color AS categoria_color,
                       s.sed_nombre AS sede_nombre,
                       c.cli_cliente_id AS rep_cliente_id,
                       c.cli_identificacion AS rep_identificacion,
                       CONCAT(c.cli_nombres, ' ', c.cli_apellidos) AS rep_nombre_completo,
                       c.cli_telefono AS rep_telefono,
                       c.cli_celular AS rep_celular,
                       c.cli_email AS rep_email,
                       c.cli_direccion AS rep_direccion,
                       c.cli_consentimiento_datos AS rep_consentimiento
                FROM alumnos a
                LEFT JOIN futbol_ficha_alumno ffa ON ffa.ffa_alumno_id = a.alu_alumno_id AND ffa.ffa_tenant_id = a.alu_tenant_id
                LEFT JOIN futbol_categorias fct ON ffa.ffa_categoria_id = fct.fct_categoria_id
                LEFT JOIN instalaciones_sedes s ON a.alu_sede_id = s.sed_sede_id
                LEFT JOIN clientes c ON a.alu_representante_id = c.cli_cliente_id AND c.cli_tenant_id = a.alu_tenant_id
                WHERE a.alu_alumno_id = ? AND a.alu_tenant_id = ?
            ");
            $stm->execute([$id, $this->tenantId]);
            $alumno = $stm->fetch(\PDO::FETCH_ASSOC);
            if (!$alumno) { header('Location: ' . url('futbol', 'alumno', 'index')); exit; }

            // Descifrar PII del alumno (LOPDP)
            $alumno = \DataProtection::decryptRow('alumnos', $alumno);

            // Descifrar PII del representante (campos aliased desde clientes)
            foreach (['rep_identificacion', 'rep_telefono', 'rep_celular', 'rep_email', 'rep_direccion'] as $field) {
                if (!empty($alumno[$field])) {
                    $alumno[$field] = \DataProtection::decrypt($alumno[$field]);
                }
            }

            // Hermanos (otros alumnos con el mismo representante)
            $hermanos = [];
            if (!empty($alumno['alu_representante_id'])) {
                $hermanos = $this->getHermanos($alumno['alu_representante_id'], $id);
            }

            // Inscripciones del alumno
            $stmIns = $this->db->prepare("
                SELECT fin.*, fg.fgr_nombre AS grupo_nombre, fg.fgr_color AS grupo_color,
                       fp.fpe_nombre AS periodo_nombre
                FROM futbol_inscripciones fin
                LEFT JOIN futbol_grupos fg ON fin.fin_grupo_id = fg.fgr_grupo_id
                LEFT JOIN futbol_periodos fp ON fin.fin_periodo_id = fp.fpe_periodo_id
                WHERE fin.fin_alumno_id = ? AND fin.fin_tenant_id = ?
                ORDER BY fin.fin_fecha_inscripcion DESC
                LIMIT 20
            ");
            $stmIns->execute([$id, $this->tenantId]);
            $inscripciones = $stmIns->fetchAll(\PDO::FETCH_ASSOC);

            // Evaluaciones
            $evaluaciones = [];
            try {
                $stmEv = $this->db->prepare("
                    SELECT fev.*
                    FROM futbol_evaluaciones fev
                    WHERE fev.fev_alumno_id = ? AND fev.fev_tenant_id = ?
                    ORDER BY fev.fev_fecha DESC
                    LIMIT 10
                ");
                $stmEv->execute([$id, $this->tenantId]);
                $evaluaciones = $stmEv->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                // Tabla puede no existir aún
            }

            // Resumen de asistencia
            $asistencia = [];
            try {
                $stmAs = $this->db->prepare("
                    SELECT
                        SUM(CASE WHEN fas_estado = 'PRESENTE' THEN 1 ELSE 0 END) AS presentes,
                        SUM(CASE WHEN fas_estado = 'AUSENTE' THEN 1 ELSE 0 END) AS ausentes,
                        SUM(CASE WHEN fas_estado = 'JUSTIFICADO' THEN 1 ELSE 0 END) AS justificadas
                    FROM futbol_asistencias
                    WHERE fas_alumno_id = ? AND fas_tenant_id = ?
                ");
                $stmAs->execute([$id, $this->tenantId]);
                $asistencia = $stmAs->fetch(\PDO::FETCH_ASSOC) ?: [];
            } catch (\Exception $e) {
                // Tabla puede no existir aún
            }

            // Becas activas del alumno
            $becas = [];
            try {
                $stmBeca = $this->db->prepare("
                    SELECT fba.fba_asignacion_id,
                           fba.fba_fecha_asignacion,
                           fba.fba_fecha_vencimiento,
                           fba.fba_motivo,
                           fba.fba_estado,
                           fb.fbe_nombre,
                           fb.fbe_tipo,
                           fb.fbe_valor,
                           fb.fbe_descripcion,
                           fb.fbe_aplica_matricula,
                           fb.fbe_aplica_mensualidad
                    FROM futbol_beca_asignaciones fba
                    JOIN futbol_becas fb ON fba.fba_beca_id = fb.fbe_beca_id
                    WHERE fba.fba_alumno_id = ?
                      AND fba.fba_tenant_id = ?
                      AND fba.fba_estado = 'ACTIVA'
                    ORDER BY fba.fba_fecha_asignacion DESC
                ");
                $stmBeca->execute([$id, $this->tenantId]);
                $becas = $stmBeca->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                // Tabla puede no existir aún
            }

            $this->viewData['alumno'] = $alumno;
            $this->viewData['ficha'] = $alumno; // viene en el mismo JOIN
            $this->viewData['hermanos'] = $hermanos;
            $this->viewData['becas'] = $becas;
            $this->viewData['inscripciones'] = $inscripciones;
            $this->viewData['evaluaciones'] = $evaluaciones;
            $this->viewData['asistencia_resumen'] = $asistencia;
            $this->viewData['foto_alumno'] = $this->getFotoAlumno($id);
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['title'] = 'Ficha del Alumno';
            $this->renderModule('futbol/alumnos/ver', $this->viewData);

        } catch (\Exception $e) {
            $this->logError("Error mostrando ficha alumno: " . $e->getMessage());
            $this->error('Error al cargar la ficha del alumno');
        }
    }

    // =========== REPRESENTANTE: BÚSQUEDA Y CREACIÓN ===========

    /**
     * Buscar representante por cédula en tabla clientes (AJAX)
     * Usa blind index (hash) para búsqueda exacta sobre datos cifrados (LOPDP)
     */

    /**
     * Buscar alumno existente por identificación (AJAX GET, cross-tenant)
     * Permite detectar alumnos ya registrados en cualquier tenant del sistema.
     */
    public function buscarAlumno() {
        try {
            $identificacion = trim($this->get('identificacion') ?? '');
            if (empty($identificacion)) return $this->jsonResponse(['success' => false, 'message' => 'Identificación requerida']);

            // Búsqueda cross-tenant por blind index (datos cifrados, LOPDP)
            $idHash = \DataProtection::blindIndex($identificacion);
            $stm = $this->db->prepare("
                SELECT alu_alumno_id, alu_tenant_id, alu_nombres, alu_apellidos,
                       alu_tipo_identificacion, alu_identificacion,
                       alu_fecha_nacimiento, alu_genero,
                       alu_tipo_sangre, alu_alergias, alu_condiciones_medicas, alu_medicamentos,
                       alu_contacto_emergencia, alu_telefono_emergencia, alu_observaciones_medicas
                FROM alumnos
                WHERE alu_identificacion_hash = ?
                ORDER BY alu_alumno_id ASC
                LIMIT 1
            ");
            $stm->execute([$idHash]);
            $alumno = $stm->fetch(\PDO::FETCH_ASSOC);
            if ($alumno) { $alumno = \DataProtection::decryptRow('alumnos', $alumno); }

            if (!$alumno) return $this->jsonResponse(['success' => false, 'message' => 'No encontrado']);

            $mismoTenant = (int)$alumno['alu_tenant_id'] === (int)$this->tenantId;

            return $this->jsonResponse([
                'success'      => true,
                'mismo_tenant' => $mismoTenant,
                'data'         => $alumno,
            ]);

        } catch (\Exception $e) {
            $this->logError("Error buscando alumno: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al buscar']);
        }
    }

    public function buscarRepresentante() {
        try {
            $cedula = trim($this->get('cedula') ?? '');
            if (empty($cedula)) return $this->jsonResponse(['success' => false, 'message' => 'Cédula requerida']);

            // Generar blind index para buscar sobre datos cifrados
            $cedulaHash = \DataProtection::blindIndex($cedula);

            $stm = $this->db->prepare("
                SELECT cli_cliente_id, cli_identificacion, cli_nombres, cli_apellidos,
                       cli_telefono, cli_celular, cli_email, cli_direccion,
                       cli_consentimiento_datos
                FROM clientes
                WHERE cli_tenant_id = ? AND cli_identificacion_hash = ? AND cli_estado = 'A'
                LIMIT 1
            ");
            $stm->execute([$this->tenantId, $cedulaHash]);
            $cliente = $stm->fetch(\PDO::FETCH_ASSOC);

            if (!$cliente) return $this->jsonResponse(['success' => false, 'message' => 'No encontrado']);

            // Descifrar campos sensibles (LOPDP)
            $cliente = \DataProtection::decryptRow('clientes', $cliente);

            // Buscar hermanos (otros alumnos con este mismo representante)
            $hermanos = $this->getHermanos($cliente['cli_cliente_id']);

            // Si se está editando un alumno existente, verificar si ya tiene beca activa
            $tieneBeca = false;
            $alumnoId  = (int)($this->get('alumno_id') ?? 0);
            if ($alumnoId) {
                $stmBeca = $this->db->prepare("
                    SELECT COUNT(*) FROM futbol_beca_asignaciones
                    WHERE fba_alumno_id = ? AND fba_tenant_id = ? AND fba_estado = 'ACTIVA'
                ");
                $stmBeca->execute([$alumnoId, $this->tenantId]);
                $tieneBeca = (int)$stmBeca->fetchColumn() > 0;
            }

            return $this->jsonResponse([
                'success'    => true,
                'data'       => $cliente,
                'hermanos'   => $hermanos,
                'tiene_beca' => $tieneBeca,
            ]);

        } catch (\Exception $e) {
            $this->logError("Error buscando representante: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al buscar']);
        }
    }

    /**
     * Actualizar datos del representante existente (AJAX POST)
     */
    public function actualizarRepresentante() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $clienteId = (int)($this->post('cliente_id') ?? 0);
            if (!$clienteId) return $this->jsonResponse(['success' => false, 'message' => 'ID de representante requerido']);

            $nombres   = trim($this->post('nombres') ?? '');
            $apellidos = trim($this->post('apellidos') ?? '');
            $telefono  = trim($this->post('telefono') ?? '');

            if (empty($nombres) || empty($apellidos) || empty($telefono)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Nombres, apellidos y teléfono son obligatorios']);
            }

            // Verificar que el cliente pertenece a este tenant
            $stmCheck = $this->db->prepare("SELECT cli_cliente_id FROM clientes WHERE cli_cliente_id = ? AND cli_tenant_id = ? AND cli_estado = 'A' LIMIT 1");
            $stmCheck->execute([$clienteId, $this->tenantId]);
            if (!$stmCheck->fetchColumn()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Representante no encontrado']);
            }

            // Cifrar campos sensibles
            $protectedData = [
                'cli_email'   => $this->post('email') ?: null,
                'cli_telefono' => $telefono,
                'cli_celular'  => null,
            ];
            $encrypted = \DataProtection::encryptRow('clientes', $protectedData);

            $stm = $this->db->prepare("
                UPDATE clientes SET
                    cli_nombres  = ?,
                    cli_apellidos = ?,
                    cli_telefono  = ?,
                    cli_email     = ?,
                    cli_email_hash = ?,
                    cli_direccion  = ?
                WHERE cli_cliente_id = ? AND cli_tenant_id = ?
            ");
            $stm->execute([
                $nombres,
                $apellidos,
                $encrypted['cli_telefono'],
                $encrypted['cli_email'],
                $encrypted['cli_email_hash'] ?? null,
                $this->post('direccion') ?: null,
                $clienteId,
                $this->tenantId,
            ]);

            // Devolver datos actualizados (descifrados)
            $cliente = $this->getClienteById($clienteId);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Datos del representante actualizados',
                'data'    => $cliente,
            ]);

        } catch (\Exception $e) {
            $this->logError("Error actualizando representante: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar representante']);
        }
    }

    /**
     * Crear representante como nuevo cliente (AJAX POST)
     */
    public function crearRepresentante() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);

            $identificacion = trim($this->post('identificacion') ?? '');
            $nombres   = trim($this->post('nombres') ?? '');
            $apellidos = trim($this->post('apellidos') ?? '');
            $telefono  = trim($this->post('telefono') ?? '');
            $tipoId    = $this->post('tipo_identificacion') === 'PAS' ? 'PAS' : 'CED';

            if (empty($identificacion) || empty($nombres) || empty($apellidos) || empty($telefono)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Identificación, nombres, apellidos y teléfono son obligatorios']);
            }

            // Verificar que no exista ya (usar blind index)
            $idHash = \DataProtection::blindIndex($identificacion);
            $stmCheck = $this->db->prepare("SELECT cli_cliente_id FROM clientes WHERE cli_tenant_id = ? AND cli_identificacion_hash = ? LIMIT 1");
            $stmCheck->execute([$this->tenantId, $idHash]);
            if ($stmCheck->fetchColumn()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Ya existe un cliente con esta identificación. Use la búsqueda.']);
            }

            $consentimiento = (int)($this->post('consentimiento') ?? 0);

            // Cifrar datos sensibles (LOPDP Ecuador)
            $protectedData = [
                'cli_identificacion' => $identificacion,
                'cli_email'          => $this->post('email') ?: null,
                'cli_telefono'       => $telefono,
                'cli_celular'        => null,
            ];
            $encrypted = \DataProtection::encryptRow('clientes', $protectedData);

            $stm = $this->db->prepare("
                INSERT INTO clientes (cli_tenant_id, cli_tipo_identificacion, cli_identificacion, cli_identificacion_hash,
                    cli_nombres, cli_apellidos, cli_telefono, cli_email, cli_email_hash, cli_direccion,
                    cli_tipo_cliente, cli_estado, cli_fecha_registro,
                    cli_consentimiento_datos, cli_consentimiento_fecha, cli_consentimiento_ip)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'REPRESENTANTE', 'A', NOW(), ?, ?, ?)
            ");
            $stm->execute([
                $this->tenantId,
                $tipoId,
                $encrypted['cli_identificacion'],
                $encrypted['cli_identificacion_hash'] ?? $idHash,
                $nombres,
                $apellidos,
                $encrypted['cli_telefono'],
                $encrypted['cli_email'],
                $encrypted['cli_email_hash'] ?? null,
                $this->post('direccion') ?: null,
                $consentimiento,
                $consentimiento ? date('Y-m-d H:i:s') : null,
                $consentimiento ? ($_SERVER['REMOTE_ADDR'] ?? null) : null,
            ]);

            $clienteId = $this->db->lastInsertId();

            // Devolver datos completos del cliente creado (descifrados)
            $stmGet = $this->db->prepare("
                SELECT cli_cliente_id, cli_identificacion, cli_nombres, cli_apellidos,
                       cli_telefono, cli_celular, cli_email, cli_direccion,
                       cli_consentimiento_datos
                FROM clientes WHERE cli_cliente_id = ?
            ");
            $stmGet->execute([$clienteId]);
            $nuevoCliente = $stmGet->fetch(\PDO::FETCH_ASSOC);
            $nuevoCliente = \DataProtection::decryptRow('clientes', $nuevoCliente);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Representante registrado correctamente',
                'data'    => $nuevoCliente,
            ]);

        } catch (\Exception $e) {
            $this->logError("Error creando representante: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al registrar representante']);
        }
    }

    // =========== FOTO DEL ALUMNO ===========

    /**
     * Subir/reemplazar foto del alumno (AJAX, multipart/form-data)
     * POST: alumno_id, csrf_token, foto (FILE)
     */
    public function subirFoto() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'POST requerido']);
            }
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            $alumnoId = (int)($this->post('alumno_id') ?? 0);
            if (!$alumnoId) {
                return $this->jsonResponse(['success' => false, 'message' => 'ID de alumno requerido']);
            }

            // Verificar que el alumno pertenece a este tenant
            $stmCheck = $this->db->prepare("SELECT alu_alumno_id FROM alumnos WHERE alu_alumno_id = ? AND alu_tenant_id = ? LIMIT 1");
            $stmCheck->execute([$alumnoId, $this->tenantId]);
            if (!$stmCheck->fetchColumn()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Alumno no encontrado']);
            }

            if (empty($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
                return $this->jsonResponse(['success' => false, 'message' => 'No se envió ningún archivo']);
            }

            $userId = (int)($_SESSION['user_id'] ?? 0);
            $fm = new \FileManager($this->db, $this->tenantId, $userId);

            $result = $fm->uploadImage($_FILES['foto'], 'alumnos', $alumnoId, 'fotos', true);

            if (!$result['success']) {
                return $this->jsonResponse(['success' => false, 'message' => $result['error']]);
            }

            // Actualizar alu_foto con la ruta relativa (compatibilidad con código legado)
            $this->db->prepare("UPDATE alumnos SET alu_foto = ? WHERE alu_alumno_id = ? AND alu_tenant_id = ?")
                ->execute([$result['ruta'], $alumnoId, $this->tenantId]);

            return $this->jsonResponse([
                'success'  => true,
                'message'  => 'Foto actualizada correctamente',
                'arc_id'   => $result['arc_id'],
                'foto_url' => \Config::baseUrl('archivo.php?id=' . $result['arc_id']),
            ]);

        } catch (\Exception $e) {
            $this->logError("Error subiendo foto alumno: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al subir la foto']);
        }
    }

    /**
     * Eliminar foto del alumno (AJAX POST)
     * POST: alumno_id, arc_id, csrf_token
     */
    public function eliminarFoto() {
        try {
            if (!\Security::validateCsrfToken($this->post('csrf_token'))) {
                return $this->jsonResponse(['success' => false, 'message' => 'Token inválido']);
            }

            $alumnoId = (int)($this->post('alumno_id') ?? 0);
            $arcId    = (int)($this->post('arc_id') ?? 0);
            if (!$alumnoId || !$arcId) {
                return $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos']);
            }

            $userId = (int)($_SESSION['user_id'] ?? 0);
            $fm = new \FileManager($this->db, $this->tenantId, $userId);

            if (!$fm->deleteFile($arcId)) {
                return $this->jsonResponse(['success' => false, 'message' => 'No se pudo eliminar la foto']);
            }

            // Limpiar alu_foto si era la foto activa
            $this->db->prepare("UPDATE alumnos SET alu_foto = NULL WHERE alu_alumno_id = ? AND alu_tenant_id = ?")
                ->execute([$alumnoId, $this->tenantId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Foto eliminada']);

        } catch (\Exception $e) {
            $this->logError("Error eliminando foto alumno: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar la foto']);
        }
    }

    // =========== HELPERS PRIVADOS ===========

    /**
     * Obtener la foto principal de un alumno desde core_archivos
     * @return array|null  Fila de core_archivos con arc_id, arc_ruta_relativa, etc.
     */
    private function getFotoAlumno(int $alumnoId): ?array {
        try {
            $userId = (int)($_SESSION['user_id'] ?? 0);
            $fm = new \FileManager($this->db, $this->tenantId, $userId);
            return $fm->getPrincipal('alumnos', $alumnoId, 'fotos');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtener cliente por ID (descifra datos sensibles)
     */
    private function getClienteById($clienteId) {
        $stm = $this->db->prepare("
            SELECT cli_cliente_id, cli_identificacion, cli_nombres, cli_apellidos,
                   cli_telefono, cli_celular, cli_email, cli_direccion,
                   cli_consentimiento_datos
            FROM clientes WHERE cli_cliente_id = ? AND cli_tenant_id = ?
        ");
        $stm->execute([$clienteId, $this->tenantId]);
        $row = $stm->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return [];
        return \DataProtection::decryptRow('clientes', $row);
    }

    /**
     * Obtener hermanos: otros alumnos activos con el mismo representante
     * @param int $representanteId  ID del cliente/representante
     * @param int|null $excluirAlumnoId  Excluir este alumno del resultado
     */
    private function getHermanos($representanteId, $excluirAlumnoId = null) {
        $sql = "SELECT alu_alumno_id, alu_nombres, alu_apellidos, alu_estado
                FROM alumnos
                WHERE alu_tenant_id = ? AND alu_representante_id = ? AND alu_estado = 'ACTIVO'";
        $params = [$this->tenantId, $representanteId];
        if ($excluirAlumnoId) {
            $sql .= ' AND alu_alumno_id != ?';
            $params[] = (int)$excluirAlumnoId;
        }
        $sql .= ' ORDER BY alu_apellidos, alu_nombres LIMIT 20';
        $stm = $this->db->prepare($sql);
        $stm->execute($params);
        return $stm->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Registrar consentimiento de tratamiento de datos
     */
    private function registrarConsentimiento($clienteId) {
        $stm = $this->db->prepare("
            UPDATE clientes SET cli_consentimiento_datos = 1,
                cli_consentimiento_fecha = NOW(),
                cli_consentimiento_ip = ?
            WHERE cli_cliente_id = ? AND cli_tenant_id = ? AND cli_consentimiento_datos = 0
        ");
        $stm->execute([
            $_SERVER['REMOTE_ADDR'] ?? null,
            $clienteId,
            $this->tenantId,
        ]);
    }

    private function getCamposActivos() {
        $stm = $this->db->prepare("SELECT * FROM futbol_campos_ficha WHERE fcf_tenant_id = ? AND fcf_activo = 1 ORDER BY fcf_orden, fcf_clave");
        $stm->execute([$this->tenantId]);
        return $stm->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getCategoriasActivas() {
        $stm = $this->db->prepare("SELECT fct_categoria_id, fct_nombre, fct_edad_min, fct_edad_max, fct_color FROM futbol_categorias WHERE fct_tenant_id = ? AND fct_activo = 1 ORDER BY fct_orden");
        $stm->execute([$this->tenantId]);
        return $stm->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getSedesActivas() {
        $stm = $this->db->prepare("SELECT sed_sede_id, sed_nombre FROM instalaciones_sedes WHERE sed_tenant_id = ? AND sed_estado = 'A' ORDER BY sed_nombre");
        $stm->execute([$this->tenantId]);
        return $stm->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Cancela todas las inscripciones ACTIVAS del alumno y libera cupos en los grupos.
     * Debe llamarse dentro de una transacción activa.
     */
    private function cancelarInscripcionesActivas(int $alumnoId): void {
        // Obtener inscripciones ACTIVAS con su grupo para actualizar cupos
        $stm = $this->db->prepare("
            SELECT fin_inscripcion_id, fin_grupo_id
            FROM futbol_inscripciones
            WHERE fin_alumno_id = ? AND fin_tenant_id = ? AND fin_estado = 'ACTIVA'
        ");
        $stm->execute([$alumnoId, $this->tenantId]);
        $inscActivas = $stm->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($inscActivas)) return;

        // Cancelar todas las inscripciones ACTIVAS en una sola sentencia
        $this->db->prepare("
            UPDATE futbol_inscripciones
            SET fin_estado = 'CANCELADA', fin_updated_at = NOW()
            WHERE fin_alumno_id = ? AND fin_tenant_id = ? AND fin_estado = 'ACTIVA'
        ")->execute([$alumnoId, $this->tenantId]);

        // Liberar cupo en cada grupo afectado
        $stmCupo = $this->db->prepare("
            UPDATE futbol_grupos
            SET fgr_cupo_actual = GREATEST(0, fgr_cupo_actual - 1)
            WHERE fgr_grupo_id = ? AND fgr_tenant_id = ?
        ");
        foreach ($inscActivas as $insc) {
            $stmCupo->execute([(int)$insc['fin_grupo_id'], $this->tenantId]);
        }
    }
}