## Mecanismo de protección y validación de rutas (enero-febrero 2026)

**Resumen:**
Todas las rutas internas del sistema DigiSports se generan mediante el helper `url()`, que encripta los parámetros (módulo, controlador, acción, parámetros, timestamp) usando AES-256-GCM. El backend solo acepta rutas válidas desencriptadas y verifica:

- Que la desencriptación sea exitosa y el timestamp no esté expirado.
- Que el módulo exista y esté habilitado.
- Que el archivo del controlador exista.
- Que la acción sea válida (solo letras, números y guiones bajos).
- Si alguna validación falla, se registra el intento y se redirige a error.

**Refuerzo aplicado (03/02/2026):**
- Se añadieron validaciones estrictas en el router para evitar acceso a módulos/controladores/acciones no permitidos, incluso si se manipula el parámetro `r`.
- Todos los intentos inválidos quedan registrados en los logs de seguridad.

**Recomendación:**
No usar rutas hardcodeadas ni manipular el parámetro `r` manualmente. Usar siempre el helper `url()` para máxima seguridad y trazabilidad.
# Auditoría y Diseño de Mejoras de Seguridad y Arquitectura

Fecha: 3 de febrero de 2026

## Resumen del contexto
DigiSports es una plataforma integral multi-tenant para la gestión de centros deportivos, con subsistemas independientes, control de acceso por roles/permisos, menús dinámicos y seguridad avanzada. El sistema debe garantizar aislamiento de datos, navegación fluida, integración SSO y cumplimiento de buenas prácticas OWASP.

---

## Auditoría de lo implementado

### 1. Middleware de seguridad centralizado
- Existe un `BaseController` que inicializa sesión, usuario y tenant.
- Helpers globales (`isAuthenticated`, `isAdmin`, `isSuperAdmin`, etc.) permiten validar sesión y roles.
- Permisos y acceso a módulos se verifican con `hasPermission($permiso)` y `hasModuleAccess($modulo)`.
- **Faltante:** No hay método centralizado tipo middleware para validar permisos por acción/recurso antes de ejecutar lógica de negocio.

### 2. Gestión de sesiones y SSO
- Helpers para gestión de sesión y usuario.
- Variables de sesión para usuario, tenant, rol, permisos y módulos.
- **Faltante:** No se observa lógica específica para SSO entre sistemas legacy y nuevos.

### 3. Auditoría y logs centralizados
- Función `registrarAuditoria` en controladores registra acciones en la tabla `auditoria_acciones`.
- Función `logMessage` para logs personalizados en archivos.
- **Faltante:** Unificar logs de acceso, cambios críticos y eventos de seguridad en una sola tabla/dashboard.

### 4. Gestión visual de menús
- Menús laterales y módulos activos se generan dinámicamente según tenant y módulos contratados.
- **Faltante:** Falta generador visual de menús basado en matriz de permisos y suscripción, editable desde el panel de admin.

### 5. Pruebas automáticas de seguridad
- Validaciones de email, RUC, cédula, sanitización de salidas, uso de prepared statements.
- **Faltante:** No se observan scripts de testing automatizados para validar aislamiento de tenants, escalabilidad y protección OWASP.

---

## Diseño propuesto para mejoras

### 1. Middleware de Seguridad Centralizado
- Método `authorize($action, $resource)` en BaseController.
- Llamar a este método al inicio de cada acción sensible.

### 2. Gestión de Sesiones y SSO
- Helper `initSSOSession($userData)` para variables de sesión compartidas.
- Lógica de redirección automática al acceder a sistemas externos.

### 3. Auditoría y Logs Centralizados
- Extender `registrarAuditoria` para registrar cualquier acceso/cambio relevante.
- Dashboard de auditoría para admins/superadmins.

### 4. Gestión Visual de Menús
- Tabla `menu_config` para definir opciones, iconos, colores y permisos de menú.
- Generador de menú lateral según usuario, rol y módulos activos.
- Panel de administración para editar la configuración visual y lógica de los menús.

### 5. Pruebas Automáticas de Seguridad
- Implementar pruebas unitarias y de integración para aislamiento de tenants, permisos y protección OWASP.
- Scripts de testing automatizados (PHPUnit, Selenium, etc).

---

## Avance de implementación (actualización 3 de febrero de 2026)

### 1. Middleware de seguridad centralizado
- Implementado método `authorize()` en BaseController.
- Integrado en todos los controladores principales del módulo de seguridad.

### 2. Gestión visual de menús
- Creado helper `getDynamicMenu()` y tabla `menu_config`.
- Menú lateral dinámico integrado en BaseController y disponible en todas las vistas.

### 3. Gestión de sesiones y SSO
- Helper `initSSOSession()` y `ssoRedirect()` implementados para SSO entre sistemas y subsistemas.
- Listo para integración con sistemas legacy y externos.

### 4. Pruebas automáticas de seguridad
- Carpeta `tests/` creada con archivo `SeguridadTest.php` (PHPUnit).
- Pruebas de aislamiento de tenants, permisos y CSRF documentadas y listas para ejecución.

---

## Siguientes pasos sugeridos
1. Implementar middleware de autorización y validación de permisos.
2. Desarrollar helper y lógica de SSO para integración transparente.
3. Unificar y visualizar logs/auditoría en dashboard.
4. Desarrollar generador visual de menús y panel de configuración.
5. Crear y ejecutar pruebas automáticas de seguridad.

---

Este documento debe ser actualizado conforme avance la implementación y validación de cada mejora.
