import { useState, useEffect } from 'react';
import axios from 'axios';

function Produccion() {
  // --- ESTADOS ---
  const [preciosManuales, setPreciosManuales] = useState({
  corte: '',
  armado: '',
  costura: '',
  soladura: '',
  plantilla: ''
});
  const [cortadores, setCortadores] = useState([]);
  const [loading, setLoading] = useState(false);
  const [mensaje, setMensaje] = useState('');
  
  // Estados para Tarifas y Precios
  const [mostrarModalTarifas, setMostrarModalTarifas] = useState(false);
  const [listaTarifas, setListaTarifas] = useState([]);
  const [precioManual, setPrecioManual] = useState(''); // Para casos "ESPECIAL"

  // Estado del Formulario
  const [form, setForm] = useState({
    referencia: '',
    color: '',
    categoria: 'ROMANA', // Valor por defecto
    materiales: '',
    observacion: '',
    destino: 'STOCK',
    clienteNombre: '',
    cortadorId: '',
    // La Curva
    t34: '', t35: '', t36: '', t37: '', t38: '', 
    t39: '', t40: '', t41: '', t42: '', t43: '', t44: ''
  });

  // --- EFECTOS ---
  useEffect(() => {
    cargarCortadores();
  }, []);

  const cargarCortadores = async () => {
    try {
      const res = await axios.get('http://localhost:4000/api/empleados/corte');
      setCortadores(res.data);
    } catch (error) {
      console.error("Error cargando cortadores", error);
    }
  };

  // --- LÓGICA DE TARIFAS (NUEVO) ---
  const abrirConfiguracion = async () => {
    try {
      const res = await axios.get('http://localhost:4000/api/tarifas');
      setListaTarifas(res.data);
      setMostrarModalTarifas(true);
    } catch (error) {
      alert("Error cargando tarifas. Asegúrate que el servidor esté corriendo.");
    }
  };

  const guardarTarifas = async () => {
    try {
      await axios.post('http://localhost:4000/api/tarifas/actualizar', listaTarifas);
      alert("✅ ¡Precios actualizados para futuras órdenes!");
      setMostrarModalTarifas(false);
    } catch (error) {
      alert("Error guardando precios");
    }
  };

  // --- MANEJADORES DEL FORMULARIO ---
  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
  e.preventDefault();
  setLoading(true);
  // ...
  try {
    const datosAEnviar = {
        ...form,
        // Enviamos los 5 precios si es especial
        precioManualCorte: form.categoria === 'ESPECIAL' ? preciosManuales.corte : 0,
        precioManualArmado: form.categoria === 'ESPECIAL' ? preciosManuales.armado : 0,
        precioManualCostura: form.categoria === 'ESPECIAL' ? preciosManuales.costura : 0,
        precioManualSoladura: form.categoria === 'ESPECIAL' ? preciosManuales.soladura : 0,
        precioManualEmplantillado: form.categoria === 'ESPECIAL' ? preciosManuales.plantilla : 0,
    };

      // Enviamos al backend
      const res = await axios.post('http://localhost:4000/api/produccion', datosAEnviar);
      
      // Mensaje de éxito con el precio que se asignó
      setMensaje(`✅ Orden creada! Precio asignado: $${res.data.precioAplicado}`);
      
      // Limpiar formulario (Menos materiales por si repite)
      setForm({
        ...form, 
        referencia: '', color: '', observacion: '', categoria: 'ROMANA',
        t34:'', t35:'', t36:'', t37:'', t38:'', t39:'', t40:'', t41:'', t42:'', t43:'', t44:''
      });
      setPrecioManual(''); // Limpiar precio manual

    } catch (error) {
      console.error(error);
      setMensaje('❌ Error al crear la orden.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ padding: '20px', maxWidth: '800px', margin: '0 auto' }}>
      
      {/* --- ENCABEZADO --- */}
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
        <h2 style={{ color: '#582e2e', margin: 0 }}>🏭 Nueva Orden de Producción</h2>
        <button 
          type="button"
          onClick={abrirConfiguracion}
          style={{ background: '#455a64', color: 'white', padding: '10px 15px', border: 'none', borderRadius: '5px', cursor: 'pointer', display: 'flex', alignItems: 'center', gap: '5px' }}
        >
          ⚙️ Tarifas
        </button>
      </div>
      
      <div style={{ background: '#fff', padding: '20px', borderRadius: '10px', boxShadow: '0 4px 10px rgba(0,0,0,0.1)' }}>
        
        <form onSubmit={handleSubmit}>
         
          {/* 1. DATOS BÁSICOS (GRID) */}
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px', marginBottom: '20px' }}>
            
            {/* REFERENCIA */}
            <div>
              <label><strong>Referencia:</strong></label>
              <input 
                required type="text" name="referencia" 
                value={form.referencia} onChange={handleChange}
                placeholder="Ej: 1028" style={inputStyle} 
              />
            </div>

            {/* COLOR */}
            <div>
              <label><strong>Color:</strong></label>
              <input 
                required type="text" name="color" 
                value={form.color} onChange={handleChange}
                placeholder="Ej: Negro" style={inputStyle} 
              />
            </div>

            {/* CATEGORÍA Y PRECIO MANUAL */}
            <div style={{ gridColumn: 'span 2', display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px' }}>
                <div>
                    <label><strong>Categoría (Tarifa):</strong></label>
                    <select 
                        required name="categoria" 
                        value={form.categoria} onChange={handleChange}
                        style={{ ...inputStyle, border: '2px solid #e91e63', background: '#fff0f5' }}
                    >
                        <option value="ROMANA">Romana (Pago Estándar)</option>
                        <option value="CLASICA">Clásica (Pago Medio)</option>
                        <option value="ZARA">Zara / Plataforma (Pago Alto)</option>
                        <option value="ESPECIAL">✨ PEDIDO ESPECIAL (Manual)</option>
                    </select>
                </div>

                {/* BLOQUE DE PRECIOS MANUALES (Solo si es ESPECIAL) */}
                {form.categoria === 'ESPECIAL' ? (
                    <div style={{ gridColumn: 'span 2', background: '#e8f5e9', padding: '15px', borderRadius: '8px', border: '1px solid #c8e6c9' }}>
                        <label style={{color: '#2e7d32', display:'block', marginBottom:'10px'}}>
                            <strong>✨ Definir Precios Manuales (Por Par):</strong>
                        </label>
                        
                        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(120px, 1fr))', gap: '10px' }}>
                            <div>
                                <small>Corte</small>
                                <input type="number" placeholder="$" required
                                    value={preciosManuales.corte}
                                    onChange={e => setPreciosManuales({...preciosManuales, corte: e.target.value})}
                                    style={inputManualStyle} />
                            </div>
                            <div>
                                <small>Armado</small>
                                <input type="number" placeholder="$" required
                                    value={preciosManuales.armado}
                                    onChange={e => setPreciosManuales({...preciosManuales, armado: e.target.value})}
                                    style={inputManualStyle} />
                            </div>
                            <div>
                                <small>Costura</small>
                                <input type="number" placeholder="$" required
                                    value={preciosManuales.costura}
                                    onChange={e => setPreciosManuales({...preciosManuales, costura: e.target.value})}
                                    style={inputManualStyle} />
                            </div>
                            <div>
                                <small>Soladura</small>
                                <input type="number" placeholder="$" required
                                    value={preciosManuales.soladura}
                                    onChange={e => setPreciosManuales({...preciosManuales, soladura: e.target.value})}
                                    style={inputManualStyle} />
                            </div>
                            <div>
                                <small>Plantilla</small>
                                <input type="number" placeholder="$" required
                                    value={preciosManuales.plantilla}
                                    onChange={e => setPreciosManuales({...preciosManuales, plantilla: e.target.value})}
                                    style={inputManualStyle} />
                            </div>
                        </div>
                    </div>
                ) : (
                    /* Si NO es especial, mostramos el destino aquí para que no quede hueco */
                    <div>
                        <label><strong>Destino:</strong></label>
                        <select name="destino" value={form.destino} onChange={handleChange} style={inputStyle}>
                            <option value="STOCK">📦 Para Stock (Bodega)</option>
                            <option value="CLIENTE">👤 Pedido Especial Cliente</option>
                        </select>
                    </div>
                )}
            </div>
            
            {/* Si es ESPECIAL, mostramos DESTINO abajo para no perderlo */}
            {form.categoria === 'ESPECIAL' && (
                <div>
                    <label><strong>Destino:</strong></label>
                    <select 
                        name="destino" value={form.destino} onChange={handleChange} 
                        style={inputStyle}
                    >
                        <option value="STOCK">📦 Para Stock (Bodega)</option>
                        <option value="CLIENTE">👤 Pedido Especial Cliente</option>
                    </select>
                </div>
            )}

          </div>

          {/* 2. MATERIALES */}
          <div style={{ marginBottom: '20px' }}>
            <label><strong>Materiales / Especificaciones:</strong></label>
            <textarea 
              required name="materiales" 
              value={form.materiales} onChange={handleChange}
              placeholder="Ej: Cuero Negro, Forro Badana..."
              style={{ ...inputStyle, height: '80px', fontFamily: 'sans-serif' }} 
            />
          </div>

          {/* 3. ASIGNAR CORTADOR */}
          <div style={{ marginBottom: '20px', background: '#e3f2fd', padding: '15px', borderRadius: '8px' }}>
            <label style={{color: '#1565c0'}}><strong>✂️ Asignar a Cortador:</strong></label>
            <select 
              required name="cortadorId" 
              value={form.cortadorId} onChange={handleChange}
              style={{ ...inputStyle, border: '2px solid #1565c0' }}
            >
              <option value="">-- Seleccione Empleado --</option>
              {cortadores.map(c => (
                <option key={c.id} value={c.id}>{c.nombre} {c.apellido}</option>
              ))}
            </select>
          </div>

          {/* 4. LA CURVA */}
          <label><strong>Curva de Tallas:</strong></label>
          <div style={{ display: 'flex', gap: '5px', overflowX: 'auto', padding: '10px 0', marginBottom: '20px' }}>
            {[34,35,36,37,38,39,40,41,42,43,44].map(talla => (
              <div key={talla} style={{ textAlign: 'center' }}>
                <span style={{ fontSize: '0.8rem', color: '#666' }}>{talla}</span>
                <input 
                  type="number" name={`t${talla}`}
                  value={form[`t${talla}`]} onChange={handleChange}
                  style={{ width: '40px', padding: '5px', textAlign: 'center', border: '1px solid #ccc', borderRadius: '4px' }} 
                />
              </div>
            ))}
          </div>

          <button 
            type="submit" disabled={loading}
            style={{ 
              width: '100%', padding: '15px', 
              background: loading ? '#ccc' : '#2e7d32', color: 'white', 
              border: 'none', borderRadius: '8px', fontSize: '1.1rem', cursor: 'pointer', fontWeight: 'bold' 
            }}
          >
            {loading ? 'Enviando...' : '🚀 ENVIAR A FÁBRICA'}
          </button>

          {mensaje && <p style={{ marginTop: '15px', textAlign: 'center', fontWeight: 'bold', padding: '10px', background: '#f1f8e9', borderRadius:'5px' }}>{mensaje}</p>}

        </form>
      </div>

      {/* --- MODAL DE TARIFAS (FLOTANTE) --- */}
      {/* --- MODAL FLOTANTE DE TARIFAS (5 PROCESOS) --- */}
      {mostrarModalTarifas && (
        <div style={{
          position: 'fixed', top: 0, left: 0, right: 0, bottom: 0,
          background: 'rgba(0,0,0,0.6)', display: 'flex', justifyContent: 'center', alignItems: 'center', zIndex: 1000
        }}>
          <div style={{ 
              background: 'white', padding: '25px', borderRadius: '12px', 
              width: '90%', maxWidth: '900px', maxHeight: '90vh', overflowY: 'auto',
              boxShadow: '0 10px 25px rgba(0,0,0,0.2)'
          }}>
            <h3 style={{marginTop:0, borderBottom:'1px solid #eee', paddingBottom:'10px'}}>
                💰 Configurar Tabla de Pagos
            </h3>
            <p style={{fontSize:'0.9rem', color:'#666'}}>Define cuánto gana cada rol por modelo de sandalia.</p>
            
            <div style={{ overflowX: 'auto' }}>
                <table style={{ width: '100%', borderCollapse: 'collapse', marginTop: '10px' }}>
                    <thead>
                        <tr style={{ background: '#f5f5f5', textAlign: 'left' }}>
                            <th style={thStyle}>Modelo</th>
                            <th style={thStyle}>✂️ Corte</th>
                            <th style={thStyle}>🔨 Armado</th>
                            <th style={thStyle}>🧵 Costura</th>
                            <th style={thStyle}>🔥 Soladura</th>
                            <th style={thStyle}>👟 Plantilla</th>
                        </tr>
                    </thead>
                    <tbody>
                        {listaTarifas.map((tarifa, index) => (
                            <tr key={tarifa.id} style={{ borderBottom: '1px solid #eee' }}>
                                <td style={{ padding: '10px', fontWeight: 'bold' }}>{tarifa.nombre}</td>
                                
                                {/* INPUT CORTE */}
                                <td style={{ padding: '5px' }}>
                                    <input type="number" value={tarifa.precioCorte}
                                        onChange={(e) => {
                                            const copia = [...listaTarifas];
                                            copia[index].precioCorte = e.target.value;
                                            setListaTarifas(copia);
                                        }}
                                        style={inputTableStyle}
                                    />
                                </td>

                                {/* INPUT ARMADO */}
                                <td style={{ padding: '5px' }}>
                                    <input type="number" value={tarifa.precioArmado}
                                        onChange={(e) => {
                                            const copia = [...listaTarifas];
                                            copia[index].precioArmado = e.target.value;
                                            setListaTarifas(copia);
                                        }}
                                        style={inputTableStyle}
                                    />
                                </td>

                                {/* INPUT COSTURA */}
                                <td style={{ padding: '5px' }}>
                                    <input type="number" value={tarifa.precioCostura}
                                        onChange={(e) => {
                                            const copia = [...listaTarifas];
                                            copia[index].precioCostura = e.target.value;
                                            setListaTarifas(copia);
                                        }}
                                        style={inputTableStyle}
                                    />
                                </td>

                                {/* INPUT SOLADURA */}
                                <td style={{ padding: '5px' }}>
                                    <input type="number" value={tarifa.precioSoladura}
                                        onChange={(e) => {
                                            const copia = [...listaTarifas];
                                            copia[index].precioSoladura = e.target.value;
                                            setListaTarifas(copia);
                                        }}
                                        style={inputTableStyle}
                                    />
                                </td>

                                {/* INPUT EMPLANTILLADO */}
                                <td style={{ padding: '5px' }}>
                                    <input type="number" value={tarifa.precioEmplantillado}
                                        onChange={(e) => {
                                            const copia = [...listaTarifas];
                                            copia[index].precioEmplantillado = e.target.value;
                                            setListaTarifas(copia);
                                        }}
                                        style={inputTableStyle}
                                    />
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <div style={{ display: 'flex', gap: '15px', marginTop: '25px', justifyContent: 'flex-end' }}>
              <button 
                onClick={() => setMostrarModalTarifas(false)}
                style={{ padding: '12px 20px', background: '#e0e0e0', color: '#333', border: 'none', borderRadius: '6px', cursor:'pointer', fontWeight:'bold' }}
              >
                Cancelar
              </button>
              <button 
                onClick={guardarTarifas}
                style={{ padding: '12px 25px', background: '#2e7d32', color: 'white', border: 'none', borderRadius: '6px', cursor:'pointer', fontWeight:'bold', boxShadow: '0 4px 6px rgba(0,0,0,0.1)' }}
              >
                💾 Guardar Tarifas
              </button>
            </div>
          </div>
        </div>
      )}

    </div>
  );
}

const inputStyle = {
  width: '100%', padding: '10px', marginTop: '5px', 
  borderRadius: '5px', border: '1px solid #ccc'
};

const thStyle = {
    padding: '12px',
    fontSize: '0.85rem',
    color: '#555',
    borderBottom: '2px solid #ddd'
};

const inputTableStyle = {
    width: '100%',
    padding: '8px',
    borderRadius: '4px',
    border: '1px solid #ccc',
    textAlign: 'center'
};
const inputManualStyle = {
    width: '100%', padding: '8px', border: '1px solid #2e7d32', borderRadius: '4px', textAlign: 'center'
};

export default Produccion;