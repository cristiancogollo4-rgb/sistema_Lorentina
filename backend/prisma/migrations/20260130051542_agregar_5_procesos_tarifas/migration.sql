/*
  Warnings:

  - You are about to drop the column `precioGuarnicion` on the `TarifaCategoria` table. All the data in the column will be lost.

*/
-- RedefineTables
PRAGMA defer_foreign_keys=ON;
PRAGMA foreign_keys=OFF;
CREATE TABLE "new_TarifaCategoria" (
    "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    "nombre" TEXT NOT NULL,
    "precioCorte" INTEGER NOT NULL DEFAULT 0,
    "precioArmado" INTEGER NOT NULL DEFAULT 0,
    "precioCostura" INTEGER NOT NULL DEFAULT 0,
    "precioSoladura" INTEGER NOT NULL DEFAULT 0,
    "precioEmplantillado" INTEGER NOT NULL DEFAULT 0
);
INSERT INTO "new_TarifaCategoria" ("id", "nombre", "precioCorte", "precioSoladura") SELECT "id", "nombre", "precioCorte", "precioSoladura" FROM "TarifaCategoria";
DROP TABLE "TarifaCategoria";
ALTER TABLE "new_TarifaCategoria" RENAME TO "TarifaCategoria";
CREATE UNIQUE INDEX "TarifaCategoria_nombre_key" ON "TarifaCategoria"("nombre");
PRAGMA foreign_keys=ON;
PRAGMA defer_foreign_keys=OFF;
