📋 Flujo Funcional del Módulo Seguridad – DigiSports
1. Acceso y Autenticación
- Login: El usuario accede vía login seguro. Se valida usuario, contraseña y, si aplica, 2FA.
- Gestión de sesión: Al iniciar sesión, se registra el acceso en la tabla log_accesos (tipo LOGIN_OK o LOGIN_FAILED).
- Logout: El usuario puede cerrar sesión desde cualquier vista.

2. Menú y Navegación
- Menú lateral: Incluye accesos a:
    . Dashboard (panel principal de Seguridad)
    . Módulos del Sistema
    . Roles
    . Usuarios
    . Asignación de módulos a tenants
    . Configuración (opciones del módulo Seguridad)
- Branding: El menú y los colores siempre reflejan el branding de Seguridad.

3. Dashboard de Seguridad
- KPIs visuales: Se muestran tarjetas con:
    . Tenants activos
    . Usuarios activos
    . Módulos activos
    . Roles activos
    . Por vencer (suscripciones)Activos
    . Logs Hoy (accesos registrados)
    . Logins Fallidos Hoy (intentos de login fallidos)
    . Actividad reciente: Últimos accesos y alertas de seguridad.

4. Gestión de Usuarios
- Listado, creación, edición y eliminación de usuarios.
- Asignación de roles y permisos.
- Validación de datos y feedback visual (SweetAlert2).
- Registro de auditoría en cada acción relevante.

5. Gestión de Roles y Permisos
- Listado, creación, edición y eliminación de roles.
- Asignación granular de permisos a cada rol.
- Vista de permisos por módulo y acción.
- Auditoría de cambios en roles y permisos.

6. Gestión de Módulos del Sistema
- Listado de módulos activos, edición y creación.
- Asignación de icono, color y descripción.
- Visualización de módulos por tenant.
- Auditoría de cambios en módulos.

7. Asignación de Módulos a Tenants
- Vista para asignar/quitar módulos a cada tenant.
- Edición masiva y feedback inmediato.
- Auditoría de asignaciones.

8. Configuración
- Pantalla de configuración del módulo Seguridad.
- Opciones para ajustes avanzados (puedes personalizar según tus necesidades).

9. Auditoría y Logs
- Registro de todas las acciones críticas en auditoria_acciones.
- Registro de accesos y logins en log_accesos.
- Visualización de logs y alertas desde el dashboard.

✅ Pruebas Funcionales Sugeridas
- Login/Logout: Probar acceso correcto y fallido, verificar registro en log_accesos.
- Dashboard: Validar que los KPIs muestran datos reales y actualizados.
- Usuarios: Crear, editar, eliminar usuarios y asignar roles. Verificar feedback y auditoría.
- Roles: Crear, editar, eliminar roles y asignar permisos. Validar cambios reflejados en la UI y auditoría.
- Módulos: Crear, editar, eliminar módulos. Verificar visualización y auditoría.
- Asignación: Asignar y quitar módulos a tenants. Validar cambios y feedback.
- Configuración: Acceder y modificar opciones (si aplica).
- Logs: Revisar que los logs de accesos y auditoría se registren y visualicen correctamente.
- Branding: Confirmar que el menú, colores y branding de Seguridad se mantienen en todas las vistas.
- Errores: Forzar errores (por ejemplo, login fallido) y validar que se registran y muestran correctamente.


Checklist de Validación Módulo Seguridad
1. Modernización y UX
 ✓ Todas las vistas usan SweetAlert2 para feedback (éxito, error, confirmación).
 ✓ Los formularios y acciones CRUD funcionan vía AJAX sin recargar la página.
 ✓ Los mensajes de error y éxito se muestran correctamente y desaparecen tras unos segundos.
 ✓ El diseño y colores corresponden al branding del módulo Seguridad.

2. Notificaciones y Correos Masivos
 ✓ El envío de notificaciones masivas por correo funciona y llega a todos los estinatarios.
 ✓ El sistema usa la configuración SMTP centralizada (config/smtp.php).
 ✓ Los logs de envío se registran en storage/logs y pueden ser consultados.
 ✓ Los errores de envío se muestran con SweetAlert2 y se loguean.

3. Configuración Centralizada
 ✓ La conexión a la base de datos usa database.php y el singleton PDO.
 ✓ El sistema utiliza siempre la base digisports_core.
 ✓ Los parámetros SMTP y de seguridad se leen correctamente desde smtp.php y config/security.php.

4. Gestión de Usuarios y Roles
 ✓ El CRUD de usuarios funciona sin errores ni pantallas en blanco.
 ✓ El CRUD de roles y permisos funciona y refleja los cambios en la base de datos.
 ✓ Los formularios validan los datos y muestran errores claros.
 ✓ El menú y sidebar muestran los módulos y opciones según el rol del usuario.

5. Branding y Plantillas
 ✓ Todas las vistas del módulo Seguridad usan layouts/module.php y muestran el ✓ color, icono y nombre correctos.
 ✓ El menú lateral y el header muestran el branding del módulo Seguridad.
 ✓ No hay inconsistencias de colores, iconos o nombres en las vistas.

6. KPIs y Dashboard
 ✓ El dashboard muestra todos los KPIs: tenants_activos, usuarios_activos, modulos_activos, roles_activos, por_vencer, logs_hoy, login_fallidos_hoy.
 ✓ Los KPIs reflejan datos reales y actualizados de la base digisports_core.
 ✓ El KPI “Logins Fallidos Hoy” se calcula correctamente usando la tabla log_accesos.
 ✓ Los KPIs muestran valores correctos incluso con datos edge-case (sin registros, muchos registros, etc.).

7. Seguridad y Permisos
 ✓ Los endpoints de los controladores validan autenticación y permisos antes de ejecutar acciones.
 ✓ Los roles y permisos se aplican correctamente en todas las vistas y acciones.
 ✓ No se puede acceder a vistas o acciones restringidas sin el rol adecuado.

8. Logs y Auditoría
 ✓ Todos los eventos importantes (login, cambios de usuario/rol, errores) se registran en storage/logs.
 ✓ Los logs pueden ser consultados y filtran por fecha y tipo de evento.
 ✓ No hay errores PHP en los logs durante las pruebas.

9. Integración y Multi-Tenant
 ✓ El contexto de tenant se respeta en todas las operaciones y vistas.
 ✓ Los datos de cada empresa están aislados y no se mezclan.
 ✓ El sistema permite cambiar de tenant y actualiza el menú y KPIs correctamente.

10. Validación Final
 ✓ No hay errores ni warnings PHP en ninguna vista o acción.
 ✓ Todas las rutas funcionan y muestran la vista esperada.
 ✓ El sistema está listo para producción según el checklist anterior.