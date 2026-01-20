/*
  Warnings:

  - You are about to drop the column `email` on the `Usuario` table. All the data in the column will be lost.
  - Added the required column `username` to the `Usuario` table without a default value. This is not possible if the table is not empty.

*/
-- CreateTable
CREATE TABLE "InventarioZapatos" (
    "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    "referencia" TEXT NOT NULL,
    "color" TEXT NOT NULL,
    "sucursal" TEXT NOT NULL,
    "tipo" TEXT NOT NULL DEFAULT 'PLANA',
    "t35" INTEGER NOT NULL DEFAULT 0,
    "t36" INTEGER NOT NULL DEFAULT 0,
    "t37" INTEGER NOT NULL DEFAULT 0,
    "t38" INTEGER NOT NULL DEFAULT 0,
    "t39" INTEGER NOT NULL DEFAULT 0,
    "t40" INTEGER NOT NULL DEFAULT 0,
    "t41" INTEGER NOT NULL DEFAULT 0,
    "t42" INTEGER NOT NULL DEFAULT 0,
    "total" INTEGER NOT NULL DEFAULT 0,
    "updatedAt" DATETIME NOT NULL
);

-- RedefineTables
PRAGMA defer_foreign_keys=ON;
PRAGMA foreign_keys=OFF;
CREATE TABLE "new_Usuario" (
    "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    "nombre" TEXT NOT NULL,
    "apellido" TEXT NOT NULL,
    "username" TEXT NOT NULL,
    "password" TEXT NOT NULL,
    "rol" TEXT NOT NULL,
    "activo" BOOLEAN NOT NULL DEFAULT true,
    "createdAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO "new_Usuario" ("activo", "apellido", "createdAt", "id", "nombre", "password", "rol") SELECT "activo", "apellido", "createdAt", "id", "nombre", "password", "rol" FROM "Usuario";
DROP TABLE "Usuario";
ALTER TABLE "new_Usuario" RENAME TO "Usuario";
CREATE UNIQUE INDEX "Usuario_username_key" ON "Usuario"("username");
PRAGMA foreign_keys=ON;
PRAGMA defer_foreign_keys=OFF;

-- CreateIndex
CREATE UNIQUE INDEX "InventarioZapatos_referencia_color_sucursal_key" ON "InventarioZapatos"("referencia", "color", "sucursal");
