<?php
/**
 * DigiSports - Configuración de Base de Datos
 * @package DigiSports
 * @author Senior Developer
 * @version 1.0.0
 */

class Database {
    private static $instance = null;
    private $conn;
    
    // Configuración de la base de datos
    private $host = 'localhost';
    private $db_name = 'digisports_core';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    
    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset} COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
            // Log de conexión exitosa
            $this->logConnection('SUCCESS');
            
        } catch(PDOException $e) {
            $this->logConnection('ERROR', $e->getMessage());
            die('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }
    
    /**
     * Patrón Singleton para conexión única
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtener conexión PDO
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Prevenir clonación
     */
    private function __clone() {}
    
    /**
     * Prevenir deserialización
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    /**
     * Registrar intentos de conexión
     */
    private function logConnection($status, $message = '') {
        $logFile = __DIR__ . '/../storage/logs/database_' . date('Y-m-d') . '.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logEntry = sprintf(
            "[%s] %s - %s - IP: %s%s\n",
            date('Y-m-d H:i:s'),
            $status,
            $this->db_name,
            $_SERVER['REMOTE_ADDR'] ?? 'CLI',
            $message ? " - {$message}" : ''
        );
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
    
    /**
     * Ejecutar query preparado con protección SQL Injection
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            $this->logQuery('ERROR', $sql, $e->getMessage());
            throw new Exception("Error en query: " . $e->getMessage());
        }
    }
    
    /**
     * Registrar queries ejecutadas
     */
    private function logQuery($status, $sql, $message = '') {
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            $logFile = __DIR__ . '/../storage/logs/queries_' . date('Y-m-d') . '.log';
            $logEntry = sprintf(
                "[%s] %s - %s%s\n",
                date('Y-m-d H:i:s'),
                $status,
                substr($sql, 0, 200),
                $message ? " - {$message}" : ''
            );
            file_put_contents($logFile, $logEntry, FILE_APPEND);
        }
    }
    
    /**
     * Iniciar transacción
     */
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    /**
     * Confirmar transacción
     */
    public function commit() {
        return $this->conn->commit();
    }
    
    /**
     * Revertir transacción
     */
    public function rollback() {
        return $this->conn->rollback();
    }
    
    /**
     * Obtener último ID insertado
     */
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
}