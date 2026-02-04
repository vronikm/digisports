# Pruebas automáticas de seguridad y acceso

## Objetivo
Validar aislamiento de tenants, control de permisos y protección ante ataques comunes (OWASP).

## Ejemplo de pruebas (PHPUnit)

```
class SeguridadTest extends \PHPUnit\Framework\TestCase {
    public function testUsuarioNoPuedeVerDatosDeOtroTenant() {
        // Simular login con tenant A
        $_SESSION['tenant_id'] = 1;
        $_SESSION['role'] = 'ADMIN';
        // Intentar acceder a recurso de tenant B
        $recurso = obtenerRecursoPorTenant(2); // Debe retornar null o error
        $this->assertNull($recurso);
    }

    public function testPermisoDenegado() {
        $_SESSION['permissions'] = ['usuarios.ver'];
        $this->assertFalse(hasPermission('usuarios.eliminar'));
    }

    public function testProteccionCSRF() {
        // Simular petición sin token válido
        $_POST['csrf_token'] = 'invalido';
        $this->assertFalse(validarCSRFToken($_POST['csrf_token']));
    }
}
```

## Sugerencia
- Implementar estos tests en una carpeta `tests/` y ejecutarlos en cada despliegue.
- Añadir pruebas de XSS, SQLi y acceso concurrente.
