import { useState, useEffect } from 'react';
import api from './api';
import LoadingState from './components/LoadingState';

function DashboardResumen({ usuario, onNavigate }) {
  const [stats, setStats] = useState({
    paresFabricar: 0,
    paresStock: 0,
    ventasSemana: 0,
    ventasMes: 0,
    ventasSemanaVendedor: 0,
    ventasMesVendedor: 0,
    clientesConApartados: 0,
    ventasSinDespachar: 0,
    topProductos: [],
    bajaRotacionAltoMargen: [],
    ordenesActivas: 0,
    empleadosActivos: 0
  });
  
  const [distribucion, setDistribucion] = useState({
    CORTE: 0, ARMADO: 0, COSTURA: 0, SOLADURA: 0, EMPLANTILLADO: 0
  });

  const [loading, setLoading] = useState(true);
  const esVendedor = usuario?.rol === 'VENDEDOR';
  const accionesRapidas = esVendedor
    ? [
        {
          icono: '📞',
          titulo: 'Contactar clientes clave',
          descripcion: 'Dar seguimiento a clientes sin compra reciente.',
          destino: 'clientes'
        },
        {
          icono: '🧾',
          titulo: 'Gestionar pedidos apartados',
          descripcion: 'Priorizar ventas online pendientes por despacho.',
          destino: 'apartados'
        }
      ]
    : [
        {
          icono: '🔨',
          titulo: 'Lanzar Nueva Producción',
          descripcion: 'Crear orden para planta',
          destino: 'fabricar'
        },
        {
          icono: '💰',
          titulo: 'Pagar Nómina',
          descripcion: 'Ver destajos y pagos de la semana',
          destino: 'nomina'
        }
      ];


  const [vendedores, setVendedores] = useState([]);
  const [filtroVendedor, setFiltroVendedor] = useState('');

  useEffect(() => {
    setLoading(true);
    const vId = esVendedor ? usuario.id : filtroVendedor;
    const params = `?rango=produccion${vId ? `&vendedor_id=${vId}` : ''}`;
    
    api.get(`/produccion/tablero${params}`)
      .then(res => {
        try {
          const ordenes = Array.isArray(res.data.ordenes) ? res.data.ordenes : [];
          setVendedores(res.data.vendedores || []);
          
          // Calcular distribución actual de las órdenes activas
          const dist = { CORTE: 0, ARMADO: 0, COSTURA: 0, SOLADURA: 0, EMPLANTILLADO: 0 };
          ordenes.forEach(o => {
            if (!o) return;
            if (o.estado === 'EN_CORTE') dist.CORTE++;
            if (o.estado === 'EN_ARMADO') dist.ARMADO++;
            if (o.estado === 'EN_COSTURA') dist.COSTURA++;
            if (o.estado === 'EN_SOLADURA') dist.SOLADURA++;
            if (o.estado === 'EN_EMPLANTILLADO') dist.EMPLANTILLADO++;
          });

          setDistribucion(dist);
          
          const s = res.data.stats || {};
          setStats({
            paresFabricar: s.paresFabricar ?? 0,
            paresStock: s.paresStock ?? 0,
            ventasSemana: s.ventasSemana ?? 0,
            ventasMes: s.ventasMes ?? 0,
            ventasSemanaVendedor: s.ventasSemanaVendedor ?? 0,
            ventasMesVendedor: s.ventasMesVendedor ?? 0,
            clientesConApartados: s.clientesConApartados ?? 0,
            ventasSinDespachar: s.ventasSinDespachar ?? 0,
            topProductos: Array.isArray(s.topProductos) ? s.topProductos : [],
            bajaRotacionAltoMargen: Array.isArray(s.bajaRotacionAltoMargen) ? s.bajaRotacionAltoMargen : [],
            caidaVentasSemana: s.caidaVentasSemana ?? 0,
            clienteImportanteSinCompra: s.clienteImportanteSinCompra || null,
            ordenesActivas: ordenes.length,
            empleadosActivos: Array.isArray(res.data.empleados) ? res.data.empleados.length : 0
          });
        } catch (innerErr) {
          console.error("Error procesando datos del dashboard:", innerErr);
        }
      })
      .catch(err => console.error("Error cargando dashboard:", err))
      .finally(() => setLoading(false));
  }, [filtroVendedor]);

  return (
    <div className="fade-in" style={{ padding: '20px', display: 'flex', flexDirection: 'column', gap: '30px' }}>
      {loading ? (
        <LoadingState mensaje="Cargando resumen del dashboard..." />
      ) : (
        <>
          {/* HEADER / BIENVENIDA */}
          <div style={{
            background: 'linear-gradient(135deg, #5D4037 0%, #3e2b25 100%)',
            padding: '30px',
            borderRadius: '20px',
            color: 'white',
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
            boxShadow: '0 10px 25px rgba(93, 64, 55, 0.3)'
          }}>
            <div>
              <h1 style={{ margin: '0 0 10px 0', fontSize: '2rem' }}>¡Hola, {usuario?.nombre || 'Administrador'}! 👋</h1>
              <p style={{ margin: 0, color: '#e5d3c3', fontSize: '1.1rem' }}>
                Aquí tienes el resumen de la fábrica de este mes. Todo está bajo control.
              </p>
            </div>
            <div style={{ display: 'flex', alignItems: 'center', gap: '20px' }}>
              <a href="http://localhost:8000" target="_blank" rel="noopener noreferrer" className="btn-gold" style={{ 
                textDecoration: 'none', 
                display: 'flex', 
                alignItems: 'center', 
                gap: '10px',
                padding: '15px 25px',
                fontSize: '1rem'
              }}>
                🛒 Visitar E-commerce
              </a>
              <div style={{ fontSize: '3rem', opacity: 0.8 }}>🏭</div>
            </div>
          </div>

          {/* KPI CARDS (Tarjetas de métricas) */}
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: '20px' }}>
            {esVendedor ? (
              <>
                <KpiCard icon="💰" titulo="Ventas esta semana" valor={`$${formatoNumero(stats?.ventasSemanaVendedor)}`} color="#8b5cf6" />
                <KpiCard icon="🗓️" titulo="Ventas del mes" valor={`$${formatoNumero(stats?.ventasMesVendedor)}`} color="#6366f1" />
                <KpiCard icon="🧾" titulo="Clientes con pedidos apartados" valor={stats?.clientesConApartados || 0} color="#f59e0b" />
                <KpiCard icon="🚚" titulo="Ventas sin pedidos despachados" valor={stats?.ventasSinDespachar || 0} color="#ef4444" />
              </>
            ) : (
              <>
                <KpiCard icon="📦" titulo="Pares a Fabricar (Mes)" valor={stats?.paresFabricar || 0} color="#3b82f6" />
                <KpiCard icon="✅" titulo="Pares Entrados a Stock" valor={stats?.paresStock || 0} color="#22c55e" />
                <KpiCard icon="📋" titulo="Órdenes Activas" valor={stats?.ordenesActivas || 0} color="#f59e0b" />
                <KpiCard icon="💰" titulo="Ventas de la Semana" valor={`$${formatoNumero(stats?.ventasSemana)}`} color="#8b5cf6" />
                <KpiCard icon="🗓️" titulo="Ventas del Mes" valor={`$${formatoNumero(stats?.ventasMes)}`} color="#6366f1" />
              </>
            )}
          </div>

          {/* SECCIÓN INFERIOR: DISTRIBUCIÓN Y ALERTAS */}
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(400px, 1fr))', gap: '20px' }}>
            {esVendedor && (
              <>
                <div style={{ background: 'white', padding: '25px', borderRadius: '20px', boxShadow: '0 4px 15px rgba(0,0,0,0.05)' }}>
                  <h3 style={{ margin: '0 0 20px 0', color: '#333' }}>🔥 Productos más vendidos</h3>
                  {stats.topProductos?.length > 0 ? (
                    stats.topProductos.map((producto, idx) => (
                      <p key={`${producto.referencia}-${producto.color}-${idx}`} style={{ margin: '8px 0', color: '#475569' }}>
                        {producto.referencia} / {producto.color}: <b>{producto.total_vendido}</b> pares
                      </p>
                    ))
                  ) : (
                    <p style={{ color: '#aaa', fontStyle: 'italic' }}>No hay datos de ventas recientes.</p>
                  )}
                </div>

                <div style={{ background: 'white', padding: '25px', borderRadius: '20px', boxShadow: '0 4px 15px rgba(0,0,0,0.05)' }}>
                  <h3 style={{ margin: '0 0 20px 0', color: '#333' }}>📉 Baja rotación / alto margen</h3>
                  {stats.bajaRotacionAltoMargen?.length > 0 ? (
                    stats.bajaRotacionAltoMargen.map((producto, idx) => (
                      <p key={`${producto.referencia}-${producto.color}-${idx}`} style={{ margin: '8px 0', color: '#475569' }}>
                        {producto.referencia} / {producto.color}: margen ${formatoNumero((producto.precio_detal || 0) - (producto.costo_produccion || 0))}
                      </p>
                    ))
                  ) : (
                    <p style={{ color: '#aaa', fontStyle: 'italic' }}>No hay datos disponibles.</p>
                  )}
                </div>
              </>
            )}
            
            {/* Gráfico de distribución / Alertas */}
            <div style={{ background: 'white', padding: '25px', borderRadius: '20px', boxShadow: '0 4px 15px rgba(0,0,0,0.05)' }}>
              <h3 style={{ margin: '0 0 20px 0', color: '#333' }}>{esVendedor ? '🚨 Alertas comerciales' : '📊 Cuellos de botella (Órdenes en curso)'}</h3>
              {esVendedor ? (
                <>
                  <p style={{ margin: '0 0 10px', color: '#475569' }}>
                    Cliente importante sin compra: <b>{stats.clienteImportanteSinCompra?.cliente || 'N/A'}</b> {stats.clienteImportanteSinCompra?.diasSinCompra != null ? `(${Math.round(stats.clienteImportanteSinCompra?.diasSinCompra)} días)` : ''}
                  </p>
                  <p style={{ margin: 0, color: (stats.caidaVentasSemana || 0) < 0 ? '#dc2626' : '#16a34a' }}>
                    Cambio vs semana anterior: <b>{Number(stats.caidaVentasSemana || 0).toFixed(1)}%</b>
                  </p>
                </>
              ) : (
                <>
                  <ProgressBar label="Corte" valor={distribucion.CORTE} total={stats.ordenesActivas} color="#f87171" />
                  <ProgressBar label="Armado" valor={distribucion.ARMADO} total={stats.ordenesActivas} color="#fbbf24" />
                  <ProgressBar label="Costura" valor={distribucion.COSTURA} total={stats.ordenesActivas} color="#34d399" />
                  <ProgressBar label="Soladura" valor={distribucion.SOLADURA} total={stats.ordenesActivas} color="#60a5fa" />
                  <ProgressBar label="Emplantillado" valor={distribucion.EMPLANTILLADO} total={stats.ordenesActivas} color="#a78bfa" />
                  {stats.ordenesActivas === 0 && <p style={{ textAlign: 'center', color: '#999', marginTop: '20px' }}>No hay órdenes activas actualmente.</p>}
                </>
              )}
            </div>

            {/* Panel de acciones */}
            <div style={{ background: 'white', padding: '25px', borderRadius: '20px', boxShadow: '0 4px 15px rgba(0,0,0,0.05)' }}>
              <h3 style={{ margin: '0 0 20px 0', color: '#333' }}>⚡ {esVendedor ? 'Acciones Comerciales' : 'Accesos Rápidos'}</h3>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '15px' }}>
                {accionesRapidas.map((accion) => (
                  <button
                    key={accion.destino}
                    type="button"
                    className="quick-action-card"
                    onClick={() => onNavigate?.(accion.destino)}
                  >
                    <span style={{ fontSize: '1.5rem' }}>{accion.icono}</span>
                    <div>
                      <h4 style={{ margin: 0, color: '#475569' }}>{accion.titulo}</h4>
                      <p style={{ margin: 0, fontSize: '0.85rem', color: '#94a3b8' }}>{accion.descripcion}</p>
                    </div>
                  </button>
                ))}
              </div>
            </div>
          </div>
        </>
      )}

    </div>
  );
}

// Componentes internos auxiliares
function KpiCard({ icon, titulo, valor, color }) {
  return (
    <div style={{
      background: 'white', padding: '25px', borderRadius: '20px', 
      boxShadow: '0 4px 15px rgba(0,0,0,0.04)', display: 'flex', alignItems: 'center', gap: '20px',
      borderLeft: `5px solid ${color}`, transition: 'transform 0.2s', cursor: 'pointer'
    }} onMouseEnter={(e) => e.currentTarget.style.transform = 'translateY(-5px)'} onMouseLeave={(e) => e.currentTarget.style.transform = 'translateY(0)'}>
      <div style={{ fontSize: '2.5rem', background: '#f8fafc', width: '60px', height: '60px', display: 'flex', justifyContent: 'center', alignItems: 'center', borderRadius: '15px', border: `1px solid ${color}33` }}>
        {icon}
      </div>
      <div>
        <p style={{ margin: 0, fontSize: '0.9rem', color: '#64748b', fontWeight: 'bold', textTransform: 'uppercase' }}>{titulo}</p>
        <h2 style={{ 
          margin: '5px 0 0 0', 
          fontSize: '1.6rem', 
          color: '#1e293b',
          whiteSpace: 'nowrap',
          overflow: 'hidden',
          textOverflow: 'ellipsis',
          maxWidth: '180px'
        }} title={valor}>
          {valor}
        </h2>
      </div>
    </div>
  );
}

function formatoNumero(valor) {
  const numero = Number(valor || 0);
  return numero.toLocaleString('es-CO');
}

function ProgressBar({ label, valor, total, color }) {
  const porcentaje = total > 0 ? (valor / total) * 100 : 0;
  return (
    <div style={{ marginBottom: '15px' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '5px' }}>
        <span style={{ fontWeight: 'bold', color: '#475569', fontSize: '0.9rem' }}>{label}</span>
        <span style={{ color: '#64748b', fontSize: '0.9rem' }}>{valor} Órdenes</span>
      </div>
      <div style={{ background: '#e2e8f0', height: '10px', borderRadius: '5px', overflow: 'hidden' }}>
        <div style={{ background: color, width: `${porcentaje}%`, height: '100%', borderRadius: '5px', transition: 'width 1s ease-in-out' }}></div>
      </div>
    </div>
  );
}

export default DashboardResumen;
