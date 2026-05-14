from pathlib import Path
from textwrap import wrap

from PIL import Image, ImageDraw, ImageFont
from reportlab.lib import colors
from reportlab.lib.pagesizes import landscape, letter
from reportlab.lib.units import inch
from reportlab.pdfgen import canvas


ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "outputs" / "exposicion-etica-sistemas"
OUT.mkdir(parents=True, exist_ok=True)

PDF = OUT / "Lorentina_etica_y_teoria_de_sistemas.pdf"
PREVIEW_DIR = OUT / "previews"
PREVIEW_DIR.mkdir(parents=True, exist_ok=True)

SLIDES = [
    {
        "title": "Ética profesional & teoría de sistemas",
        "subtitle": "Material de apoyo para sustentar el proyecto Lorentina como sistema responsable.",
        "body": [
            "Proyecto Lorentina",
            "Producción, ventas, inventario y nómina con trazabilidad.",
        ],
    },
    {
        "title": "1. ¿Qué problema resuelve?",
        "subtitle": "La ética parte de ordenar la operación para evitar errores y decisiones injustas.",
        "body": [
            "Antes: información dispersa entre ventas, inventario, producción y pagos.",
            "Riesgo: vender sin stock, perder trazabilidad de órdenes o pagar de forma imprecisa.",
            "Solución: una plataforma que conecta procesos y deja evidencia de cada operación.",
            "Resultado: control administrativo, responsabilidad por rol y datos para decidir.",
        ],
    },
    {
        "title": "2. Flujo del sistema",
        "subtitle": "Lectura desde teoría de sistemas: entrada, proceso, salida y retroalimentación.",
        "body": [
            "Entrada: clientes, empleados, productos, tarifas, stock y pedidos.",
            "Proceso: ventas, órdenes, asignación, tareas móviles, traslados y nómina.",
            "Salida: inventario actualizado, ventas registradas, órdenes cerradas y pagos.",
            "Retroalimentación: dashboard con métricas, pendientes, ventas y alertas operativas.",
        ],
    },
    {
        "title": "3. Subsistemas conectados",
        "subtitle": "Cada capa tiene una responsabilidad clara dentro del flujo de datos.",
        "body": [
            "Frontend web: administra clientes, ventas, stock, fabricación, nómina y reportes.",
            "Backend Laravel: valida reglas de negocio, guarda datos y calcula estados.",
            "App móvil Kotlin: permite al operario consultar y cerrar tareas asignadas.",
            "Base de datos: memoria del sistema y soporte de trazabilidad.",
        ],
    },
    {
        "title": "4. Componente ético",
        "subtitle": "Principios que puedes defender ante la materia de ética profesional.",
        "body": [
            "Transparencia: operaciones registradas con datos verificables.",
            "Responsabilidad: tareas asignadas y cerradas por el usuario correcto.",
            "Justicia: nómina calculada con reglas visibles y medibles.",
            "Privacidad: uso de datos necesarios para operar, separados por rol.",
        ],
    },
    {
        "title": "5. Riesgos y controles",
        "subtitle": "El proyecto reconoce riesgos y muestra mitigaciones.",
        "body": [
            "Vender productos sin stock -> validación de disponibilidad antes de guardar.",
            "Cerrar tareas incorrectas -> backend valida etapa y empleado asignado.",
            "Pagar de forma imprecisa -> cálculo por rol, pares terminados, ventas y periodo.",
            "Usar datos innecesarios -> separación de vistas y datos según rol operativo.",
        ],
    },
    {
        "title": "6. Cómo explicarlo en la exposición",
        "subtitle": "Guion corto para conectar las dos materias sin mezclarlo con el sistema.",
        "body": [
            "Primero: Lorentina resuelve un problema real de coordinación empresarial.",
            "Luego: el flujo funciona como sistema abierto con retroalimentación.",
            "Después: se explican transparencia, responsabilidad, justicia y privacidad.",
            "Cierre: las validaciones técnicas también son controles éticos.",
        ],
    },
    {
        "title": "Mensaje final",
        "subtitle": "Lorentina no es solo una aplicación: es un sistema de trazabilidad.",
        "body": [
            "Ética profesional: decisiones justas y responsables.",
            "Teoría de sistemas: flujo integrado con retroalimentación.",
            "Defensa central: la tecnología mejora la operación cuando hace visible la responsabilidad.",
        ],
    },
]


def font(size, bold=False):
    base = Path("C:/Windows/Fonts")
    candidates = ["aptos-bold.ttf", "arialbd.ttf"] if bold else ["aptos.ttf", "arial.ttf"]
    for name in candidates:
        p = base / name
        if p.exists():
            return ImageFont.truetype(str(p), size)
    return ImageFont.load_default()


def draw_wrapped(draw, text, x, y, max_chars, fill, fnt, line_gap=10):
    lines = []
    for part in text.split("\n"):
        lines.extend(wrap(part, width=max_chars) or [""])
    for line in lines:
        draw.text((x, y), line, fill=fill, font=fnt)
        y += fnt.size + line_gap
    return y


def build_previews():
    bg = "#FFF8F3"
    brown = "#4B2D2A"
    tan = "#B88A6A"
    ink = "#1F2933"
    slate = "#596779"
    green = "#2F6F5E"

    for i, slide in enumerate(SLIDES, 1):
        img = Image.new("RGB", (1600, 900), bg if i in {1, 3, 5, 7} else "white")
        d = ImageDraw.Draw(img)
        if i == 1:
            d.rectangle([1100, 0, 1600, 900], fill=brown)
            d.text((90, 85), "LORENTINA", fill=tan, font=font(22, True))
            draw_wrapped(d, slide["title"], 90, 235, 26, brown, font(62, True), 16)
            draw_wrapped(d, slide["subtitle"], 92, 505, 58, slate, font(30), 12)
            d.text((1165, 705), "Proyecto Lorentina\nExposición académica", fill="white", font=font(30, True), align="right")
        elif i == 8:
            d.rectangle([0, 0, 1600, 900], fill=brown)
            d.text((95, 90), "MENSAJE FINAL", fill=tan, font=font(22, True))
            draw_wrapped(d, "Lorentina no es solo una aplicación:\nes un sistema de trazabilidad para trabajar con responsabilidad.", 95, 250, 42, "white", font(52, True), 16)
            draw_wrapped(d, "\n".join(slide["body"]), 100, 625, 72, "#F7E8DD", font(28), 12)
        else:
            draw_wrapped(d, slide["title"], 90, 70, 54, brown, font(42, True), 10)
            draw_wrapped(d, slide["subtitle"], 92, 135, 86, slate, font(23), 8)
            y = 245
            for item in slide["body"]:
                d.ellipse([105, y + 8, 122, y + 25], fill=tan)
                y = draw_wrapped(d, item, 145, y, 82, ink, font(27), 10) + 22
            d.text((95, 835), "LORENTINA", fill=tan, font=font(16, True))
            d.text((1495, 835), f"{i:02}", fill=tan, font=font(16, True))
            if i == 3:
                d.text((210, 725), "Entrada → Proceso → Salida → Retroalimentación", fill=green, font=font(31, True))
        img.save(PREVIEW_DIR / f"slide_{i:02}.png")


def build_pdf():
    c = canvas.Canvas(str(PDF), pagesize=landscape(letter))
    width, height = landscape(letter)
    brown = colors.HexColor("#4B2D2A")
    tan = colors.HexColor("#B88A6A")
    ink = colors.HexColor("#1F2933")
    slate = colors.HexColor("#596779")
    blush = colors.HexColor("#FFF8F3")

    for i, slide in enumerate(SLIDES, 1):
        c.setFillColor(blush if i in {1, 3, 5, 7} else colors.white)
        c.rect(0, 0, width, height, fill=1, stroke=0)
        if i == 1:
            c.setFillColor(brown)
            c.rect(width * 0.69, 0, width * 0.31, height, fill=1, stroke=0)
            c.setFillColor(tan)
            c.setFont("Helvetica-Bold", 12)
            c.drawString(0.65 * inch, height - 0.75 * inch, "LORENTINA")
            c.setFillColor(brown)
            c.setFont("Helvetica-Bold", 38)
            c.drawString(0.65 * inch, height - 2.05 * inch, "Ética profesional")
            c.drawString(0.65 * inch, height - 2.65 * inch, "& teoría de sistemas")
            c.setFillColor(slate)
            c.setFont("Helvetica", 15)
            text = c.beginText(0.67 * inch, height - 3.55 * inch)
            for line in wrap(slide["subtitle"], 72):
                text.textLine(line)
            c.drawText(text)
            c.setFillColor(colors.white)
            c.setFont("Helvetica-Bold", 15)
            c.drawRightString(width - 0.72 * inch, 1.0 * inch, "Proyecto Lorentina")
            c.setFont("Helvetica", 12)
            c.drawRightString(width - 0.72 * inch, 0.72 * inch, "Exposición académica")
        elif i == 8:
            c.setFillColor(brown)
            c.rect(0, 0, width, height, fill=1, stroke=0)
            c.setFillColor(tan)
            c.setFont("Helvetica-Bold", 12)
            c.drawString(0.65 * inch, height - 0.78 * inch, "MENSAJE FINAL")
            c.setFillColor(colors.white)
            c.setFont("Helvetica-Bold", 29)
            y = height - 2.15 * inch
            for line in ["Lorentina no es solo una aplicación:", "es un sistema de trazabilidad", "para trabajar con responsabilidad."]:
                c.drawString(0.65 * inch, y, line)
                y -= 0.48 * inch
            c.setFillColor(colors.HexColor("#F7E8DD"))
            c.setFont("Helvetica", 15)
            y = 1.55 * inch
            for item in slide["body"]:
                c.drawString(0.7 * inch, y, item)
                y -= 0.32 * inch
        else:
            c.setFillColor(brown)
            c.setFont("Helvetica-Bold", 24)
            c.drawString(0.65 * inch, height - 0.8 * inch, slide["title"])
            c.setFillColor(slate)
            c.setFont("Helvetica", 11.5)
            c.drawString(0.66 * inch, height - 1.15 * inch, slide["subtitle"])
            y = height - 1.85 * inch
            c.setFillColor(ink)
            c.setFont("Helvetica", 15)
            for item in slide["body"]:
                wrapped = wrap(item, 90)
                c.setFillColor(tan)
                c.circle(0.78 * inch, y + 0.05 * inch, 3, fill=1, stroke=0)
                c.setFillColor(ink)
                for line in wrapped:
                    c.drawString(1.0 * inch, y, line)
                    y -= 0.25 * inch
                y -= 0.22 * inch
            c.setFillColor(tan)
            c.setFont("Helvetica-Bold", 8)
            c.drawString(0.65 * inch, 0.36 * inch, "LORENTINA")
            c.drawRightString(width - 0.65 * inch, 0.36 * inch, f"{i:02}")
        c.showPage()
    c.save()


if __name__ == "__main__":
    build_previews()
    build_pdf()
    print(PDF)
    print(PREVIEW_DIR)
