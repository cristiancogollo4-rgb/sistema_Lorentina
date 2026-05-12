import { useEffect, useMemo, useState } from 'react';
import api from './api';
import LoadingState from './components/LoadingState';

function formatoMoneda(valor) {
  return `$${Number(valor || 0).toLocaleString('es-CO')}`;
}

function fechaInput(date) {
  return date.toISOString().slice(0, 10);
}

function rangoSemanaActual() {
  const hoy = new Date();
  const dia = hoy.getDay();
  const diffLunes = hoy.getDate() - dia + (dia === 0 ? -6 : 1);
  const inicio = new Date(hoy);
  inicio.setDate(diffLunes);
  const fin = new Date(inicio);
  fin.setDate(inicio.getDate() + 5);
  return {
    inicio: fechaInput(inicio),
    fin: fechaInput(fin),
  };
}

export default function Nomina() {
  const semana = useMemo(() => rangoSemanaActual(), []);
  const [inicio, setInicio] = useState(semana.inicio);
  const [fin, setFin] = useState(semana.fin);
  const [data, setData] = useState(null);
  const [cargando, setCargando] = useState(true);
  const [error, setError] = useState('');
  const [empleadoActivo, setEmpleadoActivo] = useState(null);
  const [busqueda, setBusqueda] = useState('');
  const [filtroRol, setFiltroRol] = useState('TODOS');
  const [pagandoId, setPagandoId] = useState(null);

  async function cargarNomina() {
    setCargando(true);
    setError('');
    try {
      const res = await api.get('/nomina/resumen', { params: { inicio, fin } });
      setData(res.data);
      setEmpleadoActivo(null);
    } catch (e) {
      setError(e.response?.data?.error || 'No se pudo cargar la nomina.');
    } finally {
      setCargando(false);
    }
  }

  async function marcarPagado(empleado) {
    if (!empleado || empleado.pagado) return;
    const ok = window.confirm(`Registrar pago de ${empleado.nombre} por ${formatoMoneda(empleado.totalGanado)}?`);
    if (!ok) return;

    setPagandoId(empleado.empleadoId);
    setError('');
    try {
      await api.post('/nomina/pagos', {
        empleadoId: empleado.empleadoId,
        inicio,
        fin,
      });
      await cargarNomina();
      setEmpleadoActivo(empleado.empleadoId);
    } catch (e) {
      setError(e.response?.data?.error || 'No se pudo registrar el pago.');
    } finally {
      setPagandoId(null);
    }
  }

  useEffect(() => {
    cargarNomina();
  }, []);

  const empleados = data?.empleados || [];
  const empleadosFiltrados = empleados.filter((empleado) => {
    const coincideNombre = empleado.nombre.toLowerCase().includes(busqueda.toLowerCase());
    const coincideRol = filtroRol === 'TODOS' || empleado.rol === filtroRol;
    return coincideNombre && coincideRol;
  });

  const roles = Array.from(new Set(empleados.map((empleado) => empleado.rol))).sort();
  const seleccionado = empleados.find((empleado) => empleado.empleadoId === empleadoActivo) || empleadosFiltrados[0];

  return (
    <div className="fade-in" style={{ padding: '20px', maxWidth: '1480px', margin: '0 auto' }}>
      <div style={header}>
        <div>
          <h2 style={title}>Nomina semanal</h2>
          <p style={subtitle}>Consulta tareas terminadas, ventas realizadas y valores a pagar cada sabado.</p>
        </div>
        <button type="button" onClick={cargarNomina} style={btnPrimario}>
          Actualizar
        </button>
      </div>

      <div style={toolbar}>
        <div style={field}>
          <label style={label}>Desde</label>
          <input type="date" value={inicio} onChange={(e) => setInicio(e.target.value)} style={input} />
        </div>
        <div style={field}>
          <label style={label}>Hasta</label>
          <input type="date" value={fin} onChange={(e) => setFin(e.target.value)} style={input} />
        </div>
        <div style={{ ...field, flex: 1, minWidth: '220px' }}>
          <label style={label}>Buscar empleado</label>
          <input
            type="text"
            value={busqueda}
            onChange={(e) => setBusqueda(e.target.value)}
            placeholder="Nombre..."
            style={input}
          />
        </div>
        <div style={field}>
          <label style={label}>Rol</label>
          <select value={filtroRol} onChange={(e) => setFiltroRol(e.target.value)} style={input}>
            <option value="TODOS">Todos</option>
            {roles.map((rol) => (
              <option key={rol} value={rol}>{rol}</option>
            ))}
          </select>
        </div>
        <button type="button" onClick={cargarNomina} style={{ ...btnSecundario, alignSelf: 'end' }}>
          Consultar
        </button>
      </div>

      {error && <div style={errorBox}>{error}</div>}
      {cargando && <LoadingState mensaje="Calculando nomina..." />}

      {!cargando && data && (
        <>
          <div style={kpiGrid}>
            <KpiCard label="Total a pagar" value={formatoMoneda(data.totales?.pagar)} tone="#582e2e" />
            <KpiCard label="Pagado" value={formatoMoneda(data.totales?.pagado)} tone="#166534" />
            <KpiCard label="Pendiente" value={formatoMoneda(data.totales?.pendiente)} tone="#b45309" />
            <KpiCard label="Produccion" value={formatoMoneda(data.totales?.produccion)} tone="#1565c0" />
            <KpiCard label="Comisiones ventas" value={formatoMoneda(data.totales?.ventas)} tone="#2e7d32" />
            <KpiCard label="Pares liquidados" value={data.totales?.pares || 0} tone="#8d6e63" />
          </div>

          <div style={rulesBox}>
            <strong>Reglas de comision:</strong> detal {formatoMoneda(data.reglas?.comisionDetal)} por par vendido,
            mayorista {formatoMoneda(data.reglas?.comisionMayorista)} por par vendido. Periodo de pago:
            {' '}{data.periodo?.inicio} a {data.periodo?.fin}.
          </div>

          <div style={layout}>
            <div style={panel}>
              <div style={panelHeader}>Empleados</div>
              <div style={{ display: 'grid', gap: '10px' }}>
                {empleadosFiltrados.map((empleado) => (
                  <button
                    key={empleado.empleadoId}
                    type="button"
                    onClick={() => setEmpleadoActivo(empleado.empleadoId)}
                    style={{
                      ...employeeRow,
                      ...(seleccionado?.empleadoId === empleado.empleadoId ? employeeRowActive : {}),
                    }}
                  >
                    <div>
                      <div style={{ fontWeight: 800, color: '#1e293b' }}>{empleado.nombre}</div>
                      <div style={{ fontSize: '0.78rem', color: '#64748b' }}>
                        {empleado.rol} · {empleado.pagado ? 'Pagado' : 'Pendiente'}
                      </div>
                    </div>
                    <div style={{ textAlign: 'right' }}>
                      <div style={{ fontWeight: 800, color: '#582e2e' }}>{formatoMoneda(empleado.totalGanado)}</div>
                      <div style={{ fontSize: '0.78rem', color: '#64748b' }}>{empleado.totalPares} pares</div>
                    </div>
                  </button>
                ))}
                {empleadosFiltrados.length === 0 && (
                  <div style={{ padding: '20px', color: '#64748b', textAlign: 'center' }}>Sin empleados para este filtro.</div>
                )}
              </div>
            </div>

            <div style={panel}>
              <div style={detailHeader}>
                <div>
                  <div style={panelHeader}>{seleccionado?.nombre || 'Detalle'}</div>
                  <div style={{ color: '#64748b', fontSize: '0.9rem' }}>{seleccionado?.rol || ''}</div>
                  {seleccionado && (
                    <span style={badge(seleccionado.pagado ? '#dcfce7' : '#ffedd5', seleccionado.pagado ? '#166534' : '#9a3412')}>
                      {seleccionado.pagado ? `Pagado ${seleccionado.pago?.fechaPago || ''}` : 'Pendiente de pago'}
                    </span>
                  )}
                </div>
                <div style={{ textAlign: 'right' }}>
                  <div style={{ color: '#64748b', fontSize: '0.8rem' }}>Total a pagar</div>
                  <div style={{ fontSize: '1.7rem', fontWeight: 900, color: '#582e2e' }}>
                    {formatoMoneda(seleccionado?.totalGanado)}
                  </div>
                  {seleccionado && !seleccionado.pagado && (
                    <button
                      type="button"
                      onClick={() => marcarPagado(seleccionado)}
                      disabled={pagandoId === seleccionado.empleadoId || Number(seleccionado.totalGanado || 0) <= 0}
                      style={{ ...btnPrimario, marginTop: '10px', opacity: pagandoId === seleccionado.empleadoId ? 0.7 : 1 }}
                    >
                      {pagandoId === seleccionado.empleadoId ? 'Registrando...' : 'Marcar pagado'}
                    </button>
                  )}
                </div>
              </div>

              <div style={{ overflowX: 'auto' }}>
                <table className="modern-table" style={{ minWidth: '900px' }}>
                  <thead>
                    <tr>
                      <th>Fecha</th>
                      <th>Concepto</th>
                      <th>Detalle</th>
                      <th>Pares</th>
                      <th>Valor</th>
                      <th>Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    {(seleccionado?.detalle || []).map((item) => (
                      <tr key={`${item.tipo}-${item.id}`}>
                        <td>{item.fecha ? new Date(item.fecha).toLocaleDateString('es-CO') : '-'}</td>
                        <td>
                          <span style={badge(item.tipo === 'VENTA' ? '#dcfce7' : '#e0f2fe', item.tipo === 'VENTA' ? '#166534' : '#075985')}>
                            {item.tipo === 'VENTA' ? 'Venta' : item.tarea}
                          </span>
                        </td>
                        <td>
                          {item.tipo === 'VENTA' ? (
                            <>
                              <strong>{item.cliente || 'Cliente'}</strong>
                              <div style={muted}>{item.tipoCliente} · Venta {formatoMoneda(item.totalVenta)}</div>
                            </>
                          ) : (
                            <>
                              <strong>{item.numeroOrden}</strong>
                              <div style={muted}>{item.referencia} · {item.color} · {item.categoria}</div>
                            </>
                          )}
                        </td>
                        <td>{item.pares}</td>
                        <td>{formatoMoneda(item.valorUnitario)}</td>
                        <td style={{ fontWeight: 800, color: '#582e2e' }}>{formatoMoneda(item.subtotal)}</td>
                      </tr>
                    ))}
                    {(seleccionado?.detalle || []).length === 0 && (
                      <tr>
                        <td colSpan="6" style={{ textAlign: 'center', padding: '28px', color: '#64748b' }}>
                          Este empleado no tiene tareas o ventas liquidadas en el periodo.
                        </td>
                      </tr>
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </>
      )}
    </div>
  );
}

function KpiCard({ label, value, tone }) {
  return (
    <div style={kpiCard}>
      <div style={{ fontSize: '0.82rem', color: '#64748b', fontWeight: 700 }}>{label}</div>
      <div style={{ fontSize: '1.8rem', fontWeight: 900, color: tone, marginTop: '8px' }}>{value}</div>
    </div>
  );
}

function badge(background, color) {
  return {
    background,
    color,
    borderRadius: '999px',
    padding: '5px 10px',
    fontSize: '0.78rem',
    fontWeight: 800,
    display: 'inline-block',
  };
}

const header = { display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '16px', marginBottom: '18px' };
const title = { color: '#582e2e', margin: 0, fontSize: '1.8rem', fontWeight: 900 };
const subtitle = { color: '#64748b', margin: '6px 0 0 0' };
const toolbar = { display: 'flex', flexWrap: 'wrap', gap: '14px', alignItems: 'end', background: 'white', border: '1px solid #e2e8f0', borderRadius: '14px', padding: '16px', marginBottom: '18px' };
const field = { width: '170px' };
const label = { display: 'block', color: '#475569', fontWeight: 800, fontSize: '0.78rem', marginBottom: '6px' };
const input = { width: '100%', height: '42px', border: '1px solid #e2e8f0', borderRadius: '10px', padding: '0 12px', outline: 'none', background: 'white', color: '#1e293b' };
const btnPrimario = { background: '#582e2e', color: 'white', border: 'none', padding: '12px 22px', borderRadius: '10px', fontWeight: 800, cursor: 'pointer' };
const btnSecundario = { background: '#f8fafc', color: '#582e2e', border: '1px solid #e2e8f0', padding: '11px 18px', borderRadius: '10px', fontWeight: 800, cursor: 'pointer' };
const errorBox = { background: '#fff1f2', color: '#b91c1c', border: '1px solid #fecdd3', borderRadius: '12px', padding: '12px 14px', fontWeight: 700, marginBottom: '16px' };
const kpiGrid = { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(210px, 1fr))', gap: '14px', marginBottom: '14px' };
const kpiCard = { background: 'white', border: '1px solid #e2e8f0', borderRadius: '14px', padding: '18px', boxShadow: '0 2px 8px rgba(15,23,42,0.04)' };
const rulesBox = { background: '#fffbeb', border: '1px solid #fde68a', color: '#713f12', borderRadius: '12px', padding: '12px 14px', marginBottom: '16px', fontSize: '0.9rem' };
const layout = { display: 'grid', gridTemplateColumns: 'minmax(280px, 360px) minmax(0, 1fr)', gap: '16px', alignItems: 'start' };
const panel = { background: 'white', border: '1px solid #e2e8f0', borderRadius: '14px', padding: '16px', boxShadow: '0 2px 8px rgba(15,23,42,0.04)' };
const panelHeader = { color: '#582e2e', fontSize: '1.15rem', fontWeight: 900, marginBottom: '12px' };
const detailHeader = { display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: '16px', marginBottom: '12px' };
const employeeRow = { width: '100%', border: '1px solid #e2e8f0', background: '#f8fafc', borderRadius: '12px', padding: '13px', display: 'flex', justifyContent: 'space-between', gap: '12px', textAlign: 'left', cursor: 'pointer' };
const employeeRowActive = { background: '#fff7ed', borderColor: '#f0c987' };
const muted = { color: '#64748b', fontSize: '0.82rem', marginTop: '3px' };
