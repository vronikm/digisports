<?php
/**
 * DigiSports - Modelo: SeguridadTablaCatalogoModel
 * Gestión de ítems de catálogos (seguridad_tabla_catalogo)
 * 
 * @package DigiSports\Models\Seguridad
 */

namespace App\Models\Seguridad;

use PDO;

class SeguridadTablaCatalogoModel {
    
    protected $db;
    protected $tenantId;

    public function __construct($db, $tenantId = 1) {
        $this->db = $db;
        $this->tenantId = $tenantId;
    }

    /**
     * Listar ítems por grupo
     */
    public function listarPorGrupo($grupoId, $filtro = null) {
        try {
            $query = "
                SELECT stc_id, stc_tabla_id, stc_codigo, stc_valor, stc_etiqueta, 
                       stc_orden, stc_activo, stc_created_at
                FROM seguridad_tabla_catalogo
                WHERE stc_tabla_id = ?
            ";
            
            $params = [$grupoId];
            
            if ($filtro) {
                $query .= " AND (stc_codigo LIKE ? OR stc_valor LIKE ? OR stc_etiqueta LIKE ?)";
                $params[] = "%$filtro%";
                $params[] = "%$filtro%";
                $params[] = "%$filtro%";
            }
            
            $query .= " ORDER BY stc_orden ASC, stc_etiqueta ASC";
            
            $stm = $this->db->prepare($query);
            $stm->execute($params);
            
            return $stm->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            throw new \Exception("Error al listar ítems: " . $e->getMessage());
        }
    }

    /**
     * Obtener un ítem por ID
     */
    public function obtener($id) {
        try {
            $stm = $this->db->prepare("
                SELECT * FROM seguridad_tabla_catalogo WHERE stc_id = ?
            ");
            $stm->execute([$id]);
            return $stm->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            throw new \Exception("Error al obtener ítem: " . $e->getMessage());
        }
    }

    /**
     * Crear nuevo ítem
     */
    public function crear($datos) {
        try {
            // Validaciones
            $this->validarDatos($datos);
            
            // Verificar código único dentro del grupo
            if ($this->codigoExisteEnGrupo($datos['stc_tabla_id'], $datos['stc_codigo'])) {
                throw new \Exception("Ya existe un ítem con este código en este catálogo");
            }
            
            // Obtener orden máxima + 1
            $orden = $this->obtenerProximaOrden($datos['stc_tabla_id']);
            
            $stm = $this->db->prepare("
                INSERT INTO seguridad_tabla_catalogo 
                (stc_tabla_id, stc_codigo, stc_valor, stc_etiqueta, stc_orden, stc_activo, stc_created_at, stc_updated_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $stm->execute([
                $datos['stc_tabla_id'],
                $datos['stc_codigo'],
                $datos['stc_valor'],
                $datos['stc_etiqueta'],
                $orden,
                $datos['stc_activo'] ?? 1
            ]);
            
            return [
                'success' => true,
                'id' => $this->db->lastInsertId(),
                'message' => 'Ítem creado correctamente'
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
     * Actualizar ítem
     */
    public function actualizar($id, $datos) {
        try {
            // Obtener registro existente
            $existente = $this->obtener($id);
            if (!$existente) {
                throw new \Exception("Ítem no encontrado");
            }
            
            // Validaciones
            $this->validarDatos($datos);
            
            // Verificar código único (excepto el actual)
            if ($datos['stc_codigo'] !== $existente['stc_codigo'] && $this->codigoExisteEnGrupo($datos['stc_tabla_id'], $datos['stc_codigo'])) {
                throw new \Exception("Ya existe otro ítem con este código");
            }
            
            $stm = $this->db->prepare("
                UPDATE seguridad_tabla_catalogo 
                SET stc_codigo = ?, stc_valor = ?, stc_etiqueta = ?, stc_orden = ?, stc_activo = ?, stc_updated_at = NOW()
                WHERE stc_id = ?
            ");
            
            $stm->execute([
                $datos['stc_codigo'],
                $datos['stc_valor'],
                $datos['stc_etiqueta'],
                $datos['stc_orden'] ?? $existente['stc_orden'],
                $datos['stc_activo'] ?? 1,
                $id
            ]);
            
            return [
                'success' => true,
                'message' => 'Ítem actualizado correctamente'
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
     * Eliminar ítem
     */
    public function eliminar($id) {
        try {
            // Verificar que existe
            $existe = $this->obtener($id);
            if (!$existe) {
                throw new \Exception("Ítem no encontrado");
            }
            
            $stm = $this->db->prepare("DELETE FROM seguridad_tabla_catalogo WHERE stc_id = ?");
            $stm->execute([$id]);
            
            return [
                'success' => true,
                'message' => 'Ítem eliminado correctamente'
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
        if (empty($datos['stc_tabla_id'])) {
            throw new \Exception("ID del catálogo es obligatorio");
        }
        
        if (empty($datos['stc_codigo'])) {
            throw new \Exception("El código es obligatorio");
        }
        
        if (empty($datos['stc_valor'])) {
            throw new \Exception("El valor es obligatorio");
        }
        
        if (empty($datos['stc_etiqueta'])) {
            throw new \Exception("La etiqueta es obligatoria");
        }
        
        if (strlen($datos['stc_codigo']) > 100) {
            throw new \Exception("El código no puede exceder 100 caracteres");
        }
        
        if (strlen($datos['stc_valor']) > 255) {
            throw new \Exception("El valor no puede exceder 255 caracteres");
        }
        
        if (strlen($datos['stc_etiqueta']) > 255) {
            throw new \Exception("La etiqueta no puede exceder 255 caracteres");
        }
        
        if (isset($datos['stc_orden']) && !is_numeric($datos['stc_orden'])) {
            throw new \Exception("El orden debe ser un número");
        }
    }

    /**
     * Verificar si código existe en grupo
     */
    private function codigoExisteEnGrupo($grupoId, $codigo) {
        try {
            $stm = $this->db->prepare("
                SELECT COUNT(*) as total FROM seguridad_tabla_catalogo 
                WHERE stc_tabla_id = ? AND stc_codigo = ?
            ");
            $stm->execute([$grupoId, $codigo]);
            $resultado = $stm->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener próxima orden
     */
    private function obtenerProximaOrden($grupoId) {
        try {
            $stm = $this->db->prepare("
                SELECT MAX(stc_orden) as max_orden FROM seguridad_tabla_catalogo WHERE stc_tabla_id = ?
            ");
            $stm->execute([$grupoId]);
            $resultado = $stm->fetch(PDO::FETCH_ASSOC);
            return ($resultado['max_orden'] ?? 0) + 10;
        } catch (\Exception $e) {
            return 10;
        }
    }
}
