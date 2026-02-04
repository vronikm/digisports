# 🔧 PASO 4: Guía de Instalación y Deployment

**Versión**: 1.0.0  
**Fecha**: Enero 2025  
**Nivel**: Administrator  
**Tiempo estimado**: 10 minutos  

---

## ⚙️ Requisitos Previos

### Sistema

- ✅ PHP 8.2.13+ (ya instalado en WAMP)
- ✅ MySQL 8.0+ (ya instalado en WAMP)
- ✅ Apache 2.4+ (ya instalado en WAMP)

### Proyecto DigiSports

- ✅ PASO 1 completo (Autenticación)
- ✅ PASO 2 completo (Instalaciones)
- ✅ PASO 3 completo (Reservas)

Verificar:
```bash
# En línea de comando:
php -v                    # Debe mostrar PHP 8.2.13
mysql -u root -p          # Debe conectar a MySQL
```

---

## 📁 Archivos a Copiar

### 1. Controladores (2 archivos)

**Origen**: Generados por desarrollo  
**Destino**: `c:\wamp64\www\digiSports\app\controllers\facturacion\`

```
FacturaController.php     (606 líneas)
PagoController.php        (366 líneas)
```

**Verificar**:
```bash
# Windows PowerShell:
dir "c:\wamp64\www\digiSports\app\controllers\facturacion\"
# Debe mostrar ambos archivos
```

### 2. Vistas (5 archivos)

**Origen**: Generadas por desarrollo  
**Destino**: `c:\wamp64\www\digiSports\app\views\facturacion\`

```
index.php           (Listado facturas)
ver.php             (Detalles)
crear.php           (Formulario crear)
crear_pago.php      (Formulario pago)
pagos.php           (Listado pagos)
```

**Verificar**:
```bash
dir "c:\wamp64\www\digiSports\app\views\facturacion\"
# Debe mostrar 5 archivos
```

### 3. Documentación (4 archivos)

**Origen**: Generados por desarrollo  
**Destino**: `c:\wamp64\www\digiSports\`

```
PASO_4_FACTURACION.md
PASO_4_INICIO_RAPIDO.md
PASO_4_ENTREGA_FINAL.md
PASO_4_INDICE.md
PASO_4_VALIDACION_EJECUTIVA.md
```

---

## 🗄️ Instalación de Base de Datos

### Paso 1: Conectar a MySQL

```bash
# Opción A: Línea de comandos
mysql -u root -p

# Opción B: phpMyAdmin
# Ir a: http://localhost/phpmyadmin
# Usuario: root
# Contraseña: (vacía o ingresada)
```

### Paso 2: Seleccionar Base de Datos

```bash
# En MySQL CLI:
USE digisports_core;

# En phpMyAdmin:
# Hacer click en "digisports_core"
```

### Paso 3: Ejecutar Script SQL

#### Opción A: Línea de Comandos

```bash
# Desde Windows PowerShell (en directorio raíz del proyecto):
mysql -u root -p digisports_core < database\paso_4_facturacion.sql

# Verificar éxito:
# Debe decir "Query OK" sin errores
```

#### Opción B: phpMyAdmin

```
1. Abrir phpmyadmin en navegador
2. Seleccionar base de datos "digisports_core"
3. Click en pestaña "Importar"
4. Click en "Elegir archivo"
5. Seleccionar: database\paso_4_facturacion.sql
6. Click en "Ejecutar"
```

### Paso 4: Verificar Instalación

```sql
-- En MySQL CLI o phpMyAdmin:

-- Verificar tablas creadas:
SHOW TABLES LIKE 'forma%';          -- Debe mostrar "formas_pago"
SHOW TABLES LIKE 'factura%';        -- Debe mostrar 3 tablas
SHOW TABLES LIKE 'pago%';           -- Debe mostrar "pagos"

-- Verificar vistas:
SHOW FULL TABLES WHERE Table_Type = 'VIEW';
-- Debe mostrar 3 vistas de facturacion

-- Verificar datos iniciales:
SELECT COUNT(*) FROM formas_pago;
-- Debe retornar 5 (formas de pago por defecto)
```

---

## ✅ Verificación de Instalación

### 1. Verificar Archivos PHP

```bash
# En PowerShell, ir a raíz del proyecto:
cd c:\wamp64\www\digiSports

# Verificar controladores:
Test-Path "app\controllers\facturacion\FacturaController.php"
Test-Path "app\controllers\facturacion\PagoController.php"

# Verificar vistas:
Test-Path "app\views\facturacion\index.php"
Test-Path "app\views\facturacion\ver.php"
Test-Path "app\views\facturacion\crear.php"
Test-Path "app\views\facturacion\crear_pago.php"
Test-Path "app\views\facturacion\pagos.php"
```

### 2. Verificar Permisos

```bash
# Los permisos deben ser 755 (lectura/escritura):
icacls "app\controllers\facturacion" /grant:r %USERNAME%:F
icacls "app\views\facturacion" /grant:r %USERNAME%:F
```

### 3. Verificar Conectividad

```bash
# Probar conexión MySQL:
mysql -h localhost -u root -p -e "SELECT 1"
# Debe retornar "1"
```

### 4. Verificar Servidor Web

```bash
# Abrir navegador:
http://localhost

# Verificar que WAMP funciona correctamente
# El icono de WAMP debe estar verde
```

---

## 🚀 Deployment a Producción

### Pre-deployment Checklist

- [ ] Backup de base de datos actual
```bash
mysqldump -u root -p digisports_core > backup_$(date +%Y%m%d).sql
```

- [ ] Verificar PASO 3 funciona correctamente
  - Crear reserva de prueba
  - Confirmar reserva
  
- [ ] Verificar espacio en disco
```bash
dir c:\wamp64\www\digiSports
# Debe tener 100MB+ libre
```

- [ ] Revisar logs recientes
```bash
# Ver últimos errores:
type "storage\logs\*.log" | Select-Object -Last 50
```

### Deployment Steps

1. **Detener servicios Apache** (si necesario)
```bash
# En Windows Services:
# Services → Apache → Stop
```

2. **Copiar archivos**
```bash
# Copiar controllers:
Copy-Item "app\controllers\facturacion\*" -Destination "C:\wamp64\www\digiSports\app\controllers\facturacion\" -Force

# Copiar vistas:
Copy-Item "app\views\facturacion\*" -Destination "C:\wamp64\www\digiSports\app\views\facturacion\" -Force
```

3. **Ejecutar SQL**
```bash
mysql -u root -p digisports_core < database\paso_4_facturacion.sql
```

4. **Iniciar servicios**
```bash
# En Windows Services:
# Services → Apache → Start
```

5. **Verificar funcionamiento**
```
# En navegador:
http://localhost/digiSports
# Ir a: Facturación → Nueva Factura
# Debe mostrar formulario
```

---

## 🧪 Testing Post-Deployment

### Test 1: Crear Factura

```
1. Ir a: Facturación → Nueva Factura
2. Debe mostrar lista de reservas confirmadas
3. Seleccionar una reserva
4. Debe cargar datos automáticamente
5. Click "Crear Factura"
6. Debe redirigir a detalles (estado BORRADOR)
```

**Resultado esperado**: ✅ Factura creada

### Test 2: Emitir Factura

```
1. En detalles de factura (estado BORRADOR)
2. Click botón "Emitir"
3. Debe cambiar estado a EMITIDA
4. Debe generar número_factura único
```

**Resultado esperado**: ✅ Factura emitida

### Test 3: Registrar Pago

```
1. Click en "Nuevo Pago"
2. Ingresaer monto pendiente
3. Seleccionar forma de pago
4. Click "Registrar Pago"
5. Debe cambiar estado a PAGADA
```

**Resultado esperado**: ✅ Pago registrado, estado PAGADA

### Test 4: Multi-tenant

```
1. Cambiar a tenant diferente
2. Ir a Facturación
3. NO debe ver facturas de otro tenant
```

**Resultado esperado**: ✅ Datos aislados correctamente

### Test 5: Validaciones

```
1. Intentar crear factura sin reserva
   Esperado: Error "Reserva requerida"
   
2. Intentar pagar con monto mayor al pendiente
   Esperado: Error "Monto excede pendiente"
   
3. Intentar anular factura ya anulada
   Esperado: Error "Factura ya anulada"
```

**Resultado esperado**: ✅ Todas las validaciones funcionan

---

## 🔍 Troubleshooting

### Error: "Tabla no encontrada"

**Causa**: SQL no ejecutado correctamente

**Solución**:
```bash
# Verificar tablas:
mysql -u root -p digisports_core -e "SHOW TABLES LIKE 'factura%'"

# Si no aparece, ejecutar de nuevo:
mysql -u root -p digisports_core < database\paso_4_facturacion.sql
```

### Error: "Class not found"

**Causa**: Archivos no copiados correctamente

**Solución**:
```bash
# Verificar archivos existen:
Test-Path "app\controllers\facturacion\FacturaController.php"

# Si no, copiar manualmente desde desarrollo
```

### Error: "403 Forbidden"

**Causa**: Permisos incorrectos

**Solución**:
```bash
# Dar permisos de lectura/escritura:
icacls "app\controllers\facturacion" /grant:r "SYSTEM":F
icacls "app\views\facturacion" /grant:r "SYSTEM":F
```

### Error: "Conexión MySQL rechazada"

**Causa**: MySQL no está corriendo

**Solución**:
```bash
# Iniciar WAMP:
# Windows → Click en icono WAMP
# Services → MySQL → Start

# Verificar estado:
mysql -u root -p -e "SELECT 1"
```

### Error: "Undefined variable"

**Causa**: Vista no recibe datos del controlador

**Solución**:
1. Verificar que el controlador redirecciona correctamente
2. Verificar nombre de vista existe
3. Revisar logs en `storage/logs/`

```bash
# Ver logs recientes:
Get-Content "storage\logs\errors.log" -Tail 20
```

---

## 📊 Verificación Final

### Checklist de Deployment

- [ ] SQL ejecutado sin errores
- [ ] 6 tablas creadas
- [ ] 3 vistas SQL creadas
- [ ] Datos iniciales cargados
- [ ] Controllers en carpeta correcta
- [ ] Vistas en carpeta correcta
- [ ] Permisos configurados
- [ ] Servidor Apache corriendo
- [ ] MySQL conectando
- [ ] Test 1: Crear factura ✅
- [ ] Test 2: Emitir factura ✅
- [ ] Test 3: Registrar pago ✅
- [ ] Test 4: Multi-tenant ✅
- [ ] Test 5: Validaciones ✅

Si todos marcan ✅ → **DEPLOYMENT EXITOSO**

---

## 🎓 Próximos Pasos

### Para Usuarios

1. Leer: `PASO_4_INICIO_RAPIDO.md`
2. Crear factura de prueba
3. Registrar pago
4. Explorar funcionalidades

### Para Administradores

1. Configurar SRI Ecuador (opcional ahora)
2. Configurar métodos de pago adicionales
3. Establecer políticas de facturación
4. Monitorear logs

### Para Desarrolladores

1. Revisar: `PASO_4_FACTURACION.md`
2. Entender arquitectura
3. Preparar futuras features
4. Testing adicional si necesario

---

## 📞 Soporte

### En Caso de Problemas

1. Revisar `storage/logs/errors.log`
2. Verificar checklist de troubleshooting
3. Revisar documentación técnica
4. Contactar al equipo técnico

### Documentación de Referencia

```
PASO_4_FACTURACION.md           Técnica
PASO_4_INICIO_RAPIDO.md         Usuario
PASO_4_ENTREGA_FINAL.md         Entrega
PASO_4_INDICE.md                Referencia
PASO_4_VALIDACION_EJECUTIVA.md  Validación
```

---

*Guía de instalación - PASO 4*  
*Versión 1.0.0 - Enero 2025*
