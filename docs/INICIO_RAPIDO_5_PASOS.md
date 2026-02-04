# 🚀 INICIO INMEDIATO: PASO 5 - 5 PASOS

## ⏱️ Tiempo Total: 15 minutos

---

## 1️⃣ INICIAR WAMP (1 minuto)

```
1. Búsqueda Windows: "WAMP"
2. Ejecutar: WampServer
3. Esperar icono verde en bandeja
   ✅ = Listo
   🟡 = Algún servicio parado (revisar)
   🔴 = Error (reiniciar)
```

---

## 2️⃣ CREAR BASE DE DATOS (3 minutos)

### Opción RECOMENDADA: phpMyAdmin

```
1. Abrir navegador
2. URL: http://localhost/phpmyadmin
3. Usuario: root
4. Contraseña: (dejar vacío)
5. [Enter]
```

### Crear BD

```
1. Click: [Nueva]
2. Nombre: digisports_core
3. Encoding: utf8mb4_unicode_ci
4. [Crear]
```

### Importar SQL

```
1. Seleccionar: digisports_core
2. Click: [Importar]
3. [Seleccionar archivo]
4. Ruta: C:\wamp64\www\digiSports\database\digisports_core.sql
5. [Abrir]
6. [Ejecutar]

✅ Esperar mensaje verde
```

---

## 3️⃣ CARGAR DATOS DE PRUEBA (3 minutos)

### En phpMyAdmin, pestaña SQL:

**Copiar y pegar este bloque completo**:

```sql
-- Crear tenant
INSERT INTO tenants (nombre, ruc, email, telefono, estado) 
VALUES ('DigiSports Demo', '20123456789', 'info@digisports.local', '+51987654321', 'A');

-- Crear usuario
INSERT INTO usuarios (tenant_id, nombre, email, password, rol, estado) 
VALUES (1, 'Admin', 'admin@digisports.local', 'Admin123!', 'admin', 'A');

-- Crear clientes
INSERT INTO clientes (tenant_id, nombre, ruc, email, estado) VALUES 
(1, 'Acme Corp', '20111111111', 'acme@mail.com', 'A'),
(1, 'Tech Solutions', '20222222222', 'tech@mail.com', 'A'),
(1, 'Digital Agency', '20333333333', 'digital@mail.com', 'A'),
(1, 'Innovatech', '20444444444', 'innovatech@mail.com', 'A'),
(1, 'Premium Biz', '20555555555', 'premium@mail.com', 'A');

-- Crear formas de pago
INSERT INTO formas_pago (tenant_id, nombre, estado) VALUES 
(1, 'Efectivo', 'A'),
(1, 'Tarjeta Crédito', 'A'),
(1, 'Transferencia', 'A');

-- Crear facturas
INSERT INTO facturas (tenant_id, cliente_id, numero_factura, fecha_emision, total, estado) VALUES 
(1, 1, 'F001', '2024-01-10', 1000, 'PAGADA'),
(1, 2, 'F002', '2024-01-15', 2500, 'EMITIDA'),
(1, 3, 'F003', '2024-01-20', 750, 'PAGADA'),
(1, 4, 'F004', '2024-01-25', 3200, 'EMITIDA'),
(1, 5, 'F005', '2024-02-01', 1500, 'PAGADA');

-- Crear pagos
INSERT INTO pagos (tenant_id, factura_id, forma_pago_id, monto, fecha_pago) VALUES 
(1, 1, 1, 1000, '2024-01-12'),
(1, 3, 2, 750, '2024-01-22'),
(1, 5, 3, 1500, '2024-02-03');
```

Click: **[Ejecutar]**

✅ Esperar mensajes verdes

---

## 4️⃣ HACER LOGIN (2 minutos)

### Abrir navegador

```
URL: http://localhost/digiSports/public/
```

### Llenar datos

```
Email:      admin@digisports.local
Contraseña: Admin123!
[ENTRAR]
```

✅ Deberías ver el Dashboard principal

---

## 5️⃣ VER PASO 5 EN VIVO (1 minuto)

### Opción A: URL Directa

```
Copiar en navegador:
http://localhost/digiSports/reportes/index

[Enter]
```

### Opción B: Desde el menú

```
En dashboard, buscar menú "Reportes"
Click en "Dashboard"
```

---

## ✅ ¿VES ESTO?

```
┌─────────────────────────────────────────┐
│  📊 Dashboard de Reportes               │
├─────────────────────────────────────────┤
│                                         │
│ [Esta Semana] [Este Mes] ...           │
│                                         │
│ ┌────────┐ ┌────────┐ ┌────────┐      │
│ │ $8,950 │ │   5    │ │  80%   │      │
│ │Ingresos│ │Facturas│ │Cobranza│      │
│ └────────┘ └────────┘ └────────┘      │
│                                         │
│ ┌──────────────────────────────────────┐│
│ │ Gráfico: Ingresos por Día           ││
│ │ (Línea azul mostrando datos)        ││
│ └──────────────────────────────────────┘│
│                                         │
│ ┌──────────────────────────────────────┐│
│ │ Top 5 Clientes                       ││
│ │ Acme Corp      | $3,200             ││
│ │ Tech Solutions | $2,500             ││
│ └──────────────────────────────────────┘│
│                                         │
└─────────────────────────────────────────┘

✅ ¡PASO 5 FUNCIONA CORRECTAMENTE! 🎉
```

---

## 🎯 PRUEBAS RÁPIDAS

### Test 1: Cambiar Período
```
Click: [Este Mes]
Resultado esperado: 
✅ Números cambian
✅ Gráficos se actualizan
```

### Test 2: Ver Otro Reporte
```
URL: http://localhost/digiSports/reportes/facturas
Resultado esperado:
✅ Tabla con tus facturas
```

### Test 3: Exportar CSV
```
Click: [Exportar CSV]
Resultado esperado:
✅ Se descarga reporte.csv
✅ Abre en Excel correctamente
```

### Test 4: Ver KPIs
```
URL: http://localhost/digiSports/reportes/kpi?periodo=mes
Resultado esperado:
✅ 8 tarjetas con números
✅ Flechas de tendencia
```

---

## 🐛 SI ALGO FALLA

### Problema 1: "No se encontró la página"

```
❌ Error 404

Solución rápida:
1. Verificar URL exacta (reportes, no reports)
2. Verificar que estás logueado
3. Abrir consola (F12) y ver error
```

### Problema 2: "Error de base de datos"

```
❌ Database Connection Error

Solución rápida:
1. Verificar WAMP está verde
2. Verificar que digisports_core existe en phpMyAdmin
3. Si no existe, reimportar SQL
```

### Problema 3: "No hay datos"

```
❌ Tablas vacías

Solución rápida:
1. Ir a phpMyAdmin
2. Pestaña SQL
3. Copiar y ejecutar script de datos (Paso 3 arriba)
```

### Problema 4: "Gráficos en blanco"

```
❌ Chart.js no renderiza

Solución rápida:
1. Abrir F12 (consola)
2. Ver qué error dice
3. Si es CORS, revisar que CDN está accesible
```

---

## 📞 CONTACTO RÁPIDO

```
Si necesitas ayuda:
1. Revisar: PASO_5_REPORTES.md (Troubleshooting)
2. Revisar: GUIA_VISUAL_PASO5.md (Paso a paso)
3. Revisar logs: /storage/logs/
```

---

## ✅ CHECKLIST FINAL

Marca cuando esté listo:

```
[ ] WAMP corriendo (icono verde)
[ ] phpMyAdmin accesible
[ ] BD digisports_core creada
[ ] SQL importado
[ ] Datos de prueba insertados
[ ] Login funciona
[ ] http://localhost/digiSports/reportes/index CARGA
[ ] Ves 4 KPI cards
[ ] Ves 3 gráficos
[ ] Botones de período funcionan
[ ] Filtros funcionan
[ ] Exportación CSV funciona
[ ] Sin errores rojos en consola F12
```

---

## 🎊 ¡FELICIDADES!

Si checkeaste todo = **PASO 5 COMPLETAMENTE OPERATIVO** ✅

---

## 🚀 PRÓXIMOS PASOS

Ahora puedes:

1. **Crear más datos** para experimentar
2. **Personalizar reportes** según necesidad
3. **Agregar usuarios** adicionales
4. **Cambiar filtros** y campos
5. **Ir a PASO 6** (Business Intelligence avanzado)

---

**¡A disfrutar PASO 5! 🎉**

📧 Documentación completa: Ver [PASO_5_INDICE.md](PASO_5_INDICE.md)
