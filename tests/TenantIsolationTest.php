<?php
/**
 * DigiSports - Tests de aislamiento multi-tenant
 *
 * Verifica que ningún usuario pueda acceder a datos de otro tenant,
 * sin necesidad de BD real (usa PDO stubs).
 */

namespace DigiSports\Tests;

require_once __DIR__ . '/BaseTestCase.php';

class TenantIsolationTest extends BaseTestCase
{
    // ── Aislamiento de sesión ─────────────────────────────────────────

    public function testSesionContieneCorrectamenteTenantId(): void
    {
        $this->actingAs(self::USER_ADMIN, self::TENANT_A);

        $this->assertSame(self::TENANT_A, $_SESSION['tenant_id']);
        $this->assertSame(self::USER_ADMIN, $_SESSION['user_id']);
    }

    public function testUsuarioNoVeDatosDeOtroTenant(): void
    {
        $this->actingAs(self::USER_ADMIN, self::TENANT_A);

        // Simular resultado de consulta que respeta WHERE ten_id = ?
        $rows = [['id' => 1, 'ten_id' => self::TENANT_B, 'nombre' => 'Dato Ajeno']];

        // El test verifica que assertAllBelongToTenant detecta la violación
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->assertAllBelongToTenant($rows, 'ten_id', self::TENANT_A);
    }

    public function testFiltradoCorrectoEnMismoTenant(): void
    {
        $this->actingAs(self::USER_ADMIN, self::TENANT_A);

        $rows = [
            ['id' => 1, 'alu_tenant_id' => self::TENANT_A, 'nombre' => 'Alumno 1'],
            ['id' => 2, 'alu_tenant_id' => self::TENANT_A, 'nombre' => 'Alumno 2'],
        ];

        // Debe pasar sin excepciones
        $this->assertAllBelongToTenant($rows, 'alu_tenant_id', self::TENANT_A);
        $this->assertCount(2, $rows);
    }

    // ── Permisos ──────────────────────────────────────────────────────

    public function testSinPermisosNoTieneAcceso(): void
    {
        $this->actingAs(self::USER_OPERADOR, self::TENANT_A, 'OPERADOR', []);

        $permissions = $_SESSION['permissions'];
        $this->assertNotContains('usuarios.eliminar', $permissions);
    }

    public function testConPermisoExplicito(): void
    {
        $this->actingAs(self::USER_ADMIN, self::TENANT_A, 'ADMIN', ['usuarios.ver', 'usuarios.crear']);

        $this->assertContains('usuarios.ver', $_SESSION['permissions']);
        $this->assertContains('usuarios.crear', $_SESSION['permissions']);
        $this->assertNotContains('usuarios.eliminar', $_SESSION['permissions']);
    }

    public function testWithPermissionsAgrega(): void
    {
        $this->actingAs(self::USER_ADMIN, self::TENANT_A, 'ADMIN', ['usuarios.ver']);
        $this->withPermissions(['usuarios.eliminar']);

        $this->assertContains('usuarios.eliminar', $_SESSION['permissions']);
        // Mantiene los previos
        $this->assertContains('usuarios.ver', $_SESSION['permissions']);
    }

    // ── Stub de PDO ───────────────────────────────────────────────────

    public function testPdoStubDevuelveFilasEsperadas(): void
    {
        $rows = [
            ['id' => 1, 'nombre' => 'Registro A'],
            ['id' => 2, 'nombre' => 'Registro B'],
        ];

        $pdo  = $this->makePdoStub($rows);
        $stmt = $pdo->prepare('SELECT * FROM tabla WHERE id = ?');
        $stmt->execute([1]);

        $result = $stmt->fetchAll();
        $this->assertCount(2, $result);
        $this->assertSame('Registro A', $result[0]['nombre']);
    }

    public function testPdoStubSinFilasDevuelveVacio(): void
    {
        $pdo  = $this->makePdoStub([]);
        $stmt = $pdo->prepare('SELECT * FROM tabla WHERE id = ?');
        $stmt->execute([999]);

        $this->assertFalse($stmt->fetch());
        $this->assertSame(0, $stmt->rowCount());
    }

    // ── Limpieza entre tests ──────────────────────────────────────────

    public function testSesionLimpiaEntreTests(): void
    {
        // Si el test anterior dejó datos, este falla — verifica setUp()
        $this->assertArrayNotHasKey('user_id', $_SESSION);
        $this->assertArrayNotHasKey('tenant_id', $_SESSION);
    }
}
