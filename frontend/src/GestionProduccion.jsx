import { useState, useEffect } from 'react';
import api from './api';
import LoadingState from './components/LoadingState';

function GestionProduccion() {
  const fechaLocalISO = (fecha = new Date()) => {
    const offset = fecha.getTimezoneOffset() * 60000;
    return new Date(fecha.getTime() - offset).toISOString().split('T')[0];
  };

  const [ordenes, setOrdenes] = useState([]);
  const [empleados, setEmpleados] = useState([]);
  const [stats, setStats] = useState({ paresFabricar: 0, paresStock: 0 });
  const [filtro, setFiltro] = useState('');
  const [filtroEmpleado, setFiltroEmpleado] = useState('');
  const [fechaInicio, setFechaInicio] = useState(() => {
      const hoy = new Date();
      // Por defecto inicio del mes actual
      const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
      return fechaLocalISO(primerDia);
  });
  const [fechaFin, setFechaFin] = useState(() => fechaLocalISO(new Date()));
  const [usarFiltroFechas, setUsarFiltroFechas] = useState(false);
  const [cargando, setCargando] = useState(false);
  
  // MODALES
  const [modalAsignarOpen, setModalAsignarOpen] = useState(false);
  const [modalHistorialOpen, setModalHistorialOpen] = useState(false);
  
  // DATOS SELECCIONADOS
  const [ordenSeleccionada, setOrdenSeleccionada] = useState(null);
  const [empleadoSeleccionado, setEmpleadoSeleccionado] = useState('');
  const [rolNecesarioModal, setRolNecesarioModal] = useState('');

  // 1. CARGA DE DATOS
  const cargarDatos = (esPolling = false) => {
    if (!esPolling) setCargando(true);
    const url = usarFiltroFechas
      ? `/produccion/tablero?rango=custom&inicio=${fechaInicio}&fin=${fechaFin}`
      : '/produccion/tablero?rango=produccion';

    api.get(url)
      .then(res => {
        setOrdenes(res.data.ordenes || []);
        setEmpleados(res.data.empleados || []);
        if (res.data.stats) setStats(res.data.stats);
      })
      .catch(err => console.error("Error cargando tablero:", err))
      .finally(() => { if (!esPolling) setCargando(false); });
  };

  useEffect(() => {
    cargarDatos();
    // Aumentamos a 15 segundos para no saturar la red (especialmente con bases de datos en la nube como Supabase)
    const intervalo = setInterval(() => cargarDatos(true), 15000); 
    return () => clearInterval(intervalo);
  }, [usarFiltroFechas, fechaInicio, fechaFin]);

  // --- FILTRADO ---
  const ordenesFiltradas = ordenes.filter(o => {
    // Filtro de texto
    const term = filtro.toLowerCase();
    const ref = (o.referencia || '').toLowerCase();
    const col = (o.color || '').toLowerCase();
    const num = (o.numeroOrden || o.numero_orden || '').toString().toLowerCase();
    const pasaTexto = ref.includes(term) || col.includes(term) || num.includes(term);

    // Filtro por empleado
    let pasaEmpleado = true;
    if (filtroEmpleado) {
        const idEmp = parseInt(filtroEmpleado);
        pasaEmpleado = (
            o.cortadorId === idEmp ||
            o.armadorId === idEmp ||
            o.costureroId === idEmp ||
            o.soladorId === idEmp ||
            o.emplantilladorId === idEmp
        );
    }

    return pasaTexto && pasaEmpleado;
  });

  // --- OBTENER NOMBRE DEL RESPONSABLE ACTUAL ---
  const obtenerNombreResponsable = (orden) => {
      let idBuscado = null;
      const estado = orden.estado || "";

      if(estado === "EN_CORTE") idBuscado = orden.cortadorId;
      else if(estado === "EN_ARMADO") idBuscado = orden.armadorId;
      else if(estado === "EN_COSTURA") idBuscado = orden.costureroId;
      else if(estado === "EN_SOLADURA") idBuscado = orden.soladorId;
      else if(estado === "EN_EMPLANTILLADO") idBuscado = orden.emplantilladorId;

      if(!idBuscado) return <span style={{color:'#999', fontStyle:'italic'}}>-- Sin Asignar --</span>;
      
      const emp = empleados.find(e => e.id == idBuscado);
      return emp ? <b>{emp.nombre}</b> : "Desconocido";
  };

  const abrirAsignar = (orden, rol) => {
    setOrdenSeleccionada(orden);
    setRolNecesarioModal(rol); 
    setEmpleadoSeleccionado('');
    setModalAsignarOpen(true);
  };

  const guardarAsignacion = () => {
    if (!empleadoSeleccionado) return alert("Selecciona un trabajador");
    api.post('/produccion/asignar', {
        ordenId: ordenSeleccionada.id,
        empleadoId: empleadoSeleccionado,
        rol: rolNecesarioModal,
        nuevoEstado: ordenSeleccionada.estado 
    })
    .then(() => {
        setModalAsignarOpen(false);
        cargarDatos();
        alert("¡Asignado correctamente!");
    })
    .catch(err => alert("Error al asignar"));
  };

  const pasarAStock = (orden) => {
    if (!window.confirm(`¿Pasar la orden ${orden.numeroOrden || orden.numero_orden} a stock?`)) return;

    api.post('/produccion/pasar-a-stock', {
      ordenId: orden.id,
    })
    .then((res) => {
      cargarDatos();
      alert(res.data?.mensaje || "Orden ingresada a stock");
    })
    .catch((err) => {
      alert(err?.response?.data?.error || "No fue posible pasar la orden a stock");
    });
  };

  const renderBotonAccion = (orden) => {
    const estado = orden.estado || "";
    let idAsignado = null;
    let rolRequerido = "";
    let nombreEtapa = "";

    if (estado === "EN_CORTE") { idAsignado = orden.cortadorId; rolRequerido = "CORTE"; nombreEtapa = "Corte"; } 
    else if (estado === "EN_ARMADO") { idAsignado = orden.armadorId; rolRequerido = "ARMADOR"; nombreEtapa = "Armado"; }
    else if (estado === "EN_COSTURA") { idAsignado = orden.costureroId; rolRequerido = "COSTURERO"; nombreEtapa = "Costura"; }
    else if (estado === "EN_SOLADURA") { idAsignado = orden.soladorId; rolRequerido = "SOLADOR"; nombreEtapa = "Soladura"; }
    else if (estado === "EN_EMPLANTILLADO") { idAsignado = orden.emplantilladorId; rolRequerido = "EMPLANTILLADOR"; nombreEtapa = "Emplantillado"; }

    if (orden.puedePasarAStock) {
        return <button onClick={() => pasarAStock(orden)} style={{ background: '#2e7d32', color: 'white', border: 'none', padding: '8px 12px', borderRadius: '4px', cursor: 'pointer', fontWeight: 'bold' }}>Pasar a Stock</button>;
    }
    if (estado === "EN_STOCK") {
        return <span style={{ fontSize: '0.85rem', color: '#2e7d32', fontWeight: 'bold', border: '1px solid #2e7d32', padding:'4px 8px', borderRadius:'4px', background: '#e8f5e9' }}>En stock</span>;
    }
    if (!idAsignado && rolRequerido) {
        return <button onClick={() => abrirAsignar(orden, rolRequerido)} style={{ background: '#5D4037', color: 'white', border: 'none', padding: '8px 12px', borderRadius: '4px', cursor: 'pointer', fontWeight: 'bold' }}>👤 Asignar {nombreEtapa}</button>;
    }
    if (idAsignado) {
        return <span style={{ fontSize: '0.85rem', color: '#28a745', fontWeight: 'bold', border: '1px solid #28a745', padding:'4px 8px', borderRadius:'4px', background: '#e8f5e9' }}>🔨 En Proceso...</span>;
    }
    return <span style={{color:'#ccc'}}>-</span>;
  };

  const abrirHistorial = (orden) => {
    setOrdenSeleccionada(orden);
    setModalHistorialOpen(true);
  };

  const getNombreEmpleado = (id) => {
      if(!id) return "No asignado";
      const e = empleados.find(emp => emp.id == id);
      return e ? e.nombre : "Desconocido";
  };

  return (
    <div className="fade-in" style={{ padding: '20px', fontFamily: 'Arial, sans-serif' }}>
      
      <div style={{ display: 'flex', gap: '20px', marginBottom: '20px' }}>
         <div style={{ background: '#fff3e0', padding: '15px 20px', borderRadius: '8px', borderLeft: '4px solid #f57c00', flex: 1, boxShadow: '0 2px 5px rgba(0,0,0,0.05)' }}>
            <h4 style={{ margin: 0, color: '#e65100', fontSize: '0.9rem', textTransform: 'uppercase' }}>Pares Mandados a Fabricar</h4>
            <div style={{ fontSize: '1.8rem', fontWeight: 'bold', color: '#5D4037', marginTop: '5px' }}>{stats.paresFabricar}</div>
         </div>
         <div style={{ background: '#e8f5e9', padding: '15px 20px', borderRadius: '8px', borderLeft: '4px solid #43a047', flex: 1, boxShadow: '0 2px 5px rgba(0,0,0,0.05)' }}>
            <h4 style={{ margin: 0, color: '#2e7d32', fontSize: '0.9rem', textTransform: 'uppercase' }}>Pares Entrados a Stock</h4>
            <div style={{ fontSize: '1.8rem', fontWeight: 'bold', color: '#5D4037', marginTop: '5px' }}>{stats.paresStock}</div>
         </div>
      </div>

      {cargando && <LoadingState mensaje="Cargando órdenes de producción..." />}

      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
        <div>
            <h2 style={{ color: '#5D4037', margin: 0 }}>Control de Producción & Trazabilidad</h2>
            <p style={{ color: '#888', margin: '5px 0 0 0' }}>
              Monitoreo en tiempo real de la fábrica. 
              {cargando && <span style={{color: '#d97706', marginLeft: '10px', fontSize: '0.85rem', fontWeight: 'bold'}}>⏳ Sincronizando...</span>}
            </p>
        </div>

        <div style={{ display: 'flex', gap: '10px', alignItems: 'center' }}>
          
          <div style={{ display: 'flex', alignItems: 'center', gap: '5px', background: 'white', border: '1px solid #e2e8f0', borderRadius: '8px', padding: '2px 10px' }}>
              <span style={{ fontSize: '0.85rem', fontWeight: 'bold', color: '#64748b' }}>Desde:</span>
              <input 
                  type="date" 
                  value={fechaInicio} 
                  onChange={e => {
                    setFechaInicio(e.target.value);
                    setUsarFiltroFechas(true);
                  }}
                  style={{ border: 'none', outline: 'none', background: 'transparent', padding: '8px 5px', color: '#334155', fontWeight: '500', cursor: 'pointer' }}
              />
              <span style={{ fontSize: '0.85rem', fontWeight: 'bold', color: '#64748b', marginLeft: '5px' }}>Hasta:</span>
              <input 
                  type="date" 
                  value={fechaFin} 
                  onChange={e => {
                    setFechaFin(e.target.value);
                    setUsarFiltroFechas(true);
                  }}
                  style={{ border: 'none', outline: 'none', background: 'transparent', padding: '8px 5px', color: '#334155', fontWeight: '500', cursor: 'pointer' }}
              />
          </div>
          {usarFiltroFechas && (
            <button
              onClick={() => setUsarFiltroFechas(false)}
              style={{ background: '#eef2ff', color: '#3730a3', border: '1px solid #c7d2fe', borderRadius: '8px', padding: '8px 10px', cursor: 'pointer', fontWeight: 600 }}
            >
              Ver producción activa
            </button>
          )}

          <select 
              value={filtroEmpleado} 
              onChange={e => setFiltroEmpleado(e.target.value)}
              className="search-input-premium"
              style={{ width: '180px', paddingLeft: '15px' }}
          >
              <option value="">Todos los empleados</option>
              {empleados.map(e => (
                  <option key={e.id} value={e.id}>{e.nombre} {e.apellido || ''}</option>
              ))}
          </select>

          <div className="search-container" style={{ maxWidth: '300px' }}>
            <span className="search-icon-inside">🔍</span>
            <input 
                type="text" 
                placeholder="Buscar orden o referencia..." 
                value={filtro}
                onChange={e => setFiltro(e.target.value)}
                className="search-input-premium"
            />
            {filtro && (
              <button onClick={() => setFiltro('')} className="clear-search-btn">
                &times;
              </button>
            )}
          </div>
        </div>
      </div>
      
      <div className="table-responsive" style={{ marginTop: '10px', background: 'white', padding: '15px', borderRadius: '8px', boxShadow: '0 2px 8px rgba(0,0,0,0.1)' }}>
        <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '0.95rem' }}>
          <thead>
            <tr style={{ background: '#f8f9fa', color: '#5D4037', textAlign: 'left' }}>
              <th style={{ padding: '12px' }}>Orden #</th>
              <th style={{ padding: '12px' }}>Referencia</th>
              <th style={{ padding: '12px' }}>Color</th> 
              <th style={{ padding: '12px' }}>Pares</th>
              <th style={{ padding: '12px' }}>Estado Actual</th>
              <th style={{ padding: '12px' }}>Responsable</th>
              <th style={{ padding: '12px', textAlign: 'center' }}>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {ordenesFiltradas.map((orden) => (
              <tr key={orden.id} style={{ borderBottom: '1px solid #eee' }}>
                <td style={{ padding: '12px', fontWeight: 'bold' }}>{orden.numeroOrden || orden.numero_orden || 'S/N'}</td>
                <td style={{ padding: '12px' }}>{orden.referencia || '---'}</td>
                <td style={{ padding: '12px' }}>{orden.color || '---'}</td> 
                <td style={{ padding: '12px' }}>{orden.totalPares || orden.pares || 0}</td>
                <td style={{ padding: '12px' }}>
                   <span style={{ padding: '4px 10px', borderRadius: '15px', fontSize: '0.8rem', fontWeight: 'bold', background: '#e3f2fd', color: '#0d47a1', border: '1px solid #bbdefb' }}>
                       {orden.estado ? orden.estado.replace(/_/g, ' ') : 'PENDIENTE'}
                   </span>
                </td>
                <td style={{ padding: '12px', color: '#555' }}>{obtenerNombreResponsable(orden)}</td>
                <td style={{ padding: '12px', textAlign: 'center', display: 'flex', gap: '10px', justifyContent: 'center', alignItems: 'center' }}>
                  <button onClick={() => abrirHistorial(orden)} title="Ver Historial" style={{ background: '#17a2b8', color: 'white', border: 'none', padding: '8px 12px', borderRadius: '4px', cursor: 'pointer', marginRight: '10px' }}>📜</button>
                  {renderBotonAccion(orden)}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {modalAsignarOpen && ordenSeleccionada && (
        <div style={styles.overlay}>
          <div style={styles.modal}>
            <h3 style={{color:'#5D4037', marginBottom:'5px'}}>Asignar Responsable</h3>
            <p style={{marginBottom:'15px', color:'#666'}}>Orden <b>#{ordenSeleccionada.numeroOrden}</b> ({ordenSeleccionada.color})</p>
            <div style={{background:'#f5f5f5', padding:'10px', borderRadius:'5px', marginBottom:'15px'}}>
                <span style={{fontSize:'0.9rem'}}>Rol requerido:</span><br/><b style={{color: '#d84315', fontSize:'1.1rem'}}>{rolNecesarioModal}</b>
            </div>
            <label style={{display:'block', marginBottom: '5px', fontWeight:'bold'}}>Seleccionar Trabajador:</label>
            <select value={empleadoSeleccionado} onChange={(e) => setEmpleadoSeleccionado(e.target.value)} style={{ width: '100%', padding: '10px', border: '1px solid #ccc', borderRadius: '4px', fontSize:'1rem' }}>
                <option value="">-- Seleccionar --</option>
                {empleados.filter(e => e.rol && e.rol.toUpperCase() === rolNecesarioModal.toUpperCase()).map(e => (
                    <option key={e.id} value={e.id}>{e.nombre} ({e.rol})</option>
                ))}
            </select>
            <div style={{ textAlign: 'right', marginTop: '20px', display: 'flex', gap: '10px', justifyContent: 'flex-end' }}>
                <button onClick={() => setModalAsignarOpen(false)} style={styles.btnCancel}>Cancelar</button>
                <button onClick={guardarAsignacion} style={styles.btnConfirm}>Confirmar</button>
            </div>
          </div>
        </div>
      )}

      {modalHistorialOpen && ordenSeleccionada && (
        <div style={styles.overlay}>
            <div style={{...styles.modal, width: '500px'}}>
                <h3 style={{ borderBottom: '1px solid #ccc', paddingBottom: '10px', color: '#5D4037' }}>Historial #{ordenSeleccionada.numeroOrden}</h3>
                <div style={{ marginTop: '20px', maxHeight: '400px', overflowY: 'auto' }}>
                    <TimelineItem titulo="Creación de Orden" fecha={ordenSeleccionada.fechaInicio} responsable="Administración" terminado={true} />
                    <TimelineItem titulo="Corte" fecha={ordenSeleccionada.fechaFinCorte} responsable={getNombreEmpleado(ordenSeleccionada.cortadorId)} terminado={!!ordenSeleccionada.fechaFinCorte} />
                    <TimelineItem titulo="Armado" fecha={ordenSeleccionada.fechaFinArmado} responsable={getNombreEmpleado(ordenSeleccionada.armadorId)} terminado={!!ordenSeleccionada.fechaFinArmado} />
                    <TimelineItem titulo="Costura" fecha={ordenSeleccionada.fechaFinCostura} responsable={getNombreEmpleado(ordenSeleccionada.costureroId)} terminado={!!ordenSeleccionada.fechaFinCostura} />
                    <TimelineItem titulo="Soladura" fecha={ordenSeleccionada.fechaFinSoladura} responsable={getNombreEmpleado(ordenSeleccionada.soladorId)} terminado={!!ordenSeleccionada.fechaFinSoladura} />
                    <TimelineItem titulo="Emplantillado" fecha={ordenSeleccionada.fechaFinEmplantillado} responsable={getNombreEmpleado(ordenSeleccionada.emplantilladorId)} terminado={!!ordenSeleccionada.fechaFinEmplantillado} />
                </div>
                <div style={{ textAlign: 'center', marginTop: '20px' }}>
                    <button onClick={() => setModalHistorialOpen(false)} style={styles.btnCancel}>Cerrar</button>
                </div>
            </div>
        </div>
      )}
    </div>
  );
}

const TimelineItem = ({ titulo, fecha, responsable, terminado }) => (
    <div style={{ display: 'flex', marginBottom: '20px', opacity: terminado ? 1 : 0.6, borderLeft: terminado ? '3px solid #28a745' : '3px solid #ccc', paddingLeft: '15px' }}>
        <div>
            <div style={{ fontWeight: 'bold', fontSize: '1rem', color: terminado ? '#28a745' : '#555' }}>{titulo}</div>
            <div style={{ fontSize: '0.85rem', color: '#666', marginTop: '4px' }}>{terminado ? `Finalizado: ${new Date(fecha).toLocaleString()}` : 'Pendiente...'}</div>
            {terminado && <div style={{ fontSize: '0.85rem', color: '#333', fontStyle: 'italic' }}>Por: {responsable}</div>}
        </div>
    </div>
);

const styles = {
    overlay: { position: 'fixed', top: 0, left: 0, width: '100%', height: '100%', background: 'rgba(0,0,0,0.6)', display: 'flex', justifyContent: 'center', alignItems: 'center', zIndex: 999 },
    modal: { background: 'white', padding: '30px', borderRadius: '12px', width: '380px', boxShadow: '0 10px 25px rgba(0,0,0,0.2)' },
    btnCancel: { background: '#e0e0e0', border: 'none', padding: '10px 20px', borderRadius: '6px', cursor: 'pointer', color: '#333', fontWeight: 'bold' },
    btnConfirm: { background: '#5D4037', color: 'white', border: 'none', padding: '10px 20px', borderRadius: '6px', cursor: 'pointer', fontWeight: 'bold' }
};

export default GestionProduccion;
