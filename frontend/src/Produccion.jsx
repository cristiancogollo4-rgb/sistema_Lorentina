import { useState, useEffect } from 'react';
import axios from 'axios';

function Produccion() {
  const [cortadores, setCortadores] = useState([]);
  const [loading, setLoading] = useState(false);
  const [mensaje, setMensaje] = useState('');

  // ESTADO DEL FORMULARIO
  const [form, setForm] = useState({
    referencia: '',
    color: '',
    materiales: '', // "Cuero Napa, Hilo rojo..."
    observacion: '',
    destino: 'STOCK', // o 'CLIENTE'
    clienteNombre: '', // Solo visual si es cliente
    cortadorId: '',    // A quién se le entrega
    // La Curva
    t34: '', t35: '', t36: '', t37: '', t38: '', 
    t39: '', t40: '', t41: '', t42: '', t43: '', t44: ''
  });

  // CARGAR EMPLEADOS DE CORTE AL INICIAR
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

  // MANEJAR CAMBIOS EN LOS INPUTS
  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  // ENVIAR ORDEN A FABRICAR
  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setMensaje('');

    try {
      // Enviamos los datos al backend
      await axios.post('http://localhost:4000/api/produccion', form);
      
      setMensaje('✅ ¡Orden enviada a Corte con éxito!');
      
      // Limpiar formulario (Dejamos materiales por si hace otra igual)
      setForm({
        ...form, 
        referencia: '',
        color: '', 
        observacion: '',
        t34:'', t35:'', t36:'', t37:'', t38:'', t39:'', t40:'', t41:'', t42:'', t43:'', t44:''
      });

    } catch (error) {
      console.error(error);
      setMensaje('❌ Error al crear la orden.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ padding: '20px', maxWidth: '800px' }}>
      <h2 style={{ color: '#582e2e' }}>🏭 Nueva Orden de Producción</h2>
      
      <div style={{ background: '#fff', padding: '20px', borderRadius: '10px', boxShadow: '0 4px 10px rgba(0,0,0,0.1)' }}>
        
        <form onSubmit={handleSubmit}>
          
          {/* 1. DATOS BÁSICOS */}
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px', marginBottom: '20px' }}>
            <div>
              <label><strong>Referencia:</strong></label>
              <input 
                required
                name="referencia" 
                value={form.referencia} 
                onChange={handleChange}
                placeholder="Ej: 1006"
                style={inputStyle} 
              />
            </div>
            {/* COLOR */}
            <div>
              <label><strong>Color:</strong></label>
              <input 
                required
                name="color" 
                value={form.color} 
                onChange={handleChange}
                placeholder="Ej: Negro, Miel..."
                style={inputStyle} 
              />
            </div>

            <div>
              <label><strong>Destino:</strong></label>
              <select name="destino" value={form.destino} onChange={handleChange} style={inputStyle}>
                <option value="STOCK">📦 Para Stock (Bodega)</option>
                <option value="CLIENTE">👤 Pedido Especial Cliente</option>
              </select>
            </div>
          </div>

          {/* 2. MATERIALES (INPUT GRANDE) */}
          <div style={{ marginBottom: '20px' }}>
            <label><strong>Materiales / Especificaciones:</strong></label>
            <textarea 
              required
              name="materiales" 
              value={form.materiales} 
              onChange={handleChange}
              placeholder="Ej: Cuero Negro, Forro Badana, Suela Blanca..."
              style={{ ...inputStyle, height: '80px', fontFamily: 'sans-serif' }} 
            />
          </div>

          {/* 3. ASIGNAR CORTADOR */}
          <div style={{ marginBottom: '20px', background: '#e3f2fd', padding: '15px', borderRadius: '8px' }}>
            <label style={{color: '#1565c0'}}><strong>✂️ Asignar a Cortador:</strong></label>
            <select 
              required
              name="cortadorId" 
              value={form.cortadorId} 
              onChange={handleChange}
              style={{ ...inputStyle, border: '2px solid #1565c0' }}
            >
              <option value="">-- Seleccione Empleado --</option>
              {cortadores.map(c => (
                <option key={c.id} value={c.id}>
                  {c.nombre} {c.apellido}
                </option>
              ))}
            </select>
          </div>

          {/* 4. LA CURVA (TALLAS) */}
          <label><strong>Curva de Tallas (Cantidad de pares):</strong></label>
          <div style={{ display: 'flex', gap: '5px', overflowX: 'auto', padding: '10px 0', marginBottom: '20px' }}>
            {[34,35,36,37,38,39,40,41,42,43,44].map(talla => (
              <div key={talla} style={{ textAlign: 'center' }}>
                <span style={{ fontSize: '0.8rem', color: '#666' }}>{talla}</span>
                <input 
                  type="number" 
                  name={`t${talla}`}
                  value={form[`t${talla}`]}
                  onChange={handleChange}
                  style={{ width: '40px', padding: '5px', textAlign: 'center', border: '1px solid #ccc', borderRadius: '4px' }} 
                />
              </div>
            ))}
          </div>

          <button 
            type="submit" 
            disabled={loading}
            style={{ 
              width: '100%', padding: '15px', 
              background: loading ? '#ccc' : '#2e7d32', color: 'white', 
              border: 'none', borderRadius: '8px', fontSize: '1.1rem', cursor: 'pointer', fontWeight: 'bold' 
            }}
          >
            {loading ? 'Enviando...' : '🚀 ENVIAR A FÁBRICA'}
          </button>

          {mensaje && <p style={{ marginTop: '15px', textAlign: 'center', fontWeight: 'bold' }}>{mensaje}</p>}

        </form>
      </div>
    </div>
  );
}

const inputStyle = {
  width: '100%', padding: '10px', marginTop: '5px', 
  borderRadius: '5px', border: '1px solid #ccc'
};

export default Produccion;