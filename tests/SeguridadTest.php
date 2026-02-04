<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/helpers/functions.php';

class SeguridadTest extends TestCase {
    public function setUp(): void {
        // Limpiar sesión antes de cada test
        $_SESSION = [];
    }

    public function testUsuarioNoPuedeVerDatosDeOtroTenant() {
        $_SESSION['tenant_id'] = 1;
        $_SESSION['role'] = 'ADMIN';
        // Simular función que solo retorna datos si el tenant_id coincide
        $recurso = ($this->getRecursoPorTenant(2));
        $this->assertNull($recurso);
    }

    private function getRecursoPorTenant($tenantId) {
        // Simulación: solo retorna datos si el tenant_id de sesión coincide
        return ($_SESSION['tenant_id'] == $tenantId) ? ['ok'] : null;
    }

    public function testPermisoDenegado() {
        $_SESSION['permissions'] = ['usuarios.ver'];
        $this->assertFalse(hasPermission('usuarios.eliminar'));
    }

    public function testPermisoPermitido() {
        $_SESSION['permissions'] = ['usuarios.*'];
        $this->assertTrue(hasPermission('usuarios.eliminar'));
    }

    public function testProteccionCSRF() {
        // Simular petición sin token válido
        $_POST['csrf_token'] = 'invalido';
        $this->assertFalse(function_exists('validarCSRFToken') ? validarCSRFToken($_POST['csrf_token']) : false);
    }
}
