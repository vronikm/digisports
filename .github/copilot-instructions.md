# 🤖 Instrucciones para Agentes AI en digiSports

## Arquitectura y Componentes Clave
- **MVC estricto**: Usa `app/controllers/`, `app/models/`, `app/views/`.
- **Multi-Tenant**: Cada empresa (tenant) tiene datos aislados. Controla el contexto de tenant en cada operación.
- **Seguridad**: Autenticación, roles, y permisos en controladores. Usa prepared statements y sanitización en modelos.
- **Facturación Electrónica**: Integración con SRI Ecuador en `app/controllers/facturacion/` y helpers asociados.
- **Reportes y Dashboards**: KPIs y gráficos en `app/controllers/reportes/` y vistas en `app/views/dashboard/`.

## Flujos de Trabajo Esenciales
- **Instalación**: Verifica PHP 8.2+, MySQL 8+, Apache 2.4. Usa scripts SQL en `database/` para inicializar.
- **Configuración**: Edita `config/database.php` y otros archivos en `config/` para entornos locales o producción.
- **Logs y almacenamiento**: Usa subcarpetas en `storage/` para logs, caché y archivos subidos.
- **Acceso web**: El entrypoint es `public/index.php`.

## Convenciones y Patrones Específicos
- **Controladores**: Heredan de `BaseController.php`. Métodos públicos = endpoints.
- **Rutas**: Definidas en `config/Router.php`.
- **Vistas**: Usa layouts en `app/views/layouts/`. Fragmentos reutilizables en subcarpetas.
- **Helpers**: Funciones utilitarias en `app/helpers/`.
- **Estados de Factura**: Usa los valores `BORRADOR`, `EMITIDA`, `PAGADA`, `ANULADA`.
- **Usuarios de prueba**: Usa los emails y contraseñas documentados en el README.

## Integraciones y Dependencias
- **Frontend**: Bootstrap 5, FontAwesome, Chart.js, jQuery (ver `public/assets/`).
- **SRI**: Facturación electrónica requiere certificados en `storage/certificados/`.
- **Documentación**: Consulta los archivos `PASO_*` y el README para detalles de cada módulo.

## Ejemplo de flujo típico
1. El usuario inicia sesión (`/public/index.php` → `AuthController`).
2. Se determina el tenant y los permisos.
3. El usuario navega a módulos (instalaciones, facturación, reportes) según su rol.
4. Las acciones CRUD pasan por controladores y modelos, respetando el contexto multi-tenant.
5. Los reportes se generan en controladores de `reportes/` y se muestran en dashboard.

## Archivos y directorios clave
- `app/controllers/`, `app/models/`, `app/views/`
- `config/database.php`, `config/Router.php`, `config/security.php`
- `database/digisports_core.sql`, `database/schema_instalaciones.sql`
- `public/index.php`, `public/assets/`
- `storage/` (logs, uploads, certificados)

---

Para detalles adicionales, revisa el README_PROYECTO.md y la documentación por PASO. Si alguna convención no está clara, pregunta antes de modificar patrones estructurales.