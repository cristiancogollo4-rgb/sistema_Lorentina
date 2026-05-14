# Componente etico y flujo sistemico de Lorentina

## Proposito del sistema

Lorentina integra la operacion de una fabrica de calzado: clientes, ventas, inventario, ordenes de produccion, tareas de operarios y nomina. Su objetivo es reducir errores manuales, mejorar la trazabilidad y apoyar decisiones administrativas con datos actualizados.

## Componente etico

El proyecto aplica principios de etica profesional porque no se limita a guardar datos: define responsabilidades y controles para proteger a clientes, empleados y administradores.

- Transparencia: cada venta, orden, movimiento de stock y pago de nomina queda registrado con datos verificables.
- Responsabilidad: las tareas de produccion se asignan a empleados concretos y la app movil valida que el empleado correcto cierre la tarea.
- Justicia: el calculo de nomina usa reglas visibles por rol, cantidad de pares, ventas y periodo de pago.
- Privacidad: se usa solo la informacion necesaria para operar el negocio: clientes, empleados, ventas, inventario y produccion.
- Integridad de datos: el backend valida cantidades, productos, clientes, stock y estados antes de guardar cambios.

## Flujo del sistema

1. Entrada:
   Clientes, empleados, productos, tarifas, stock inicial y pedidos.

2. Proceso:
   El sistema registra ventas, crea ordenes de produccion, asigna responsables, permite terminar tareas desde la app movil, mueve inventario y calcula nomina.

3. Salida:
   Ventas registradas, inventario descontado o aumentado, ordenes terminadas, reportes de dashboard y pagos calculados.

4. Retroalimentacion:
   El dashboard muestra ventas, pendientes por asignar, stock, pares fabricados y alertas comerciales para tomar decisiones y ajustar la operacion.

## Vision desde teoria de sistemas

Lorentina es un sistema abierto porque recibe informacion del entorno comercial y productivo, la transforma en procesos internos y genera resultados para la administracion. Sus subsistemas son:

- Frontend web: administracion, ventas, stock, clientes, fabricacion, nomina y dashboard.
- Backend Laravel: reglas de negocio, validaciones, persistencia y calculos.
- App movil Kotlin: consulta y cierre de tareas por operario.
- Base de datos: memoria del sistema y fuente de trazabilidad.

## Controles implementados

- Validacion de credenciales al iniciar sesion.
- Separacion de roles entre administrador, vendedor y operarios.
- Validacion de stock antes de registrar ventas.
- Descuento automatico de inventario al vender.
- Asignacion y cierre de tareas por empleado.
- Nomina calculada con tareas terminadas y ventas registradas.
- Vista de etica y flujo sistemico disponible dentro del dashboard administrativo.

## Riesgos y mitigacion

- Error al digitar cantidades: se validan numeros y disponibilidad antes de guardar.
- Venta sin stock: el backend bloquea la venta si no existen pares suficientes.
- Cierre incorrecto de tareas: la app movil envia el empleado y el backend verifica la asignacion.
- Uso inadecuado de datos personales: el sistema separa funciones por rol y evita exponer datos fuera del proceso operativo.
