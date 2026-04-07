/*
  Warnings:

  - You are about to drop the `OrdenProduccionTalla` table. If the table is not empty, all the data it contains will be lost.
  - You are about to drop the `ProcesoProduccion` table. If the table is not empty, all the data it contains will be lost.
  - You are about to drop the column `createdAt` on the `OrdenProduccion` table. All the data in the column will be lost.
  - You are about to drop the column `fechaFinEstimada` on the `OrdenProduccion` table. All the data in the column will be lost.
  - You are about to drop the column `localDestinoId` on the `OrdenProduccion` table. All the data in the column will be lost.
  - You are about to drop the column `productoId` on the `OrdenProduccion` table. All the data in the column will be lost.
  - Added the required column `color` to the `OrdenProduccion` table without a default value. This is not possible if the table is not empty.
  - Added the required column `destino` to the `OrdenProduccion` table without a default value. This is not possible if the table is not empty.
  - Added the required column `materiales` to the `OrdenProduccion` table without a default value. This is not possible if the table is not empty.
  - Added the required column `numeroOrden` to the `OrdenProduccion` table without a default value. This is not possible if the table is not empty.
  - Added the required column `referencia` to the `OrdenProduccion` table without a default value. This is not possible if the table is not empty.
  - Added the required column `totalPares` to the `OrdenProduccion` table without a default value. This is not possible if the table is not empty.

*/
-- DropTable
PRAGMA foreign_keys=off;
DROP TABLE "OrdenProduccionTalla";
PRAGMA foreign_keys=on;

-- DropTable
PRAGMA foreign_keys=off;
DROP TABLE "ProcesoProduccion";
PRAGMA foreign_keys=on;

-- CreateTable
CREATE TABLE "TarifaCategoria" (
    "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    "nombre" TEXT NOT NULL,
    "precioCorte" INTEGER NOT NULL DEFAULT 0,
    "precioGuarnicion" INTEGER NOT NULL DEFAULT 0,
    "precioSoladura" INTEGER NOT NULL DEFAULT 0
);

-- RedefineTables
PRAGMA defer_foreign_keys=ON;
PRAGMA foreign_keys=OFF;
CREATE TABLE "new_OrdenProduccion" (
    "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    "numeroOrden" TEXT NOT NULL,
    "fechaInicio" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "estado" TEXT NOT NULL DEFAULT 'EN_CORTE',
    "referencia" TEXT NOT NULL,
    "color" TEXT NOT NULL,
    "fotoUrl" TEXT,
    "materiales" TEXT NOT NULL,
    "observacion" TEXT,
    "categoria" TEXT NOT NULL DEFAULT 'ROMANA',
    "precioPactado" INTEGER NOT NULL DEFAULT 0,
    "destino" TEXT NOT NULL,
    "clienteId" INTEGER,
    "cortadorId" INTEGER,
    "armadorId" INTEGER,
    "costureroId" INTEGER,
    "soladorId" INTEGER,
    "emplantilladorId" INTEGER,
    "t34" INTEGER NOT NULL DEFAULT 0,
    "t35" INTEGER NOT NULL DEFAULT 0,
    "t36" INTEGER NOT NULL DEFAULT 0,
    "t37" INTEGER NOT NULL DEFAULT 0,
    "t38" INTEGER NOT NULL DEFAULT 0,
    "t39" INTEGER NOT NULL DEFAULT 0,
    "t40" INTEGER NOT NULL DEFAULT 0,
    "t41" INTEGER NOT NULL DEFAULT 0,
    "t42" INTEGER NOT NULL DEFAULT 0,
    "t43" INTEGER NOT NULL DEFAULT 0,
    "t44" INTEGER NOT NULL DEFAULT 0,
    "totalPares" INTEGER NOT NULL,
    CONSTRAINT "OrdenProduccion_clienteId_fkey" FOREIGN KEY ("clienteId") REFERENCES "Cliente" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT "OrdenProduccion_cortadorId_fkey" FOREIGN KEY ("cortadorId") REFERENCES "Usuario" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT "OrdenProduccion_armadorId_fkey" FOREIGN KEY ("armadorId") REFERENCES "Usuario" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT "OrdenProduccion_costureroId_fkey" FOREIGN KEY ("costureroId") REFERENCES "Usuario" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT "OrdenProduccion_soladorId_fkey" FOREIGN KEY ("soladorId") REFERENCES "Usuario" ("id") ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT "OrdenProduccion_emplantilladorId_fkey" FOREIGN KEY ("emplantilladorId") REFERENCES "Usuario" ("id") ON DELETE SET NULL ON UPDATE CASCADE
);
INSERT INTO "new_OrdenProduccion" ("clienteId", "estado", "fechaInicio", "id") SELECT "clienteId", "estado", "fechaInicio", "id" FROM "OrdenProduccion";
DROP TABLE "OrdenProduccion";
ALTER TABLE "new_OrdenProduccion" RENAME TO "OrdenProduccion";
CREATE UNIQUE INDEX "OrdenProduccion_numeroOrden_key" ON "OrdenProduccion"("numeroOrden");
PRAGMA foreign_keys=ON;
PRAGMA defer_foreign_keys=OFF;

-- CreateIndex
CREATE UNIQUE INDEX "TarifaCategoria_nombre_key" ON "TarifaCategoria"("nombre");
