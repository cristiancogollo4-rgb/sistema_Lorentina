import { useEffect, useMemo, useState } from 'react';
import api from './api';
import LoadingState from './components/LoadingState';

const METODOS_PAGO = ['EFECTIVO', 'TRANSFERENCIA', 'TARJETA', 'NEQUI', 'DAVIPLATA', 'PAYPAL', 'WIRE'];
const CANALES = {
  ONLINE: { label: 'Online' },
  LOCAL: { label: 'En local' },
};

const FORM_VENTA = {
  cliente_id: '',
  canal_venta: 'ONLINE',
  local_id: '',
  sucursal: 'CABECERA',
  metodo_pago: 'TRANSFERENCIA',
  items: [],
};

function nuevaLinea() {
  return {
    seleccion: '',
    producto_id: '',
    talla: '',
    cantidad: 1,
    precio_unitario: '',
  };
}

export default function Ventas({ usuario }) {
  const SUCURSALES_STOCK = ['CABECERA', 'FABRICA'];
  const [ventas, setVentas] = useState([]);
  const [clientes, setClientes] = useState([]);
  const [locales, setLocales] = useState([]);
  const [paresDisponibles, setParesDisponibles] = useState([]);
  const [mostrarModal, setMostrarModal] = useState(false);
  const [guardando, setGuardando] = useState(false);
  const [cargando, setCargando] = useState(true);
  const [error, setError] = useState('');
  const [form, setForm] = useState(FORM_VENTA);

  useEffect(() => {
    cargarTodo();
  }, []);

  useEffect(() => {
    if (!mostrarModal) {
      return;
    }

    cargarCatalogo(form.sucursal || 'CABECERA');
  }, [form.sucursal, mostrarModal]);

  const opcionesPorClave = useMemo(() => {
    return paresDisponibles.reduce((acc, item) => {
      acc[item.key] = item;
      return acc;
    }, {});
  }, [paresDisponibles]);

  const totalVenta = form.items.reduce((acc, item) => {
    const cantidad = Number(item.cantidad) || 0;
    const precio = Number(item.precio_unitario) || 0;
    return acc + (cantidad * precio);
  }, 0);

  const cargarTodo = async () => {
    setCargando(true);
    try {
      const [ventasRes, catalogoRes] = await Promise.all([
        api.get('/ventas'),
        api.get(`/ventas/catalogo?sucursal=${form.sucursal || 'CABECERA'}`),
      ]);

      setVentas(ventasRes.data);
      setClientes(catalogoRes.data.clientes || []);
      setLocales(catalogoRes.data.locales || []);
      setParesDisponibles(catalogoRes.data.paresDisponibles || []);
      setError('');
    } catch (e) {
      console.error('Error cargando ventas:', e);
      setError('No se pudo cargar la seccion de ventas.');
    } finally {
      setCargando(false);
    }
  };

  const cargarCatalogo = async (sucursal) => {
    try {
      const catalogoRes = await api.get(`/ventas/catalogo?sucursal=${sucursal}`);
      setClientes(catalogoRes.data.clientes || []);
      setLocales(catalogoRes.data.locales || []);
      setParesDisponibles(catalogoRes.data.paresDisponibles || []);
    } catch (e) {
      console.error('Error cargando catalogo:', e);
      setError('No se pudo actualizar el catalogo de stock.');
    }
  };

  const abrirNuevaVenta = () => {
    setForm({
      ...FORM_VENTA,
      items: [nuevaLinea()],
    });
    setError('');
    setMostrarModal(true);
  };

  const cerrarModal = () => {
    setMostrarModal(false);
    setGuardando(false);
    setError('');
  };

  const setField = (field, value) => {
    setForm((prev) => {
      if (field === 'canal_venta') {
        return {
          ...prev,
          canal_venta: value,
          local_id: value === 'LOCAL' ? prev.local_id : '',
        };
      }

      if (field === 'sucursal') {
        return {
          ...prev,
          sucursal: value,
          items: prev.items.map(() => nuevaLinea()),
        };
      }

      return {
        ...prev,
        [field]: value,
      };
    });
  };

  const agregarLinea = () => {
    setForm((prev) => ({
      ...prev,
      items: [...prev.items, nuevaLinea()],
    }));
  };

  const eliminarLinea = (index) => {
    setForm((prev) => ({
      ...prev,
      items: prev.items.filter((_, i) => i !== index),
    }));
  };

  const actualizarLinea = (index, field, value) => {
    setForm((prev) => {
      const items = [...prev.items];
      const actual = { ...items[index], [field]: value };

      if (field === 'seleccion') {
        const opcion = opcionesPorClave[value];
        actual.producto_id = opcion ? opcion.productoId : '';
        actual.talla = opcion ? opcion.talla : '';
        actual.cantidad = 1;
      }

      items[index] = actual;
      return { ...prev, items };
    });
  };

  const guardarVenta = async () => {
    if (!form.cliente_id) {
      setError('Debes seleccionar un cliente.');
      return;
    }
    if (form.canal_venta === 'LOCAL' && !form.local_id) {
      setError('Debes seleccionar el local donde se hizo la venta.');
      return;
    }
    if (!form.metodo_pago) {
      setError('Debes indicar el metodo de pago.');
      return;
    }
    if (form.items.length === 0) {
      setError('Agrega al menos un par a la venta.');
      return;
    }

    const items = [];

    for (const item of form.items) {
      if (!item.producto_id || !item.talla) {
        setError('Cada linea debe estar ligada a un producto y talla.');
        return;
      }
      if (!(Number(item.cantidad) > 0)) {
        setError('La cantidad de cada linea debe ser mayor a cero.');
        return;
      }
      if (!(Number(item.precio_unitario) >= 0)) {
        setError('El precio unitario debe ser valido.');
        return;
      }

      items.push({
        producto_id: Number(item.producto_id),
        talla: Number(item.talla),
        cantidad: Number(item.cantidad),
        precio_unitario: Number(item.precio_unitario),
      });
    }

    setGuardando(true);
    setError('');

    try {
      await api.post('/ventas', {
        cliente_id: Number(form.cliente_id),
        vendedor_id: usuario.id,
        canal_venta: form.canal_venta,
        local_id: form.canal_venta === 'LOCAL' ? Number(form.local_id) : null,
        sucursal: form.sucursal,
        metodo_pago: form.metodo_pago,
        items,
      });

      await cargarTodo();
      cerrarModal();
    } catch (e) {
      setError(e.response?.data?.error || e.response?.data?.message || 'No se pudo guardar la venta.');
    } finally {
      setGuardando(false);
    }
  };

  const totalParesVendidos = ventas.reduce((acc, venta) => acc + (venta.totalPares || 0), 0);
  const totalVendido = ventas.reduce((acc, venta) => acc + (venta.total || 0), 0);
  const ventasLocales = ventas.filter((venta) => venta.canalVenta === 'LOCAL').length;

  return (
    <div className="fade-in" style={{ padding: '20px', maxWidth: '1280px', margin: '0 auto' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
        <div>
          <h2 style={{ color: '#582e2e', margin: 0 }}>Ventas</h2>
          <p style={{ color: '#888', margin: '5px 0 0 0' }}>
            Registra ventas ligadas a cliente, canal, local y stock disponible.
          </p>
        </div>
        <button onClick={abrirNuevaVenta} style={btnPrimario}>+ Nueva Venta</button>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: '15px', marginBottom: '20px' }}>
        <KpiCard label="Ventas registradas" value={ventas.length} color="#582e2e" />
        <KpiCard label="Pares vendidos" value={totalParesVendidos} color="#1565c0" />
        <KpiCard label="Total vendido" value={`$${formatoNumero(totalVendido)}`} color="#2e7d32" subtitle={`${ventasLocales} en local`} />
      </div>

      {error && !mostrarModal && <div style={errorBox}>{error}</div>}
      {cargando && <LoadingState mensaje="Cargando ventas..." />}

      <div className="content-card" style={{ overflowX: 'auto' }}>
        <table className="modern-table" style={{ width: '100%', minWidth: '1100px' }}>
          <thead>
            <tr style={{ background: '#f8f9fa' }}>
              <th style={{ padding: '14px 16px', textAlign: 'left' }}>Fecha</th>
              <th>Cliente</th>
              <th>Canal</th>
              <th>Pago</th>
              <th>Pares</th>
              <th>Total</th>
              <th style={{ textAlign: 'left' }}>Detalle</th>
            </tr>
          </thead>
          <tbody>
            {!cargando && ventas.map((venta) => (
              <tr key={venta.id} style={{ borderBottom: '1px solid #f0f0f0' }}>
                <td style={{ padding: '12px 16px', color: '#555', fontSize: '0.88rem' }}>
                  {venta.fechaVenta ? new Date(venta.fechaVenta).toLocaleString() : '-'}
                </td>
                <td style={{ fontWeight: 'bold', color: '#333' }}>{venta.cliente || '-'}</td>
                <td style={{ color: '#555', fontSize: '0.9rem' }}>
                  <strong>{CANALES[venta.canalVenta]?.label || venta.canalVenta}</strong>
                  {venta.local && (
                    <>
                      <br />
                      <span style={{ color: '#888', fontSize: '0.8rem' }}>{venta.local}</span>
                    </>
                  )}
                </td>
                <td style={{ color: '#555', fontSize: '0.9rem' }}>{venta.metodoPago}</td>
                <td style={{ textAlign: 'center', fontWeight: 'bold', color: '#582e2e' }}>{venta.totalPares}</td>
                <td style={{ fontWeight: 'bold', color: '#2e7d32' }}>${formatoNumero(venta.total || 0)}</td>
                <td style={{ fontSize: '0.86rem', color: '#555', minWidth: '320px' }}>
                  {(venta.items || []).map((item) => (
                    <div key={item.id} style={{ marginBottom: '6px' }}>
                      <strong>{item.referencia}</strong> {item.color} | {item.numeroOrden} | T{item.talla} x {item.cantidad}
                    </div>
                  ))}
                </td>
              </tr>
            ))}
            {!cargando && ventas.length === 0 && (
              <tr>
                <td colSpan={7} style={{ textAlign: 'center', padding: '40px', color: '#999' }}>
                  Aun no hay ventas registradas.
                </td>
              </tr>
            )}
            {cargando && (
              <tr>
                <td colSpan={7} style={{ textAlign: 'center', padding: '40px', color: '#999' }}>
                  Cargando ventas...
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
              <h3 style={{ margin: 0, color: '#582e2e' }}>Nueva Venta</h3>
              <button onClick={cerrarModal} style={btnCerrar}>x</button>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: '1.2fr 1fr 1fr 1fr', gap: '14px' }}>
              <div style={{ gridColumn: '1 / span 2' }}>
                <label style={labelStyle}>Cliente *</label>
                <select style={selectStyle} value={form.cliente_id} onChange={(e) => setField('cliente_id', e.target.value)}>
                  <option value="">-- Seleccionar cliente --</option>
                  {clientes.map((cliente) => (
                    <option key={cliente.id} value={cliente.id}>
                      {cliente.nombre} {cliente.tipo_cliente ? `(${cliente.tipo_cliente})` : ''}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label style={labelStyle}>Canal *</label>
                <select style={selectStyle} value={form.canal_venta} onChange={(e) => setField('canal_venta', e.target.value)}>
                  {Object.entries(CANALES).map(([key, value]) => (
                    <option key={key} value={key}>{value.label}</option>
                  ))}
                </select>
              </div>

              <div>
                <label style={labelStyle}>Descontar de *</label>
                <select style={selectStyle} value={form.sucursal} onChange={(e) => setField('sucursal', e.target.value)}>
                  {SUCURSALES_STOCK.map((sucursal) => (
                    <option key={sucursal} value={sucursal}>{sucursal}</option>
                  ))}
                </select>
              </div>

              <div>
                <label style={labelStyle}>Metodo de pago *</label>
                <select style={selectStyle} value={form.metodo_pago} onChange={(e) => setField('metodo_pago', e.target.value)}>
                  {METODOS_PAGO.map((metodo) => (
                    <option key={metodo} value={metodo}>{metodo}</option>
                  ))}
                </select>
              </div>

              {form.canal_venta === 'LOCAL' && (
                <div style={{ gridColumn: '1 / span 2' }}>
                  <label style={labelStyle}>Local *</label>
                  <select style={selectStyle} value={form.local_id} onChange={(e) => setField('local_id', e.target.value)}>
                    <option value="">-- Seleccionar local --</option>
                    {locales.map((local) => (
                      <option key={local.id} value={local.id}>{local.nombre}</option>
                    ))}
                  </select>
                </div>
              )}
            </div>

            <div style={{ marginTop: '22px', marginBottom: '8px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <h4 style={{ margin: 0, color: '#333' }}>Pares vendidos</h4>
              <button type="button" onClick={agregarLinea} style={btnSecundario}>+ Agregar linea</button>
            </div>

            <div style={{ display: 'grid', gap: '12px' }}>
              {form.items.map((item, index) => {
                const opcionSeleccionada = item.seleccion ? opcionesPorClave[item.seleccion] : null;

                return (
                  <div key={index} style={lineaVenta}>
                    <div style={{ flex: 2.5 }}>
                      <label style={labelStyle}>Producto *</label>
                      <select
                        style={selectStyle}
                        value={item.seleccion}
                        onChange={(e) => actualizarLinea(index, 'seleccion', e.target.value)}
                      >
                        <option value="">-- Seleccionar producto y talla --</option>
                        {paresDisponibles.map((opcion) => (
                          <option key={opcion.key} value={opcion.key}>
                            {`${opcion.referencia} ${opcion.color} | ${opcion.tipo} | ${opcion.sucursal} | T${opcion.talla} | ${opcion.disponibles} disp.`}
                          </option>
                        ))}
                      </select>
                    </div>

                    <div style={{ width: '90px' }}>
                      <label style={labelStyle}>Cant.</label>
                      <input
                        style={inputStyle}
                        type="number"
                        min="1"
                        max={opcionSeleccionada?.disponibles || undefined}
                        value={item.cantidad}
                        onChange={(e) => actualizarLinea(index, 'cantidad', e.target.value)}
                      />
                    </div>

                    <div style={{ width: '140px' }}>
                      <label style={labelStyle}>Precio unit.</label>
                      <input
                        style={inputStyle}
                        type="number"
                        min="0"
                        value={item.precio_unitario}
                        onChange={(e) => actualizarLinea(index, 'precio_unitario', e.target.value)}
                      />
                    </div>

                    <div style={{ width: '110px' }}>
                      <label style={labelStyle}>Subtotal</label>
                      <div style={subtotalBox}>
                        ${formatoNumero((Number(item.cantidad) || 0) * (Number(item.precio_unitario) || 0))}
                      </div>
                    </div>

                    <button type="button" onClick={() => eliminarLinea(index)} style={btnEliminar} disabled={form.items.length === 1}>
                      Eliminar
                    </button>
                  </div>
                );
              })}
            </div>

            {error && <div style={{ ...errorBox, marginTop: '16px' }}>{error}</div>}

            <div style={{ marginTop: '22px', padding: '16px 18px', background: '#f8f4f1', borderRadius: '14px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <div style={{ color: '#666', fontSize: '0.92rem' }}>
                Cada linea queda ligada a un producto disponible en stock y a una talla especifica.
              </div>
              <div style={{ fontWeight: 'bold', color: '#582e2e', fontSize: '1.1rem' }}>
                Total: ${formatoNumero(totalVenta)}
              </div>
            </div>

            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '10px', marginTop: '24px' }}>
              <button onClick={cerrarModal} style={btnSecundario}>Cancelar</button>
              <button onClick={guardarVenta} disabled={guardando} style={btnPrimario}>
                {guardando ? 'Guardando...' : 'Registrar venta'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

function KpiCard({ label, value, color, subtitle }) {
  return (
    <div style={{
      background: 'white',
      borderRadius: '14px',
      padding: '18px 22px',
      boxShadow: '0 2px 8px rgba(0,0,0,0.07)',
    }}>
      <div style={{ fontSize: '0.8rem', color: '#888' }}>{label}</div>
      <div style={{ fontSize: '1.8rem', fontWeight: 'bold', color, lineHeight: 1.1, marginTop: '8px' }}>{value}</div>
      {subtitle && <div style={{ fontSize: '0.8rem', color: '#888', marginTop: '6px' }}>{subtitle}</div>}
    </div>
  );
}

function formatoNumero(valor) {
  return Number(valor || 0).toLocaleString('es-CO');
}

const overlay = { position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.45)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000, backdropFilter: 'blur(3px)' };
const modal = { background: 'white', borderRadius: '20px', padding: '30px', width: '980px', maxWidth: '96vw', maxHeight: '92vh', overflowY: 'auto', boxShadow: '0 20px 60px rgba(0,0,0,0.25)' };
const btnCerrar = { background: '#f5f5f5', border: 'none', borderRadius: '50%', width: '32px', height: '32px', cursor: 'pointer', fontSize: '1rem', display: 'flex', alignItems: 'center', justifyContent: 'center' };
const labelStyle = { display: 'block', fontSize: '0.82rem', fontWeight: '600', color: '#555', marginBottom: '5px' };
const inputStyle = { width: '100%', padding: '10px 12px', border: '1.5px solid #e0e0e0', borderRadius: '10px', fontSize: '0.95rem', outline: 'none', boxSizing: 'border-box' };
const selectStyle = { ...inputStyle, background: 'white', cursor: 'pointer' };
const btnPrimario = { background: '#582e2e', color: 'white', border: 'none', padding: '11px 24px', borderRadius: '30px', cursor: 'pointer', fontWeight: 'bold', fontSize: '0.95rem' };
const btnSecundario = { background: '#f5f5f5', color: '#555', border: 'none', padding: '11px 22px', borderRadius: '30px', cursor: 'pointer', fontWeight: 'bold', fontSize: '0.95rem' };
const btnEliminar = { background: '#fff1f2', color: '#b91c1c', border: '1px solid #fecdd3', padding: '10px 14px', borderRadius: '10px', cursor: 'pointer', fontWeight: 'bold', alignSelf: 'end' };
const lineaVenta = { display: 'flex', gap: '12px', alignItems: 'flex-end', background: '#fcfcfc', border: '1px solid #eee', borderRadius: '14px', padding: '14px' };
const subtotalBox = { padding: '10px 12px', borderRadius: '10px', background: '#f6f6f6', border: '1px solid #e3e3e3', fontWeight: 'bold', color: '#333', minHeight: '42px', display: 'flex', alignItems: 'center' };
const errorBox = { background: '#fff1f2', color: '#b91c1c', border: '1px solid #fecdd3', borderRadius: '12px', padding: '12px 14px', fontWeight: '600', marginBottom: '16px' };
