import { useState, useEffect } from 'react';
import api from './api';

function DashboardResumen({ usuario }) {
  const [stats, setStats] = useState({
    paresFabricar: 0,
    paresStock: 0,
    ventasSemana: 0,
    ventasMes: 0,
    ordenesActivas: 0,
    empleadosActivos: 0
  });
  
  const [distribucion, setDistribucion] = useState({
    CORTE: 0, ARMADO: 0, COSTURA: 0, SOLADURA: 0, EMPLANTILLADO: 0
  });

  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Órdenes activas: desde la más antigua en producción hasta la más reciente.
    // Ventas: métricas semanales y mensuales desde backend.
    api.get('/produccion/tablero?rango=produccion')
      .then(res => {
        const ordenes = res.data.ordenes || [];
        
        // Calcular distribución actual de las órdenes activas
        const dist = { CORTE: 0, ARMADO: 0, COSTURA: 0, SOLADURA: 0, EMPLANTILLADO: 0 };
        ordenes.forEach(o => {
          if (o.estado === 'EN_CORTE') dist.CORTE++;
          if (o.estado === 'EN_ARMADO') dist.ARMADO++;
          if (o.estado === 'EN_COSTURA') dist.COSTURA++;
          if (o.estado === 'EN_SOLADURA') dist.SOLADURA++;
          if (o.estado === 'EN_EMPLANTILLADO') dist.EMPLANTILLADO++;
        });

        setDistribucion(dist);
        setStats({
          paresFabricar: res.data.stats?.paresFabricar || 0,
          paresStock: res.data.stats?.paresStock || 0,
          ventasSemana: res.data.stats?.ventasSemana || 0,
          ventasMes: res.data.stats?.ventasMes || 0,
          ordenesActivas: ordenes.length,
          empleadosActivos: res.data.empleados?.length || 0
        });
      })
      .catch(err => console.error(err))
      .finally(() => setLoading(false));
  }, []);

  return (
    <div className="fade-in" style={{ padding: '20px', display: 'flex', flexDirection: 'column', gap: '30px' }}>
      
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
        <div style={{ fontSize: '3rem', opacity: 0.8 }}>🏭</div>
      </div>

      {/* KPI CARDS (Tarjetas de métricas) */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: '20px' }}>
        <KpiCard icon="📦" titulo="Pares a Fabricar (Mes)" valor={stats.paresFabricar} color="#3b82f6" />
        <KpiCard icon="✅" titulo="Pares Entrados a Stock" valor={stats.paresStock} color="#22c55e" />
        <KpiCard icon="📋" titulo="Órdenes Activas" valor={stats.ordenesActivas} color="#f59e0b" />
        <KpiCard icon="💰" titulo="Ventas de la Semana" valor={`$${formatoNumero(stats.ventasSemana || 0)}`} color="#8b5cf6" />
        <KpiCard icon="🗓️" titulo="Ventas del Mes" valor={`$${formatoNumero(stats.ventasMes || 0)}`} color="#6366f1" />
      </div>

      {/* SECCIÓN INFERIOR: DISTRIBUCIÓN Y ALERTAS */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(400px, 1fr))', gap: '20px' }}>
        
        {/* Gráfico de distribución (barras horizontales) */}
        <div style={{ background: 'white', padding: '25px', borderRadius: '20px', boxShadow: '0 4px 15px rgba(0,0,0,0.05)' }}>
          <h3 style={{ margin: '0 0 20px 0', color: '#333' }}>📊 Cuellos de botella (Órdenes en curso)</h3>
          
          <ProgressBar label="Corte" valor={distribucion.CORTE} total={stats.ordenesActivas} color="#f87171" />
          <ProgressBar label="Armado" valor={distribucion.ARMADO} total={stats.ordenesActivas} color="#fbbf24" />
          <ProgressBar label="Costura" valor={distribucion.COSTURA} total={stats.ordenesActivas} color="#34d399" />
          <ProgressBar label="Soladura" valor={distribucion.SOLADURA} total={stats.ordenesActivas} color="#60a5fa" />
          <ProgressBar label="Emplantillado" valor={distribucion.EMPLANTILLADO} total={stats.ordenesActivas} color="#a78bfa" />
          
          {stats.ordenesActivas === 0 && <p style={{ textAlign: 'center', color: '#999', marginTop: '20px' }}>No hay órdenes activas actualmente.</p>}
        </div>

        {/* Panel de alertas o accesos rápidos */}
        <div style={{ background: 'white', padding: '25px', borderRadius: '20px', boxShadow: '0 4px 15px rgba(0,0,0,0.05)' }}>
          <h3 style={{ margin: '0 0 20px 0', color: '#333' }}>⚡ Accesos Rápidos</h3>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '15px' }}>
            <div className="quick-action-card">
              <span style={{ fontSize: '1.5rem' }}>🔨</span>
              <div>
                <h4 style={{ margin: 0, color: '#475569' }}>Lanzar Nueva Producción</h4>
                <p style={{ margin: 0, fontSize: '0.85rem', color: '#94a3b8' }}>Crear orden para planta</p>
              </div>
            </div>
            <div className="quick-action-card">
              <span style={{ fontSize: '1.5rem' }}>💰</span>
              <div>
                <h4 style={{ margin: 0, color: '#475569' }}>Pagar Nómina</h4>
                <p style={{ margin: 0, fontSize: '0.85rem', color: '#94a3b8' }}>Ver destajos y pagos de la semana</p>
              </div>
            </div>
          </div>
        </div>

      </div>

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
      <div style={{ fontSize: '2.5rem', background: `${color}15`, width: '60px', height: '60px', display: 'flex', justifyContent: 'center', alignItems: 'center', borderRadius: '15px' }}>
        {icon}
      </div>
      <div>
        <p style={{ margin: 0, fontSize: '0.9rem', color: '#64748b', fontWeight: 'bold', textTransform: 'uppercase' }}>{titulo}</p>
        <h2 style={{ margin: '5px 0 0 0', fontSize: '2rem', color: '#1e293b' }}>{valor}</h2>
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
