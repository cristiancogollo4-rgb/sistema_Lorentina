import json
import sqlite3
import os

# Paths
BASE_DIR = r"c:\Users\LENOVO LOQ\Desktop\PROYECTOS\Lorentina"
DB_PATH = os.path.join(BASE_DIR, "backend-laravel", "database", "database.sqlite")
CATALOG_PATH = os.path.join(BASE_DIR, "backend-laravel", "database", "seeders", "data", "productos_link_catalog.json")

def normalize(val):
    if not val: return ""
    return str(val).strip().upper()

try:
    # 1. Load Catalog
    with open(CATALOG_PATH, 'r', encoding='utf-8') as f:
        catalog = json.load(f)
    
    # Map catalog by ref|color|tipo
    catalog_map = {}
    for item in catalog:
        key = f"{normalize(item.get('referencia'))}|{normalize(item.get('color'))}|{normalize(item.get('tipo', 'PLANA'))}"
        if item.get('image_url'):
            catalog_map[key] = item['image_url']

    print(f"Loaded {len(catalog_map)} entries from catalog.")

    # 2. Update DB
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    
    cursor.execute("SELECT id, referencia, color, tipo FROM productos")
    productos = cursor.fetchall()
    
    updated_count = 0
    for prod_id, ref, color, tipo in productos:
        key = f"{normalize(ref)}|{normalize(color)}|{normalize(tipo or 'PLANA')}"
        if key in catalog_map:
            drive_url = catalog_map[key]
            cursor.execute("UPDATE productos SET imagen = ? WHERE id = ?", (drive_url, prod_id))
            updated_count += 1
            
    conn.commit()
    conn.close()
    
    print(f"Successfully restored Google Drive URLs for {updated_count} products.")

except Exception as e:
    print(f"Error: {e}")
