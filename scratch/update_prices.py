import sqlite3
import os

# Paths
BASE_DIR = r"c:\Users\LENOVO LOQ\Desktop\PROYECTOS\Lorentina"
DB_PATH = os.path.join(BASE_DIR, "backend-laravel", "database", "database.sqlite")

try:
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    
    # 1. Update Planas (Clasicas/Romanas)
    # Detal: 200,000 | Mayor: 110,000
    cursor.execute("""
        UPDATE productos 
        SET precio_detal = 200000, precio_mayor = 110000 
        WHERE tipo = 'PLANA' OR tipo IS NULL OR tipo = ''
    """)
    planas_count = cursor.rowcount
    
    # 2. Update Plataformas / Zaras (Starts with Z or tipo PLATAFORMA)
    # Detal: 240,000 | Mayor: 116,000
    cursor.execute("""
        UPDATE productos 
        SET precio_detal = 240000, precio_mayor = 116000 
        WHERE tipo = 'PLATAFORMA' OR nombre_modelo LIKE 'Z%'
    """)
    zaras_count = cursor.rowcount
    
    conn.commit()
    conn.close()
    
    print(f"Updated {planas_count} Planas/Clasicas/Romanas.")
    print(f"Updated {zaras_count} Plataformas/Zaras.")

except Exception as e:
    print(f"Error: {e}")
