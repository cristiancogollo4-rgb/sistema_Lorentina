import { useEffect, useMemo, useState } from 'react';
import api from './api';
import LoadingState from './components/LoadingState';

const METODOS_PAGO = ['EFECTIVO', 'BANCOLOMBIA', 'DAVIPLATA', 'ADDI', 'BOLD'];
const CANALES = {
  WHATSAPP: { label: 'WhatsApp' },
  INSTAGRAM: { label: 'Instagram' },
  LOCAL: { label: 'Local' },
  MESSENGER: { label: 'Messenger' },
};

const FORM_VENTA = {
  cliente_id: '',
  canal_venta: 'WHATSAPP',
  local_id: '',
  sucursal: 'CABECERA',
  metodo_pago: 'EFECTIVO',
  titular_cuenta: '',
  items: [],
  notas: '',
};

function nuevaLinea() {
  return {
    seleccion: '', // key: ref-color-sucursal-talla
    producto_id: '',
    talla: '',
    cantidad: 1,
    precio_unitario: '',
    referencia: '',
    color: '',
  };
}

export default function Ventas({ usuario }) {
  const SUCURSALES_STOCK = ['CABECERA', 'FABRICA'];
  const [ventas, setVentas] = useState([]);
  const [clientes, setClientes] = useState([]);
  const [locales, setLocales] = useState([]);
  const [paresDisponibles, setParesDisponibles] = useState([]);
  const [mostrarModal, setMostrarModal] = useState(false);
  const [ventaSeleccionada, setVentaSeleccionada] = useState(null);
  const [guardando, setGuardando] = useState(false);
  const [cargando, setCargando] = useState(true);
  const [error, setError] = useState('');
  const [form, setForm] = useState(FORM_VENTA);

  // Filtros
  const [busqueda, setBusqueda] = useState('');
  const [busquedaCliente, setBusquedaCliente] = useState('');
  const [busquedaProducto, setBusquedaProducto] = useState('');
  const [mostrarNuevoCliente, setMostrarNuevoCliente] = useState(false);
  const [filtroTipo, setFiltroTipo] = useState('TODOS');
  const [rangoFecha, setRangoFecha] = useState('TODOS');
  const [fechaDesde, setFechaDesde] = useState('');
  const [fechaHasta, setFechaHasta] = useState('');

  const [vendedores, setVendedores] = useState([]);
  const [filtroVendedor, setFiltroVendedor] = useState('');
  const clienteSeleccionado = useMemo(
    () => clientes.find(c => c.id === Number(form.cliente_id)),
    [clientes, form.cliente_id]
  );
  const esClienteMayorista = clienteSeleccionado?.tipo_cliente === 'MAYORISTA' || clienteSeleccionado?.tipo_cliente === 'MAYOR';

  // Auto-titular de cuenta
  useEffect(() => {
    if (!form.metodo_pago) return;

    const clienteObj = clientes.find(c => c.id === Number(form.cliente_id));
    const esMayorista = clienteObj?.tipo_cliente === 'MAYORISTA' || clienteObj?.tipo_cliente === 'MAYOR';
    
    let titular = '';
    const m = form.metodo_pago;

    if (m === 'BANCOLOMBIA') {
      titular = esMayorista ? 'Jhon Mario Rojas' : 'Lorayne Rojas';
    } else if (m === 'DAVIPLATA') {
      titular = 'Jhon Mario Rojas';
    } else if (m === 'ADDI' || m === 'BOLD') {
      titular = 'Lorayne Rojas';
    } else {
      titular = ''; // Efectivo no tiene titular de cuenta bancaria
    }

    if (titular !== form.titular_cuenta) {
      setField('titular_cuenta', titular);
    }
  }, [form.metodo_pago, form.cliente_id, clientes]);

  const productosAgrupados = useMemo(() => {
    const grupos = {};
    const opcionesVisibles = esClienteMayorista
      ? paresDisponibles.filter(op => op.catalogoPermitidoMayorista)
      : paresDisponibles;

    opcionesVisibles.forEach(op => {
      const key = `${op.referencia}-${op.color}-${op.tipo}`;
      if (!grupos[key]) {
        grupos[key] = {
          referencia: op.referencia,
          color: op.color,
          tipo: op.tipo,
          tallas: {}
        };
      }
      grupos[key].tallas[op.talla] = {
        disponibles: op.disponibles,
        key: op.key,
        productoId: op.productoId
      };
    });

    // Filtrar por búsqueda de producto
    if (!busquedaProducto) return Object.values(grupos);
    
    const search = busquedaProducto.toLowerCase();
    return Object.values(grupos).filter(g => 
      g.referencia.toLowerCase().includes(search) || 
      g.color.toLowerCase().includes(search)
    );
  }, [paresDisponibles, busquedaProducto, esClienteMayorista]);

  // Clientes filtrados para el buscador del modal
  const clientesFiltradosModal = useMemo(() => {
    if (!busquedaCliente) return [];
    const search = busquedaCliente.toLowerCase();
    return clientes.filter(c => 
      c.nombre.toLowerCase().includes(search) || 
      (c.telefono || '').includes(search)
    );
  }, [clientes, busquedaCliente]);

  async function cargarTodo() {
    setCargando(true);
    try {
      const isVendedor = usuario?.rol === 'VENDEDOR' || usuario?.rol?.includes('VENDEDOR');
      const vId = isVendedor ? usuario.id : filtroVendedor;
      const params = vId ? `?vendedor_id=${vId}` : '';
      
      const [ventasRes, catalogoRes] = await Promise.all([
        api.get(`/ventas${params}`),
        api.get(`/ventas/catalogo?sucursal=${form.sucursal || 'CABECERA'}${vId ? `&vendedor_id=${vId}` : ''}`),
      ]);

      setVentas(ventasRes.data || []);
      setClientes(catalogoRes.data?.clientes || []);
      setLocales(catalogoRes.data?.locales || []);
      setVendedores(catalogoRes.data?.vendedores || []);
      setParesDisponibles(catalogoRes.data?.paresDisponibles || []);
      setError('');
    } catch (e) {
      console.error('Error cargando ventas:', e);
      setError('Error de conexión o de servidor al cargar ventas.');
    } finally {
      setCargando(false);
    }
  }

  async function cargarCatalogo(sucursal) {
    try {
      const isVendedor = usuario?.rol === 'VENDEDOR' || usuario?.rol?.includes('VENDEDOR');
      const vId = isVendedor ? usuario.id : filtroVendedor;
      const catalogoRes = await api.get(`/ventas/catalogo?sucursal=${sucursal}${vId ? `&vendedor_id=${vId}` : ''}`);
      setClientes(catalogoRes.data?.clientes || []);
      setLocales(catalogoRes.data?.locales || []);
      setVendedores(catalogoRes.data?.vendedores || []);
      setParesDisponibles(catalogoRes.data?.paresDisponibles || []);
    } catch (e) {
      console.error('Error cargando catalogo:', e);
    }
  }

  useEffect(() => {
    cargarTodo();
  }, [filtroVendedor]);

  useEffect(() => {
    if (!mostrarModal) return;
    cargarCatalogo(form.sucursal || 'CABECERA');
  }, [form.sucursal, mostrarModal]);

  const opcionesPorClave = useMemo(() => {
    const opcionesVisibles = esClienteMayorista
      ? (paresDisponibles || []).filter(item => item.catalogoPermitidoMayorista)
      : (paresDisponibles || []);

    return opcionesVisibles.reduce((acc, item) => {
      acc[item.key] = item;
      return acc;
    }, {});
  }, [paresDisponibles, esClienteMayorista]);

  useEffect(() => {
    if (!esClienteMayorista) return;

    setForm(prev => ({
      ...prev,
      items: prev.items.filter(item => !item.seleccion || opcionesPorClave[item.seleccion]),
    }));
  }, [esClienteMayorista, opcionesPorClave]);

  const totalVenta = (form?.items || []).reduce((acc, item) => {
    const cantidad = Number(item.cantidad) || 0;
    const precio = Number(item.precio_unitario) || 0;
    return acc + (cantidad * precio);
  }, 0);

  const ventasFiltradas = useMemo(() => {
    return (ventas || []).filter((venta) => {
      const matchesBusqueda = String(venta.cliente || '').toLowerCase().includes(busqueda.toLowerCase());
      const matchesTipo = filtroTipo === 'TODOS' || venta.tipoCliente === filtroTipo;

      let matchesFecha = true;
      if (!venta.fechaVenta) return matchesBusqueda && matchesTipo;

      const fechaV = new Date(venta.fechaVenta);
      const hoy = new Date();

      if (rangoFecha === 'SEMANA') {
        const inicioSemana = new Date(hoy);
        const day = hoy.getDay();
        const diff = hoy.getDate() - day + (day === 0 ? -6 : 1); // Lunes
        inicioSemana.setDate(diff);
        inicioSemana.setHours(0, 0, 0, 0);
        matchesFecha = fechaV >= inicioSemana;
      } else if (rangoFecha === 'MES') {
        const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1, 0, 0, 0);
        matchesFecha = fechaV >= inicioMes;
      } else if (rangoFecha === 'PERSONALIZADO') {
        if (fechaDesde) {
          const [y, m, d] = fechaDesde.split('-').map(Number);
          const start = new Date(y, m - 1, d, 0, 0, 0);
          matchesFecha = matchesFecha && fechaV >= start;
        }
        if (fechaHasta) {
          const [y, m, d] = fechaHasta.split('-').map(Number);
          const end = new Date(y, m - 1, d, 23, 59, 59, 999);
          matchesFecha = matchesFecha && fechaV <= end;
        }
      }

      return matchesBusqueda && matchesTipo && matchesFecha;
    });
  }, [ventas, busqueda, filtroTipo, rangoFecha, fechaDesde, fechaHasta]);

  const totalParesFiltrados = (ventasFiltradas || []).reduce((acc, venta) => acc + (venta.totalPares || 0), 0);
  const totalVendidoFiltrado = (ventasFiltradas || []).reduce((acc, venta) => acc + (venta.total || 0), 0);

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
        titular_cuenta: form.titular_cuenta,
        notas: form.notas,
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
    <div className="fade-in" style={{ padding: '20px', maxWidth: '1400px', margin: '0 auto' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
        <div>
          <h2 style={{ color: '#582e2e', margin: 0 }}>Gestión de Ventas</h2>
          <p style={{ color: '#888', margin: '5px 0 0 0' }}>
            Monitorea, filtra y registra todas las operaciones comerciales.
          </p>
        </div>
        <button onClick={abrirNuevaVenta} style={btnPrimario}>+ Registrar Nueva Venta</button>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: '15px', marginBottom: '25px' }}>
        <KpiCard label="Ventas en vista" value={ventasFiltradas.length} color="#582e2e" />
        <KpiCard label="Pares filtrados" value={totalParesFiltrados} color="#1565c0" />
        <KpiCard label="Total en vista" value={`$${formatoNumero(totalVendidoFiltrado)}`} color="#2e7d32" subtitle={`De un histórico de $${formatoNumero(totalVendido)}`} />
      </div>

      <div className="content-card" style={{ marginBottom: '20px', padding: '20px' }}>
        <div style={{ display: 'flex', flexWrap: 'wrap', gap: '15px', alignItems: 'flex-end' }}>
          <div style={{ flex: '1', minWidth: '250px' }}>
            <label style={labelStyle}>Buscar cliente</label>
            <input
              type="text"
              style={inputStyle}
              placeholder="Nombre del cliente..."
              value={busqueda}
              onChange={(e) => setBusqueda(e.target.value)}
            />
          </div>

          <div style={{ width: '180px' }}>
            <label style={labelStyle}>Tipo cliente</label>
            <select style={selectStyle} value={filtroTipo} onChange={(e) => setFiltroTipo(e.target.value)}>
              <option value="TODOS">Todos los tipos</option>
              <option value="DETAL">Detal</option>
              <option value="MAYORISTA">Al por mayor</option>
            </select>
          </div>

          <div style={{ width: '200px' }}>
            <label style={labelStyle}>Rango de fecha</label>
            <select style={selectStyle} value={rangoFecha} onChange={(e) => setRangoFecha(e.target.value)}>
              <option value="TODOS">Histórico completo</option>
              <option value="SEMANA">Esta semana</option>
              <option value="MES">Este mes</option>
              <option value="PERSONALIZADO">Personalizado...</option>
            </select>
          </div>

          {rangoFecha === 'PERSONALIZADO' && (
            <>
              <div style={{ width: '160px' }}>
                <label style={labelStyle}>Desde</label>
                <input type="date" style={inputStyle} value={fechaDesde} onChange={(e) => setFechaDesde(e.target.value)} />
              </div>
              <div style={{ width: '160px' }}>
                <label style={labelStyle}>Hasta</label>
                <input type="date" style={inputStyle} value={fechaHasta} onChange={(e) => setFechaHasta(e.target.value)} />
              </div>
            </>
          )}

          {usuario?.rol === 'ADMIN' && (
            <div style={{ width: '200px' }}>
              <label style={labelStyle}>Vendedor</label>
              <select 
                style={selectStyle} 
                value={filtroVendedor} 
                onChange={(e) => setFiltroVendedor(e.target.value)}
              >
                <option value="">Todos los vendedores</option>
                {vendedores.map(v => (
                  <option key={v.id} value={v.id}>
                    {v.nombre} {v.apellido} {v.rol === 'ADMIN' ? '(Admin)' : ''}
                  </option>
                ))}
              </select>
            </div>
          )}

          <button
            style={{ ...btnSecundario, height: '42px', padding: '0 20px' }}
            onClick={() => {
              setBusqueda('');
              setFiltroTipo('TODOS');
              setRangoFecha('TODOS');
              setFechaDesde('');
              setFechaHasta('');
              setFiltroVendedor('');
            }}
          >
            Limpiar
          </button>
        </div>
      </div>

      {error && !mostrarModal && <div style={errorBox}>{error}</div>}
      {cargando && <LoadingState mensaje="Cargando ventas..." />}

      <div className="content-card" style={{ overflowX: 'auto' }}>
        <table className="modern-table" style={{ width: '100%', minWidth: '1200px' }}>
          <thead>
            <tr style={{ background: '#f8f9fa' }}>
              <th style={{ padding: '14px 16px', textAlign: 'left' }}>Fecha</th>
              <th>Cliente</th>
              <th>Vendedor</th>
              <th>Tipo</th>
              <th>Canal</th>
              <th>Pago</th>
              <th>Pares</th>
              <th>Total</th>
              <th style={{ textAlign: 'left' }}>Detalle/Nota</th>
              <th style={{ textAlign: 'right' }}>Acción</th>
            </tr>
          </thead>
          <tbody>
            {!cargando && ventasFiltradas.map((venta) => (
              <tr key={venta.id} style={{ borderBottom: '1px solid #f0f0f0' }}>
                <td style={{ padding: '12px 16px', color: '#555', fontSize: '0.88rem' }}>
                  {venta.fechaVenta ? new Date(venta.fechaVenta).toLocaleString() : '-'}
                </td>
                <td style={{ fontWeight: 'bold', color: '#333' }}>{venta.cliente || '-'}</td>
                <td style={{ color: '#0284c7', fontSize: '0.85rem', fontWeight: 'bold' }}>{venta.vendedor || 'Sistema'}</td>
                <td style={{ textAlign: 'center', minWidth: '120px' }}>
                  <span style={{
                    padding: '4px 12px',
                    borderRadius: '20px',
                    fontSize: '0.75rem',
                    fontWeight: 'bold',
                    background: (venta.tipoCliente === 'MAYORISTA' || venta.tipoCliente === 'MAYOR') ? '#e0f2f1' : '#f3e5f5',
                    color: (venta.tipoCliente === 'MAYORISTA' || venta.tipoCliente === 'MAYOR') ? '#00695c' : '#7b1fa2',
                    whiteSpace: 'nowrap',
                    display: 'inline-block'
                  }}>
                    {venta.tipoCliente}
                  </span>
                </td>
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
                <td style={{ padding: '12px 16px', color: '#666', fontSize: '0.88rem', maxWidth: '300px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                  {venta.notas || <span style={{ color: '#ccc' }}>Sin notas</span>}
                </td>
                <td style={{ textAlign: 'right' }}>
                  <button onClick={() => setVentaSeleccionada(venta)} style={btnVerDetalle}>Ver pares</button>
                </td>
              </tr>
            ))}
            {!cargando && ventasFiltradas.length === 0 && (
              <tr>
                <td colSpan={10} style={{ textAlign: 'center', padding: '40px', color: '#999' }}>
                  No se encontraron ventas con los filtros aplicados.
                </td>
              </tr>
            )}
            {cargando && (
              <tr>
                <td colSpan={10} style={{ textAlign: 'center', padding: '40px', color: '#999' }}>
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
              <div style={{ gridColumn: '1 / span 2', position: 'relative' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '5px' }}>
                  <label style={{ ...labelStyle, marginBottom: 0 }}>Cliente *</label>
                  <button 
                    type="button" 
                    onClick={() => setMostrarNuevoCliente(true)}
                    style={{ background: 'none', border: 'none', color: '#8d6e63', fontSize: '0.75rem', fontWeight: 'bold', cursor: 'pointer', textDecoration: 'underline' }}
                  >
                    + Nuevo cliente
                  </button>
                </div>
                
                <input 
                  type="text" 
                  placeholder="Buscar cliente por nombre o tel..." 
                  style={inputStyle}
                  value={busquedaCliente}
                  onChange={(e) => setBusquedaCliente(e.target.value)}
                  onFocus={() => { if(!form.cliente_id) setBusquedaCliente('') }}
                />
                
                {busquedaCliente && !form.cliente_id && (
                  <div style={dropdownList}>
                    {clientesFiltradosModal.length > 0 ? (
                      clientesFiltradosModal.map(c => (
                        <div 
                          key={c.id} 
                          style={dropdownItem} 
                          onClick={() => {
                            setField('cliente_id', c.id);
                            setBusquedaCliente(`${c.nombre} (${c.tipo_cliente})`);
                          }}
                        >
                          <strong>{c.nombre}</strong> - {c.tipo_cliente} {c.telefono ? `(${c.telefono})` : ''}
                        </div>
                      ))
                    ) : (
                      <div style={{ ...dropdownItem, color: '#999', textAlign: 'center' }}>
                        No se encontraron resultados
                      </div>
                    )}
                  </div>
                )}

                {form.cliente_id && !busquedaCliente && (
                  <div style={{ position: 'absolute', top: '35px', right: '10px', color: '#8d6e63', fontSize: '0.8rem', cursor: 'pointer' }} onClick={() => { setField('cliente_id', ''); setBusquedaCliente(''); }}>
                    Limpiar ✕
                  </div>
                )}
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
                <label style={labelStyle}>Sucursal (Stock) *</label>
                <select style={selectStyle} value={form.sucursal} onChange={(e) => setField('sucursal', e.target.value)}>
                  {SUCURSALES_STOCK.map((sucursal) => (
                    <option key={sucursal} value={sucursal}>{sucursal}</option>
                  ))}
                </select>
              </div>

              <div style={{ gridColumn: '1 / span 2' }}>
                <label style={labelStyle}>Metodo de pago *</label>
                <select style={selectStyle} value={form.metodo_pago} onChange={(e) => setField('metodo_pago', e.target.value)}>
                  {METODOS_PAGO.map((metodo) => (
                    <option key={metodo} value={metodo}>{metodo}</option>
                  ))}
                </select>
              </div>

              <div style={{ gridColumn: '3 / span 2' }}>
                <label style={labelStyle}>Titular Responsable</label>
                <input 
                  type="text" 
                  readOnly 
                  style={{ ...inputStyle, background: '#f9f9f9', color: '#555' }} 
                  value={form.titular_cuenta || 'N/A'} 
                />
              </div>

              <div style={{ gridColumn: '1 / span 4' }}>
                <label style={labelStyle}>Mensaje adicional (Notas)</label>
                <textarea
                  style={{ ...inputStyle, height: '80px', resize: 'none' }}
                  placeholder="Escribe aquí cualquier observación sobre esta venta..."
                  value={form.notas}
                  onChange={(e) => setField('notas', e.target.value)}
                />
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

            <div style={{ marginTop: '22px', border: '1px solid #eee', borderRadius: '14px', padding: '16px' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '15px' }}>
                <h4 style={{ margin: 0, color: '#333' }}>
                  Menú de Productos Inteligente
                  {esClienteMayorista && (
                    <span style={{ marginLeft: '10px', color: '#00695c', fontSize: '0.78rem' }}>
                      Catálogo mayorista autorizado
                    </span>
                  )}
                </h4>
                <div style={{ display: 'flex', gap: '10px' }}>
                  <input 
                    type="text" 
                    placeholder="Buscar producto..." 
                    style={{ ...inputStyle, width: '250px', padding: '6px 12px' }}
                    value={busquedaProducto}
                    onChange={(e) => setBusquedaProducto(e.target.value)}
                  />
                </div>
              </div>

              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: '12px', maxHeight: '400px', overflowY: 'auto', paddingRight: '5px' }}>
                {productosAgrupados.map(prod => (
                  <div key={`${prod.referencia}-${prod.color}`} style={cardProducto}>
                    <div style={{ marginBottom: '8px' }}>
                      <div style={{ fontWeight: 'bold', fontSize: '0.95rem' }}>{prod.referencia}</div>
                      <div style={{ fontSize: '0.8rem', color: '#8d6e63' }}>{prod.color} | {prod.tipo}</div>
                    </div>
                    
                    <div style={{ display: 'flex', flexWrap: 'wrap', gap: '6px' }}>
                      {[34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44].map(t => {
                        const info = prod.tallas[t];
                        const estaSeleccionado = form.items.some(it => it.seleccion === info?.key);
                        
                        if (!info) return (
                          <div key={t} style={{ ...tallaBox, opacity: 0.2, cursor: 'not-allowed' }}>{t}</div>
                        );

                        return (
                          <div 
                            key={t} 
                            style={{ 
                              ...tallaBox, 
                              ...(estaSeleccionado ? tallaBoxActivo : {}),
                              position: 'relative'
                            }}
                            onClick={() => {
                              if (estaSeleccionado) {
                                setForm(prev => ({
                                  ...prev,
                                  items: prev.items.filter(it => it.seleccion !== info.key)
                                }));
                              } else {
                                const precioSugerido = (clientes.find(c => c.id === Number(form.cliente_id))?.tipo_cliente === 'MAYORISTA' || clientes.find(c => c.id === Number(form.cliente_id))?.tipo_cliente === 'MAYOR') 
                                  ? 0 : 0; // Se podria precargar el precio del producto aqui
                                
                                setForm(prev => ({
                                  ...prev,
                                  items: [...prev.items, {
                                    seleccion: info.key,
                                    producto_id: info.productoId,
                                    talla: t,
                                    cantidad: 1,
                                    precio_unitario: 0,
                                    referencia: prod.referencia,
                                    color: prod.color
                                  }]
                                }));
                              }
                            }}
                          >
                            {t}
                            <span style={dispBadge}>{info.disponibles}</span>
                          </div>
                        );
                      })}
                    </div>
                  </div>
                ))}
                {productosAgrupados.length === 0 && (
                  <div style={{ gridColumn: '1 / -1', padding: '20px', textAlign: 'center', color: '#888' }}>
                    No hay productos disponibles para este filtro.
                  </div>
                )}
              </div>
            </div>

            {form.items.length > 0 && (
              <div style={{ marginTop: '20px' }}>
                <h4 style={{ fontSize: '0.9rem', marginBottom: '10px' }}>Resumen de items seleccionados</h4>
                <div style={{ display: 'grid', gap: '8px' }}>
                  {form.items.map((item, index) => (
                    <div key={index} style={{ ...lineaVenta, padding: '8px 14px' }}>
                      <div style={{ flex: 1 }}>
                        <div style={{ fontWeight: 'bold', fontSize: '0.85rem' }}>{item.referencia} {item.color}</div>
                        <div style={{ fontSize: '0.75rem', color: '#888' }}>Talla {item.talla}</div>
                      </div>
                      
                      <div style={{ width: '70px' }}>
                        <input
                          style={{ ...inputStyle, padding: '4px 8px', fontSize: '0.85rem' }}
                          type="number"
                          value={item.cantidad}
                          onChange={(e) => actualizarLinea(index, 'cantidad', e.target.value)}
                        />
                      </div>

                      <div style={{ width: '120px' }}>
                        <input
                          style={{ ...inputStyle, padding: '4px 8px', fontSize: '0.85rem' }}
                          type="number"
                          placeholder="Precio"
                          value={item.precio_unitario}
                          onChange={(e) => actualizarLinea(index, 'precio_unitario', e.target.value)}
                        />
                      </div>

                      <div style={{ width: '100px', textAlign: 'right', fontWeight: 'bold', fontSize: '0.9rem' }}>
                        ${formatoNumero(item.cantidad * item.precio_unitario)}
                      </div>

                      <button type="button" onClick={() => eliminarLinea(index)} style={{ background: 'none', border: 'none', color: '#ff4d4d', cursor: 'pointer', fontSize: '1.2rem' }}>✕</button>
                    </div>
                  ))}
                </div>
              </div>
            )}

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

      {ventaSeleccionada && (
        <div style={overlay} onClick={() => setVentaSeleccionada(null)}>
          <div style={{ ...modal, width: '600px' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
              <h3 style={{ margin: 0, color: '#582e2e' }}>Pares Vendidos</h3>
              <button onClick={() => setVentaSeleccionada(null)} style={btnCerrar}>x
              </button>
            </div>

            <div style={{ background: '#fcfcfc', borderRadius: '14px', padding: '20px', border: '1px solid #eee' }}>
              <div style={{ marginBottom: '15px', paddingBottom: '10px', borderBottom: '1px dashed #ddd' }}>
                <div style={{ fontSize: '0.9rem', color: '#888' }}>Cliente</div>
                <div style={{ fontWeight: 'bold', color: '#333' }}>{ventaSeleccionada.cliente}</div>
              </div>

              <div style={{ display: 'grid', gap: '10px' }}>
                {(ventaSeleccionada.items || []).map((item) => (
                  <div key={item.id} style={{ ...lineaVenta, background: 'white' }}>
                    <div style={{ flex: 1 }}>
                      <div style={{ fontWeight: 'bold', fontSize: '1rem' }}>{item.referencia}</div>
                      <div style={{ fontSize: '0.85rem', color: '#8d6e63' }}>{item.color}</div>
                    </div>
                    <div style={{ display: 'flex', gap: '15px', alignItems: 'center' }}>
                      <div style={tagTalla}>Talla {item.talla}</div>
                      <div style={{ ...tagCantidad, padding: '4px 10px', fontSize: '0.9rem' }}>{item.cantidad} par(es)</div>
                    </div>
                  </div>
                ))}
              </div>

              <div style={{ marginTop: '20px', textAlign: 'right', fontWeight: 'bold', fontSize: '1.2rem', color: '#2e7d32' }}>
                Total: ${formatoNumero(ventaSeleccionada.total)}
              </div>
            </div>

            {ventaSeleccionada.notas && (
              <div style={{ marginTop: '20px', padding: '15px', background: '#fff9f5', borderRadius: '12px', border: '1px solid #ffe8d6' }}>
                <div style={{ fontSize: '0.8rem', color: '#8d6e63', marginBottom: '5px', fontWeight: 'bold' }}>NOTA DEL VENDEDOR:</div>
                <div style={{ fontSize: '0.9rem', color: '#582e2e', fontStyle: 'italic' }}>"{ventaSeleccionada.notas}"</div>
              </div>
            )}

            <div style={{ display: 'flex', justifyContent: 'flex-end', marginTop: '25px' }}>
              <button onClick={() => setVentaSeleccionada(null)} style={btnPrimario}>Cerrar</button>
            </div>
          </div>
        </div>
      )}
      {mostrarNuevoCliente && (
        <NuevoClienteModal 
          onCerrar={() => setMostrarNuevoCliente(false)} 
          onGuardar={(nuevo) => {
            setClientes(prev => [...prev, nuevo]);
            setField('cliente_id', nuevo.id);
            setBusquedaCliente(`${nuevo.nombre} (${nuevo.tipo_cliente})`);
          }}
        />
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
const btnVerDetalle = { background: '#f8f4f1', color: '#582e2e', border: '1px solid #eaddd7', padding: '6px 12px', borderRadius: '20px', cursor: 'pointer', fontWeight: 'bold', fontSize: '0.8rem' };

const itemChip = {
  background: '#f8f4f1',
  border: '1px solid #eaddd7',
  borderRadius: '8px',
  padding: '5px 10px',
  fontSize: '0.82rem',
  color: '#582e2e',
  display: 'inline-flex',
  alignItems: 'center',
  gap: '8px',
  boxShadow: '0 1px 2px rgba(0,0,0,0.03)'
};

const tagTalla = {
  background: '#eaddd7',
  color: '#582e2e',
  fontWeight: 'bold',
  padding: '1px 6px',
  borderRadius: '4px',
  fontSize: '0.75rem'
};

const tagCantidad = {
  background: '#582e2e',
  color: 'white',
  fontWeight: 'bold',
  padding: '1px 6px',
  borderRadius: '4px',
  fontSize: '0.75rem'
};

const cardProducto = { background: 'white', border: '1px solid #eee', borderRadius: '12px', padding: '12px', transition: 'all 0.2s' };
const tallaBox = { width: '36px', height: '36px', border: '1.5px solid #eee', borderRadius: '8px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '0.85rem', fontWeight: 'bold', cursor: 'pointer', background: '#fdfdfd', color: '#666', transition: 'all 0.15s' };
const tallaBoxActivo = { background: '#582e2e', color: 'white', borderColor: '#582e2e' };
const dispBadge = { position: 'absolute', top: '-6px', right: '-6px', background: '#fdf4f1', color: '#8d6e63', fontSize: '0.62rem', padding: '1px 3px', borderRadius: '4px', border: '1px solid #eaddd7', minWidth: '14px', textAlign: 'center' };
const dropdownList = { position: 'absolute', top: '100%', left: 0, right: 0, background: 'white', border: '1px solid #eee', borderRadius: '10px', boxShadow: '0 10px 25px rgba(0,0,0,0.1)', zIndex: 10, maxHeight: '200px', overflowY: 'auto', marginTop: '5px' };
const dropdownItem = { padding: '10px 15px', cursor: 'pointer', borderBottom: '1px solid #f5f5f5', fontSize: '0.9rem', hover: { background: '#fcfcfc' } };

function NuevoClienteModal({ onCerrar, onGuardar }) {
  const [nuevoCli, setNuevoCli] = useState({ nombre: '', telefono: '', tipo_cliente: 'DETAL' });
  const [cargando, setCargando] = useState(false);

  const guardar = async () => {
    if (!nuevoCli.nombre) return alert('El nombre es obligatorio');
    setCargando(true);
    try {
      const res = await api.post('/clientes', nuevoCli);
      onGuardar(res.data);
      onCerrar();
    } catch (err) {
      alert('Error al guardar cliente');
    } finally {
      setCargando(false);
    }
  };

  return (
    <div style={{ ...overlay, zIndex: 1100 }} onClick={onCerrar}>
      <div style={{ ...modal, width: '400px' }} onClick={e => e.stopPropagation()}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
          <h3 style={{ margin: 0, color: '#582e2e' }}>Registrar Nuevo Cliente</h3>
          <button onClick={onCerrar} style={btnCerrar}>x</button>
        </div>
        
        <div style={{ display: 'grid', gap: '15px' }}>
          <div>
            <label style={labelStyle}>Nombre Completo *</label>
            <input 
              style={inputStyle} 
              placeholder="Ej: Maria Perez"
              value={nuevoCli.nombre} 
              onChange={e => setNuevoCli({...nuevoCli, nombre: e.target.value})} 
            />
          </div>
          <div>
            <label style={labelStyle}>Teléfono</label>
            <input 
              style={inputStyle} 
              placeholder="Ej: 3001234567"
              value={nuevoCli.telefono} 
              onChange={e => setNuevoCli({...nuevoCli, telefono: e.target.value})} 
            />
          </div>
          <div>
            <label style={labelStyle}>Tipo de Cliente</label>
            <select 
              style={selectStyle} 
              value={nuevoCli.tipo_cliente} 
              onChange={e => setNuevoCli({...nuevoCli, tipo_cliente: e.target.value})}
            >
              <option value="DETAL">Detal</option>
              <option value="MAYORISTA">Mayorista</option>
            </select>
          </div>
        </div>
        <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '10px', marginTop: '25px' }}>
          <button onClick={onCerrar} style={btnSecundario}>Cancelar</button>
          <button onClick={guardar} disabled={cargando} style={btnPrimario}>
            {cargando ? 'Guardando...' : 'Guardar Cliente'}
          </button>
        </div>
      </div>
    </div>
  );
}
