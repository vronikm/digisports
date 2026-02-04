# ⚡ PASO 4: Inicio Rápido - Sistema de Facturación

**Tiempo de lectura**: 5 minutos  
**Nivel**: Principiante  

---

## 🎯 En 3 Pasos

### 1️⃣ Instalar Base de Datos

```bash
# En MySQL/phpMyAdmin:
1. Ir a Importar
2. Seleccionar: database/paso_4_facturacion.sql
3. Click: Ejecutar
```

**Resultado**: 6 tablas + 3 vistas + datos iniciales

### 2️⃣ Crear Primera Factura

```
1. Ir a: Facturación → Nueva Factura
2. Seleccionar una Reserva CONFIRMADA
3. Sistema carga automáticamente:
   - Cliente
   - Líneas de servicios
   - Subtotal calculado
4. Click: "Crear Factura"
```

**Resultado**: Factura en estado BORRADOR

### 3️⃣ Emitir y Pagar

```
1. Abrir factura creada
2. Click: "Emitir"
3. Factura pasa a estado EMITIDA
4. Click: "Nuevo Pago"
5. Ingresar monto y forma de pago
6. Click: "Registrar Pago"

✅ Si monto = total → Factura PAGADA
⚠️ Si monto < total → Factura EMITIDA (parcial)
```

---

## 📁 Archivos Clave

| Archivo | Propósito |
|---------|-----------|
| `app/controllers/facturacion/FacturaController.php` | Gestión de facturas |
| `app/controllers/facturacion/PagoController.php` | Gestión de pagos |
| `app/views/facturacion/index.php` | Listado de facturas |
| `app/views/facturacion/ver.php` | Detalles de factura |
| `app/views/facturacion/crear.php` | Crear factura desde reserva |
| `app/views/facturacion/crear_pago.php` | Registrar pago |
| `database/paso_4_facturacion.sql` | Script SQL |

---

## 🗄️ Tablas Principales

### facturas
```
factura_id      → ID único
numero_factura  → RES-00001, etc
reserva_id      → FK a reservas (PASO 3)
estado          → BORRADOR, EMITIDA, PAGADA, ANULADA
total           → Monto a pagar
```

### pagos
```
pago_id         → ID único
factura_id      → FK a facturas
monto           → Cantidad pagada
forma_pago_id   → Efectivo, Tarjeta, etc
estado          → CONFIRMADO, ANULADO
```

---

## 🔄 Estados de Factura

```
BORRADOR → EMITIDA → PAGADA
           ↘
            ANULADA
```

| Estado | Significado | Puede Emitirse | Puede Pagarse |
|--------|-------------|-----------------|---|
| BORRADOR | Recién creada | ✅ Sí | ❌ No |
| EMITIDA | Emitida al cliente | ❌ No | ✅ Sí |
| PAGADA | Completamente pagada | ❌ No | ❌ No |
| ANULADA | Cancelada/Deshecha | ❌ No | ❌ No |

---

## 💰 Cálculo Automático

```
Ejemplo:
─────────────────────
Subtotal:    $100.00
IVA (15%):   $ 15.00
──────────────────────
TOTAL:       $115.00
```

**Configuración**:
- IVA fijo: 15% (editable en formulario)
- Descuentos: Opcionales
- Decimales: 2 (USD)

---

## 🔐 Seguridad

✅ **Multi-tenant**: Cada empresa ve solo sus datos  
✅ **CSRF**: Tokens en todos los formularios  
✅ **Auditoría**: Cada cambio se registra  
✅ **Prepared Statements**: Protección SQL Injection  

---

## 🆘 Errores Comunes

### ❌ "Factura no encontrada"
**Causa**: No existe esa factura en tu empresa  
**Solución**: Verificar ID de factura correcto

### ❌ "Monto excede lo pendiente"
**Causa**: Pagaste más de lo que queda por pagar  
**Solución**: Revisar monto pendiente (en rojo)

### ❌ "Reserva no válida para facturación"
**Causa**: Reserva no está CONFIRMADA  
**Solución**: Ir a PASO 3, confirmar reserva primero

### ❌ "No hay reservas disponibles"
**Causa**: No hay reservas confirmadas sin factura  
**Solución**: Crear y confirmar reserva en PASO 3

---

## 📊 Reportes Listos

```sql
-- Ver todas las facturas con saldos:
SELECT * FROM vw_facturas_resumen

-- Ver facturas vencidas:
SELECT * FROM vw_facturas_vencidas

-- Ver ingresos por forma de pago:
SELECT * FROM vw_ingresos_por_forma_pago
```

---

## 🎓 Próximo Paso

Leer: **PASO_4_FACTURACION.md** para documentación completa

---

*¿Necesitas ayuda? Consulta la documentación principal.*
