# 🤖 Instrucciones para Agentes AI en digiSports

## Arquitectura General
- **MVC estricto**: Usa `app/controllers/`, `app/models/`, `app/views/` para separar lógica, datos y presentación.
- **Multi-Tenant**: Cada tenant (empresa) tiene datos aislados. Toda operación debe respetar el contexto del tenant.
- **Seguridad**: Autenticación, roles y permisos gestionados en controladores. Sanitiza entradas y usa prepared statements en modelos.
- **Facturación Electrónica**: Integración con SRI Ecuador en `app/controllers/facturacion/` y helpers asociados.
- **Reportes y Dashboards**: KPIs y gráficos en `app/controllers/reportes/` y vistas en `app/views/dashboard/`.

## Flujos de Trabajo Esenciales
- **Instalación**: Requiere PHP 8.2+, MySQL 8+, Apache 2.4. Inicializa con scripts SQL en `database/`.
- **Configuración**: Edita `config/database.php` y otros archivos en `config/` según el entorno.
- **Logs y archivos**: Usa subcarpetas en `storage/` para logs, caché y archivos subidos.
- **Acceso web**: El entrypoint es `public/index.php`.

## Convenciones y Patrones Específicos
- **Controladores**: Heredan de `BaseController.php`. Métodos públicos = endpoints. Ejemplo: `app/controllers/FacturacionController.php`.
- **Rutas**: Definidas en `config/Router.php`.
- **Vistas**: Usa layouts en `app/views/layouts/`. Fragmentos reutilizables en subcarpetas.
- **Helpers**: Funciones utilitarias en `app/helpers/`.
- **Estados de Factura**: Usa los valores `BORRADOR`, `EMITIDA`, `PAGADA`, `ANULADA` (ver modelos de facturación).
- **Usuarios de prueba**: Emails y contraseñas documentados en el README.

## Integraciones y Dependencias
- **Frontend**: Bootstrap 5, FontAwesome, Chart.js, jQuery (ver `public/assets/`).
- **SRI**: Certificados digitales en `storage/certificados/` para facturación electrónica.
- **Documentación**: Archivos `PASO_*` y `README_PROYECTO.md` explican módulos y flujos.

## Ejemplo de Flujo Típico
1. Usuario inicia sesión (`public/index.php` → `AuthController`).
2. Se determina el tenant y permisos.
3. Navegación por módulos según rol (instalaciones, facturación, reportes).
4. Acciones CRUD pasan por controladores/modelos, siempre respetando el contexto multi-tenant.
5. Reportes generados en controladores de `reportes/` y mostrados en dashboard.

## Archivos y Directorios Clave
- `app/controllers/`, `app/models/`, `app/views/`
- `config/database.php`, `config/Router.php`, `config/security.php`
- `database/digisports_core.sql`, `database/schema_instalaciones.sql`
- `public/index.php`, `public/assets/`
- `storage/` (logs, uploads, certificados)

---

Para detalles adicionales, revisa `README_PROYECTO.md` y la documentación por PASO. Si alguna convención no está clara, pregunta antes de modificar patrones estructurales. Si implementas lógica multi-tenant, seguridad o facturación, revisa los controladores y helpers asociados antes de modificar.