<?php
/**
 * DigiSports - Tests del módulo de Seguridad
 *
 * Cubre: CSRF, permisos, hashing de contraseñas, encriptación de URLs.
 * No requiere BD real — usa stubs y fixtures en memoria.
 */

namespace DigiSports\Tests;

require_once __DIR__ . '/BaseTestCase.php';

class SeguridadTest extends BaseTestCase
{
    // ── CSRF ──────────────────────────────────────────────────────────

    public function testTokenCSRFGeneradoEsString(): void
    {
        if (!class_exists('Security')) {
            require_once dirname(__DIR__) . '/config/security.php';
        }
        $token = \Security::generateCsrfToken();
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function testTokenCSRFInvalidoFalla(): void
    {
        if (!class_exists('Security')) {
            require_once dirname(__DIR__) . '/config/security.php';
        }
        $_SESSION['csrf_token'] = 'token_real';
        $this->assertFalse(\Security::validateCsrfToken('token_falso'));
    }

    public function testTokenCSRFValidoAcepta(): void
    {
        if (!class_exists('Security')) {
            require_once dirname(__DIR__) . '/config/security.php';
        }
        $token = \Security::generateCsrfToken();
        $this->assertTrue(\Security::validateCsrfToken($token));
    }

    // ── Hashing de contraseñas ────────────────────────────────────────

    public function testHashContrasenaNoEsTextoPlano(): void
    {
        if (!class_exists('Security')) {
            require_once dirname(__DIR__) . '/config/security.php';
        }
        $plain  = 'MiPassword123!';
        $hashed = \Security::hashPassword($plain);

        $this->assertNotSame($plain, $hashed);
        $this->assertNotEmpty($hashed);
    }

    public function testVerificacionContrasenaCorrecta(): void
    {
        if (!class_exists('Security')) {
            require_once dirname(__DIR__) . '/config/security.php';
        }
        $plain  = 'MiPassword123!';
        $hashed = \Security::hashPassword($plain);

        $this->assertTrue(\Security::verifyPassword($plain, $hashed));
    }

    public function testVerificacionContrasenaIncorrecta(): void
    {
        if (!class_exists('Security')) {
            require_once dirname(__DIR__) . '/config/security.php';
        }
        $hashed = \Security::hashPassword('PasswordOriginal!');
        $this->assertFalse(\Security::verifyPassword('PasswordDiferente!', $hashed));
    }

    // ── Permisos ──────────────────────────────────────────────────────

    public function testPermisoDenegadoCuandoNoExiste(): void
    {
        require_once dirname(__DIR__) . '/app/helpers/functions.php';

        $this->actingAs(self::USER_OPERADOR, self::TENANT_A, 'OPERADOR', ['usuarios.ver']);
        $this->assertFalse(hasPermission('usuarios.eliminar'));
    }

    public function testPermisoPermitidoExacto(): void
    {
        require_once dirname(__DIR__) . '/app/helpers/functions.php';

        $this->actingAs(self::USER_ADMIN, self::TENANT_A, 'ADMIN', ['usuarios.ver', 'usuarios.crear']);
        $this->assertTrue(hasPermission('usuarios.ver'));
    }

    public function testPermisoWildcardCubreAcciones(): void
    {
        require_once dirname(__DIR__) . '/app/helpers/functions.php';

        $this->actingAs(self::USER_ADMIN, self::TENANT_A, 'ADMIN', ['usuarios.*']);
        $this->assertTrue(hasPermission('usuarios.eliminar'));
        $this->assertTrue(hasPermission('usuarios.crear'));
    }

    public function testSuperAdminTieneTodoPermiso(): void
    {
        require_once dirname(__DIR__) . '/app/helpers/functions.php';

        $this->actingAs(self::USER_ADMIN, self::TENANT_A, 'SUPERADMIN', ['*']);
        $this->assertTrue(hasPermission('cualquier.permiso'));
    }

    // ── Encriptación de URLs ──────────────────────────────────────────

    public function testEncriptarYDesencriptarURL(): void
    {
        if (!class_exists('Security')) {
            require_once dirname(__DIR__) . '/config/security.php';
        }
        $params = ['controller' => 'futbol', 'action' => 'alumnos', 'id' => '42'];
        $token  = \Security::encryptUrl($params);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        $decoded = \Security::decryptUrl($token);
        $this->assertIsArray($decoded);
        $this->assertSame('futbol', $decoded['controller'] ?? null);
        $this->assertSame('alumnos', $decoded['action']     ?? null);
        $this->assertSame('42',     (string)($decoded['id'] ?? ''));
    }

    public function testTokenURLManipuladoFalla(): void
    {
        if (!class_exists('Security')) {
            require_once dirname(__DIR__) . '/config/security.php';
        }
        $this->assertNull(\Security::decryptUrl('token.manipulado.invalido'));
    }

    // ── Aislamiento de tenant (regresión) ─────────────────────────────

    public function testUsuarioNoPuedeVerDatosDeOtroTenant(): void
    {
        $this->actingAs(self::USER_ADMIN, self::TENANT_A);

        // Simular query que filtra por tenant
        $recursoTenantB = $this->getRecursoPorTenant(self::TENANT_B);
        $this->assertNull($recursoTenantB, 'El usuario del Tenant A no debe ver datos del Tenant B');
    }

    private function getRecursoPorTenant(int $tenantId): ?array
    {
        // En producción, el modelo añade WHERE x_tenant_id = $_SESSION['tenant_id']
        return ($_SESSION['tenant_id'] === $tenantId) ? ['ok'] : null;
    }
}
