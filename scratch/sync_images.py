import os
import shutil
import sqlite3
import re

# Paths
BASE_DIR = r"c:\Users\LENOVO LOQ\Desktop\PROYECTOS\Lorentina"
FOTOS_DIR = os.path.join(BASE_DIR, "fotos")
PUBLIC_IMAGES_DIR = os.path.join(BASE_DIR, "backend-laravel", "public", "images")
DB_PATH = os.path.join(BASE_DIR, "backend-laravel", "database", "database.sqlite")

if not os.path.exists(PUBLIC_IMAGES_DIR):
    os.makedirs(PUBLIC_IMAGES_DIR)

def sanitize_filename(filename):
    # Just to be safe with shell commands or URLs later
    return filename

# 1. Map all available photos
photo_map = {} # name_without_ext -> relative_path
image_extensions = ('.jpg', '.jpeg', '.png', '.gif', '.webp', '.bmp', '.heic', '.png', '.jpg', '.JPG', '.PNG', '.JPEG')

print("Scanning fotos directory...")
for root, dirs, files in os.walk(FOTOS_DIR):
    for file in files:
        if file.lower().endswith(image_extensions):
            name_no_ext = os.path.splitext(file)[0].upper().strip()
            # If multiple photos for same model, prefer ones without "PIE" or "COLLAGE" if possible
            # Or just take the first one found
            if name_no_ext not in photo_map:
                photo_map[name_no_ext] = os.path.join(root, file)
            elif "PIE" in photo_map[name_no_ext] and "PIE" not in name_no_ext:
                 photo_map[name_no_ext] = os.path.join(root, file)

print(f"Found {len(photo_map)} unique photo names.")

# 2. Connect to DB and update
try:
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    
    cursor.execute("SELECT id, nombre_modelo, referencia FROM productos")
    productos = cursor.fetchall()
    
    updated_count = 0
    for prod_id, nombre, ref in productos:
        # Try to find a match
        match = None
        
        # Clean nombre for matching
        clean_nombre = nombre.upper().strip()
        
        # Strategy A: Exact match with nombre_modelo
        if clean_nombre in photo_map:
            match = photo_map[clean_nombre]
        
        # Strategy B: Match with Referencia if nombre is generic
        if not match and ref:
            clean_ref = ref.upper().strip()
            if clean_ref in photo_map:
                match = photo_map[clean_ref]
        
        # Strategy C: Partial match (e.g. "Z1059 COÑAC" matches "Z1059 COÑAC 1.jpeg")
        if not match:
            for photo_name, photo_path in photo_map.items():
                if clean_nombre in photo_name or (ref and ref.upper() in photo_name):
                    match = photo_path
                    break
        
        if match:
            filename = os.path.basename(match)
            dest_path = os.path.join(PUBLIC_IMAGES_DIR, filename)
            
            # Copy file
            try:
                shutil.copy2(match, dest_path)
                
                # Update DB
                cursor.execute("UPDATE productos SET imagen = ? WHERE id = ?", (filename, prod_id))
                updated_count += 1
            except Exception as e:
                print(f"Error copying {match}: {e}")
    
    conn.commit()
    conn.close()
    print(f"Successfully updated {updated_count} products with local images.")

except Exception as e:
    print(f"Database error: {e}")
