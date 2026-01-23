import { PrismaClient } from '@prisma/client'
import bcrypt from 'bcryptjs'

const prisma = new PrismaClient()

async function main() {
  // 1. Encriptar la contraseña genérica "1234"
  const password = await bcrypt.hash('1234', 10)

  console.log('🌱 Iniciando carga de empleados con datos completos...')

  // 2. Lista exacta con apellidos inventados y roles asignados
  const empleados = [
    // --- CORTE ---
    { nombre: 'Jorge', apellido: 'Pérez', username: 'jorge.perez', rol: 'CORTE' },
    { nombre: 'Jhon', apellido: 'Gómez', username: 'jhon.gomez', rol: 'CORTE' },

    // --- ARMADO ---
    { nombre: 'Jackeline', apellido: 'Rojas', username: 'jackeline.rojas', rol: 'ARMADO' },
    { nombre: 'Sandra', apellido: 'Milena', username: 'sandra.milena', rol: 'ARMADO' },
    { nombre: 'Ana', apellido: 'Castellano', username: 'ana.castellano', rol: 'ARMADO' },
    { nombre: 'Yuleidy', apellido: 'Méndez', username: 'yuleidy.mendez', rol: 'ARMADO' },
    { nombre: 'Diana', apellido: 'Vargas', username: 'diana.vargas', rol: 'ARMADO' },
    { nombre: 'Sandra', apellido: 'Olarte', username: 'sandra.olarte', rol: 'ARMADO' },

    // --- COSTURA ---
    { nombre: 'Yolanda', apellido: 'Díaz', username: 'yolanda.diaz', rol: 'COSTURA' },
    { nombre: 'Andrea', apellido: 'Ruiz', username: 'andrea.ruiz', rol: 'COSTURA' },
    { nombre: 'Viviana', apellido: 'Castro', username: 'viviana.castro', rol: 'COSTURA' },

    // --- SOLADURA ---
    { nombre: 'Julian', apellido: 'Martínez', username: 'julian.martinez', rol: 'SOLADURA' },
    { nombre: 'Cesar', apellido: 'Romero', username: 'cesar.romero', rol: 'SOLADURA' },
    { nombre: 'Rodolfo', apellido: 'Vega', username: 'rodolfo.vega', rol: 'SOLADURA' },
  ]

  for (const emp of empleados) {
    // Verificamos si ya existe para no duplicar error
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
          telefono: '3000000000', // Teléfono genérico
          cedula: '123456789'     // Cédula genérica
        }
      })
      console.log(`✅ Creado: ${emp.nombre} ${emp.apellido} (${emp.rol})`)
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