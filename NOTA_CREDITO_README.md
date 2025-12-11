# Módulo Nota de Crédito Compra - Guía de Uso

## ✅ Archivos Creados

### 1. Páginas HTML
- `paginas/movimientos/nota_credito/listar.php` - Lista todas las notas de crédito
- `paginas/movimientos/nota_credito/agregar.php` - Formulario para crear notas
- `paginas/movimientos/nota_credito/print.php` - Página de impresión

### 2. Backend
- `controladores/nota_credito.php` - Controlador con todas las operaciones CRUD

### 3. Frontend
- `vista/nota_credito.js` - Lógica JavaScript para toda la funcionalidad

### 4. Base de Datos
- `sql/create_nota_credito_tables.sql` - Script SQL para crear las tablas

### 5. Configuración
- `main.php` - Actualizado con menú y scripts

## 📊 Funcionalidades

✅ Crear notas de crédito con detalles de productos  
✅ Cálculo automático de totales (subtotal, IVA 5%, IVA 10%, exenta)  
✅ Buscar y filtrar notas de crédito  
✅ Ver detalles en modal  
✅ Anular notas (cambio de estado)  
✅ Imprimir notas de crédito  

## 🗄️ Tablas de Base de Datos

```sql
-- Cabecera de notas de crédito
CREATE TABLE nota_credito (
  id_nota_credito, numero_nota, fecha_nota, 
  id_factura_compra, id_proveedor, 
  motivo, observaciones, monto_total, 
  estado, id_usuario, fecha_creacion
)

-- Detalles de productos en notas
CREATE TABLE detalle_nota_credito (
  id_detalle_nota, id_nota_credito, 
  id_productos, cantidad, 
  precio_unitario, total
)
```

## 🚀 Instalación

1. **Ejecutar script SQL:**
   ```
   mysql -u root -p compra < sql/create_nota_credito_tables.sql
   ```

2. **Acceder al menú:**
   - Compras → Nota de Crédito Compra

## 💡 Flujo de Uso

### Crear Nueva Nota:
1. Click "+ Nueva Nota"
2. Completar formulario:
   - Número de nota
   - Fecha
   - Factura compra relacionada
   - Proveedor (auto-llena)
   - Motivo y observaciones
3. Agregar productos con cantidades y precios
4. Verificar totales
5. Click "Guardar"

### Ver Detalles:
- Click en icono "Ver" (ojo)
- Se abre modal con información

### Anular:
- Click en icono "Anular" (X)
- Confirmar acción
- Estado cambia a INACTIVO

### Imprimir:
- Click en icono "Imprimir"
- Se abre ventana para imprimir

## 🔍 Búsqueda

- Por número de nota
- Por nombre de proveedor
- Por número de factura

## 📝 Notas Técnicas

- **Patrón:** Sigue la estructura de Factura Compra
- **IVA:** Utiliza campo directo del producto (5, 10, 0)
- **Estados:** ACTIVO / INACTIVO
- **Cálculos:** Realizados en frontend y validados en backend
- **Transacciones:** Base de datos transaccional para consistencia

