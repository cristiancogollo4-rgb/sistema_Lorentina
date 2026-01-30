import express from 'express';
import cors from 'cors';
import { PrismaClient } from '@prisma/client';
import multer from 'multer';
import * as xlsx from 'xlsx';
import fs from 'fs';
import * as path from 'path';
import bcrypt from 'bcryptjs'; // <--- IMPORTANTE: Para las contraseñas

// --- CONFIGURACIÓN INICIAL ---
const app = express();
const PORT = 4000;
const prisma = new PrismaClient();

// 1. CORS
app.use(cors({
    origin: '*', 
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization']
}));

// 2. AUMENTAR TAMAÑO MAXIMO
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ limit: '50mb', extended: true }));

// 3. LOG "CHISMOSO"
app.use((req, res, next) => {
  console.log(`🔔 Petición entrante: ${req.method} ${req.url}`);
  next();
});

// --- CONFIGURACIÓN DE CARPETA Y MULTER ---
const uploadDir = path.join(__dirname, '../uploads');
if (!fs.existsSync(uploadDir)) {
    console.log("📁 Creando carpeta uploads...");
    fs.mkdirSync(uploadDir, { recursive: true });
}
const upload = multer({ dest: uploadDir });

// --- UTILIDADES ---
const parseIntSafe = (value: any): number => {
  if (!value) return 0;
  const parsed = parseInt(value);
  return isNaN(parsed) ? 0 : parsed;
};

// ==========================================
//  SECCIÓN DE STOCK (EXCEL)
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
    
    // Validaciones básicas para saltar filas que no son zapatos
    if (texto.includes('REF Y COLOR') || texto.includes('STOCK') || texto.includes('ENTRADAS')) continue;
    if (texto.startsWith('TOTAL')) continue;
    const meses = ['ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO','JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'];
    if (meses.some(mes => texto.includes(mes) && texto.length < 15)) continue;

    // Procesar datos
    const partes = refColor.trim().split(' ');
    const referencia = partes[0].toUpperCase();
    const color = partes.slice(1).join(' ') || 'UNICO';

    let tipo = 'PLANA'; 
    if (forzarTipo) {
      tipo = forzarTipo;
    } else {
      if (referencia.startsWith('Z') || referencia.startsWith('LOLA') || referencia.includes('TENIS') || referencia.startsWith('P')) {
        tipo = 'PLATAFORMA';
      }
    }

    const t35 = parseIntSafe(row[2]);
    const t36 = parseIntSafe(row[3]);
    const t37 = parseIntSafe(row[4]);
    const t38 = parseIntSafe(row[5]);
    const t39 = parseIntSafe(row[6]);
    const t40 = parseIntSafe(row[7]);
    const t41 = parseIntSafe(row[8]);
    const t42 = parseIntSafe(row[9]);
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

// RUTA: SUBIDA MASIVA
app.post('/api/stock/masivo', upload.single('file'), async (req: any, res: any) => {
  try {
    if (!req.file) return res.status(400).json({ error: 'No se recibió ningún archivo.' });

    const workbook = xlsx.readFile(req.file.path);
    
    // Limpieza previa
    await prisma.inventarioZapatos.deleteMany({
        where: { sucursal: { in: ['CABECERA', 'FABRICA', 'TOTAL'] } }
    });

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

    res.json({ 
      mensaje: 'Stock sincronizado correctamente', 
      detalles: `Se borró lo anterior y se cargó: Cabecera (${c1}), Fabrica (${c2+c3}), Total (${c4})`
    });

  } catch (error) {
    console.error("❌ ERROR CRÍTICO:", error);
    res.status(500).json({ error: 'Error procesando el Excel' });
  }
});

// RUTA: OBTENER STOCK
app.get('/api/stock/zapatos', async (req: any, res: any) => {
    const { sucursal } = req.query;
    try {
        const stock = await prisma.inventarioZapatos.findMany({
            where: sucursal ? { sucursal: String(sucursal) } : {},
            orderBy: { referencia: 'asc' }
        });
        res.json(stock);
    } catch(e) {
        res.status(500).json({error: "Error leyendo DB"});
    }
});

// ==========================================
//  SECCIÓN DE USUARIOS (EMPLEADOS)
// ==========================================

// 1. CREAR USUARIOa
app.post('/api/usuarios', async (req: any, res: any) => {
    try {
      const { nombre, apellido, username, password, rol, telefono, cedula, activo } = req.body;
  
      // Validar duplicado
      const existe = await prisma.usuario.findUnique({ where: { username } });
      if (existe) {
          return res.status(400).json({ error: `El usuario '${username}' ya existe.` });
      }
  
      // Encriptar contraseña
      const hashedPassword = await bcrypt.hash(password, 10);
  
      const nuevoUsuario = await prisma.usuario.create({
        data: {
          nombre,
          apellido,
          username,
          password: hashedPassword,
          rol,
          telefono,
          cedula,
          activo: activo !== undefined ? activo : true // Valor por defecto si no viene
        }
      });
  
      res.json(nuevoUsuario);
    } catch (error) {
      console.error("Error creando usuario:", error);
      res.status(500).json({ error: 'Error interno al crear usuario' });
    }
  });
  
  // 2. LISTAR USUARIOS 
app.get('/api/usuarios', async (req: any, res: any) => {
  try {
    const usuarios = await prisma.usuario.findMany({
      orderBy: { id: 'desc' } // Muestra los más nuevos primero
    });
    res.json(usuarios);
  } catch (error) {
    console.error("Error obteniendo usuarios:", error);
    res.status(500).json({ error: 'Error al obtener la lista' });
  }
});
  // --- NUEVO: 2.5 ACTUALIZAR USUARIO (COMPLETAR PERFIL) ---
  app.put('/api/usuarios/:id', async (req: any, res: any) => {
    try {
      const { id } = req.params;
      const { nombre, apellido, username, rol, password, telefono, cedula, activo } = req.body;

      // Preparamos los datos a actualizar
      const datosActualizar: any = {
        nombre,
        apellido,
        username,
        rol,
        telefono,
        cedula,       // Ahora guardaremos string separado por comas: "ARMADO,COSTURA"
        activo: Boolean(activo), // Importante para "Despedir" sin borrar historial
        // Aquí podrías agregar campos futuros: sueldo_base, fecha_ingreso, etc.
      };

      // Si envían contraseña, la encriptamos. Si viene vacía, NO la tocamos.
      if (password && password.trim() !== "") {
        datosActualizar.password = await bcrypt.hash(password, 10);
      }

      const usuarioActualizado = await prisma.usuario.update({
        where: { id: Number(id) },
        data: datosActualizar
      });

      res.json(usuarioActualizado);
    } catch (error) {
      console.error("Error actualizando:", error);
      res.status(500).json({ error: 'No se pudo actualizar el perfil' });
    }
  });
  
  // 3. ELIMINAR USUARIO
  app.delete('/api/usuarios/:id', async (req: any, res: any) => {
    try {
      await prisma.usuario.delete({ where: { id: Number(req.params.id) } });
      res.json({ message: 'Usuario eliminado' });
    } catch (error) {
      res.status(500).json({ error: 'No se pudo eliminar' });
    }
  });

  // 4. LOGIN (ACTUALIZADO CON USERNAME Y BCRYPT)
 // 4. LOGIN (HÍBRIDO: Funciona para Web y para Android)
app.post('/api/login', async (req: any, res: any) => {
  const { username, password } = req.body;

  try {
    const usuario = await prisma.usuario.findUnique({ where: { username } });

    if (!usuario) {
      return res.status(401).json({ error: 'Usuario no encontrado' });
    }

    const validPassword = await bcrypt.compare(password, usuario.password);

    if (!validPassword) {
      return res.status(401).json({ error: 'Contraseña incorrecta' });
    }

    // Quitamos la contraseña para no enviarla
    const { password: _, ...datosUsuario } = usuario;

    // --- EL TRUCO DE MAGIA AQUÍ ---
    res.json({
      // 1. FORMATO PARA ANDROID (Datos sueltos en la raíz)
      ...datosUsuario, 
      
      // 2. FORMATO PARA LA WEB (Datos dentro de "usuario")
      // Esto asegura que tu frontend de React/Vue/Angular NO se rompa
      usuario: datosUsuario 
    });

  } catch (error) {
    console.error(error);
    res.status(500).json({ error: 'Error interno en login' });
  }
});

  // ==========================================
// 5. RUTAS DE PRODUCCIÓN
// ==========================================

// A. OBTENER CORTADORES (Para llenar el select)
app.get('/api/empleados/corte', async (req: any, res: any) => {
  try {
    const cortadores = await prisma.usuario.findMany({
      where: { rol: 'CORTE', activo: true }
    });
    res.json(cortadores);
  } catch (error) {
    res.status(500).json({ error: 'Error cargando cortadores' });
  }
});

// B. CREAR ORDEN DE PRODUCCIÓN
app.post('/api/produccion', async (req: any, res: any) => {
  try {
    const data = req.body;
    
    // Calcular total de pares sumando las tallas
    const totalPares = 
      (Number(data.t34)||0) + (Number(data.t35)||0) + (Number(data.t36)||0) +
      (Number(data.t37)||0) + (Number(data.t38)||0) + (Number(data.t39)||0) +
      (Number(data.t40)||0) + (Number(data.t41)||0) + (Number(data.t42)||0) +
      (Number(data.t43)||0) + (Number(data.t44)||0);

    const nuevaOrden = await prisma.ordenProduccion.create({
      data: {
        numeroOrden: `OP-${Date.now().toString().slice(-6)}`, // Genera OP-839210
        referencia: data.referencia,
        color: data.color,
        materiales: data.materiales,
        observacion: data.observacion,
        destino: data.destino,
        // Si viene cortadorId, lo convertimos a número
        cortadorId: data.cortadorId ? Number(data.cortadorId) : null,
        
        // Tallas
        t34: Number(data.t34)||0, t35: Number(data.t35)||0, t36: Number(data.t36)||0,
        t37: Number(data.t37)||0, t38: Number(data.t38)||0, t39: Number(data.t39)||0,
        t40: Number(data.t40)||0, t41: Number(data.t41)||0, t42: Number(data.t42)||0,
        t43: Number(data.t43)||0, t44: Number(data.t44)||0,
        
        totalPares,
        estado: 'EN_CORTE'
      }
    });

    res.json(nuevaOrden);
  } catch (error) {
    console.error("Error creando orden:", error);
    res.status(500).json({ error: 'Error interno al crear orden' });
  }
});

// ==========================================
// 6. RUTAS PARA LA APP MÓVIL (CORTADORES)
// ==========================================
// --- OBTENER PERFIL DE USUARIO ---
app.get('/api/usuarios/:id', async (req: any, res: any) => {
  try {
    const { id } = req.params;
    const usuario = await prisma.usuario.findUnique({
      where: { id: Number(id) }
    });

    if (!usuario) {
      return res.status(404).json({ error: 'Usuario no encontrado' });
    }

    // Devolvemos los datos (sin la contraseña por seguridad)
    const { password, ...datosPublicos } = usuario;
    res.json(datosPublicos);
  } catch (error) {
    console.error("Error al obtener perfil:", error);
    res.status(500).json({ error: 'Error interno' });
  }
});

// Obtener tareas asignadas a un empleado específico
app.get('/api/mis-tareas/:empleadoId', async (req: any, res: any) => {
  try {
    const { empleadoId } = req.params;

    const tareas = await prisma.ordenProduccion.findMany({
      where: {
        cortadorId: Number(empleadoId), // Filtra por el ID del cortador
        estado: 'EN_CORTE'              // Solo muestra lo pendiente
      },
      orderBy: {
        numeroOrden: 'desc'
      }
    });

    res.json(tareas);
  } catch (error) {
    console.error("Error buscando tareas:", error);
    res.status(500).json({ error: 'Error al cargar tareas' });
  }
});

// --- INICIAR SERVIDOR ---
app.listen(PORT, '0.0.0.0', () => {
  console.log(`
  🚀 SERVIDOR REINICIADO Y LISTO
  --------------------------------
  👉 Local:   http://localhost:${PORT}
  👉 Red:     http://127.0.0.1:${PORT}
  --------------------------------
  `);
});