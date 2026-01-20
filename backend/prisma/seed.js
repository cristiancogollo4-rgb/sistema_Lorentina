// backend/prisma/seed.js

const { PrismaClient } = require('@prisma/client');

const prisma = new PrismaClient();

async function main() {
  console.log('🌱 Iniciando la siembra de datos (Modo JS)...');

  // 1. Crear Locales (Sedes)
  const fabrica = await prisma.local.create({
    data: {
      nombre: 'Fábrica Principal',
      direccion: 'Calle 10 # 5-20, Zona Industrial',
      activo: true,
    },
  });

  const tiendaCentro = await prisma.local.create({
    data: {
      nombre: 'Tienda Centro',
      direccion: 'Carrera 5 # 8-12',
      activo: true,
    },
  });

  console.log('✅ Locales creados.');

  // 2. Crear Usuarios
  const admin = await prisma.usuario.create({
    data: {
      nombre: 'Carlos',
      apellido: 'Admin',
      email: 'admin@lorentina.com',
      password: 'admin123',
      rol: 'ADMIN',
      activo: true,
    },
  });

  const cortador = await prisma.usuario.create({
    data: {
      nombre: 'Pedro',
      apellido: 'Perez',
      email: 'pedro@lorentina.com',
      password: '123',
      rol: 'CORTADOR',
    },
  });

  console.log('✅ Usuarios creados (Admin y Cortador).');

  // 3. Crear Clientes
  const clienteGenerico = await prisma.cliente.create({
    data: {
      nombre: 'Cliente Mostrador',
      tipoCliente: 'DETAL',
      email: 'ventas@lorentina.com',
    },
  });

  console.log('✅ Cliente genérico creado.');

  // 4. Crear un Producto (Zapato)
  const zapatoEscolar = await prisma.producto.create({
    data: {
      nombreModelo: 'Mocasín Escolar Mafalda',
      descripcion: 'Zapato escolar clásico en cuero, suela de goma.',
      precioDetal: 85000,
      precioMayor: 65000,
      costoProduccion: 45000,
      activo: true,
    },
  });

  console.log('✅ Producto creado: Mocasín Escolar.');

  // 5. Crear Stock Inicial
  await prisma.stock.create({
    data: {
      productoId: zapatoEscolar.id,
      localId: tiendaCentro.id,
      talla: 36,
      paresDisponibles: 5,
    },
  });

  await prisma.stock.create({
    data: {
      productoId: zapatoEscolar.id,
      localId: fabrica.id,
      talla: 38,
      paresDisponibles: 20,
    },
  });

  console.log('✅ Inventario inicial cargado.');
  console.log('🚀 ¡Base de datos lista para usar!');
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });