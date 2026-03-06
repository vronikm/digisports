<?php
/**
 * DigiSports - Modelo: SeguridadTablaModel
 * Gestión de grupos de catálogos (seguridad_tabla)
 * 
 * @package DigiSports\Models\Seguridad
 */

namespace App\Models\Seguridad;

use PDO;

class SeguridadTablaModel {
    
    protected $db;
    protected $tenantId;

    public function __construct($db, $tenantId = 1) {
        $this->db = $db;
        $this->tenantId = $tenantId;
    }

    /**
     * Listar todos los grupos de catálogos
     */
    public function listar($filtro = null) {
        try {
            $query = "
                SELECT st_id, st_nombre, st_descripcion, st_activo, st_created_at,
                       (SELECT COUNT(*) FROM seguridad_tabla_catalogo WHERE stc_tabla_id = st_id) as cantidad_items
                FROM seguridad_tabla
                WHERE 1=1
            ";
            
            $params = [];
            
            if ($filtro) {
                $query .= " AND (st_nombre LIKE ? OR st_descripcion LIKE ?)";
                $params[] = "%$filtro%";
                $params[] = "%$filtro%";
            }
            
            $query .= " ORDER BY st_nombre ASC";
            
            $stm = $this->db->prepare($query);
            $stm->execute($params);
            
            return $stm->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            throw new \Exception("Error al listar catálogos: " . $e->getMessage());
        }
    }

    /**
     * Obtener un grupo por ID
     */
    public function obtener($id) {
        try {
            $stm = $this->db->prepare("
                SELECT * FROM seguridad_tabla WHERE st_id = ?
            ");
            $stm->execute([$id]);
            return $stm->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            throw new \Exception("Error al obtener catálogo: " . $e->getMessage());
        }
    }

    /**
     * Crear nuevo grupo
     */
    public function crear($datos) {
        try {
            // Validaciones
            $this->validarDatos($datos);
            
            // Verificar nombre único
            if ($this->nombreExiste($datos['st_nombre'])) {
                throw new \Exception("Ya existe un catálogo con este nombre");
            }
            
            $stm = $this->db->prepare("
                INSERT INTO seguridad_tabla 
                (st_nombre, st_descripcion, st_activo, st_created_at, st_updated_at)
                VALUES (?, ?, ?, NOW(), NOW())
            ");
            
            $stm->execute([
                $datos['st_nombre'],
                $datos['st_descripcion'] ?? null,
                $datos['st_activo'] ?? 1
            ]);
            
            return [
                'success' => true,
                'id' => $this->db->lastInsertId(),
                'message' => 'Catálogo creado correctamente'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'CREATE_ERROR'
            ];
        }
    }

    /**
     * Actualizar grupo
     */
    public function actualizar($id, $datos) {
        try {
            // Obtener registro existente
            $existente = $this->obtener($id);
            if (!$existente) {
                throw new \Exception("Catálogo no encontrado");
            }
            
            // Validaciones
            $this->validarDatos($datos);
            
            // Verificar nombre único (excepto el actual)
            if ($datos['st_nombre'] !== $existente['st_nombre'] && $this->nombreExiste($datos['st_nombre'])) {
                throw new \Exception("Already exists a catalog with this name");
            }
            
            $stm = $this->db->prepare("
                UPDATE seguridad_tabla 
                SET st_nombre = ?, st_descripcion = ?, st_activo = ?, st_updated_at = NOW()
                WHERE st_id = ?
            ");
            
            $stm->execute([
                $datos['st_nombre'],
                $datos['st_descripcion'] ?? null,
                $datos['st_activo'] ?? 1,
                $id
            ]);
            
            return [
                'success' => true,
                'message' => 'Catálogo actualizado correctamente'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'UPDATE_ERROR'
            ];
        }
    }

    /**
     * Eliminar grupo (solo si no tiene ítems)
     */
    public function eliminar($id) {
        try {
            // Verificar que existe
            $existe = $this->obtener($id);
            if (!$existe) {
                throw new \Exception("Catálogo no encontrado");
            }
            
            // Verificar que no tiene ítems asociados
            $stm = $this->db->prepare("
                SELECT COUNT(*) as total FROM seguridad_tabla_catalogo WHERE stc_tabla_id = ?
            ");
            $stm->execute([$id]);
            $resultado = $stm->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado['total'] > 0) {
                throw new \Exception("No puedes eliminar este catálogo: tiene {$resultado['total']} ítems asociados");
            }
            
            $stm = $this->db->prepare("DELETE FROM seguridad_tabla WHERE st_id = ?");
            $stm->execute([$id]);
            
            return [
                'success' => true,
                'message' => 'Catálogo eliminado correctamente'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'DELETE_ERROR'
            ];
        }
    }

    /**
     * Validaciones de datos
     */
    private function validarDatos($datos) {
        if (empty($datos['st_nombre'])) {
            throw new \Exception("El nombre del catálogo es obligatorio");
        }
        
        if (strlen($datos['st_nombre']) > 255) {
            throw new \Exception("El nombre no puede exceder 255 caracteres");
        }
        
        if (strlen($datos['st_descripcion'] ?? '') > 500) {
            throw new \Exception("La descripción no puede exceder 500 caracteres");
        }
    }

    /**
     * Verificar si nombre existe
     */
    private function nombreExiste($nombre) {
        try {
            $stm = $this->db->prepare("
                SELECT COUNT(*) as total FROM seguridad_tabla WHERE st_nombre = ?
            ");
            $stm->execute([$nombre]);
            $resultado = $stm->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener cantidad de ítems por grupo
     */
    public function contarItems($id) {
        try {
            $stm = $this->db->prepare("
                SELECT COUNT(*) as total FROM seguridad_tabla_catalogo WHERE stc_tabla_id = ?
            ");
            $stm->execute([$id]);
            $resultado = $stm->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
