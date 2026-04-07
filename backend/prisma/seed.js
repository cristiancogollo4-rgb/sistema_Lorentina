import { PrismaClient } from '@prisma/client'
import bcrypt from 'bcryptjs'

const prisma = new PrismaClient()

async function main() {
  // 1. Encriptar la contraseña genérica "1234"
  const password = await bcrypt.hash('1234', 10)

  console.log('🌱 Iniciando carga de base de datos...')

  // 2. CREAR USUARIO ADMINISTRADOR (CRISTIAN)
  const adminExistente = await prisma.usuario.findUnique({ where: { username: 'cristian' } })
  if (!adminExistente) {
    await prisma.usuario.create({
      data: {
        nombre: 'Cristian',
        apellido: 'Administrador',
        username: 'cristian',
        password: password,
        rol: 'ADMIN',
        activo: true,
        telefono: '3000000000',
        cedula: '111111111'
      }
    })
    console.log('👑 Usuario ADMIN "cristian" creado con éxito.')
  }

  // 3. LISTA DE EMPLEADOS CON ROLES ESTANDARIZADOS (Coinciden con index.ts)
  const empleados = [
    // --- CORTE ---
    { nombre: 'Jorge', apellido: 'Pérez', username: 'jorge.perez', rol: 'CORTE' },
    { nombre: 'Jhon', apellido: 'Gómez', username: 'jhon.gomez', rol: 'CORTE' },

    // --- ARMADORES (Cambiado de ARMADO a ARMADOR) ---
    { nombre: 'Jackeline', apellido: 'Rojas', username: 'jackeline.rojas', rol: 'ARMADOR' },
    { nombre: 'Sandra', apellido: 'Milena', username: 'sandra.milena', rol: 'ARMADOR' },
    { nombre: 'Ana', apellido: 'Castellano', username: 'ana.castellano', rol: 'ARMADOR' },

    // --- COSTUREROS (Cambiado de COSTURA a COSTURERO) ---
    { nombre: 'Yolanda', apellido: 'Díaz', username: 'yolanda.diaz', rol: 'COSTURERO' },
    { nombre: 'Andrea', apellido: 'Ruiz', username: 'andrea.ruiz', rol: 'COSTURERO' },

    // --- SOLADORES (Cambiado de SOLADURA a SOLADOR) ---
    { nombre: 'Julian', apellido: 'Martínez', username: 'julian.martinez', rol: 'SOLADOR' },
    { nombre: 'Cesar', apellido: 'Romero', username: 'cesar.romero', rol: 'SOLADOR' },

    // --- EMPLANTILLADORES ---
    { nombre: 'Ricardo', apellido: 'Sosa', username: 'ricardo.sosa', rol: 'EMPLANTILLADOR' }
  ]

  for (const emp of empleados) {
    const existe = await prisma.usuario.findUnique({ where: { username: emp.username } })
    
    if (!existe) {
      await prisma.usuario.create({
        data: {
          nombre: emp.nombre,
          apellido: emp.apellido,
          username: emp.username,
          password: password,
          rol: emp.rol,
          activo: true,
          telefono: '3000000000',
          cedula: '123456789'
        }
      })
      console.log(`✅ Empleado creado: ${emp.nombre} (${emp.rol})`)
    } else {
      console.log(`⚠️ Omitido (Ya existe): ${emp.username}`)
    }
  }
}

main()
  .catch((e) => {
    console.error(e)
    process.exit(1)
  })
  .finally(async () => {
    await prisma.$disconnect()
  })