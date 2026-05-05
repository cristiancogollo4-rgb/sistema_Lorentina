import { useEffect, useState } from 'react';
import api from './api';
import colombiaData from './data/colombia.json';

const COLOMBIA = colombiaData
  .slice()
  .sort((a, b) => a.departamento.localeCompare(b.departamento, 'es'))
  .reduce((acc, item) => {
    acc[item.departamento] = [...new Set(item.ciudades)].sort((a, b) => a.localeCompare(b, 'es'));
    return acc;
  }, {});

const DEPARTAMENTOS = Object.keys(COLOMBIA);
const PAISES_FRECUENTES = ['Colombia', 'Estados Unidos', 'México', 'Panamá', 'Ecuador', 'Perú', 'España', 'Chile'];

const TIPO_LABELS = {
  TODOS: { label: 'Todos', emoji: '👥' },
  MAYORISTA: { label: 'Mayorista', emoji: '🏪' },
  DETAL: { label: 'Detal', emoji: '🛍️' },
};

const badgeStyle = (tipo) => ({
  fontSize: '0.72rem',
  fontWeight: 'bold',
  padding: '4px 12px',
  borderRadius: '20px',
  background: tipo === 'MAYORISTA' ? '#e3f2fd' : '#f3e5f5',
  color: tipo === 'MAYORISTA' ? '#1565c0' : '#6a1b9a',
  border: `1px solid ${tipo === 'MAYORISTA' ? '#90caf9' : '#ce93d8'}`,
  whiteSpace: 'nowrap',
  display: 'inline-flex',
  alignItems: 'center',
  gap: '4px'
});

const FORM_VACIO = {
  nombre: '',
  telefono: '',
  email: '',
  direccion: '',
  pais: 'Colombia',
  departamento: '',
  region_estado: '',
  ciudad: '',
  codigo_postal: '',
  moneda_preferida: 'COP',
  tipo_cliente: 'DETAL',
};

export default function Clientes({ usuario }) {
  const [clientes, setClientes] = useState([]);
  const [filtroTipo, setFiltroTipo] = useState('TODOS');
  const [busqueda, setBusqueda] = useState('');
  const [mostrarModal, setMostrarModal] = useState(false);
  const [form, setForm] = useState(FORM_VACIO);
  const [editandoId, setEditandoId] = useState(null);
  const [guardando, setGuardando] = useState(false);
  const [error, setError] = useState('');

  const [vendedores, setVendedores] = useState([]);
  const [filtroVendedor, setFiltroVendedor] = useState('');

  useEffect(() => {
    cargar();
  }, [filtroVendedor]);

  useEffect(() => {
    if (usuario?.rol === 'ADMIN') {
      api.get('/usuarios').then(res => {
         const vends = res.data.filter(u => u.rol === 'VENDEDOR' || u.rol === 'ADMIN');
         setVendedores(vends);
      }).catch(err => console.error(err));
    }
  }, []);

  const cargar = async () => {
    try {
      const isVendedor = usuario?.rol?.includes('VENDEDOR');
      const vId = isVendedor ? usuario.id : filtroVendedor;
      const params = vId ? `?vendedor_id=${vId}` : '';
      const res = await api.get(`/clientes${params}`);
      setClientes(res.data);
    } catch (e) {
      console.error('Error cargando clientes:', e);
    }
  };

  const esColombia = form.pais === 'Colombia';
  const ciudadesDisponibles = esColombia && form.departamento ? COLOMBIA[form.departamento] || [] : [];

  const descripcionUbicacion = (cliente) => {
    if ((cliente.pais || 'Colombia') === 'Colombia') {
      return {
        principal: cliente.ciudad || '—',
        secundaria: cliente.departamento || '',
      };
    }

    return {
      principal: cliente.ciudad || '—',
      secundaria: [cliente.region_estado, cliente.pais].filter(Boolean).join(', '),
    };
  };

  const clientesFiltrados = clientes.filter((c) => {
    const matchTipo = filtroTipo === 'TODOS' || c.tipo_cliente === filtroTipo;
    const term = busqueda.toLowerCase().trim();
    const matchBusqueda = !term ||
      c.nombre?.toLowerCase().includes(term) ||
      c.telefono?.toLowerCase().includes(term) ||
      c.email?.toLowerCase().includes(term) ||
      c.pais?.toLowerCase().includes(term) ||
      c.ciudad?.toLowerCase().includes(term) ||
      c.departamento?.toLowerCase().includes(term) ||
      c.region_estado?.toLowerCase().includes(term) ||
      c.codigo_postal?.toLowerCase().includes(term) ||
      c.moneda_preferida?.toLowerCase().includes(term);

    return matchTipo && matchBusqueda;
  });

  const abrirNuevo = () => {
    setForm(FORM_VACIO);
    setEditandoId(null);
    setError('');
    setMostrarModal(true);
  };

  const abrirEditar = (cliente) => {
    setForm({
      nombre: cliente.nombre,
      telefono: cliente.telefono || '',
      email: cliente.email || '',
      direccion: cliente.direccion || '',
      pais: cliente.pais || 'Colombia',
      departamento: cliente.departamento || '',
      region_estado: cliente.region_estado || '',
      ciudad: cliente.ciudad || '',
      codigo_postal: cliente.codigo_postal || '',
      moneda_preferida: cliente.moneda_preferida || ((cliente.pais || 'Colombia') === 'Colombia' ? 'COP' : 'USD'),
      tipo_cliente: cliente.tipo_cliente,
    });
    setEditandoId(cliente.id);
    setError('');
    setMostrarModal(true);
  };

  const cerrarModal = () => {
    setMostrarModal(false);
    setError('');
  };

  const setField = (field, value) => {
    setForm((prev) => {
      const next = { ...prev, [field]: value };

      if (field === 'departamento') {
        next.ciudad = '';
      }

      if (field === 'pais') {
        const esPaisColombia = value === 'Colombia';
        next.departamento = '';
        next.region_estado = '';
        next.ciudad = '';
        next.codigo_postal = '';
        next.moneda_preferida = esPaisColombia ? 'COP' : (prev.moneda_preferida === 'COP' ? 'USD' : prev.moneda_preferida || 'USD');
      }

      return next;
    });
  };

  const handleGuardar = async () => {
    if (!form.nombre.trim()) {
      setError('El nombre es obligatorio.');
      return;
    }
    if (!form.pais.trim()) {
      setError('El país es obligatorio.');
      return;
    }
    if (esColombia && !form.departamento) {
      setError('El departamento es obligatorio para clientes en Colombia.');
      return;
    }
    if (!form.ciudad.trim()) {
      setError('La ciudad es obligatoria.');
      return;
    }
    if (!form.moneda_preferida.trim()) {
      setError('La moneda preferida es obligatoria.');
      return;
    }

    setGuardando(true);
    setError('');

    try {
      const payload = {
        ...form,
        moneda_preferida: form.moneda_preferida.toUpperCase(),
        vendedor_id: usuario?.id ?? null,
      };

      if (editandoId) {
        await api.put(`/clientes/${editandoId}`, payload);
      } else {
        await api.post('/clientes', payload);
      }

      await cargar();
      cerrarModal();
    } catch (e) {
      setError(e.response?.data?.message || e.response?.data?.error || 'Error al guardar.');
    } finally {
      setGuardando(false);
    }
  };

  const totalMayoristas = clientes.filter((c) => c.tipo_cliente === 'MAYORISTA').length;
  const totalDetal = clientes.filter((c) => c.tipo_cliente === 'DETAL').length;

  return (
    <div className="fade-in" style={{ padding: '20px', maxWidth: '1200px', margin: '0 auto' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
        <div>
          <h2 style={{ color: '#582e2e', margin: 0 }}>Clientes</h2>
          <p style={{ color: '#888', margin: '5px 0 0 0' }}>Gestión y consulta de clientes</p>
        </div>
        <button onClick={abrirNuevo} style={btnPrimario}>+ Nuevo Cliente</button>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: '15px', marginBottom: '20px' }}>
        {[
          { label: 'Total Clientes', value: clientes.length, emoji: '👥', color: '#582e2e' },
          { label: 'Mayoristas', value: totalMayoristas, emoji: '🏪', color: '#1565c0' },
          { label: 'Detal', value: totalDetal, emoji: '🛍️', color: '#6a1b9a' },
        ].map((kpi) => (
          <div
            key={kpi.label}
            style={{
              background: 'white',
              borderRadius: '14px',
              padding: '18px 22px',
              boxShadow: '0 2px 8px rgba(0,0,0,0.07)',
              display: 'flex',
              alignItems: 'center',
              gap: '16px',
            }}
          >
            <span style={{ fontSize: '2rem' }}>{kpi.emoji}</span>
            <div>
              <div style={{ fontSize: '1.8rem', fontWeight: 'bold', color: kpi.color, lineHeight: 1 }}>{kpi.value}</div>
              <div style={{ fontSize: '0.8rem', color: '#888', marginTop: '2px' }}>{kpi.label}</div>
            </div>
          </div>
        ))}
      </div>

      <div style={{ display: 'flex', gap: '12px', marginBottom: '18px', flexWrap: 'wrap', alignItems: 'center' }}>
        <div style={{ display: 'flex', gap: '6px' }}>
          {Object.entries(TIPO_LABELS).map(([key, { label, emoji }]) => (
            <button key={key} onClick={() => setFiltroTipo(key)} style={filtroTipo === key ? chipActive : chip}>
              {emoji} {label}
            </button>
          ))}
        </div>
        
        {usuario?.rol === 'ADMIN' && (
          <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
            <span style={{ fontSize: '0.85rem', fontWeight: 'bold', color: '#555' }}>Vendedor:</span>
            <select
              style={{ ...selectStyle, width: '200px', padding: '6px 10px', height: '36px', fontSize: '0.85rem' }}
              value={filtroVendedor}
              onChange={(e) => setFiltroVendedor(e.target.value)}
            >
              <option value="">Todos los vendedores</option>
              {vendedores.map(v => (
                <option key={v.id} value={v.id}>{v.nombre} {v.apellido} {v.rol === 'ADMIN' ? '(Admin)' : ''}</option>
              ))}
            </select>
          </div>
        )}

        <div className="search-container" style={{ flex: 1, maxWidth: '420px' }}>
          <span className="search-icon-inside">🔍</span>
          <input
            type="text"
            placeholder="Buscar por nombre, ubicación, país o moneda..."
            value={busqueda}
            onChange={(e) => setBusqueda(e.target.value)}
            className="search-input-premium"
          />
          {busqueda && <button onClick={() => setBusqueda('')} className="clear-search-btn">&times;</button>}
        </div>
      </div>

      <div className="content-card" style={{ overflowX: 'auto' }}>
        <table className="modern-table" style={{ width: '100%', minWidth: '980px' }}>
          <thead>
            <tr style={{ background: '#f8f9fa' }}>
              <th style={{ padding: '14px 16px', textAlign: 'left' }}>Cliente</th>
              <th>Tipo</th>
              <th>Creado Por</th>
              <th>Teléfono</th>
              <th>Ubicación</th>
              <th>Moneda</th>
              <th>Dirección</th>
              <th style={{ textAlign: 'center' }}>Acción</th>
            </tr>
          </thead>
          <tbody>
            {clientesFiltrados.map((c) => {
              const ubicacion = descripcionUbicacion(c);

              return (
                <tr key={c.id} style={{ borderBottom: '1px solid #f0f0f0' }}>
                  <td style={{ padding: '12px 16px', fontWeight: 'bold', color: '#333' }}>{c.nombre}</td>
                  <td style={{ minWidth: '130px' }}>
                    <span style={badgeStyle(c.tipo_cliente)}>
                      {TIPO_LABELS[c.tipo_cliente]?.emoji} {c.tipo_cliente}
                    </span>
                  </td>
                  <td style={{ color: '#0284c7', fontSize: '0.85rem', fontWeight: 'bold' }}>{c.vendedor?.nombre || 'Admin/General'}</td>
                  <td style={{ color: '#555', fontSize: '0.9rem' }}>{c.telefono || '—'}</td>
                  <td style={{ fontSize: '0.88rem', color: '#555' }}>
                    <strong>{ubicacion.principal}</strong>
                    {ubicacion.secundaria && (
                      <>
                        <br />
                        <span style={{ color: '#999', fontSize: '0.78rem' }}>{ubicacion.secundaria}</span>
                      </>
                    )}
                  </td>
                  <td style={{ color: '#555', fontSize: '0.9rem', fontWeight: 'bold' }}>{c.moneda_preferida || '—'}</td>
                  <td style={{ color: '#777', fontSize: '0.85rem' }}>{c.direccion || '—'}</td>
                  <td style={{ textAlign: 'center' }}>
                    <button onClick={() => abrirEditar(c)} style={btnEditar}>Editar</button>
                  </td>
                </tr>
              );
            })}
            {clientesFiltrados.length === 0 && (
              <tr>
                <td colSpan={8} style={{ textAlign: 'center', padding: '40px', color: '#aaa' }}>
                  No se encontraron clientes.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {mostrarModal && (
        <div style={overlay} onClick={(e) => { if (e.target === e.currentTarget) cerrarModal(); }}>
          <div style={modal}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
              <h3 style={{ margin: 0, color: '#582e2e' }}>
                {editandoId ? 'Editar Cliente' : 'Nuevo Cliente'}
              </h3>
              <button onClick={cerrarModal} style={btnCerrar}>×</button>
            </div>

            <div style={{ marginBottom: '18px' }}>
              <label style={labelStyle}>Tipo de cliente *</label>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '10px', marginTop: '8px' }}>
                {[
                  { value: 'DETAL', emoji: '🛍️', titulo: 'Detal', desc: 'Compras unitarias o en pequeñas cantidades.' },
                  { value: 'MAYORISTA', emoji: '🏪', titulo: 'Mayorista', desc: 'Pensado para clientes recurrentes, incluso del exterior.' },
                ].map((op) => (
                  <div
                    key={op.value}
                    onClick={() => setField('tipo_cliente', op.value)}
                    style={{
                      border: `2px solid ${form.tipo_cliente === op.value ? '#582e2e' : '#e0e0e0'}`,
                      borderRadius: '12px',
                      padding: '14px',
                      cursor: 'pointer',
                      background: form.tipo_cliente === op.value ? '#fdf6f2' : 'white',
                      transition: 'all 0.2s',
                    }}
                  >
                    <div style={{ fontSize: '1.4rem' }}>{op.emoji}</div>
                    <div style={{ fontWeight: 'bold', color: '#333', marginTop: '4px' }}>{op.titulo}</div>
                    <div style={{ fontSize: '0.78rem', color: '#888', marginTop: '3px' }}>{op.desc}</div>
                  </div>
                ))}
              </div>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '14px' }}>
              <div style={{ gridColumn: '1 / -1' }}>
                <label style={labelStyle}>Nombre completo *</label>
                <input
                  style={inputStyle}
                  placeholder="Ej: María González"
                  value={form.nombre}
                  onChange={(e) => setField('nombre', e.target.value)}
                />
              </div>

              <div>
                <label style={labelStyle}>Teléfono</label>
                <input
                  style={inputStyle}
                  placeholder="Ej: +57 3001234567"
                  value={form.telefono}
                  onChange={(e) => setField('telefono', e.target.value)}
                />
              </div>

              <div>
                <label style={labelStyle}>Email</label>
                <input
                  style={inputStyle}
                  type="email"
                  placeholder="correo@ejemplo.com"
                  value={form.email}
                  onChange={(e) => setField('email', e.target.value)}
                />
              </div>

              <div>
                <label style={labelStyle}>País *</label>
                <input
                  style={inputStyle}
                  list="paises-frecuentes"
                  placeholder="Ej: Colombia"
                  value={form.pais}
                  onChange={(e) => setField('pais', e.target.value)}
                />
                <datalist id="paises-frecuentes">
                  {PAISES_FRECUENTES.map((pais) => <option key={pais} value={pais} />)}
                </datalist>
              </div>

              <div>
                <label style={labelStyle}>Moneda preferida *</label>
                <input
                  style={inputStyle}
                  maxLength={3}
                  placeholder="Ej: COP o USD"
                  value={form.moneda_preferida}
                  onChange={(e) => setField('moneda_preferida', e.target.value.toUpperCase())}
                />
              </div>

              {esColombia ? (
                <>
                  <div>
                    <label style={labelStyle}>Departamento *</label>
                    <select
                      style={selectStyle}
                      value={form.departamento}
                      onChange={(e) => setField('departamento', e.target.value)}
                    >
                      <option value="">-- Seleccionar --</option>
                      {DEPARTAMENTOS.map((d) => <option key={d} value={d}>{d}</option>)}
                    </select>
                  </div>

                  <div>
                    <label style={labelStyle}>Ciudad *</label>
                    {ciudadesDisponibles.length > 0 ? (
                      <select
                        style={selectStyle}
                        value={form.ciudad}
                        onChange={(e) => setField('ciudad', e.target.value)}
                      >
                        <option value="">-- Seleccionar ciudad --</option>
                        {ciudadesDisponibles.map((c) => <option key={c} value={c}>{c}</option>)}
                      </select>
                    ) : (
                      <input
                        style={{ ...inputStyle, background: '#f9f9f9', color: '#aaa' }}
                        placeholder="Selecciona primero un departamento"
                        disabled
                      />
                    )}
                  </div>
                </>
              ) : (
                <>
                  <div>
                    <label style={labelStyle}>Región / Estado</label>
                    <input
                      style={inputStyle}
                      placeholder="Ej: Florida, Madrid, Texas"
                      value={form.region_estado}
                      onChange={(e) => setField('region_estado', e.target.value)}
                    />
                  </div>

                  <div>
                    <label style={labelStyle}>Ciudad *</label>
                    <input
                      style={inputStyle}
                      placeholder="Ej: Miami"
                      value={form.ciudad}
                      onChange={(e) => setField('ciudad', e.target.value)}
                    />
                  </div>

                  <div>
                    <label style={labelStyle}>Código postal</label>
                    <input
                      style={inputStyle}
                      placeholder="Ej: 33101"
                      value={form.codigo_postal}
                      onChange={(e) => setField('codigo_postal', e.target.value)}
                    />
                  </div>
                </>
              )}

              <div style={{ gridColumn: '1 / -1' }}>
                <label style={labelStyle}>Dirección</label>
                <input
                  style={inputStyle}
                  placeholder="Ej: Calle 10 #5-20 o dirección internacional"
                  value={form.direccion}
                  onChange={(e) => setField('direccion', e.target.value)}
                />
              </div>
            </div>

            {error && (
              <p style={{ color: '#d32f2f', fontWeight: 'bold', marginTop: '12px', fontSize: '0.9rem' }}>
                {error}
              </p>
            )}

            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '10px', marginTop: '24px' }}>
              <button onClick={cerrarModal} style={btnSecundario}>Cancelar</button>
              <button onClick={handleGuardar} disabled={guardando} style={btnPrimarioPill}>
                {guardando ? 'Guardando...' : (editandoId ? 'Actualizar' : 'Crear Cliente')}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

const chip = { background: '#eee', border: 'none', padding: '7px 16px', borderRadius: '20px', cursor: 'pointer', fontSize: '0.85rem', color: '#555' };
const chipActive = { ...chip, background: '#e5d3c3', color: '#582e2e', border: '1px solid #582e2e', fontWeight: 'bold' };
const btnEditar = { background: 'transparent', border: '1px solid #582e2e', color: '#582e2e', padding: '5px 14px', borderRadius: '20px', cursor: 'pointer', fontSize: '0.82rem', fontWeight: 'bold' };
const overlay = { position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.45)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000, backdropFilter: 'blur(3px)' };
const modal = { background: 'white', borderRadius: '20px', padding: '30px', width: '680px', maxWidth: '95vw', maxHeight: '92vh', overflowY: 'auto', boxShadow: '0 20px 60px rgba(0,0,0,0.25)' };
const btnCerrar = { background: '#f5f5f5', border: 'none', borderRadius: '50%', width: '32px', height: '32px', cursor: 'pointer', fontSize: '1rem', display: 'flex', alignItems: 'center', justifyContent: 'center' };
const labelStyle = { display: 'block', fontSize: '0.82rem', fontWeight: '600', color: '#555', marginBottom: '5px' };
const inputStyle = { width: '100%', padding: '10px 14px', border: '1.5px solid #e0e0e0', borderRadius: '10px', fontSize: '0.95rem', outline: 'none', boxSizing: 'border-box' };
const selectStyle = { ...inputStyle, background: 'white', cursor: 'pointer' };
const btnPrimario = { background: '#582e2e', color: 'white', border: 'none', padding: '10px 22px', borderRadius: '30px', cursor: 'pointer', fontWeight: 'bold', boxShadow: '0 4px 10px rgba(0,0,0,0.2)', fontSize: '0.95rem' };
const btnPrimarioPill = { ...btnPrimario, padding: '11px 28px' };
const btnSecundario = { background: '#f5f5f5', color: '#555', border: 'none', padding: '11px 22px', borderRadius: '30px', cursor: 'pointer', fontWeight: 'bold', fontSize: '0.95rem' };
