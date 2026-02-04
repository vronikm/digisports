# 📊 INSTRUCCIONES DE IMPORTACIÓN - PASO 2

## 🎯 Objetivo
Importar las tablas necesarias para PASO 2 (Gestión de Instalaciones) a la base de datos MySQL.

---

## ✅ REQUISITOS PREVIOS

1. **MySQL/WAMP instalado y corriendo**
   ```bash
   # Verificar que WAMP está activo
   http://localhost/phpmyadmin
   ```

2. **Base de datos `digisports_core` creada**
   - Si no existe, ejecutar primero: `database/digisports_core.sql`

3. **Usuario MySQL con permisos de creación de tablas**
   - Usuario por defecto: `root` (sin contraseña en WAMP)

---

## 🔧 MÉTODO 1: Via PhpMyAdmin (Más fácil)

### Pasos:

1. **Abrir PhpMyAdmin**
   ```
   URL: http://localhost/phpmyadmin
   ```

2. **Seleccionar base de datos**
   - Click en `digisports_core` en el panel izquierdo

3. **Ir a la pestaña SQL**
   - Click en la pestaña "SQL" en la parte superior

4. **Copiar y pegar el contenido**
   ```
   - Abrir: c:\wamp64\www\digiSports\database\paso_2_instalaciones.sql
   - Copiar TODO el contenido
   - Pegar en el editor SQL de PhpMyAdmin
   ```

5. **Ejecutar**
   - Click en el botón "Ejecutar" (abajo derecha)
   - Esperar a que complete (verás un mensaje de éxito)

6. **Verificar**
   ```
   - Ir a "Estructura" de digisports_core
   - Deberías ver las nuevas tablas:
     ✓ canchas
     ✓ tarifas
     ✓ mantenimientos
     ✓ disponibilidad_canchas
     ✓ eventos_canchas
```

---

## 🔧 MÉTODO 2: Via Terminal CMD (Windows)

### Pasos:

1. **Abrir Command Prompt**
   - Presionar `Win + R`
   - Escribir `cmd` y presionar Enter

2. **Navegar a carpeta WAMP MySQL**
   ```bash
   cd "C:\wamp64\bin\mysql\mysql8.0.13\bin"
   ```

3. **Ejecutar el comando**
   ```bash
   mysql -u root digisports_core < "C:\wamp64\www\digiSports\database\paso_2_instalaciones.sql"
   ```

4. **Resultado esperado**
   ```
   (Sin errores = éxito)
   ```

5. **Verificar**
   ```bash
   mysql -u root digisports_core -e "SHOW TABLES;"
   ```
   Deberías ver:
   ```
   ┌─────────────────────────┐
   │ Tables_in_digisports_core│
   ├─────────────────────────┤
   │ canchas                 │
   │ disponibilidad_canchas  │
   │ eventos_canchas         │
   │ mantenimientos          │
   │ tarifas                 │
   │ ... (otras tablas)      │
   └─────────────────────────┘
   ```

---

## 🔧 MÉTODO 3: Via HeidiSQL (Recomendado)

HeidiSQL es una herramienta gráfica mejor que PhpMyAdmin.

### Pasos:

1. **Descargar HeidiSQL** (si no lo tienes)
   ```
   https://www.heidisql.com/download.php
   ```

2. **Conectar a MySQL**
   - Abrir HeidiSQL
   - Click "New" para crear conexión
   - Host: `127.0.0.1`
   - Usuario: `root`
   - Contraseña: (vacío)
   - Click "Guardar"

3. **Abrir archivo SQL**
   - File → Open SQL file...
   - Seleccionar: `c:\wamp64\www\digiSports\database\paso_2_instalaciones.sql`

4. **Ejecutar**
   - Click en el icono "Ejecutar" (▶)
   - O presionar `Ctrl + E`

5. **Verificar**
   - En el panel izquierdo, expandir `digisports_core`
   - Deberías ver las 5 nuevas tablas

---

## ❌ SOLUCIÓN DE PROBLEMAS

### Error: "Access denied for user 'root'@'localhost'"
```
Solución: 
1. En WAMP, MySQL por defecto NO tiene contraseña
2. Asegúrate de que MySQL esté corriendo (verificar en taskbar)
3. Verifica en PhpMyAdmin que puedes conectar sin contraseña
```

### Error: "Database 'digisports_core' doesn't exist"
```
Solución:
1. Primero ejecutar: c:\wamp64\www\digiSports\database\digisports_core.sql
2. O crear manualmente:
   mysql> CREATE DATABASE digisports_core;
   mysql> USE digisports_core;
```

### Error: "Duplicate key name" o "Duplicate column specification"
```
Solución:
1. Las tablas ya existen
2. Ejecutar este comando para limpiar:
   DROP TABLE IF EXISTS canchas, tarifas, mantenimientos, 
   disponibilidad_canchas, eventos_canchas;
3. Luego ejecutar paso_2_instalaciones.sql nuevamente
```

### Error: "Syntax error near 'CREATE OR REPLACE VIEW'"
```
Solución:
1. Reducir el SQL en partes
2. Ejecutar primero CREATE TABLE statements
3. Luego ejecutar CREATE OR REPLACE VIEW statements por separado
```

---

## ✅ VALIDACIÓN POST-IMPORTACIÓN

Ejecutar este SQL para verificar que todo está correcto:

```sql
-- Verificar tablas creadas
SHOW TABLES LIKE 'cancha%';
SHOW TABLES LIKE 'tarifa%';
SHOW TABLES LIKE 'mantenimiento%';

-- Verificar estructura de canchas
DESCRIBE canchas;

-- Verificar vistas creadas
SHOW FULL TABLES IN digisports_core WHERE TABLE_TYPE LIKE 'VIEW';

-- Verificar relaciones (foreign keys)
SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'digisports_core' AND REFERENCED_TABLE_NAME IS NOT NULL;

-- Verificar índices
SHOW INDEXES FROM canchas;
SHOW INDEXES FROM tarifas;
SHOW INDEXES FROM mantenimientos;
```

---

## 📋 CHECKLIST DE COMPLETITUD

Después de ejecutar el SQL, verificar:

- [ ] Tabla `canchas` creada con 14 columnas
- [ ] Tabla `tarifas` creada con 8 columnas
- [ ] Tabla `mantenimientos` creada con 14 columnas
- [ ] Tabla `disponibilidad_canchas` creada
- [ ] Tabla `eventos_canchas` creada
- [ ] Vista `vw_tarifas_por_dia` creada
- [ ] Vista `vw_mantenimientos_pendientes` creada
- [ ] Vista `vw_estadisticas_canchas` creada
- [ ] Índices FULLTEXT en canchas y mantenimientos
- [ ] Foreign keys establecidas
- [ ] Todos los DEFAULT VALUES configurados
- [ ] Collation UTF8MB4 en todas las tablas
- [ ] ENGINE InnoDB en todas las tablas

---

## 🔄 DATOS DE PRUEBA (Opcional)

Si quieres agregar datos de ejemplo para probar, descomenta la sección al final del archivo SQL:

```sql
-- Descomentar las líneas de datos de prueba:
INSERT INTO canchas (tenant_id, instalacion_id, nombre, tipo, ...) 
VALUES (1, 1, 'Cancha 1', 'futbol', ...);
```

---

## 📞 SOPORTE

Si encuentras problemas:

1. Verifica que `digisports_core.sql` fue importado primero
2. Revisa los logs de MySQL en WAMP
3. Ejecuta `SHOW ERRORS;` después de la importación
4. Verifica permisos del usuario MySQL

---

**Última actualización:** 24 Enero 2026  
**Versión SQL:** 1.0.0  
**MySQL requerido:** 8.0+
