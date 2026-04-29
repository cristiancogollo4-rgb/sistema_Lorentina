# Recomendación: identidad de producto + trazabilidad por orden

## Objetivo
Permitir que varias órdenes de fabricación del mismo modelo/color/tipo **se sumen en stock vendible**,
pero mantener la trazabilidad de qué pares entraron por cada orden/lote.

## Propuesta (modelo en dos capas)

1. **SKU comercial (agregado vendible)**
   - Mantener la identidad comercial por `referencia + color + tipo`.
   - Este SKU es el que usan catálogo, precios y disponibilidad para venta.

2. **Lote de producción (trazabilidad)**
   - Crear una entidad separada por ingreso de orden, por ejemplo `lotes_producto` o `inventario_movimientos`.
   - Clave sugerida: `orden_produccion_id + referencia + color + tipo + sucursal + talla`.
   - Cada orden que pasa a stock genera movimientos `IN` por talla.

3. **Stock derivado (single source of truth)**
   - El stock mostrado y vendible debe salir del mismo cálculo:
     `stock_disponible = sum(IN) - sum(OUT) ± transferencias/ajustes`.
   - Evitar tener una pantalla leyendo “bruto” y otra “descontado”.

## Cómo aterriza en este código

- Hoy `productos` ya agrupa por `referencia,color,tipo` (único), lo cual está bien para SKU comercial.
- El problema no es ese `unique`, sino que falta una capa explícita de movimientos/lotes para no perder origen.
- En `pasarAStock`, al cerrar una orden, además de sumar inventario actual, registrar el movimiento por orden y talla.
- En ventas, al descontar inventario, registrar movimientos `OUT` por talla y, si hace falta trazabilidad completa, asignar salida por FIFO de lotes.

## Cambios mínimos recomendados (fase 1)

1. Crear tabla `inventario_movimientos`:
   - `id`, `tipo_movimiento` (`IN`,`OUT`,`TRANSFER_IN`,`TRANSFER_OUT`,`AJUSTE`),
   - `orden_produccion_id` nullable, `venta_id` nullable,
   - `referencia`, `color`, `tipo`, `sucursal`, `talla`, `cantidad`,
   - `created_at`, `usuario_id`.

2. Escribir movimientos en:
   - `ProductionController@pasarAStock` (IN),
   - `VentaController` al confirmar venta (OUT),
   - `StockController@transferir` (TRANSFER_OUT/TRANSFER_IN).

3. Unificar lectura de stock:
   - endpoint de stock y catálogo deben consumir el mismo cálculo.

## Beneficio
- **Sí se suman** pedidos distintos del mismo producto en un único stock comercial.
- **No se pierde trazabilidad**: cada par conserva su orden/lote de origen en movimientos.
- **Consistencia operativa**: admin, vendedor y caja ven la misma disponibilidad.
