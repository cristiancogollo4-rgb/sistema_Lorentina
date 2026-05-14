import { useEffect, useMemo, useState } from 'react';
import api from './api';
import LoadingState from './components/LoadingState';

const vistas = [
  { id: 'catalogo', label: 'Catalogo' },
  { id: 'precios', label: 'Precios' },
  { id: 'promos', label: 'Promociones' },
  { id: 'configuracion', label: 'Configuracion' },
];

function formatoNumero(valor) {
  return Number(valor || 0).toLocaleString('es-CO');
}

function formatoMoneda(valor) {
  return `$${Number(valor || 0).toLocaleString('es-CO')}`;
}

function aplicarEstadoVisual(producto) {
  if (!producto.activo) {
    return { ...producto, estado_online: 'Inactivo' };
  }

  if (!producto.visible_ecommerce) {
    return { ...producto, estado_online: 'Oculto' };
  }

  if (Number(producto.stock_total || 0) <= 0) {
    return { ...producto, estado_online: 'Sin stock' };
  }

  if (Number(producto.stock_total || 0) <= 5) {
    return { ...producto, estado_online: 'Bajo stock' };
  }

  return { ...producto, estado_online: 'Visible' };
}

function EcommerceAdmin() {
  const [vista, setVista] = useState('catalogo');
  const [sucursal, setSucursal] = useState('CABECERA');
  const [filtroEstado, setFiltroEstado] = useState('TODOS');
  const [busqueda, setBusqueda] = useState('');
  const [productos, setProductos] = useState([]);
  const [cargando, setCargando] = useState(true);
  const [guardandoId, setGuardandoId] = useState(null);
  const [error, setError] = useState('');
  const [mensaje, setMensaje] = useState('');

  async function cargarProductos() {
    setCargando(true);
    setError('');

    try {
      const res = await api.get('/ecommerce/productos', { params: { sucursal } });
      setProductos(Array.isArray(res.data.productos) ? res.data.productos : []);
    } catch (err) {
      setError(err.response?.data?.error || 'No se pudo cargar el panel de ecommerce.');
    } finally {
      setCargando(false);
    }
  }

  useEffect(() => {
    cargarProductos();
  }, [sucursal]);

  async function cambiarVisibilidad(producto) {
    const nuevoValor = !producto.visible_ecommerce;
    setGuardandoId(producto.id);
    setMensaje('');
    setError('');

    setProductos((actuales) => actuales.map((item) => (
      item.id === producto.id ? aplicarEstadoVisual({ ...item, visible_ecommerce: nuevoValor }) : item
    )));

    try {
      const res = await api.patch(`/ecommerce/productos/${producto.id}/visibilidad`, {
        visible_ecommerce: nuevoValor,
      });

      setMensaje(res.data.message || 'Visibilidad actualizada.');
      window.setTimeout(() => setMensaje(''), 1800);
    } catch (err) {
      setError(err.response?.data?.error || 'No se pudo actualizar la visibilidad.');
      setProductos((actuales) => actuales.map((item) => (
        item.id === producto.id ? aplicarEstadoVisual({ ...item, visible_ecommerce: producto.visible_ecommerce }) : item
      )));
    } finally {
      setGuardandoId(null);
    }
  }

  async function actualizarPrecio(producto, precio) {
    setGuardandoId(producto.id);
    setMensaje('');
    setError('');

    try {
      const res = await api.patch(`/ecommerce/productos/${producto.id}/precio`, {
        precio_detal: Number(precio),
      });

      setProductos((actuales) => actuales.map((item) => (
        item.id === producto.id
          ? {
              ...item,
              precio_detal: res.data.precio_detal,
              en_promocion: res.data.en_promocion,
              precio_promocion: res.data.precio_promocion,
            }
          : item
      )));
      setMensaje(res.data.message || 'Precio actualizado.');
      window.setTimeout(() => setMensaje(''), 1800);
    } catch (err) {
      setError(err.response?.data?.error || 'No se pudo actualizar el precio.');
    } finally {
      setGuardandoId(null);
    }
  }

  async function actualizarPromocion(producto, payload) {
    setGuardandoId(producto.id);
    setMensaje('');
    setError('');

    try {
      const res = await api.patch(`/ecommerce/productos/${producto.id}/promocion`, payload);

      setProductos((actuales) => actuales.map((item) => (
        item.id === producto.id
          ? {
              ...item,
              en_promocion: res.data.en_promocion,
              precio_promocion: res.data.precio_promocion,
              etiqueta_promocion: res.data.etiqueta_promocion,
            }
          : item
      )));
      setMensaje(res.data.message || 'Promocion actualizada.');
      window.setTimeout(() => setMensaje(''), 1800);
    } catch (err) {
      setError(err.response?.data?.error || 'No se pudo actualizar la promocion.');
    } finally {
      setGuardandoId(null);
    }
  }

  const productosFiltrados = useMemo(() => {
    const termino = busqueda.toLowerCase().trim();

    return productos.filter((item) => {
      const coincideBusqueda = !termino || `${item.nombre_modelo || ''} ${item.referencia || ''} ${item.color || ''} ${item.tipo || ''}`
        .toLowerCase()
        .includes(termino);

      const coincideEstado =
        filtroEstado === 'TODOS' ||
        (filtroEstado === 'VISIBLES' && item.visible_ecommerce) ||
        (filtroEstado === 'OCULTOS' && !item.visible_ecommerce) ||
        (filtroEstado === 'SIN_STOCK' && Number(item.stock_total || 0) <= 0) ||
        (filtroEstado === 'BAJO_STOCK' && Number(item.stock_total || 0) > 0 && Number(item.stock_total || 0) <= 5);

      return coincideBusqueda && coincideEstado;
    });
  }, [productos, busqueda, filtroEstado]);

  const resumen = useMemo(() => {
    const visibles = productos.filter((item) => item.visible_ecommerce).length;
    const ocultos = productos.length - visibles;
    const sinStock = productos.filter((item) => Number(item.stock_total || 0) <= 0).length;
    const bajoStock = productos.filter((item) => Number(item.stock_total || 0) > 0 && Number(item.stock_total || 0) <= 5).length;
    const pares = productos.reduce((acc, item) => acc + Number(item.stock_total || 0), 0);

    return { visibles, ocultos, sinStock, bajoStock, pares };
  }, [productos]);

  return (
    <div className="fade-in" style={page}>
      <div style={header}>
        <div>
          <span style={eyebrow}>Tienda online</span>
          <h2 style={title}>Panel de E-commerce</h2>
          <p style={subtitle}>Controla productos visibles, stock, precios y pendientes de la tienda.</p>
        </div>

        <div style={actions}>
          <a href="http://localhost:8000/productos" target="_blank" rel="noopener noreferrer" style={btnSecondary}>
            Ver catalogo
          </a>
          <a href="http://localhost:8000" target="_blank" rel="noopener noreferrer" style={btnPrimary}>
            Abrir tienda
          </a>
        </div>
      </div>

      <div style={tabs}>
        {vistas.map((item) => (
          <button
            key={item.id}
            type="button"
            onClick={() => setVista(item.id)}
            style={vista === item.id ? tabActive : tab}
          >
            {item.label}
          </button>
        ))}
      </div>

      {mensaje && <div style={successBox}>{mensaje}</div>}
      {error && <div style={errorBox}>{error}</div>}

      <div style={kpiGrid}>
        <Kpi label="Visibles en tienda" value={resumen.visibles} tone="#16a34a" />
        <Kpi label="Ocultos" value={resumen.ocultos} tone="#64748b" />
        <Kpi label="Bajo stock" value={resumen.bajoStock} tone="#f59e0b" />
        <Kpi label={`Pares en ${sucursal}`} value={formatoNumero(resumen.pares)} tone="#6366f1" />
      </div>

      {vista === 'catalogo' && (
        <section style={panel}>
          <div style={panelHead}>
            <div>
              <h3 style={panelTitle}>Catalogo visible</h3>
              <p style={panelText}>Activa u oculta productos de la tienda sin afectar stock ni ventas internas.</p>
            </div>

            <div style={filters}>
              <select value={sucursal} onChange={(e) => setSucursal(e.target.value)} style={input}>
                <option value="CABECERA">Cabecera</option>
                <option value="FABRICA">Fabrica</option>
                <option value="TODAS">Todas</option>
              </select>
              <select value={filtroEstado} onChange={(e) => setFiltroEstado(e.target.value)} style={input}>
                <option value="TODOS">Todos</option>
                <option value="VISIBLES">Visibles</option>
                <option value="OCULTOS">Ocultos</option>
                <option value="BAJO_STOCK">Bajo stock</option>
                <option value="SIN_STOCK">Sin stock</option>
              </select>
              <input
                type="search"
                value={busqueda}
                onChange={(e) => setBusqueda(e.target.value)}
                placeholder="Buscar ref, color o tipo..."
                style={{ ...input, minWidth: '230px' }}
              />
            </div>
          </div>

          {cargando && <LoadingState mensaje="Cargando productos del ecommerce..." />}
          {!cargando && <CatalogTable productos={productosFiltrados} guardandoId={guardandoId} onToggle={cambiarVisibilidad} />}
        </section>
      )}

      {vista === 'precios' && (
        <section style={panel}>
          <h3 style={panelTitle}>Precios online</h3>
          <p style={panelText}>Edita el precio que se muestra en el ecommerce. Base actual: planas $200.000 y plataformas $240.000.</p>
          <div style={priceGrid}>
            {productosFiltrados.map((producto) => (
              <ProductPriceEditor
                key={producto.id}
                producto={producto}
                disabled={guardandoId === producto.id}
                onSave={actualizarPrecio}
              />
            ))}
          </div>
        </section>
      )}

      {vista === 'promos' && (
        <section style={panel}>
          <h3 style={panelTitle}>Promociones por referencia</h3>
          <p style={panelText}>Elige productos del catalogo, pon precio promocional y etiqueta. Al guardar, la promo se ve en la tienda.</p>
          <div style={priceGrid}>
            {productosFiltrados.map((producto) => (
              <PromoEditor
                key={producto.id}
                producto={producto}
                disabled={guardandoId === producto.id}
                onSave={actualizarPromocion}
              />
            ))}
          </div>
        </section>
      )}

      {vista === 'configuracion' && (
        <section style={configGrid}>
          <div style={panel}>
            <h3 style={panelTitle}>Reglas actuales</h3>
            <SettingRow label="Mostrar productos ocultos" value="No" />
            <SettingRow label="Fuente de tallas" value="Stock real" />
            <SettingRow label="Precios base" value="Planas 200k / Plataformas 240k" />
          </div>
          <div style={panel}>
            <h3 style={panelTitle}>Pendientes convertidos en vistas</h3>
            <ActionItem title="Precios online" detail="Abre revision de precios visibles" onClick={() => setVista('precios')} />
            <ActionItem title="Promociones" detail="Publica descuentos por referencia" onClick={() => setVista('promos')} />
            <ActionItem title="Catalogo visible" detail="Vuelve a activar u ocultar productos" onClick={() => setVista('catalogo')} />
          </div>
        </section>
      )}
    </div>
  );
}

function CatalogTable({ productos, guardandoId, onToggle }) {
  if (productos.length === 0) {
    return <div style={emptyBox}>No hay productos con esos filtros.</div>;
  }

  return (
    <div style={tableWrap}>
      <table style={table}>
        <thead>
          <tr>
            <th style={th}>Visible</th>
            <th style={th}>Producto</th>
            <th style={th}>Tipo</th>
            <th style={th}>Tallas online</th>
            <th style={th}>Pares</th>
            <th style={th}>Estado</th>
          </tr>
        </thead>
        <tbody>
          {productos.map((item) => (
            <tr key={item.id}>
              <td style={td}>
                <button
                  type="button"
                  disabled={guardandoId === item.id}
                  onClick={() => onToggle(item)}
                  style={switchStyle(item.visible_ecommerce, guardandoId === item.id)}
                  aria-label={item.visible_ecommerce ? 'Ocultar producto' : 'Mostrar producto'}
                >
                  <span style={switchKnob(item.visible_ecommerce)} />
                </button>
              </td>
              <td style={td}>
                <div style={productCell}>
                  <ProductImage producto={item} style={thumb} />
                  <div>
                    <strong style={productTitle}>{item.nombre_modelo}</strong>
                    <div style={muted}>{item.referencia} / {item.color}</div>
                  </div>
                </div>
              </td>
              <td style={td}>{item.tipo}</td>
              <td style={td}>
                <div style={sizes}>
                  {item.tallas_disponibles.length > 0 ? (
                    item.tallas_disponibles.map((talla) => <span key={talla} style={sizeChip}>{talla}</span>)
                  ) : (
                    <span style={muted}>Sin tallas</span>
                  )}
                </div>
              </td>
              <td style={tdStrong}>{item.stock_total}</td>
              <td style={td}>
                <span style={badgeFor(item.estado_online)}>{item.estado_online}</span>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

function ProductImage({ producto, style }) {
  const urls = producto.imagenes_src?.length ? producto.imagenes_src : [producto.imagen_src];
  const [index, setIndex] = useState(0);
  const src = urls[index];

  useEffect(() => {
    setIndex(0);
  }, [producto.id, urls.join('|')]);

  if (!src || index >= urls.length) {
    return <div style={{ ...style, ...imageFallback }}>Sin foto</div>;
  }

  return (
    <img
      src={src}
      alt={producto.nombre_modelo}
      style={style}
      loading="lazy"
      referrerPolicy="no-referrer"
      onError={() => {
        setIndex((actual) => actual + 1);
      }}
    />
  );
}

function ProductPriceEditor({ producto, disabled, onSave }) {
  const [precio, setPrecio] = useState(producto.precio_detal || 0);

  useEffect(() => {
    setPrecio(producto.precio_detal || 0);
  }, [producto.id, producto.precio_detal]);

  return (
    <form
      style={priceCard}
      onSubmit={(event) => {
        event.preventDefault();
        onSave(producto, precio);
      }}
    >
      <span style={muted}>{producto.referencia} / {producto.color}</span>
      <strong style={priceValue}>{formatoMoneda(producto.precio_detal)}</strong>
      <input
        type="number"
        min="0"
        value={precio}
        onChange={(event) => setPrecio(event.target.value)}
        style={input}
      />
      <button type="submit" disabled={disabled} style={smallButton}>
        {disabled ? 'Guardando...' : 'Guardar precio'}
      </button>
    </form>
  );
}

function PromoEditor({ producto, disabled, onSave }) {
  const [activa, setActiva] = useState(Boolean(producto.en_promocion));
  const [precioPromo, setPrecioPromo] = useState(producto.precio_promocion || '');
  const [etiqueta, setEtiqueta] = useState(producto.etiqueta_promocion || 'Promo');

  useEffect(() => {
    setActiva(Boolean(producto.en_promocion));
    setPrecioPromo(producto.precio_promocion || '');
    setEtiqueta(producto.etiqueta_promocion || 'Promo');
  }, [producto.id, producto.en_promocion, producto.precio_promocion, producto.etiqueta_promocion]);

  return (
    <form
      style={{ ...priceCard, borderColor: activa ? '#f59e0b' : '#e2e8f0' }}
      onSubmit={(event) => {
        event.preventDefault();
        onSave(producto, {
          en_promocion: activa,
          precio_promocion: activa ? Number(precioPromo) : null,
          etiqueta_promocion: etiqueta,
        });
      }}
    >
      <span style={muted}>{producto.referencia} / {producto.color}</span>
      <strong style={productTitle}>{producto.nombre_modelo}</strong>
      <span style={muted}>Precio normal {formatoMoneda(producto.precio_detal)}</span>

      <label style={checkLine}>
        <input type="checkbox" checked={activa} onChange={(event) => setActiva(event.target.checked)} />
        Publicar promo
      </label>

      <input
        type="number"
        min="0"
        value={precioPromo}
        onChange={(event) => setPrecioPromo(event.target.value)}
        placeholder="Precio promocional"
        disabled={!activa}
        style={input}
      />

      <input
        type="text"
        value={etiqueta}
        onChange={(event) => setEtiqueta(event.target.value)}
        placeholder="Etiqueta: Promo, 15% OFF..."
        disabled={!activa}
        style={input}
      />

      <button type="submit" disabled={disabled} style={smallButton}>
        {disabled ? 'Guardando...' : activa ? 'Publicar promo' : 'Desactivar promo'}
      </button>
    </form>
  );
}

function Kpi({ label, value, tone }) {
  return (
    <div style={{ ...kpiCard, borderLeftColor: tone }}>
      <span style={{ ...kpiIcon, color: tone }}>●</span>
      <div>
        <p style={kpiLabel}>{label}</p>
        <h3 style={kpiValue}>{value}</h3>
      </div>
    </div>
  );
}

function SettingRow({ label, value }) {
  return (
    <div style={settingRow}>
      <span style={settingLabel}>{label}</span>
      <span style={settingValue}>{value}</span>
    </div>
  );
}

function ActionItem({ title, detail, onClick }) {
  return (
    <button type="button" onClick={onClick} style={actionItem}>
      <div>
        <strong style={actionTitle}>{title}</strong>
        <p style={actionDetail}>{detail}</p>
      </div>
      <span style={actionArrow}>›</span>
    </button>
  );
}

function badgeFor(estado) {
  const colors = {
    Visible: '#16a34a',
    Disponible: '#16a34a',
    Oculto: '#64748b',
    Inactivo: '#64748b',
    'Bajo stock': '#d97706',
    'Sin stock': '#dc2626',
  };
  const color = colors[estado] || '#64748b';

  return {
    display: 'inline-flex',
    alignItems: 'center',
    minWidth: '92px',
    justifyContent: 'center',
    padding: '6px 10px',
    borderRadius: '999px',
    background: `${color}14`,
    color,
    fontSize: '0.78rem',
    fontWeight: 800,
  };
}

function switchStyle(active, disabled) {
  return {
    width: '48px',
    height: '28px',
    border: 'none',
    borderRadius: '999px',
    padding: '3px',
    background: active ? '#16a34a' : '#cbd5e1',
    cursor: disabled ? 'wait' : 'pointer',
    opacity: disabled ? 0.6 : 1,
    transition: '0.2s',
  };
}

function switchKnob(active) {
  return {
    display: 'block',
    width: '22px',
    height: '22px',
    borderRadius: '999px',
    background: 'white',
    transform: active ? 'translateX(20px)' : 'translateX(0)',
    transition: '0.2s',
    boxShadow: '0 2px 6px rgba(0,0,0,0.2)',
  };
}

const page = { padding: '20px', maxWidth: '1480px', margin: '0 auto' };
const header = { display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '20px', marginBottom: '18px' };
const eyebrow = { color: '#c5a059', fontWeight: 900, textTransform: 'uppercase', fontSize: '0.75rem' };
const title = { margin: '4px 0', color: '#1e293b', fontSize: '2rem' };
const subtitle = { margin: 0, color: '#64748b' };
const actions = { display: 'flex', gap: '10px', flexWrap: 'wrap' };
const btnBase = { borderRadius: '10px', padding: '12px 18px', fontWeight: 800, textDecoration: 'none', border: '1px solid transparent' };
const btnPrimary = { ...btnBase, background: '#582e2e', color: 'white' };
const btnSecondary = { ...btnBase, background: 'white', color: '#582e2e', borderColor: '#e2e8f0' };
const tabs = { display: 'flex', gap: '8px', flexWrap: 'wrap', marginBottom: '18px' };
const tab = { border: '1px solid #e2e8f0', background: 'white', color: '#475569', borderRadius: '999px', padding: '10px 16px', fontWeight: 800, cursor: 'pointer' };
const tabActive = { ...tab, background: '#582e2e', color: 'white', borderColor: '#582e2e' };
const kpiGrid = { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: '16px', marginBottom: '20px' };
const kpiCard = { background: 'white', borderRadius: '18px', padding: '20px', borderLeft: '5px solid', boxShadow: '0 4px 15px rgba(0,0,0,0.05)', display: 'flex', alignItems: 'center', gap: '14px' };
const kpiIcon = { fontSize: '1.1rem' };
const kpiLabel = { margin: 0, color: '#64748b', fontSize: '0.82rem', fontWeight: 900, textTransform: 'uppercase' };
const kpiValue = { margin: '4px 0 0', color: '#0f172a', fontSize: '1.7rem' };
const panel = { background: 'white', borderRadius: '20px', padding: '22px', boxShadow: '0 4px 15px rgba(0,0,0,0.05)', minWidth: 0 };
const panelHead = { display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '16px', marginBottom: '18px' };
const panelTitle = { margin: 0, color: '#1e293b', fontSize: '1.25rem' };
const panelText = { margin: '5px 0 0', color: '#64748b', fontSize: '0.9rem' };
const filters = { display: 'flex', gap: '10px', flexWrap: 'wrap' };
const input = { border: '1px solid #e2e8f0', borderRadius: '10px', padding: '11px 12px', color: '#334155', background: '#f8fafc', fontWeight: 700 };
const tableWrap = { overflowX: 'auto' };
const table = { width: '100%', borderCollapse: 'collapse', minWidth: '860px' };
const th = { textAlign: 'left', padding: '12px', color: '#64748b', fontSize: '0.78rem', textTransform: 'uppercase', borderBottom: '1px solid #e2e8f0' };
const td = { padding: '13px 12px', borderBottom: '1px solid #f1f5f9', color: '#475569', verticalAlign: 'middle' };
const tdStrong = { ...td, color: '#0f172a', fontWeight: 900 };
const productCell = { display: 'flex', alignItems: 'center', gap: '12px', minWidth: '260px' };
const thumb = { width: '54px', height: '48px', borderRadius: '10px', objectFit: 'cover', background: '#f1f5f9' };
const productTitle = { display: 'block', color: '#0f172a', maxWidth: '290px' };
const sizes = { display: 'flex', gap: '6px', flexWrap: 'wrap' };
const sizeChip = { background: '#f4e5dc', color: '#582e2e', borderRadius: '999px', padding: '4px 8px', fontSize: '0.78rem', fontWeight: 900 };
const muted = { color: '#94a3b8', fontSize: '0.85rem' };
const emptyBox = { padding: '30px', textAlign: 'center', color: '#94a3b8', background: '#f8fafc', borderRadius: '14px', fontWeight: 800 };
const imageFallback = { display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#94a3b8', fontSize: '0.75rem', fontWeight: 800 };
const priceGrid = { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: '14px', marginTop: '18px' };
const priceCard = { display: 'grid', gap: '8px', border: '1px solid #e2e8f0', borderRadius: '14px', padding: '16px', background: '#f8fafc' };
const priceValue = { color: '#582e2e', fontSize: '1.35rem' };
const smallButton = { border: 'none', borderRadius: '10px', background: '#582e2e', color: 'white', padding: '10px 12px', fontWeight: 900, cursor: 'pointer' };
const checkLine = { display: 'flex', alignItems: 'center', gap: '8px', color: '#334155', fontWeight: 900 };
const configGrid = { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(320px, 1fr))', gap: '18px' };
const settingRow = { display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '12px', padding: '14px 0', borderBottom: '1px solid #f1f5f9' };
const settingLabel = { color: '#475569', fontWeight: 800 };
const settingValue = { color: '#582e2e', background: '#f4e5dc', borderRadius: '999px', padding: '5px 10px', fontWeight: 900 };
const actionItem = { width: '100%', border: '1px solid #e2e8f0', background: '#f8fafc', borderRadius: '12px', padding: '14px', display: 'flex', alignItems: 'center', justifyContent: 'space-between', textAlign: 'left', marginTop: '10px', cursor: 'pointer', font: 'inherit' };
const actionTitle = { color: '#334155' };
const actionDetail = { margin: '3px 0 0', color: '#94a3b8', fontSize: '0.85rem' };
const actionArrow = { color: '#c5a059', fontSize: '1.6rem', fontWeight: 900 };
const errorBox = { background: '#fef2f2', color: '#b91c1c', border: '1px solid #fecaca', borderRadius: '12px', padding: '12px 14px', marginBottom: '12px', fontWeight: 800 };
const successBox = { background: '#f0fdf4', color: '#15803d', border: '1px solid #bbf7d0', borderRadius: '12px', padding: '12px 14px', marginBottom: '12px', fontWeight: 800 };

export default EcommerceAdmin;
