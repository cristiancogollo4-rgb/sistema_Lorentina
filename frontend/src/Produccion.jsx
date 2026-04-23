import { useState, useEffect } from 'react';
import api from './api';

function Produccion() {
  const [referencia, setReferencia] = useState('');
  const [color, setColor] = useState('');
  const [categoria, setCategoria] = useState('');
  const [destino, setDestino] = useState('STOCK');
  const [materiales, setMateriales] = useState('');
  const [cortadorId, setCortadorId] = useState('');
  const [tallas, setTallas] = useState({ t35: 0, t36: 0, t37: 0, t38: 0, t39: 0, t40: 0, t41: 0, t42: 0 });
  
  // Precios especiales (si elige categoría especial)
  const [precioVentaEspecial, setPrecioVentaEspecial] = useState('');
  const [precioFabricaEspecial, setPrecioFabricaEspecial] = useState('');

  const [empleados, setEmpleados] = useState([]);
  const [tarifas, setTarifas] = useState([]);
  const [showTarifasModal, setShowTarifasModal] = useState(false);

  useEffect(() => {
    api.get('/usuarios').then(res => setEmpleados(res.data)).catch(err => console.error(err));
    api.get('/tarifas').then(res => setTarifas(res.data)).catch(err => console.error(err));
  }, []);

  const handleTallaChange = (talla, valor) => {
    setTallas({ ...tallas, [talla]: parseInt(valor) || 0 });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    const totalPares = Object.values(tallas).reduce((a, b) => a + b, 0);
    if (totalPares === 0) return alert("Debes ingresar al menos un par.");

    const payload = {
      referencia, color, categoria, destino, materiales, 
      cortadorId: cortadorId || null,
      ...tallas,
      precioManualCorte: categoria === 'ESPECIAL' ? precioVentaEspecial : null,
      precioManualArmado: categoria === 'ESPECIAL' ? precioFabricaEspecial : null,
      precioManualCostura: categoria === 'ESPECIAL' ? precioFabricaEspecial : null,
      precioManualSoladura: categoria === 'ESPECIAL' ? precioFabricaEspecial : null,
      precioManualEmplantillado: categoria === 'ESPECIAL' ? precioFabricaEspecial : null
    };

    try {
      await api.post('/produccion', payload);
      alert("✅ Orden de fabricación creada con éxito.");
      // Reset
      setReferencia(''); setColor(''); setTallas({ t35: 0, t36: 0, t37: 0, t38: 0, t39: 0, t40: 0, t41: 0, t42: 0 });
    } catch (error) {
      alert("Error: " + (error.response?.data?.error || "Error de conexión"));
    }
  };

  return (
    <div className="fab-container fade-in">
      
      <div className="fab-header">
        <div>
          <h2 style={{ color: '#582e2e', margin: 0 }}>⚒️ Centro de Fabricación</h2>
          <p style={{ color: '#64748b', margin: '5px 0 0 0' }}>Crea y gestiona nuevas órdenes de producción</p>
        </div>
        <button 
          onClick={() => setShowTarifasModal(true)}
          style={{ background: '#e2e8f0', border: 'none', padding: '10px 20px', borderRadius: '30px', fontWeight: '700', cursor: 'pointer', color: '#475569' }}
        >
          📜 Ver Tarifario
        </button>
      </div>

      <form onSubmit={handleSubmit}>
        <div className="fab-grid">
          
          {/* COLUMNA 1: MODELO */}
          <div className="fab-card">
            <div className="fab-section-title">
              <span>🏷️</span> Identidad del Modelo
            </div>
            
            <div className="lorentina-input-group">
              <label>REFERENCIA (MODELO)</label>
              <input 
                className="lorentina-input"
                placeholder="Ej: 1028"
                value={referencia}
                onChange={e => setReferencia(e.target.value)}
                required
              />
            </div>

            <div className="lorentina-input-group">
              <label>COLOR / ACABADO</label>
              <input 
                className="lorentina-input"
                placeholder="Ej: Negro Charol"
                value={color}
                onChange={e => setColor(e.target.value)}
                required
              />
            </div>

            <div className="lorentina-input-group">
              <label>CATEGORÍA DE TARIFA</label>
              <select 
                className="lorentina-input"
                value={categoria}
                onChange={e => setCategoria(e.target.value)}
                required
              >
                <option value="">-- Seleccionar --</option>
                {tarifas.map(t => (
                  <option key={t.id} value={t.nombre}>{t.nombre} (${t.precioCorte})</option>
                ))}
                <option value="ESPECIAL">💎 PEDIDO ESPECIAL (Manual)</option>
              </select>
            </div>

            {categoria === 'ESPECIAL' && (
              <div className="special-price-notice">
                <p style={{marginTop:0, fontWeight:'bold', color:'#856404'}}>💰 Precios Personalizados</p>
                <div style={{display:'flex', gap:'10px'}}>
                  <input type="number" placeholder="Venta $" className="lorentina-input" value={precioVentaEspecial} onChange={e=>setPrecioVentaEspecial(e.target.value)} required />
                  <input type="number" placeholder="Fábrica $" className="lorentina-input" value={precioFabricaEspecial} onChange={e=>setPrecioFabricaEspecial(e.target.value)} required />
                </div>
              </div>
            )}
          </div>

          {/* COLUMNA 2: LOGÍSTICA */}
          <div className="fab-card">
            <div className="fab-section-title">
              <span>🚛</span> Logística y Asignación
            </div>

            <div className="lorentina-input-group">
              <label>DESTINO FINAL</label>
              <select className="lorentina-input" value={destino} onChange={e => setDestino(e.target.value)}>
                <option value="STOCK">🏭 ALMACÉN (Stock Lorentina)</option>
                <option value="CLIENTE">👤 PEDIDO CLIENTE (Directo)</option>
              </select>
            </div>

            <div className="lorentina-input-group">
              <label>ASIGNAR A CORTADOR</label>
              <select className="lorentina-input" value={cortadorId} onChange={e => setCortadorId(e.target.value)}>
                <option value="">-- Sin asignar (Libre) --</option>
                {empleados.filter(emp => emp.rol === 'CORTE').map(emp => (
                  <option key={emp.id} value={emp.id}>{emp.nombre}</option>
                ))}
              </select>
            </div>

            <div className="lorentina-input-group">
              <label>NOTAS DE MATERIALES</label>
              <textarea 
                className="lorentina-input"
                style={{height: '105px', resize: 'none'}}
                placeholder="Ej: Cuero negro, forro badana, suela plana..."
                value={materiales}
                onChange={e => setMateriales(e.target.value)}
              />
            </div>
          </div>

          {/* COLUMNA 3: TALLAS */}
          <div className="fab-card" style={{ gridColumn: 'span 1' }}>
            <div className="fab-section-title">
              <span>📏</span> Curva de Producción
            </div>
            
            <p style={{fontSize:'0.8rem', color:'#64748b', marginBottom: '15px'}}>Ingresa la cantidad de pares por cada talla:</p>
            
            <div className="talla-visual-grid">
              {[35, 36, 37, 38, 39, 40, 41, 42].map(num => (
                <div key={num} className="talla-input-wrapper">
                  <span>T{num}</span>
                  <input 
                    type="number"
                    min="0"
                    className="input-talla-premium"
                    value={tallas[`t${num}`]}
                    onChange={e => handleTallaChange(`t${num}`, e.target.value)}
                  />
                </div>
              ))}
            </div>

            <div style={{ marginTop: '25px', padding: '15px', background: '#F1F5F9', borderRadius: '12px', textAlign: 'center' }}>
              <span style={{ display: 'block', fontSize: '0.8rem', color: '#64748b', fontWeight: '800' }}>TOTAL DE LA ORDEN</span>
              <span style={{ fontSize: '2rem', fontWeight: '900', color: '#582e2e' }}>
                {Object.values(tallas).reduce((a, b) => a + b, 0)} <small style={{fontSize:'1rem'}}>PARES</small>
              </span>
            </div>
          </div>
        </div>

        <button type="submit" className="btn-fab-submit">
          🚀 LANZAR ORDEN A PRODUCCIÓN
        </button>
      </form>

      {/* MODAL TARIFAS */}
      {showTarifasModal && (
        <div style={{ position: 'fixed', top: 0, left: 0, width: '100%', height: '100%', background: 'rgba(0,0,0,0.6)', display: 'flex', justifyContent: 'center', alignItems: 'center', zIndex: 1000 }}>
          <div style={{ background: 'white', padding: '30px', borderRadius: '20px', width: '90%', maxWidth: '500px', maxHeight: '85vh', overflowY: 'auto', boxShadow: '0 20px 50px rgba(0,0,0,0.3)' }}>

            <h3 style={{ marginTop: 0, color: '#582e2e', borderBottom: '2px solid #eee', paddingBottom: '10px' }}>📋 Tarifario de Mano de Obra</h3>
            <table className="modern-table">
              <thead>
                <tr>
                  <th>Categoría</th>
                  <th style={{ textAlign: 'right' }}>Venta</th>
                  <th style={{ textAlign: 'right' }}>Fábrica</th>
                </tr>
              </thead>
              <tbody>
                {tarifas.map(t => (
                  <tr key={t.id}>
                    <td style={{fontWeight:'bold'}}>{t.nombre}</td>
                    <td style={{ textAlign: 'right', color: '#22c55e', fontWeight: 'bold' }}>${t.precioCorte}</td>
                    <td style={{ textAlign: 'right', color: '#64748b' }}>${t.precioArmado}</td>
                  </tr>
                ))}
              </tbody>
            </table>
            <button 
              onClick={() => setShowTarifasModal(false)}
              style={{ width: '100%', marginTop: '20px', padding: '12px', background: '#582e2e', color: 'white', border: 'none', borderRadius: '10px', fontWeight: 'bold', cursor: 'pointer' }}
            >
              Cerrar
            </button>
          </div>
        </div>
      )}
    </div>
  );
}

export default Produccion;
