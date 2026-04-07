import { useState, useEffect } from 'react';
import axios from 'axios';

function GestionProduccion() {
  const [ordenes, setOrdenes] = useState([]);
  const [empleados, setEmpleados] = useState([]);
  
  // MODALES
  const [modalAsignarOpen, setModalAsignarOpen] = useState(false);
  const [modalHistorialOpen, setModalHistorialOpen] = useState(false);
  
  // DATOS SELECCIONADOS
  const [ordenSeleccionada, setOrdenSeleccionada] = useState(null);
  const [empleadoSeleccionado, setEmpleadoSeleccionado] = useState('');
  const [rolNecesarioModal, setRolNecesarioModal] = useState('');

  // 1. CARGA DE DATOS
  const cargarDatos = () => {
    axios.get('http://localhost:4000/api/produccion/tablero')
      .then(res => {
        // Aseguramos que existan arrays aunque vengan vacíos
        setOrdenes(res.data.ordenes || []);
        setEmpleados(res.data.empleados || []);
      })
      .catch(err => console.error("Error cargando tablero:", err));
  };

  useEffect(() => {
    cargarDatos();
    const intervalo = setInterval(cargarDatos, 5000); 
    return () => clearInterval(intervalo);
  }, []);

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

  // --- ABRIR MODAL ASIGNAR ---
  const abrirAsignar = (orden, rol) => {
    setOrdenSeleccionada(orden);
    setRolNecesarioModal(rol); 
    setEmpleadoSeleccionado('');
    setModalAsignarOpen(true);
  };

  // --- GUARDAR ASIGNACIÓN ---
  const guardarAsignacion = () => {
    if (!empleadoSeleccionado) return alert("Selecciona un trabajador");

    axios.post('http://localhost:4000/api/produccion/asignar', {
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

  // --- BOTÓN INTELIGENTE CORREGIDO ---
const renderBotonAccion = (orden) => {
    const estado = orden.estado || "";
    
    let idAsignado = null;
    let rolRequerido = "";
    let nombreEtapa = "";

    // IMPORTANTE: Estos nombres deben ser IGUALES a los del index.ts (Líneas 193-196)
    if (estado === "EN_CORTE") {
        idAsignado = orden.cortadorId;
        rolRequerido = "CORTE"; 
        nombreEtapa = "Corte";
    } 
    else if (estado === "EN_ARMADO") {
        idAsignado = orden.armadorId;
        rolRequerido = "ARMADOR";   // Antes decía ARMADO
        nombreEtapa = "Armado";
    }
    else if (estado === "EN_COSTURA") {
        idAsignado = orden.costureroId;
        rolRequerido = "COSTURERO"; // Antes decía COSTURA
        nombreEtapa = "Costura";
    }
    else if (estado === "EN_SOLADURA") {
        idAsignado = orden.soladorId;
        rolRequerido = "SOLADOR";   // Antes decía SOLADURA
        nombreEtapa = "Soladura";
    }
    else if (estado === "EN_EMPLANTILLADO") {
        idAsignado = orden.emplantilladorId;
        rolRequerido = "EMPLANTILLADOR"; 
        nombreEtapa = "Emplantillado";
    }

    if (!idAsignado && rolRequerido) {
        return (
            <button 
                onClick={() => abrirAsignar(orden, rolRequerido)}
                style={{ 
                    background: '#5D4037', color: 'white', border: 'none', padding: '8px 12px', 
                    borderRadius: '4px', cursor: 'pointer', fontWeight: 'bold'
                }}
            >
                👤 Asignar {nombreEtapa}
            </button>
        );
    }
    
    if (idAsignado) {
        return (
            <span style={{ 
                fontSize: '0.85rem', color: '#28a745', fontWeight: 'bold', 
                border: '1px solid #28a745', padding:'4px 8px', borderRadius:'4px', background: '#e8f5e9'
            }}>
                🔨 En Proceso...
            </span>
        );
    }

    return <span style={{color:'#ccc'}}>-</span>;
};

  // --- HISTORIAL ---
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
    <div style={{ padding: '20px', fontFamily: 'Arial, sans-serif' }}>
      <h2 style={{ color: '#5D4037', borderBottom: '2px solid #5D4037', paddingBottom: '10px' }}>
        Control de Producción & Trazabilidad
      </h2>
      
      <div className="table-responsive" style={{ marginTop: '20px', background: 'white', padding: '15px', borderRadius: '8px', boxShadow: '0 2px 8px rgba(0,0,0,0.1)' }}>
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
            {ordenes.map((orden) => (
              <tr key={orden.id} style={{ borderBottom: '1px solid #eee' }}>
                <td style={{ padding: '12px', fontWeight: 'bold' }}>{orden.numeroOrden || orden.numero_orden || 'S/N'}</td>
                <td style={{ padding: '12px' }}>{orden.referencia || '---'}</td>
                <td style={{ padding: '12px' }}>{orden.color || '---'}</td> 
                <td style={{ padding: '12px' }}>{orden.totalPares || orden.pares || 0}</td>
                
                <td style={{ padding: '12px' }}>
                   <span style={{
                       padding: '4px 10px', borderRadius: '15px', fontSize: '0.8rem', fontWeight: 'bold',
                       background: '#e3f2fd', color: '#0d47a1', border: '1px solid #bbdefb'
                   }}>
                       {orden.estado ? orden.estado.replace(/_/g, ' ') : 'PENDIENTE'}
                   </span>
                </td>

                <td style={{ padding: '12px', color: '#555' }}>
                   {obtenerNombreResponsable(orden)}
                </td>

                <td style={{ padding: '12px', textAlign: 'center', display: 'flex', gap: '10px', justifyContent: 'center', alignItems: 'center' }}>
                  <button 
                    onClick={() => abrirHistorial(orden)}
                    title="Ver Historial"
                    style={{ background: '#17a2b8', color: 'white', border: 'none', padding: '8px 12px', borderRadius: '4px', cursor: 'pointer', marginRight: '10px' }}
                  >
                    📜
                  </button>

                  {renderBotonAccion(orden)}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* ================= MODAL ASIGNAR ================= */}
      {modalAsignarOpen && ordenSeleccionada && (
        <div style={styles.overlay}>
          <div style={styles.modal}>
            <h3 style={{color:'#5D4037', marginBottom:'5px'}}>Asignar Responsable</h3>
            <p style={{marginBottom:'15px', color:'#666'}}>
                Orden <b>#{ordenSeleccionada.numeroOrden}</b> ({ordenSeleccionada.color})
            </p>
            
            <div style={{background:'#f5f5f5', padding:'10px', borderRadius:'5px', marginBottom:'15px'}}>
                <span style={{fontSize:'0.9rem'}}>Rol requerido:</span>
                <br/>
                <b style={{color: '#d84315', fontSize:'1.1rem'}}>{rolNecesarioModal}</b>
            </div>
            
            <label style={{display:'block', marginBottom: '5px', fontWeight:'bold'}}>Seleccionar Trabajador:</label>
            <select 
                value={empleadoSeleccionado}
                onChange={(e) => setEmpleadoSeleccionado(e.target.value)}
                style={{ width: '100%', padding: '10px', border: '1px solid #ccc', borderRadius: '4px', fontSize:'1rem' }}
            >
                <option value="">-- Seleccionar --</option>
                {empleados
                  // 🔥 FILTRO ACTUALIZADO: Compara el rol de la BD con el requerido
                  .filter(e => {
                      if (!e.rol) return false;
                      return e.rol.toUpperCase() === rolNecesarioModal.toUpperCase();
                  })
                  .map(e => (
                    <option key={e.id} value={e.id}>
                        {e.nombre} ({e.rol})
                    </option>
                  ))
                }
            </select>
            
            {empleados.filter(e => e.rol && e.rol.toUpperCase() === rolNecesarioModal.toUpperCase()).length === 0 && (
                <p style={{color:'red', fontSize:'0.8rem', marginTop:'5px'}}>
                    ⚠️ No hay empleados con el rol "{rolNecesarioModal}". Revisa la pestaña "Empleados".
                </p>
            )}

            <div style={{ textAlign: 'right', marginTop: '20px', display: 'flex', gap: '10px', justifyContent: 'flex-end' }}>
                <button onClick={() => setModalAsignarOpen(false)} style={styles.btnCancel}>Cancelar</button>
                <button onClick={guardarAsignacion} style={styles.btnConfirm}>Confirmar</button>
            </div>
          </div>
        </div>
      )}

      {/* ================= MODAL HISTORIAL ================= */}
      {modalHistorialOpen && ordenSeleccionada && (
        <div style={styles.overlay}>
            <div style={{...styles.modal, width: '500px'}}>
                <h3 style={{ borderBottom: '1px solid #ccc', paddingBottom: '10px', color: '#5D4037' }}>
                    Historial #{ordenSeleccionada.numeroOrden}
                </h3>
                
                <div style={{ marginTop: '20px', maxHeight: '400px', overflowY: 'auto' }}>
                    <TimelineItem titulo="Creación de Orden" fecha={ordenSeleccionada.fechaInicio} responsable="Administración" terminado={true} />
                    <TimelineItem titulo="Corte" fecha={ordenSeleccionada.fechaFinCorte} responsable={getNombreEmpleado(ordenSeleccionada.cortadorId)} terminado={!!ordenSeleccionada.fechaFinCorte} />
                    <TimelineItem titulo="Armado" fecha={ordenSeleccionada.fechaFinArmado} responsable={getNombreEmpleado(ordenSeleccionada.armadorId)} terminado={!!ordenSeleccionada.fechaFinArmado} />
                    <TimelineItem titulo="Costura" fecha={ordenSeleccionada.fechaFinCostura} responsable={getNombreEmpleado(ordenSeleccionada.costureroId)} terminado={!!ordenSeleccionada.fechaFinCostura} />
                    <TimelineItem titulo="Soladura" fecha={ordenSeleccionada.fechaFinSoladura} responsable={getNombreEmpleado(ordenSeleccionada.soladorId)} terminado={!!ordenSeleccionada.fechaFinSoladura} />
                <TimelineItem titulo="Emplantillado" fecha={ordenSeleccionada.fechaFinSoladura} responsable={getNombreEmpleado(ordenSeleccionada.soladorId)} terminado={!!ordenSeleccionada.fechaFinEmplantillado} />
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
            <div style={{ fontSize: '0.85rem', color: '#666', marginTop: '4px' }}>
                {terminado ? `Finalizado: ${new Date(fecha).toLocaleString()}` : 'Pendiente...'}
            </div>
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