<?php
/**
 * DigiSports - Modelo Base Abstracto
 *
 * Proporciona CRUD seguro con aislamiento de tenant, paginación,
 * soft-delete y helpers de query para todos los subsistemas.
 *
 * Uso:
 *   class AlumnoModel extends BaseModel {
 *       protected string $table        = 'futbol_alumnos';
 *       protected string $primaryKey   = 'alu_id';
 *       protected string $tenantColumn = 'alu_tenant_id';
 *   }
 *
 * @package DigiSports\Models
 * @version 1.0.0
 */
abstract class BaseModel {

    protected $db;
    protected $tenantId;

    // ── Obligatorio definir en cada subclase ─────────────────────────────
    /** Nombre de la tabla, ej: 'futbol_alumnos' */
    protected string $table;

    /** Clave primaria, ej: 'alu_id' */
    protected string $primaryKey;

    /** Columna del tenant_id en esta tabla, ej: 'alu_tenant_id' */
    protected string $tenantColumn;

    // ── Opcionales ──────────────────────────────────────────────────────
    /** Activar soft-delete (en lugar de DELETE físico) */
    protected bool $softDelete = false;

    /** Columna del soft-delete, ej: 'alu_estado' */
    protected string $softDeleteColumn = '';

    /** Valor que indica "activo" en softDeleteColumn */
    protected string $softDeleteActive = 'A';

    /** Valor que indica "eliminado" en softDeleteColumn */
    protected string $softDeleteDeleted = 'E';

    /**
     * Si false, omite el filtro de tenant_id (para tablas globales/catálogos).
     * Usar con precaución.
     */
    protected bool $tenantScoped = true;

    // ────────────────────────────────────────────────────────────────────

    public function __construct() {
        $this->db       = Database::getInstance()->getConnection();
        $this->tenantId = function_exists('getTenantId') ? getTenantId() : null;
    }

    /**
     * Permite inyectar un tenant_id diferente (útil en tareas CLI/background)
     */
    public function setTenantId($tenantId): self {
        $this->tenantId = $tenantId;
        return $this;
    }

    // ════════════════════════════════════════════════════════════════════
    // LECTURA
    // ════════════════════════════════════════════════════════════════════

    /**
     * Obtener un registro por PK (con aislamiento de tenant)
     * @return array|null
     */
    public function find(int $id): ?array {
        $sql    = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $params = [$id];

        if ($this->tenantScoped) {
            $sql      .= " AND {$this->tenantColumn} = ?";
            $params[]  = $this->tenantId;
        }
        if ($this->softDelete && $this->softDeleteColumn) {
            $sql .= " AND {$this->softDeleteColumn} = '{$this->softDeleteActive}'";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    /**
     * Obtener todos los registros con condiciones opcionales
     *
     * @param array  $where    ['columna' => valor] — null usa IS NULL
     * @param string $orderBy  ej: 'alu_apellidos ASC'
     * @param int    $limit    0 = sin límite
     * @param int    $offset
     * @return array
     */
    public function findAll(
        array  $where   = [],
        string $orderBy = '',
        int    $limit   = 0,
        int    $offset  = 0
    ): array {
        [$whereSql, $params] = $this->buildBaseWhere();

        foreach ($where as $col => $val) {
            if ($val === null) {
                $whereSql .= " AND {$col} IS NULL";
            } else {
                $whereSql .= " AND {$col} = ?";
                $params[]  = $val;
            }
        }

        $sql = "SELECT * FROM {$this->table} WHERE {$whereSql}";

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        if ($limit > 0) {
            $sql     .= " LIMIT ?";
            $params[] = $limit;
            if ($offset > 0) {
                $sql     .= " OFFSET ?";
                $params[] = $offset;
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Contar registros con condiciones opcionales
     */
    public function count(array $where = []): int {
        [$whereSql, $params] = $this->buildBaseWhere();

        foreach ($where as $col => $val) {
            if ($val === null) {
                $whereSql .= " AND {$col} IS NULL";
            } else {
                $whereSql .= " AND {$col} = ?";
                $params[]  = $val;
            }
        }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE {$whereSql}"
        );
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Obtener resultados paginados
     *
     * @return array{data: array, total: int, page: int, per_page: int,
     *               total_pages: int, from: int, to: int}
     */
    public function paginate(
        int    $page    = 1,
        int    $perPage = 20,
        array  $where   = [],
        string $orderBy = ''
    ): array {
        $page    = max(1, $page);
        $perPage = min(max(1, $perPage), 100);
        $total   = $this->count($where);
        $offset  = ($page - 1) * $perPage;
        $items   = $this->findAll($where, $orderBy, $perPage, $offset);

        return [
            'data'        => $items,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $total > 0 ? (int) ceil($total / $perPage) : 1,
            'from'        => $total > 0 ? $offset + 1 : 0,
            'to'          => min($offset + $perPage, $total),
        ];
    }

    // ════════════════════════════════════════════════════════════════════
    // ESCRITURA
    // ════════════════════════════════════════════════════════════════════

    /**
     * Insertar un nuevo registro (agrega tenant_id automáticamente)
     * @return int ID del registro creado
     */
    public function create(array $data): int {
        if ($this->tenantScoped) {
            $data[$this->tenantColumn] = $this->tenantId;
        }

        $columns      = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualizar un registro (siempre con tenant guard)
     */
    public function update(int $id, array $data): bool {
        if (empty($data)) {
            return false;
        }

        $setParts = [];
        $params   = [];
        foreach ($data as $col => $val) {
            $setParts[] = "{$col} = ?";
            $params[]   = $val;
        }
        $params[] = $id;

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = ?',
            $this->table,
            implode(', ', $setParts),
            $this->primaryKey
        );

        if ($this->tenantScoped) {
            $sql      .= " AND {$this->tenantColumn} = ?";
            $params[]  = $this->tenantId;
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params) && $stmt->rowCount() > 0;
    }

    /**
     * Eliminar un registro (soft o hard delete según configuración)
     */
    public function delete(int $id): bool {
        if ($this->softDelete && $this->softDeleteColumn) {
            return $this->update($id, [$this->softDeleteColumn => $this->softDeleteDeleted]);
        }

        $sql    = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $params = [$id];

        if ($this->tenantScoped) {
            $sql      .= " AND {$this->tenantColumn} = ?";
            $params[]  = $this->tenantId;
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params) && $stmt->rowCount() > 0;
    }

    // ════════════════════════════════════════════════════════════════════
    // HELPERS PARA QUERIES PERSONALIZADAS
    // ════════════════════════════════════════════════════════════════════

    /**
     * Ejecutar SELECT personalizado (con tenant_id en params si aplica)
     */
    protected function raw(string $sql, array $params = []): array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Ejecutar SELECT que devuelve una sola fila
     */
    protected function rawOne(string $sql, array $params = []): ?array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    /**
     * Ejecutar INSERT/UPDATE/DELETE personalizado, retorna filas afectadas
     */
    protected function exec(string $sql, array $params = []): int {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    // ════════════════════════════════════════════════════════════════════
    // SEGURIDAD / VALIDACIÓN
    // ════════════════════════════════════════════════════════════════════

    /**
     * Verificar que un registro pertenece al tenant actual
     * Usar antes de operaciones sensibles con IDs externos
     */
    public function belongsToTenant(int $id): bool {
        if (!$this->tenantScoped) {
            return true;
        }
        $stmt = $this->db->prepare(
            "SELECT 1 FROM {$this->table} WHERE {$this->primaryKey} = ? AND {$this->tenantColumn} = ? LIMIT 1"
        );
        $stmt->execute([$id, $this->tenantId]);
        return $stmt->fetchColumn() !== false;
    }

    /**
     * Verificar que un valor es único en la tabla (con tenant scope)
     * @param string $column    Columna a verificar
     * @param mixed  $value     Valor a buscar
     * @param int|null $excludeId ID a excluir (para edición)
     */
    public function isUnique(string $column, $value, ?int $excludeId = null): bool {
        $sql    = "SELECT COUNT(*) FROM {$this->table} WHERE {$column} = ?";
        $params = [$value];

        if ($this->tenantScoped) {
            $sql      .= " AND {$this->tenantColumn} = ?";
            $params[]  = $this->tenantId;
        }
        if ($excludeId !== null) {
            $sql      .= " AND {$this->primaryKey} != ?";
            $params[]  = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() === 0;
    }

    // ════════════════════════════════════════════════════════════════════
    // TRANSACCIONES
    // ════════════════════════════════════════════════════════════════════

    protected function beginTransaction(): void {
        $this->db->beginTransaction();
    }

    protected function commit(): void {
        $this->db->commit();
    }

    protected function rollback(): void {
        $this->db->rollBack();
    }

    // ════════════════════════════════════════════════════════════════════
    // PRIVADO
    // ════════════════════════════════════════════════════════════════════

    /**
     * Construir la cláusula WHERE base con tenant_id y soft-delete
     * @return array{0: string, 1: array}
     */
    private function buildBaseWhere(): array {
        $parts  = [];
        $params = [];

        if ($this->tenantScoped) {
            $parts[]  = "{$this->tenantColumn} = ?";
            $params[] = $this->tenantId;
        }

        if ($this->softDelete && $this->softDeleteColumn) {
            $parts[] = "{$this->softDeleteColumn} = '{$this->softDeleteActive}'";
        }

        $sql = empty($parts) ? '1=1' : implode(' AND ', $parts);
        return [$sql, $params];
    }
}
