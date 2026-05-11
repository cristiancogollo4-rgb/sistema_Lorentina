import json
import sqlite3
import os
import re

# Paths
BASE_DIR = r"c:\Users\LENOVO LOQ\Desktop\PROYECTOS\Lorentina"
DB_PATH = os.path.join(BASE_DIR, "backend-laravel", "database", "database.sqlite")
CATALOG_PATH = os.path.join(BASE_DIR, "backend-laravel", "database", "seeders", "data", "productos_link_catalog.json")
FOTOS_DIR = os.path.join(BASE_DIR, "fotos")
PUBLIC_IMAGES_DIR = os.path.join(BASE_DIR, "backend-laravel", "public", "images")

def normalize(val):
    if not val: return ""
    # Remove accents and uppercase
    val = str(val).strip().upper()
    val = val.replace('Á', 'A').replace('É', 'E').replace('Í', 'I').replace('Ó', 'O').replace('Ú', 'U')
    return val

try:
    # 1. Load Catalog
    with open(CATALOG_PATH, 'r', encoding='utf-8') as f:
        catalog = json.load(f)
    
    # Map catalog by ref|color|tipo
    # Note: We use normalized keys to match better
    catalog_map = {}
    for item in catalog:
        if item.get('image_url'):
            key = f"{normalize(item.get('referencia'))}|{normalize(item.get('color'))}|{normalize(item.get('tipo', 'PLANA'))}"
            catalog_map[key] = item['image_url']

    # 2. Map Local Photos strictly
    local_photo_map = {} # normalized_ref_color -> filename
    image_extensions = ('.jpg', '.jpeg', '.png', '.gif', '.webp', '.bmp', '.heic')
    
    for root, dirs, files in os.walk(FOTOS_DIR):
        for file in files:
            if file.lower().endswith(image_extensions):
                name_no_ext = os.path.splitext(file)[0].upper()
                # Store the file path for copying later
                # We normalize the name for matching
                local_photo_map[normalize(name_no_ext)] = os.path.join(root, file)

    # 3. Update DB
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    
    cursor.execute("SELECT id, referencia, color, tipo, nombre_modelo FROM productos")
    productos = cursor.fetchall()
    
    stats = {"drive": 0, "local": 0, "none": 0}
    
    for prod_id, ref, color, tipo, nombre in productos:
        new_image = None
        
        # Priority 1: Exact Catalog Match (Drive)
        key = f"{normalize(ref)}|{normalize(color)}|{normalize(tipo or 'PLANA')}"
        if key in catalog_map:
            new_image = catalog_map[key]
            stats["drive"] += 1
        
        # Priority 2: Strict Local Match (Referencia + Color in filename)
        if not new_image:
            # Try to find a file that contains BOTH ref and color
            target_match = f"{normalize(ref)} {normalize(color)}"
            # Or if nombre_modelo matches a filename
            norm_nombre = normalize(nombre)
            
            match_path = None
            if norm_nombre in local_photo_map:
                match_path = local_photo_map[norm_nombre]
            else:
                # Search through all local photos for one that contains BOTH ref and color
                for photo_norm_name, path in local_photo_map.items():
                    if normalize(ref) in photo_norm_name and normalize(color) in photo_norm_name:
                        match_path = path
                        break
            
            if match_path:
                filename = os.path.basename(match_path)
                dest_path = os.path.join(PUBLIC_IMAGES_DIR, filename)
                try:
                    if not os.path.exists(dest_path):
                        import shutil
                        shutil.copy2(match_path, dest_path)
                    new_image = filename
                    stats["local"] += 1
                except:
                    pass
        
        if not new_image:
            stats["none"] += 1
            
        cursor.execute("UPDATE productos SET imagen = ? WHERE id = ?", (new_image, prod_id))
            
    conn.commit()
    conn.close()
    
    print(f"Update Results:")
    print(f"- From Drive: {stats['drive']}")
    print(f"- From Local: {stats['local']}")
    print(f"- No Image: {stats['none']}")

except Exception as e:
    print(f"Error: {e}")
