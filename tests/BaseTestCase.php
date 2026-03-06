<?php
/**
 * DigiSports - Caso base para todos los tests
 *
 * Bootstrapea el entorno sin HTTP, proporciona helpers de tenant isolation
 * y un stub de base de datos para tests unitarios sin BD real.
 *
 * Uso:
 *   class MiTest extends BaseTestCase { ... }
 *
 * Para correr:
 *   vendor/bin/phpunit tests/
 */

namespace DigiSports\Tests;

use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    // ── Constantes de fixtures ────────────────────────────────────────

    protected const TENANT_A = 1;
    protected const TENANT_B = 2;
    protected const USER_ADMIN    = 10;
    protected const USER_OPERADOR = 20;

    // ── Bootstrap ────────────────────────────────────────────────────

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Definir constante BASE_PATH si no está definida (tests sin index.php)
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__));
        }
        if (!defined('CONFIG_PATH')) {
            define('CONFIG_PATH', BASE_PATH . '/config');
        }

        // Cargar .env para que Config:: y env() funcionen
        $envFile = BASE_PATH . '/.env';
        if (file_exists($envFile)) {
            require_once CONFIG_PATH . '/env.php';
        }

        // Arrancar sesión PHP si no está activa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Sesión limpia entre tests
        $_SESSION = [];
        $_POST    = [];
        $_GET     = [];
        $_SERVER['REMOTE_ADDR']     = '127.0.0.1';
        $_SERVER['REQUEST_METHOD']  = 'GET';
        $_SERVER['REQUEST_URI']     = '/test';
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit';
    }

    // ── Helpers de sesión ─────────────────────────────────────────────

    /**
     * Simular sesión de un usuario autenticado en el tenant dado.
     */
    protected function actingAs(
        int $userId,
        int $tenantId,
        string $role = 'ADMIN',
        array $permissions = []
    ): void {
        $_SESSION['user_id']      = $userId;
        $_SESSION['tenant_id']    = $tenantId;
        $_SESSION['role']         = $role;
        $_SESSION['username']     = "user_{$userId}";
        $_SESSION['nombres']      = 'Test';
        $_SESSION['apellidos']    = 'User';
        $_SESSION['email']        = "user{$userId}@test.com";
        $_SESSION['rol_id']       = 1;
        $_SESSION['nivel_acceso'] = 5;
        $_SESSION['permissions']  = $permissions;
        $_SESSION['tenant_nombre']= "Tenant {$tenantId}";
    }

    /**
     * Simular que el usuario tiene los permisos listados.
     */
    protected function withPermissions(array $permissions): void
    {
        $_SESSION['permissions'] = array_merge($_SESSION['permissions'] ?? [], $permissions);
    }

    // ── Helpers de aserción ───────────────────────────────────────────

    /**
     * Verificar que un array de registros pertenece exclusivamente al tenant esperado.
     */
    protected function assertAllBelongToTenant(array $rows, string $tenantColumn, int $expectedTenantId): void
    {
        foreach ($rows as $row) {
            $this->assertArrayHasKey(
                $tenantColumn,
                $row,
                "La fila no tiene columna '{$tenantColumn}'"
            );
            $this->assertSame(
                $expectedTenantId,
                (int)$row[$tenantColumn],
                "Filtrado de tenant incorrecto: esperado {$expectedTenantId}, obtenido {$row[$tenantColumn]}"
            );
        }
    }

    /**
     * Construir un stub PDO que devuelve $rows en fetchAll().
     * Útil para testear modelos sin BD real.
     */
    protected function makePdoStub(array $rows = []): \PDO
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchAll')->willReturn($rows);
        $stmt->method('fetch')->willReturn($rows[0] ?? false);
        $stmt->method('rowCount')->willReturn(count($rows));

        $pdo = $this->createMock(\PDO::class);
        $pdo->method('prepare')->willReturn($stmt);
        $pdo->method('lastInsertId')->willReturn('1');
        $pdo->method('beginTransaction')->willReturn(true);
        $pdo->method('commit')->willReturn(true);
        $pdo->method('rollBack')->willReturn(true);

        return $pdo;
    }
}
