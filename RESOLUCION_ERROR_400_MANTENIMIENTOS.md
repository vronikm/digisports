# RESOLUCIÓN COMPLETA: Error "Error al cargar los mantenimientos" - 400

## RESUMEN DEL PROBLEMA

El sistema DigiSports fue actualizado con un nuevo esquema de base de datos donde las tablas ahora usan prefijos:
- `seguridad_usuarios` (en lugar de `usuarios`)
- `seguridad_roles` (en lugar de `roles`)
- `instalaciones_mantenimientos` (en lugar de `mantenimientos`)

Los controladores aún se referían a los nombres antiguos, causando errores 400 en múltiples módulos.

---

## CORRECCIONES APLICADAS

### ✅ 1. MantenimientoController.php
**Ubicación:** `app/controllers/instalaciones/MantenimientoController.php`

Cambios:
- `usuarios` → `seguridad_usuarios`
- `roles` → `seguridad_roles`
- Columnas actualizadas con prefijos `usu_*` y `rol_*`
- **3 métodos corregidos:** index(), ver(), crear()

### ✅ 2. FacturaController.php
**Ubicación:** `app/controllers/facturacion/FacturaController.php`

Cambios:
- `usuarios` → `seguridad_usuarios`
- Columna `u.nombre` → `CONCAT(u.usu_nombres, ' ', u.usu_apellidos)`
- **1 método corregido:** ver()

### ✅ 3. TenantController.php
**Ubicación:** `app/controllers/core/TenantController.php`

Cambios:
- `usuarios` → `seguridad_usuarios`
- Columnas actualizadas con prefijos `usu_*`
- **1 método corregido:** listTenants()

### ✅ 4. DashboardController.php (core)
**Ubicación:** `app/controllers/core/DashboardController.php`

Cambios:
- `usuarios` → `seguridad_usuarios`
- Columnas actualizadas con prefijos `usu_*`
- **1 método corregido:** getQuickStats()

### ✅ 5. AuthController.php
**Ubicación:** `app/controllers/core/AuthController.php`

Cambios (6 ubicaciones):
- `usuarios` → `seguridad_usuarios`
- `roles` → `seguridad_roles`
- Todas las columnas actualizadas con prefijos correctos
- **Métodos corregidos:** forgetPassword(), resetPassword(), validateReset(), register(), changePassword()

---

## PASO SIGUIENTE: CREAR VISTAS DE COMPATIBILIDAD

⚠️ **CRÍTICO**: Las vistas de compatibilidad deben crearse en la base de datos.

### Opción A: URL automática (RECOMENDADO)

Accede a tu navegador:

```
http://localhost/digisports/public/setup_views.php
```

Espera a ver el mensaje: **"✓ Todas las vistas se crearon correctamente."**

### Opción B: Manual (SQL directo)

Si eres administrador de BD, ejecuta manualmente:

```sql
-- Vista: usuarios → seguridad_usuarios
DROP VIEW IF EXISTS usuarios;
CREATE VIEW usuarios AS
SELECT
    usu_usuario_id      AS usuario_id,
    usu_tenant_id       AS tenant_id,
    usu_nombres         AS nombres,
    usu_apellidos       AS apellidos,
    usu_email           AS email,
    usu_username        AS username,
    usu_rol_id          AS rol_id,
    usu_estado          AS estado
FROM seguridad_usuarios;

-- Vista: roles → seguridad_roles
DROP VIEW IF EXISTS roles;
CREATE VIEW roles AS
SELECT
    rol_rol_id          AS rol_id,
    rol_codigo          AS codigo,
    rol_nombre          AS nombre,
    rol_estado          AS estado
FROM seguridad_roles;

-- Vista: mantenimientos → instalaciones_mantenimientos
DROP VIEW IF EXISTS mantenimientos;
CREATE VIEW mantenimientos AS
SELECT
    man_mantenimiento_id AS mantenimiento_id,
    man_tenant_id        AS tenant_id,
    man_cancha_id        AS cancha_id,
    man_tipo             AS tipo,
    man_descripcion      AS descripcion,
    man_fecha_inicio     AS fecha_inicio,
    man_fecha_fin        AS fecha_fin,
    man_responsable_id   AS responsable_id,
    man_estado           AS estado
FROM instalaciones_mantenimientos;
```

---

## VERIFICACIÓN

Después de crear las vistas, prueba:

1. **Módulo de Mantenimientos**
   ```
   http://localhost/digisports/public/index.php?r=<encrypted_route>&module=mantenimientos
   ```
   Debería cargar sin error 400

2. **Verifica que las vistas existan:**
   ```sql
   SHOW FULL TABLES WHERE Table_Type = 'VIEW';
   ```
   Debe mostrar: `usuarios`, `roles`, `mantenimientos`

---

## LIMPIEZA

Una vez que todo funcione, puedes eliminar estos archivos:

```
public/setup_views.php              (script de inicialización)
database/fase1_vistas_compatibilidad_usuarios.sql   (archivo SQL)
INSTALAR_VISTAS.txt                 (este documento)
```

---

## REFERENCIA TÉCNICA

### Mapeo de Tablas/Columnas Corregidos

| Tabla Antigua | Tabla Nueva | Ejemplos de Cambios |
|---|---|---|
| `usuarios` | `seguridad_usuarios` | `usuario_id` → `usu_usuario_id`, `nombres` → `usu_nombres`, `email` → `usu_email` |
| `roles` | `seguridad_roles` | `rol_id` → `rol_rol_id`, `codigo` → `rol_codigo` |
| `mantenimientos` | `instalaciones_mantenimientos` | `mantenimiento_id` → `man_mantenimiento_id` |

### JOINs Corregidos

**Antes:**
```sql
LEFT JOIN usuarios u ON m.responsable_id = u.usuario_id
INNER JOIN roles r ON u.rol_id = r.rol_id
```

**Después:**
```sql
LEFT JOIN seguridad_usuarios u ON m.responsable_id = u.usu_usuario_id
INNER JOIN seguridad_roles r ON u.usu_rol_id = r.rol_rol_id
WHERE ... AND r.rol_codigo IN ('ADMIN', 'SUPERADMIN', 'TECNICO')
```

---

## SOPORTE

Si el error persiste después de crear las vistas:

1. Verifica conexión a base de datos en `config/database.php`
2. Confirma que las vistas fueron creadas (`SHOW FULL TABLES LIKE 'usuarios'`)
3. Revisa los logs de error de PHP en `storage/logs/`
4. Documenta el error exacto y contacta soporte técnico

**Contacto:** soporte@digisports.ec
