/*
  Warnings:

  - You are about to drop the column `fechaTerminado` on the `OrdenProduccion` table. All the data in the column will be lost.

*/
-- RedefineTables
PRAGMA defer_foreign_keys=ON;
PRAGMA foreign_keys=OFF;
CREATE TABLE "new_OrdenProduccion" (
    "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    "numeroOrden" TEXT NOT NULL,
    "fechaInicio" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "fechaFinCorte" DATETIME,
    "fechaFinArmado" DATETIME,
    "fechaFinSoladura" DATETIME,
    "fechaFinTerminado" DATETIME,
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
INSERT INTO "new_OrdenProduccion" ("armadorId", "categoria", "clienteId", "color", "cortadorId", "costureroId", "destino", "emplantilladorId", "estado", "fechaInicio", "fotoUrl", "id", "materiales", "numeroOrden", "observacion", "precioPactado", "referencia", "soladorId", "t34", "t35", "t36", "t37", "t38", "t39", "t40", "t41", "t42", "t43", "t44", "totalPares") SELECT "armadorId", "categoria", "clienteId", "color", "cortadorId", "costureroId", "destino", "emplantilladorId", "estado", "fechaInicio", "fotoUrl", "id", "materiales", "numeroOrden", "observacion", "precioPactado", "referencia", "soladorId", "t34", "t35", "t36", "t37", "t38", "t39", "t40", "t41", "t42", "t43", "t44", "totalPares" FROM "OrdenProduccion";
DROP TABLE "OrdenProduccion";
ALTER TABLE "new_OrdenProduccion" RENAME TO "OrdenProduccion";
CREATE UNIQUE INDEX "OrdenProduccion_numeroOrden_key" ON "OrdenProduccion"("numeroOrden");
PRAGMA foreign_keys=ON;
PRAGMA defer_foreign_keys=OFF;
