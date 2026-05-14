import fs from "node:fs";
import path from "node:path";
import { pathToFileURL } from "node:url";

const pptxgenPath = "C:/Users/LENOVO LOQ/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/pptxgenjs/dist/pptxgen.es.js";
const { default: PptxGenJS } = await import(pathToFileURL(pptxgenPath).href);

const outDir = path.resolve("outputs/exposicion-etica-sistemas");
fs.mkdirSync(outDir, { recursive: true });

const pptx = new PptxGenJS();
pptx.layout = "LAYOUT_WIDE";
pptx.author = "Lorentina";
pptx.company = "Lorentina";
pptx.subject = "Componente etico y flujo sistemico del proyecto Lorentina";
pptx.title = "Etica profesional y teoria de sistemas - Lorentina";
pptx.lang = "es-CO";
pptx.theme = {
  headFontFace: "Aptos Display",
  bodyFontFace: "Aptos",
  lang: "es-CO",
};
pptx.defineLayout({ name: "WIDE", width: 13.333, height: 7.5 });

const colors = {
  brown: "4B2D2A",
  tan: "B88A6A",
  blush: "FFF8F3",
  ink: "1F2933",
  slate: "596779",
  green: "2F6F5E",
  amber: "B7791F",
  white: "FFFFFF",
  line: "E7D8CE",
};

function addBrand(slide, number) {
  slide.addText("LORENTINA", {
    x: 0.55,
    y: 7.05,
    w: 2.4,
    h: 0.22,
    fontFace: "Aptos",
    fontSize: 8.5,
    bold: true,
    color: colors.tan,
    charSpace: 1.2,
    margin: 0,
  });
  slide.addText(String(number).padStart(2, "0"), {
    x: 12.35,
    y: 7.02,
    w: 0.45,
    h: 0.24,
    fontSize: 8.5,
    color: colors.tan,
    align: "right",
    margin: 0,
  });
}

function title(slide, text, subtitle) {
  slide.addText(text, {
    x: 0.72,
    y: 0.55,
    w: 8.5,
    h: 0.58,
    fontSize: 23,
    bold: true,
    color: colors.brown,
    margin: 0,
    breakLine: false,
  });
  if (subtitle) {
    slide.addText(subtitle, {
      x: 0.74,
      y: 1.14,
      w: 7.2,
      h: 0.32,
      fontSize: 11.5,
      color: colors.slate,
      margin: 0,
    });
  }
}

function bulletList(slide, items, x, y, w, fontSize = 16) {
  slide.addText(items.map((text) => ({ text, options: { bullet: { indent: 14 }, hanging: 4 } })), {
    x,
    y,
    w,
    h: 4.8,
    fontSize,
    color: colors.ink,
    fit: "shrink",
    paraSpaceAfterPt: 14,
    breakLine: false,
  });
}

function chip(slide, text, x, y, w, color = colors.brown) {
  slide.addShape(pptx.ShapeType.roundRect, {
    x,
    y,
    w,
    h: 0.4,
    rectRadius: 0.08,
    fill: { color },
    line: { color },
  });
  slide.addText(text, {
    x: x + 0.12,
    y: y + 0.1,
    w: w - 0.24,
    h: 0.18,
    fontSize: 8.8,
    bold: true,
    color: colors.white,
    align: "center",
    margin: 0,
    fit: "shrink",
  });
}

function cover() {
  const slide = pptx.addSlide();
  slide.background = { color: colors.blush };
  slide.addShape(pptx.ShapeType.rect, { x: 0, y: 0, w: 13.333, h: 7.5, fill: { color: colors.blush }, line: { color: colors.blush } });
  slide.addShape(pptx.ShapeType.rect, { x: 9.15, y: 0, w: 4.18, h: 7.5, fill: { color: colors.brown }, line: { color: colors.brown } });
  slide.addText("LORENTINA", {
    x: 0.78,
    y: 0.65,
    w: 2.4,
    h: 0.28,
    fontSize: 10,
    bold: true,
    color: colors.tan,
    charSpace: 1.6,
    margin: 0,
  });
  slide.addText("Ética profesional\n& teoría de sistemas", {
    x: 0.75,
    y: 1.65,
    w: 7.2,
    h: 1.72,
    fontSize: 39,
    bold: true,
    color: colors.brown,
    margin: 0,
    breakLine: false,
    fit: "shrink",
  });
  slide.addText("Material de apoyo para sustentar el proyecto como un sistema responsable de producción, ventas, inventario y nómina.", {
    x: 0.78,
    y: 3.72,
    w: 6.65,
    h: 0.75,
    fontSize: 16,
    color: colors.slate,
    margin: 0,
    breakLine: false,
    fit: "shrink",
  });
  slide.addText("Proyecto Lorentina\nExposición académica", {
    x: 9.72,
    y: 5.85,
    w: 2.72,
    h: 0.62,
    fontSize: 16,
    bold: true,
    color: colors.white,
    align: "right",
    margin: 0,
    fit: "shrink",
  });
}

function slide2() {
  const slide = pptx.addSlide();
  slide.background = { color: colors.white };
  title(slide, "1. ¿Qué problema resuelve?", "La ética parte de ordenar la operación para evitar errores y decisiones injustas.");
  bulletList(slide, [
    "Antes: información dispersa entre ventas, inventario, producción y pagos.",
    "Riesgo: vender sin stock, perder trazabilidad de órdenes o pagar de forma imprecisa.",
    "Solución: una plataforma que conecta procesos y deja evidencia de cada operación.",
    "Resultado esperado: control administrativo, responsabilidad por rol y datos para decidir.",
  ], 0.92, 1.85, 7.2, 16.5);
  slide.addText("Idea clave", { x: 9.25, y: 1.8, w: 2.65, h: 0.3, fontSize: 11, bold: true, color: colors.tan, margin: 0 });
  slide.addText("Un sistema ético no solo funciona: permite explicar quién hizo qué, cuándo y con qué impacto.", {
    x: 9.25,
    y: 2.18,
    w: 2.85,
    h: 1.35,
    fontSize: 20,
    bold: true,
    color: colors.brown,
    margin: 0,
    fit: "shrink",
  });
  addBrand(slide, 2);
}

function slide3() {
  const slide = pptx.addSlide();
  slide.background = { color: colors.blush };
  title(slide, "2. Flujo del sistema", "Lectura desde teoría de sistemas: entrada, proceso, salida y retroalimentación.");
  const stages = [
    ["Entrada", "Clientes, empleados, productos, tarifas, stock y pedidos."],
    ["Proceso", "Ventas, órdenes, asignación, tareas móviles, traslados y nómina."],
    ["Salida", "Inventario actualizado, ventas registradas, órdenes cerradas y pagos."],
    ["Retroalimentación", "Dashboard con métricas, pendientes, ventas y alertas operativas."],
  ];
  stages.forEach(([name, desc], i) => {
    const x = 0.8 + i * 3.05;
    slide.addShape(pptx.ShapeType.roundRect, { x, y: 2.05, w: 2.55, h: 2.55, rectRadius: 0.08, fill: { color: colors.white }, line: { color: colors.line, width: 1.1 } });
    slide.addText(String(i + 1), { x: x + 0.18, y: 2.25, w: 0.32, h: 0.28, fontSize: 13, bold: true, color: colors.tan, margin: 0 });
    slide.addText(name, { x: x + 0.18, y: 2.82, w: 2.05, h: 0.3, fontSize: 16, bold: true, color: colors.brown, margin: 0 });
    slide.addText(desc, { x: x + 0.18, y: 3.28, w: 2.12, h: 0.82, fontSize: 11.5, color: colors.slate, margin: 0, fit: "shrink" });
    if (i < 3) {
      slide.addText("→", { x: x + 2.66, y: 3.03, w: 0.28, h: 0.28, fontSize: 21, color: colors.tan, margin: 0 });
    }
  });
  slide.addText("Sistema abierto: recibe información del negocio, la transforma en procesos y devuelve resultados para controlar la operación.", {
    x: 1.1,
    y: 5.38,
    w: 10.8,
    h: 0.46,
    fontSize: 18,
    bold: true,
    color: colors.green,
    align: "center",
    margin: 0,
    fit: "shrink",
  });
  addBrand(slide, 3);
}

function slide4() {
  const slide = pptx.addSlide();
  slide.background = { color: colors.white };
  title(slide, "3. Subsistemas conectados", "Cada capa tiene una responsabilidad clara dentro del flujo de datos.");
  const systems = [
    ["Frontend web", "Administra clientes, ventas, stock, fabricación, nómina y reportes."],
    ["Backend Laravel", "Valida reglas de negocio, guarda datos y calcula estados, stock y pagos."],
    ["App móvil Kotlin", "Permite al operario consultar y cerrar tareas asignadas."],
    ["Base de datos", "Funciona como memoria del sistema y soporte de trazabilidad."],
  ];
  systems.forEach(([name, desc], i) => {
    const y = 1.68 + i * 1.05;
    chip(slide, name, 0.9, y, 2.0, [colors.brown, colors.green, colors.amber, colors.tan][i]);
    slide.addText(desc, { x: 3.25, y: y + 0.03, w: 7.8, h: 0.34, fontSize: 15.5, color: colors.ink, margin: 0, fit: "shrink" });
    slide.addShape(pptx.ShapeType.line, { x: 0.9, y: y + 0.68, w: 10.6, h: 0, line: { color: "EFE3DB", width: 0.9 } });
  });
  slide.addText("La calidad ética depende de que cada subsistema haga bien su parte y no rompa la cadena de responsabilidad.", {
    x: 1,
    y: 6.2,
    w: 10.7,
    h: 0.38,
    fontSize: 17,
    bold: true,
    color: colors.brown,
    align: "center",
    margin: 0,
    fit: "shrink",
  });
  addBrand(slide, 4);
}

function slide5() {
  const slide = pptx.addSlide();
  slide.background = { color: colors.blush };
  title(slide, "4. Componente ético", "Principios que puedes defender ante la materia de ética profesional.");
  const principles = [
    ["Transparencia", "Operaciones registradas con datos verificables."],
    ["Responsabilidad", "Tareas asignadas y cerradas por el usuario correcto."],
    ["Justicia", "Nómina calculada con reglas visibles y medibles."],
    ["Privacidad", "Uso de datos necesarios para operar, separados por rol."],
  ];
  principles.forEach(([p, d], i) => {
    const x = i % 2 === 0 ? 0.95 : 6.85;
    const y = i < 2 ? 1.88 : 4.05;
    slide.addText(p, { x, y, w: 4.8, h: 0.38, fontSize: 24, bold: true, color: colors.brown, margin: 0 });
    slide.addShape(pptx.ShapeType.line, { x, y: y + 0.48, w: 4.5, h: 0, line: { color: colors.tan, width: 2 } });
    slide.addText(d, { x, y: y + 0.72, w: 4.75, h: 0.52, fontSize: 15, color: colors.slate, margin: 0, fit: "shrink" });
  });
  addBrand(slide, 5);
}

function slide6() {
  const slide = pptx.addSlide();
  slide.background = { color: colors.white };
  title(slide, "5. Riesgos y controles", "La evaluación ética mejora cuando el proyecto reconoce riesgos y muestra mitigaciones.");
  slide.addText("Riesgo", { x: 1.0, y: 1.55, w: 2, h: 0.3, fontSize: 13, bold: true, color: colors.amber, margin: 0 });
  slide.addText("Control implementado", { x: 6.65, y: 1.55, w: 3, h: 0.3, fontSize: 13, bold: true, color: colors.green, margin: 0 });
  const rows = [
    ["Vender productos sin stock", "Validación de disponibilidad antes de guardar la venta."],
    ["Cerrar tareas incorrectas", "Backend valida etapa y empleado asignado."],
    ["Pagar de forma imprecisa", "Cálculo por rol, pares terminados, ventas y periodo."],
    ["Usar datos innecesarios", "Separación de vistas y datos según rol operativo."],
  ];
  rows.forEach(([risk, control], i) => {
    const y = 2.02 + i * 0.95;
    slide.addText(risk, { x: 1.0, y, w: 4.3, h: 0.35, fontSize: 14.5, color: colors.ink, margin: 0, fit: "shrink" });
    slide.addText("→", { x: 5.75, y: y - 0.02, w: 0.25, h: 0.25, fontSize: 18, color: colors.tan, margin: 0 });
    slide.addText(control, { x: 6.65, y, w: 4.95, h: 0.35, fontSize: 14.5, color: colors.ink, margin: 0, fit: "shrink" });
    slide.addShape(pptx.ShapeType.line, { x: 1, y: y + 0.58, w: 10.65, h: 0, line: { color: "EFE3DB", width: 0.8 } });
  });
  addBrand(slide, 6);
}

function slide7() {
  const slide = pptx.addSlide();
  slide.background = { color: colors.blush };
  title(slide, "6. Cómo explicarlo en la exposición", "Guion corto para conectar las dos materias sin mezclarlo con la funcionalidad del sistema.");
  bulletList(slide, [
    "Primero muestro que Lorentina resuelve un problema real de coordinación empresarial.",
    "Luego explico el flujo como sistema abierto: entradas, procesos, salidas y retroalimentación.",
    "Después presento los principios éticos: transparencia, responsabilidad, justicia y privacidad.",
    "Cierro demostrando que las validaciones técnicas son también controles éticos.",
  ], 1.05, 1.82, 10.2, 17);
  addBrand(slide, 7);
}

function slide8() {
  const slide = pptx.addSlide();
  slide.background = { color: colors.brown };
  slide.addText("Mensaje final", { x: 0.8, y: 0.75, w: 2.4, h: 0.28, fontSize: 11, bold: true, color: colors.tan, margin: 0 });
  slide.addText("Lorentina no es solo una aplicación:\nes un sistema de trazabilidad para trabajar con responsabilidad.", {
    x: 0.8,
    y: 2.05,
    w: 10.5,
    h: 1.55,
    fontSize: 34,
    bold: true,
    color: colors.white,
    margin: 0,
    fit: "shrink",
  });
  slide.addText("Ética profesional: decisiones justas y responsables.\nTeoría de sistemas: flujo integrado con retroalimentación.", {
    x: 0.85,
    y: 5.15,
    w: 7.3,
    h: 0.72,
    fontSize: 16,
    color: "F7E8DD",
    margin: 0,
    fit: "shrink",
  });
  slide.addText("08", { x: 12.2, y: 7.02, w: 0.48, h: 0.25, fontSize: 8.5, color: colors.tan, align: "right", margin: 0 });
}

[cover, slide2, slide3, slide4, slide5, slide6, slide7, slide8].forEach((fn) => fn());

await pptx.writeFile({ fileName: path.join(outDir, "Lorentina_etica_y_teoria_de_sistemas.pptx") });
console.log(path.join(outDir, "Lorentina_etica_y_teoria_de_sistemas.pptx"));
