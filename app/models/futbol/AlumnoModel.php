<?php
/**
 * DigiSports - Modelo de Alumnos (Fútbol)
 *
 * Encapsula toda la lógica de acceso a `futbol_alumnos` con:
 * - Aislamiento de tenant automático (heredado de BaseModel)
 * - Descifrado/cifrado de PII (identificación) vía DataProtection
 * - Búsqueda por blind index para campos cifrados
 * - Soft-delete por alu_estado (INACTIVO en lugar de DELETE)
 *
 * Uso desde un controlador:
 *   $model   = new AlumnoModel($tenantId);
 *   $alumno  = $model->find($id);
 *   $activos = $model->findActivos();
 *   $page    = $model->paginate(1, 20);
 *
 * @package DigiSports\Models\Futbol
 */

require_once dirname(__DIR__) . '/BaseModel.php';

class AlumnoModel extends BaseModel
{
    protected string $table          = 'futbol_alumnos';
    protected string $primaryKey     = 'alu_alumno_id';
    protected string $tenantColumn   = 'alu_tenant_id';

    // Soft-delete: marcar como INACTIVO en lugar de borrar físicamente
    protected bool   $softDelete       = true;
    protected string $softDeleteColumn = 'alu_estado';
    protected string $activeValue      = 'ACTIVO';
    protected string $deletedValue     = 'INACTIVO';

    // ── Campos PII cifrados ────────────────────────────────────────────
    /** Campos que se cifran/descifran automáticamente con DataProtection */
    private const ENCRYPTED_FIELDS = ['alu_identificacion'];

    // ── Lectura ────────────────────────────────────────────────────────

    /**
     * Obtener un alumno por ID (descifra PII automáticamente).
     */
    public function find(int $id): ?array
    {
        $row = parent::find($id);
        return $row ? $this->decryptRow($row) : null;
    }

    /**
     * Listado paginado de alumnos activos.
     *
     * @param int    $page     Página (1-indexed)
     * @param int    $perPage  Registros por página (máx 100)
     * @param string $orderBy  Columna y dirección, ej: 'alu_apellidos ASC'
     */
    public function paginate(int $page = 1, int $perPage = 20, array $where = [], string $orderBy = 'alu_apellidos ASC'): array
    {
        $result = parent::paginate($page, $perPage, $where, $orderBy);
        $result['data'] = array_map([$this, 'decryptRow'], $result['data']);
        return $result;
    }

    /**
     * Obtener todos los alumnos activos del tenant.
     */
    public function findActivos(string $orderBy = 'alu_apellidos ASC'): array
    {
        $rows = $this->findAll(['alu_estado' => 'ACTIVO'], $orderBy);
        return array_map([$this, 'decryptRow'], $rows);
    }

    /**
     * Buscar alumno por número de identificación (cédula/pasaporte).
     * Usa el blind index para no exponer datos cifrados en la cláusula WHERE.
     *
     * @param string $identificacion Número en texto plano
     */
    public function findByIdentificacion(string $identificacion): ?array
    {
        if (!class_exists('DataProtection')) return null;

        $hash = \DataProtection::blindIndex($identificacion);
        $row  = $this->rawOne(
            "SELECT * FROM {$this->table}
             WHERE {$this->tenantColumn} = ? AND alu_identificacion_hash = ?
             LIMIT 1",
            [$this->tenantId, $hash]
        );
        return $row ? $this->decryptRow($row) : null;
    }

    /**
     * Buscar alumnos por nombre o apellido (LIKE, case-insensitive).
     *
     * @param string $query Texto a buscar
     * @param int    $limit Máximo de resultados
     */
    public function searchByNombre(string $query, int $limit = 50): array
    {
        $q    = '%' . str_replace(['%', '_'], ['\%', '\_'], $query) . '%';
        $rows = $this->raw(
            "SELECT * FROM {$this->table}
             WHERE {$this->tenantColumn} = ?
               AND alu_estado = 'ACTIVO'
               AND (alu_nombres LIKE ? OR alu_apellidos LIKE ?)
             ORDER BY alu_apellidos ASC, alu_nombres ASC
             LIMIT ?",
            [$this->tenantId, $q, $q, $limit]
        );
        return array_map([$this, 'decryptRow'], $rows);
    }

    /**
     * Obtener alumnos por representante.
     *
     * @param int $representanteId ID del cliente representante
     */
    public function findByRepresentante(int $representanteId): array
    {
        $rows = $this->raw(
            "SELECT a.*, c.cli_nombres AS rep_nombres, c.cli_apellidos AS rep_apellidos
             FROM {$this->table} a
             LEFT JOIN clientes c ON c.cli_cliente_id = a.alu_representante_id
                                  AND c.cli_tenant_id = a.{$this->tenantColumn}
             WHERE a.{$this->tenantColumn} = ?
               AND a.alu_representante_id = ?
             ORDER BY a.alu_apellidos ASC",
            [$this->tenantId, $representanteId]
        );
        return array_map([$this, 'decryptRow'], $rows);
    }

    /**
     * Obtener alumnos de una sede específica.
     */
    public function findBySede(int $sedeId, string $estado = 'ACTIVO'): array
    {
        $rows = $this->raw(
            "SELECT * FROM {$this->table}
             WHERE {$this->tenantColumn} = ? AND alu_sede_id = ? AND alu_estado = ?
             ORDER BY alu_apellidos ASC",
            [$this->tenantId, $sedeId, $estado]
        );
        return array_map([$this, 'decryptRow'], $rows);
    }

    /**
     * Contar alumnos por estado.
     */
    public function countByEstado(string $estado = 'ACTIVO'): int
    {
        return $this->count(['alu_estado' => $estado]);
    }

    // ── Escritura ──────────────────────────────────────────────────────

    /**
     * Crear nuevo alumno (cifra PII automáticamente).
     * Devuelve el ID del nuevo registro.
     *
     * @param array $data Datos del alumno (texto plano)
     */
    public function create(array $data): int
    {
        return parent::create($this->encryptRow($data));
    }

    /**
     * Actualizar alumno (cifra PII automáticamente).
     */
    public function update(int $id, array $data): bool
    {
        return parent::update($id, $this->encryptRow($data));
    }

    /**
     * Cambiar estado del alumno.
     *
     * @param int    $id     ID del alumno
     * @param string $estado ACTIVO | INACTIVO | SUSPENDIDO
     */
    public function cambiarEstado(int $id, string $estado): bool
    {
        $estadosValidos = ['ACTIVO', 'INACTIVO', 'SUSPENDIDO'];
        if (!in_array($estado, $estadosValidos, true)) return false;

        return (bool)$this->exec(
            "UPDATE {$this->table}
             SET alu_estado = ?
             WHERE {$this->primaryKey} = ? AND {$this->tenantColumn} = ?",
            [$estado, $id, $this->tenantId]
        );
    }

    // ── Helpers de cifrado PII ─────────────────────────────────────────

    /**
     * Cifrar campos PII antes de persistir.
     */
    private function encryptRow(array $data): array
    {
        if (!class_exists('DataProtection')) return $data;

        if (isset($data['alu_identificacion']) && $data['alu_identificacion'] !== '') {
            $plain = $data['alu_identificacion'];
            $data['alu_identificacion']      = \DataProtection::encrypt($plain);
            $data['alu_identificacion_hash'] = \DataProtection::blindIndex($plain);
        }
        return $data;
    }

    /**
     * Descifrar campos PII tras leer de BD.
     */
    private function decryptRow(array $row): array
    {
        if (!class_exists('DataProtection')) return $row;

        foreach (self::ENCRYPTED_FIELDS as $field) {
            if (isset($row[$field]) && \DataProtection::isEncrypted($row[$field])) {
                $row[$field] = \DataProtection::decrypt($row[$field]);
            }
        }
        return $row;
    }
}
