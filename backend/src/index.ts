import express from 'express';
import cors from 'cors';
import { PrismaClient } from '@prisma/client';
import multer from 'multer';
import * as xlsx from 'xlsx';
import fs from 'fs';
import * as path from 'path';
import bcrypt from 'bcryptjs';

// ==========================================
// 1. CONFIGURACIÓN INICIAL Y MIDDLEWARE
// ==========================================
const app = express();
const PORT = 4000;
const prisma = new PrismaClient();

// Configuración de CORS (Permitir todo por ahora)
app.use(cors({
    origin: '*', 
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization']
}));

// Aumentar tamaño máximo para subida de Excel pesado
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ limit: '50mb', extended: true }));

// Log "Chismoso" para ver peticiones en consola
app.use((req, res, next) => {
  console.log(`🔔 ${req.method} ${req.url}`);
  next();
});

// Configuración de carpeta de subidas
const uploadDir = path.join(__dirname, '../uploads');
if (!fs.existsSync(uploadDir)) {
    console.log("📁 Creando carpeta uploads...");
    fs.mkdirSync(uploadDir, { recursive: true });
}
const upload = multer({ dest: uploadDir });

// Utilidad para parsear números seguros
const parseIntSafe = (value: any): number => {
  if (!value) return 0;
  const parsed = parseInt(value);
  return isNaN(parsed) ? 0 : parsed;
};

// ==========================================
// 2. MÓDULO DE USUARIOS Y AUTENTICACIÓN
// ==========================================

// Login Híbrido (Web y Android)
app.post('/api/login', async (req: any, res: any) => {
  const { username, password } = req.body;
  try {
    const usuario = await prisma.usuario.findUnique({ where: { username } });
    if (!usuario) return res.status(401).json({ error: 'Usuario no encontrado' });

    const validPassword = await bcrypt.compare(password, usuario.password);
    if (!validPassword) return res.status(401).json({ error: 'Contraseña incorrecta' });

    const { password: _, ...datosUsuario } = usuario;

    res.json({
      ...datosUsuario,    // Para Android (raíz)
      usuario: datosUsuario // Para Web (dentro de objeto)
    });
  } catch (error) {
    res.status(500).json({ error: 'Error interno en login' });
  }
});

// Crear Usuario
app.post('/api/usuarios', async (req: any, res: any) => {
    try {
      const { nombre, apellido, username, password, rol, telefono, cedula, activo } = req.body;
      const existe = await prisma.usuario.findUnique({ where: { username } });
      if (existe) return res.status(400).json({ error: `El usuario '${username}' ya existe.` });
  
      const hashedPassword = await bcrypt.hash(password, 10);
  
      const nuevoUsuario = await prisma.usuario.create({
        data: { nombre, apellido, username, password: hashedPassword, rol, telefono, cedula, activo: activo ?? true }
      });
      res.json(nuevoUsuario);
    } catch (error) {
      res.status(500).json({ error: 'Error creando usuario' });
    }
});

// Listar Usuarios
app.get('/api/usuarios', async (req: any, res: any) => {
  try {
    const usuarios = await prisma.usuario.findMany({ orderBy: { id: 'desc' } });
    res.json(usuarios);
  } catch (error) { res.status(500).json({ error: 'Error listando usuarios' }); }
});

// Obtener Perfil de un Usuario (Para App Móvil)
app.get('/api/usuarios/:id', async (req: any, res: any) => {
    try {
      const { id } = req.params;
      const usuario = await prisma.usuario.findUnique({ where: { id: Number(id) } });
      if (!usuario) return res.status(404).json({ error: 'Usuario no encontrado' });
  
      const { password, ...datosPublicos } = usuario;
      res.json(datosPublicos);
    } catch (error) { res.status(500).json({ error: 'Error interno' }); }
});

// Actualizar Usuario
app.put('/api/usuarios/:id', async (req: any, res: any) => {
    try {
      const { id } = req.params;
      const { nombre, apellido, username, rol, password, telefono, cedula, activo } = req.body;
      const datosActualizar: any = { nombre, apellido, username, rol, telefono, cedula, activo: Boolean(activo) };

      if (password && password.trim() !== "") {
        datosActualizar.password = await bcrypt.hash(password, 10);
      }

      const usuarioActualizado = await prisma.usuario.update({
        where: { id: Number(id) },
        data: datosActualizar
      });
      res.json(usuarioActualizado);
    } catch (error) { res.status(500).json({ error: 'No se pudo actualizar' }); }
});

// Eliminar Usuario
app.delete('/api/usuarios/:id', async (req: any, res: any) => {
    try {
      await prisma.usuario.delete({ where: { id: Number(req.params.id) } });
      res.json({ message: 'Usuario eliminado' });
    } catch (error) { res.status(500).json({ error: 'No se pudo eliminar' }); }
});

// Obtener empleados por rol (Ej: solo cortadores para el select)
app.get('/api/empleados/corte', async (req: any, res: any) => {
    try {
      const cortadores = await prisma.usuario.findMany({ where: { rol: 'CORTE', activo: true } });
      res.json(cortadores);
    } catch (error) { res.status(500).json({ error: 'Error cargando cortadores' }); }
});

// ==========================================
// 3. MÓDULO DE TARIFAS Y PRECIOS
// ==========================================

// Leer todas las tarifas
app.get('/api/tarifas', async (req: any, res: any) => {
    const tarifas = await prisma.tarifaCategoria.findMany();
    res.json(tarifas);
});
  
// Actualizar precios de tarifas
// --- 2. ENDPOINT PARA ACTUALIZAR LAS TARIFAS ---
app.post('/api/tarifas/actualizar', async (req: any, res: any) => {
  const nuevasTarifas = req.body; 
  
  try {
    for (const t of nuevasTarifas) {
      await prisma.tarifaCategoria.update({
        where: { nombre: t.nombre },
        data: { 
            precioCorte: Number(t.precioCorte),
            precioArmado: Number(t.precioArmado),
            precioCostura: Number(t.precioCostura),
            precioSoladura: Number(t.precioSoladura),
            precioEmplantillado: Number(t.precioEmplantillado),
        }
      });
    }
    res.json({ msg: "Precios actualizados correctamente" });
  } catch (error) {
    console.error(error);
    res.status(500).json({ error: "Error guardando precios" });
  }
});

// 🛠️ SETUP: Crear precios iniciales CON LOS 5 PROCESOS
app.get('/api/configurar-precios', async (req: any, res: any) => {
    try {
      const tarifas = [
        { 
            nombre: 'ROMANA', 
            precioCorte: 1500, precioArmado: 1000, precioCostura: 1200, precioSoladura: 2000, precioEmplantillado: 500 
        },
        { 
            nombre: 'CLASICA', 
            precioCorte: 1200, precioArmado: 900, precioCostura: 1000, precioSoladura: 1800, precioEmplantillado: 400 
        },
        { 
            nombre: 'ZARA', 
            precioCorte: 1800, precioArmado: 1500, precioCostura: 1600, precioSoladura: 2500, precioEmplantillado: 600 
        },
      ];
      
      for (const t of tarifas) {
        await prisma.tarifaCategoria.upsert({ where: { nombre: t.nombre }, update: t, create: t });
      }
      res.send("<h1>✅ Tarifas completas (5 Procesos) configuradas</h1>");
    } catch (error) { res.status(500).send("Error configurando precios"); }
});

// 🛠️ SETUP: Crear precios iniciales (Si la BD está vacía)
app.get('/api/configurar-precios', async (req: any, res: any) => {
    try {
      const tarifas = [
        { nombre: 'ROMANA', precioCorte: 1500, precioGuarnicion: 2500, precioSoladura: 2000 },
        { nombre: 'CLASICA', precioCorte: 1200, precioGuarnicion: 2200, precioSoladura: 1800 },
        { nombre: 'ZARA', precioCorte: 1800, precioGuarnicion: 3000, precioSoladura: 2500 },
      ];
      for (const t of tarifas) {
        await prisma.tarifaCategoria.upsert({ where: { nombre: t.nombre }, update: t, create: t });
      }
      res.send("<h1>✅ Tarifas configuradas correctamente</h1><p>Ya puedes usar el sistema.</p>");
    } catch (error) { res.status(500).send("Error configurando precios"); }
});

// ==========================================
// 4. MÓDULO DE PRODUCCIÓN
// ==========================================

// ==========================================
// CREAR ORDEN (Lógica Mult-Precios)
// ==========================================
app.post('/api/produccion', async (req: any, res: any) => {
  try {
    const data = req.body;
    const categoria = data.categoria || 'ROMANA';

    // Variables para guardar los precios
    let pCorte = 0, pArmado = 0, pCostura = 0, pSoladura = 0, pEmplantillado = 0;

    if (categoria === 'ESPECIAL') {
        // CASO 1: TÚ ESCRIBES LOS PRECIOS MANUALMENTE
        pCorte = Number(data.precioManualCorte) || 0;
        pArmado = Number(data.precioManualArmado) || 0;
        pCostura = Number(data.precioManualCostura) || 0;
        pSoladura = Number(data.precioManualSoladura) || 0;
        pEmplantillado = Number(data.precioManualEmplantillado) || 0;
    } else {
        // CASO 2: AUTOMÁTICO DESDE LA BASE DE DATOS
        const tarifa = await prisma.tarifaCategoria.findUnique({ where: { nombre: categoria } });
        if (tarifa) {
            pCorte = tarifa.precioCorte;
            pArmado = tarifa.precioArmado;
            pCostura = tarifa.precioCostura;
            pSoladura = tarifa.precioSoladura;
            pEmplantillado = tarifa.precioEmplantillado;
        }
    }

    // Calcular total pares
    const totalPares = (Number(data.t34)||0) + (Number(data.t35)||0) + (Number(data.t36)||0) +
                       (Number(data.t37)||0) + (Number(data.t38)||0) + (Number(data.t39)||0) +
                       (Number(data.t40)||0) + (Number(data.t41)||0) + (Number(data.t42)||0) +
                       (Number(data.t43)||0) + (Number(data.t44)||0);

    // Crear la Orden guardando TODOS los precios
    const nuevaOrden = await prisma.ordenProduccion.create({
      data: {
        numeroOrden: `OP-${Date.now().toString().slice(-6)}`,
        referencia: data.referencia,
        color: data.color,
        categoria: categoria, 
        
        // ¡Aquí guardamos el desglose completo!
        precioCorte: pCorte,
        precioArmado: pArmado,
        precioCostura: pCostura,
        precioSoladura: pSoladura,
        precioEmplantillado: pEmplantillado,
        

      

        materiales: data.materiales,
        observacion: data.observacion,
        destino: data.destino,
        cortadorId: data.cortadorId ? Number(data.cortadorId) : null,
        
        // Tallas...
        t34: Number(data.t34)||0, t35: Number(data.t35)||0, t36: Number(data.t36)||0,
        t37: Number(data.t37)||0, t38: Number(data.t38)||0, t39: Number(data.t39)||0,
        t40: Number(data.t40)||0, t41: Number(data.t41)||0, t42: Number(data.t42)||0,
        t43: Number(data.t43)||0, t44: Number(data.t44)||0,
        
        totalPares,
        estado: 'EN_CORTE'
      }
    });

    res.json({ msg: "Orden creada con precios desglosados", orden: nuevaOrden.numeroOrden });

  } catch (error) {
    console.error("Error creando orden:", error);
    res.status(500).json({ error: 'Error interno al crear orden' });
  }
});

app.get('/api/mis-tareas/:empleadoId', async (req: any, res: any) => {
  try {
    const { empleadoId } = req.params;
    const id = Number(empleadoId);

    // Buscamos en todas las columnas posibles donde este ID sea responsable
    // y la orden esté en ese estado específico.
    const tareas = await prisma.ordenProduccion.findMany({
      where: {
        OR: [
          { cortadorId: id, estado: 'EN_CORTE' },
          { armadorId: id, estado: 'EN_ARMADO' },
          { costureroId: id, estado: 'EN_COSTURA' },
          { soladorId: id, estado: 'EN_SOLADURA' },
          { emplantilladorId: id, estado: 'EN_EMPLANTILLADO' }
        ]
      },
      orderBy: { id: 'desc' }
    });

    res.json(tareas);
  } catch (error) {
    res.status(500).json({ error: 'Error al cargar tareas' });
  }
});
// ==========================================
// AVANZAR ORDEN (Detecta etapa y pasa a la siguiente)
// ==========================================
app.put('/api/ordenes/:id/avanzar', async (req: any, res: any) => {
  try {
    const { id } = req.params;
    const orden = await prisma.ordenProduccion.findUnique({ where: { id: Number(id) } });

    if (!orden) return res.status(404).json({ error: 'Orden no encontrada' });

    let nuevoEstado = '';
    let updateData: any = {};
    const ahora = new Date();

    // MÁQUINA DE ESTADOS: Define cuál es el siguiente paso
    switch (orden.estado) {
      case 'EN_CORTE':
        nuevoEstado = 'EN_ARMADO';
        updateData = { estado: nuevoEstado, fechaFinCorte: ahora };
        break;
      case 'EN_ARMADO':
        nuevoEstado = 'EN_SOLADURA'; 
        // Nota: Si usas Costura, cambia EN_SOLADURA por EN_COSTURA y ajusta el orden
        updateData = { estado: nuevoEstado, fechaFinArmado: ahora };
        break;
      case 'EN_SOLADURA': // O Costura si aplica
        nuevoEstado = 'EN_EMPLANTILLADO';
        updateData = { estado: nuevoEstado, fechaFinSoladura: ahora };
        break;
      case 'EN_EMPLANTILLADO':
        nuevoEstado = 'TERMINADO';
        updateData = { estado: nuevoEstado, fechaFinEmplantillado: ahora, fechaFinTerminado: ahora };
        break;
      default:
        return res.status(400).json({ error: 'La orden ya está terminada o en estado desconocido' });
    }

    const ordenActualizada = await prisma.ordenProduccion.update({
      where: { id: Number(id) },
      data: updateData
    });

    res.json({ message: `Orden avanzó a ${nuevoEstado}`, orden: ordenActualizada });

  } catch (error) {
    console.error("Error avanzando orden:", error);
    res.status(500).json({ error: 'Error interno al avanzar orden' });
  }
});

// CONFIRMAR TAREA DE CORTE (Transición a Armado)
app.put('/api/ordenes/:id/terminar-corte', async (req: any, res: any) => {
    try {
      const { id } = req.params;
      const orden = await prisma.ordenProduccion.findUnique({ where: { id: Number(id) }});
      
      if (!orden || orden.estado !== 'EN_CORTE') {
          return res.status(400).json({ error: 'Orden no disponible para terminar corte' });
      }
  
      const ordenActualizada = await prisma.ordenProduccion.update({
        where: { id: Number(id) },
        data: {
          estado: 'EN_ARMADO',       // Avanza al siguiente proceso
          fechaFinCorte: new Date()  // Marca fecha de cobro para cortador
        }
      });
      res.json({ message: 'Enviado a Armado', orden: ordenActualizada });
    } catch (error) { res.status(500).json({ error: 'Error al procesar' }); }
});

// ==========================================
// CÁLCULO DE NÓMINA (SOPORTE MULTI-ROL)
// ==========================================
app.get('/api/nomina/:empleadoId', async (req: any, res: any) => {
  try {
    const { empleadoId } = req.params;
    const { rol } = req.query; // Esperamos: "CORTE", "ARMADO", "COSTURA", etc.

    // 1. CONFIGURAR FILTROS SEGÚN EL ROL
    // Definimos qué campos buscar y qué fechas revisar
    let whereClause: any = {};
    let orderByClause: any = {};

    switch (rol) {
        case 'CORTE':
            whereClause = { cortadorId: Number(empleadoId), fechaFinCorte: { not: null } };
            orderByClause = { fechaFinCorte: 'desc' };
            break;
        case 'ARMADO': // o GUARNICION
            whereClause = { armadorId: Number(empleadoId), fechaFinArmado: { not: null } };
            orderByClause = { fechaFinArmado: 'desc' };
            break;
        case 'COSTURA':
            whereClause = { costureroId: Number(empleadoId), fechaFinCostura: { not: null } };
            orderByClause = { fechaFinCostura: 'desc' };
            break;
        case 'SOLADURA':
            whereClause = { soladorId: Number(empleadoId), fechaFinSoladura: { not: null } };
            orderByClause = { fechaFinSoladura: 'desc' };
            break;
        case 'EMPLANTILLADO':
            whereClause = { emplantilladorId: Number(empleadoId), fechaFinEmplantillado: { not: null } }; // Asumiendo que agregaste fechaFinEmplantillado
            // Si no tienes fechaFinEmplantillado en la BD aún, usa fechaFinSoladura o agrega el campo.
            orderByClause = { id: 'desc' }; 
            break;
        default:
            // Si no llega rol, no devolvemos nada por seguridad
            return res.json({ totalGanado: 0, detalle: [] });
    }

    // 2. BUSCAR TAREAS TERMINADAS
    const tareasRealizadas = await prisma.ordenProduccion.findMany({
      where: whereClause,
      orderBy: orderByClause
    });

    let totalPagar = 0;

    // 3. CALCULAR TOTALES USANDO EL PRECIO ESPECÍFICO
    const detalle = tareasRealizadas.map((orden: any) => {
      
      // Seleccionamos el precio y la fecha correcta según el rol
      let precioAplicado = 0;
      let fechaFin = null;

      if (rol === 'CORTE') {
          precioAplicado = orden.precioCorte;
          fechaFin = orden.fechaFinCorte;
      } else if (rol === 'ARMADO') {
          precioAplicado = orden.precioArmado;
          fechaFin = orden.fechaFinArmado;
      } else if (rol === 'COSTURA') {
          precioAplicado = orden.precioCostura;
          fechaFin = orden.fechaFinCostura;
      } else if (rol === 'SOLADURA') {
          precioAplicado = orden.precioSoladura;
          fechaFin = orden.fechaFinSoladura;
      } else if (rol === 'EMPLANTILLADO') {
          precioAplicado = orden.precioEmplantillado;
          fechaFin = orden.fechaFinEmplantillado || orden.fechaFinSoladura; // Fallback
      }

      // Cálculo matemático
      const subtotal = orden.totalPares * precioAplicado;
      totalPagar += subtotal;

      return {
        id: orden.id,
        numeroOrden: orden.numeroOrden,
        referencia: orden.referencia,
        pares: orden.totalPares,
        
        // Enviamos al frontend los datos procesados
        precio: precioAplicado, 
        subtotal: subtotal,
        fecha: fechaFin
      };
    });

    res.json({ totalGanado: totalPagar, detalle });

  } catch (error) {
    console.error("Error en nómina:", error);
    res.status(500).json({ error: 'Error calculando nómina' });
  }
});

// ==========================================
// ENDPOINT TABLERO DE PRODUCCIÓN 
// ==========================================
app.get('/api/produccion/tablero', async (req, res) => {
  try {
    // Es vital que las llaves se llamen 'ordenes' y 'empleados'
    const ordenes = await prisma.ordenProduccion.findMany({
      orderBy: { id: 'desc' }
    });
    const empleados = await prisma.usuario.findMany({
      where: { activo: true },
      select: { id: true, nombre: true, rol: true } // Traemos solo lo necesario
    });

    res.json({ ordenes, empleados });
  } catch (error) {
    console.error("Error en tablero:", error);
    res.status(500).json({ error: "No se pudo cargar el tablero" });
  }
});

// ==========================================
// 5. MÓDULO DE STOCK (CARGA MASIVA EXCEL)
// ==========================================

async function procesarHoja(sheet: xlsx.WorkSheet, sucursalAsignada: string, forzarTipo: string | null = null) {
  if (!sheet) return 0;
  const rawData = xlsx.utils.sheet_to_json(sheet, { header: 1 }) as any[];
  let contador = 0;

  for (let i = 0; i < rawData.length; i++) {
    const row = rawData[i];
    if (!row || row.length === 0) continue;

    const refColor = row[0]; 
    if (!refColor || typeof refColor !== 'string') continue;
    const texto = refColor.toUpperCase();
    
    // Validaciones de limpieza de Excel
    if (texto.includes('REF Y COLOR') || texto.includes('STOCK') || texto.includes('ENTRADAS')) continue;
    if (texto.startsWith('TOTAL')) continue;
    const meses = ['ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO','JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'];
    if (meses.some(mes => texto.includes(mes) && texto.length < 15)) continue;

    const partes = refColor.trim().split(' ');
    const referencia = partes[0].toUpperCase();
    const color = partes.slice(1).join(' ') || 'UNICO';

    let tipo = 'PLANA'; 
    if (forzarTipo) tipo = forzarTipo;
    else if (referencia.startsWith('Z') || referencia.startsWith('LOLA') || referencia.includes('TENIS') || referencia.startsWith('P')) {
        tipo = 'PLATAFORMA';
    }

    const t35 = parseIntSafe(row[2]); const t36 = parseIntSafe(row[3]); const t37 = parseIntSafe(row[4]);
    const t38 = parseIntSafe(row[5]); const t39 = parseIntSafe(row[6]); const t40 = parseIntSafe(row[7]);
    const t41 = parseIntSafe(row[8]); const t42 = parseIntSafe(row[9]);
    const total = t35 + t36 + t37 + t38 + t39 + t40 + t41 + t42;

    if (referencia.length > 1) {
      try {
        await prisma.inventarioZapatos.upsert({
          where: { referencia_color_sucursal: { referencia, color, sucursal: sucursalAsignada } },
          update: { t35, t36, t37, t38, t39, t40, t41, t42, total, tipo },
          create: { referencia, color, sucursal: sucursalAsignada, tipo, t35, t36, t37, t38, t39, t40, t41, t42, total }
        });
        contador++;
      } catch (e: any) {}
    }
  }
  return contador;
}

app.post('/api/stock/masivo', upload.single('file'), async (req: any, res: any) => {
  try {
    if (!req.file) return res.status(400).json({ error: 'No se recibió ningún archivo.' });
    const workbook = xlsx.readFile(req.file.path);
    
    // Limpieza previa
    await prisma.inventarioZapatos.deleteMany({ where: { sucursal: { in: ['CABECERA', 'FABRICA', 'TOTAL'] } } });

    const buscarHoja = (nombre: string) => {
      const nombreLimpio = nombre.toUpperCase().replace(/\s/g, ''); 
      const key = workbook.SheetNames.find(n => n.toUpperCase().replace(/\s/g, '').includes(nombreLimpio));
      return key ? workbook.Sheets[key] : null;
    };

    const p1 = procesarHoja(buscarHoja('CABECERA') as xlsx.WorkSheet, 'CABECERA', null);
    const p2 = procesarHoja(buscarHoja('FABRICA') as xlsx.WorkSheet, 'FABRICA', 'PLANA');
    const hojaZaraNombre = workbook.SheetNames.find(n => n.toUpperCase().includes('ZARA') || n.toUpperCase().includes('LOLA'));
    const p3 = procesarHoja(workbook.Sheets[hojaZaraNombre || ''] as xlsx.WorkSheet, 'FABRICA', 'PLATAFORMA');
    const p4 = procesarHoja(buscarHoja('TOTAL') as xlsx.WorkSheet, 'TOTAL', null);

    const [c1, c2, c3, c4] = await Promise.all([p1, p2, p3, p4]);
    try { fs.unlinkSync(req.file.path); } catch(e) {}

    res.json({ mensaje: 'Stock sincronizado', detalles: `Cargados: Cabecera (${c1}), Fabrica (${c2+c3}), Total (${c4})` });

  } catch (error) {
    console.error("❌ ERROR CRÍTICO EXCEL:", error);
    res.status(500).json({ error: 'Error procesando el Excel' });
  }
});

app.get('/api/stock/zapatos', async (req: any, res: any) => {
    const { sucursal } = req.query;
    try {
        const stock = await prisma.inventarioZapatos.findMany({
            where: sucursal ? { sucursal: String(sucursal) } : {},
            orderBy: { referencia: 'asc' }
        });
        res.json(stock);
    } catch(e) { res.status(500).json({error: "Error leyendo DB"}); }
});

// ==========================================
// 4. GESTIÓN DE PRODUCCIÓN (TABLERO ADMIN)
// ==========================================

// A. OBTENER EL TABLERO (Órdenes vivas + Empleados)
app.get('/api/produccion/tablero', async (req: any, res: any) => {
    try {
        // 1. Buscar todas las órdenes que NO estén terminadas
        const ordenes = await prisma.ordenProduccion.findMany({
            where: {
                estado: { not: 'TERMINADO' }
            },
            orderBy: { id: 'desc' }
        });

        // 2. Buscar empleados (excluyendo al admin para no asignarle tareas manuales)
        const empleados = await prisma.usuario.findMany({
            where: { rol: { not: 'ADMIN' } },
            select: { id: true, nombre: true, rol: true } // Solo traemos lo necesario
        });

        res.json({ ordenes, empleados });

    } catch (error) {
        console.error("Error obteniendo tablero:", error);
        res.status(500).json({ error: 'Error al cargar el tablero' });
    }
});

// B. ASIGNAR TRABAJADOR Y CAMBIAR ESTADO
app.post('/api/produccion/asignar', async (req: any, res: any) => {
    const { ordenId, empleadoId, rol, nuevoEstado } = req.body;

    try {
        // Convertimos a número porque vienen como string del frontend
        const idOrden = parseInt(ordenId);
        const idEmpleado = parseInt(empleadoId);

        // Preparamos el objeto de actualización dinámica
        let datosActualizar: any = { 
            estado: nuevoEstado 
        };

        // Según el rol, llenamos la casilla correspondiente en la BD
        if (rol === 'ARMADOR') datosActualizar.armadorId = idEmpleado;
        else if (rol === 'COSTURERO') datosActualizar.costureroId = idEmpleado;
        else if (rol === 'SOLADOR') datosActualizar.soladorId = idEmpleado;
        else if (rol === 'EMPLANTILLADOR') datosActualizar.emplantilladorId = idEmpleado;

        // Ejecutamos la actualización en Prisma
        await prisma.ordenProduccion.update({
            where: { id: idOrden },
            data: datosActualizar
        });

        console.log(`✅ Orden #${idOrden} asignada a empleado ${idEmpleado} como ${rol}`);
        res.json({ mensaje: 'Asignación exitosa' });

    } catch (error) {
        console.error("Error asignando tarea:", error);
        res.status(500).json({ error: 'Error al guardar la asignación' });
    }
});


// ==========================================
// ENDPOINT PARA TERMINAR TAREA DESDE CELULAR
// ==========================================
app.post('/api/produccion/terminar-tarea', async (req: any, res: any) => {
    try {
        const { ordenId, rol } = req.body;
        console.log(`🔔 Petición recibida: Terminar Orden ${ordenId} para el rol ${rol}`);

        const ahora = new Date();
        
        // 1. Declaramos la variable con un tipo flexible para evitar el error
        let datosActualizar: any = {};
        let nuevoEstado = '';

        // 2. Mapeo de estados
        const rolUpper = rol.toUpperCase();
        
        if (rolUpper === 'CORTE') {
            nuevoEstado = 'EN_ARMADO';
            datosActualizar = { estado: nuevoEstado, fechaFinCorte: ahora };
        } 
        else if (rolUpper === 'ARMADOR' || rolUpper === 'ARMADO') {
            nuevoEstado = 'EN_COSTURA';
            datosActualizar = { estado: nuevoEstado, fechaFinArmado: ahora };
        }
        else if (rolUpper === 'COSTURERO' || rolUpper === 'COSTURA') {
            nuevoEstado = 'EN_SOLADURA';
            datosActualizar = { estado: nuevoEstado, fechaFinCostura: ahora };
        }
        else if (rolUpper === 'SOLADOR' || rolUpper === 'SOLADURA') {
            nuevoEstado = 'EN_EMPLANTILLADO';
            datosActualizar = { estado: nuevoEstado, fechaFinSoladura: ahora };
        }
        else if (rolUpper === 'EMPLANTILLADOR') {
            nuevoEstado = 'TERMINADO';
            datosActualizar = { 
                estado: nuevoEstado, 
                fechaFinEmplantillado: ahora, 
                fechaFinTerminado: ahora 
            };
        } else {
            return res.status(400).json({ error: 'Rol no válido' });
        }

        // 3. Actualizamos en la Base de Datos
        await prisma.ordenProduccion.update({
            where: { id: parseInt(ordenId) },
            data: datosActualizar
        });

        // 4. Usamos la variable 'nuevoEstado' que definimos arriba
        console.log(`✅ Orden ${ordenId} actualizada a estado: ${nuevoEstado}`);
        res.json({ 
            mensaje: '¡Tarea terminada con éxito!', 
            nuevoEstado: nuevoEstado 
        });

    } catch (error) {
        console.error("❌ Error al terminar tarea:", error);
        res.status(500).json({ error: 'Error interno del servidor' });
    }
});

// ==========================================
// 6. INICIAR SERVIDOR
// ==========================================
app.listen(PORT, '0.0.0.0', () => {
  console.log(`
  🚀 SERVIDOR REINICIADO Y LISTO
  --------------------------------
  👉 Local:   http://localhost:${PORT}
  👉 Red:     http://127.0.0.1:${PORT}
  --------------------------------
  `);
});