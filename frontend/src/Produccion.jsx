import { useState, useEffect } from 'react';
import api from './api';

function Produccion({ usuario }) {
  const [referencia, setReferencia] = useState('');
  const [color, setColor] = useState('');
  const [categoria, setCategoria] = useState('');
  const [destino, setDestino] = useState('STOCK');
  const [materiales, setMateriales] = useState('');
  const [cortadorId, setCortadorId] = useState('');
  const [tallas, setTallas] = useState({ t35: 0, t36: 0, t37: 0, t38: 0, t39: 0, t40: 0, t41: 0, t42: 0 });
  
  // Precios especiales
  const [esPedidoEspecial, setEsPedidoEspecial] = useState(false);
  const [precioManualCorte, setPrecioManualCorte] = useState('');
  const [precioManualArmado, setPrecioManualArmado] = useState('');
  const [precioManualCostura, setPrecioManualCostura] = useState('');
  const [precioManualSoladura, setPrecioManualSoladura] = useState('');
  const [precioManualEmplantillado, setPrecioManualEmplantillado] = useState('');

  const [empleados, setEmpleados] = useState([]);
  const [tarifas, setTarifas] = useState([]);
  const [showTarifasModal, setShowTarifasModal] = useState(false);

  useEffect(() => {
    api.get('/usuarios').then(res => setEmpleados(res.data)).catch(err => console.error(err));
    api.get('/tarifas').then(res => setTarifas(res.data)).catch(err => console.error(err));
  }, []);

  const handleTarifaChange = (id, campo, valor) => {
    setTarifas(tarifas.map(t => t.id === id ? { ...t, [campo]: parseInt(valor) || 0 } : t));
  };

  const handleActualizarTarifas = async () => {
    try {
      await api.post('/tarifas/actualizar', tarifas);
      alert("✅ Tarifario actualizado con éxito.");
      setShowTarifasModal(false);
    } catch (error) {
      alert("Error al actualizar tarifas: " + (error.response?.data?.error || "Error de conexión"));
    }
  };

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
      isEspecial: esPedidoEspecial,
      precioManualCorte: esPedidoEspecial ? precioManualCorte : null,
      precioManualArmado: esPedidoEspecial ? precioManualArmado : null,
      precioManualCostura: esPedidoEspecial ? precioManualCostura : null,
      precioManualSoladura: esPedidoEspecial ? precioManualSoladura : null,
      precioManualEmplantillado: esPedidoEspecial ? precioManualEmplantillado : null
    };

    try {
      await api.post('/produccion', payload);
      alert("✅ Orden de fabricación creada con éxito.");
      // Reset
      setReferencia(''); setColor(''); setTallas({ t35: 0, t36: 0, t37: 0, t38: 0, t39: 0, t40: 0, t41: 0, t42: 0 });
      setEsPedidoEspecial(false);
      setPrecioManualCorte(''); setPrecioManualArmado(''); setPrecioManualCostura(''); setPrecioManualSoladura(''); setPrecioManualEmplantillado('');
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
              <label>CATEGORÍA DE TARIFA (Sandalia/Tenis)</label>
              <select 
                className="lorentina-input"
                value={categoria}
                onChange={e => setCategoria(e.target.value)}
                required
              >
                <option value="">-- Seleccionar --</option>
                {tarifas.map(t => (
                  <option key={t.id} value={t.nombre}>{t.nombre}</option>
                ))}
              </select>
            </div>

            <div className="lorentina-input-group" style={{ display: 'flex', alignItems: 'center', gap: '10px', marginTop: '10px', padding: '10px', background: '#fffbeb', borderRadius: '8px', border: '1px solid #fef3c7' }}>
              <input 
                type="checkbox" 
                id="pedidoEspecial"
                checked={esPedidoEspecial}
                onChange={e => setEsPedidoEspecial(e.target.checked)}
                style={{ width: '18px', height: '18px', cursor: 'pointer' }}
              />
              <label htmlFor="pedidoEspecial" style={{ margin: 0, cursor: 'pointer', color: '#856404', fontWeight: 'bold' }}>💎 ES UN PEDIDO ESPECIAL (Precios Manuales)</label>
            </div>

            {categoria && !esPedidoEspecial && (
              <div style={{ background: '#f8fafc', padding: '12px', borderRadius: '10px', border: '1px solid #e2e8f0', marginBottom: '15px' }}>
                <p style={{ margin: '0 0 8px 0', fontSize: '0.75rem', fontWeight: '800', color: '#64748b', textTransform: 'uppercase' }}>💰 Precios Aplicados (Inteligente)</p>
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '8px', fontSize: '0.8rem' }}>
                  <div title="Corte">✂️ ${tarifas.find(t => t.nombre === categoria)?.precioCorte}</div>
                  <div title="Armado">🔨 ${tarifas.find(t => t.nombre === categoria)?.precioArmado}</div>
                  <div title="Costura">🧵 ${tarifas.find(t => t.nombre === categoria)?.precioCostura}</div>
                  <div title="Soladura">👟 ${tarifas.find(t => t.nombre === categoria)?.precioSoladura}</div>
                  <div title="Emplantillado">👣 ${tarifas.find(t => t.nombre === categoria)?.precioEmplantillado}</div>
                </div>
              </div>
            )}

            {esPedidoEspecial && (
              <div className="special-price-notice">
                <p style={{marginTop:0, fontWeight:'bold', color:'#856404'}}>💰 Ingresa los precios a pagar:</p>
                <div style={{display:'grid', gridTemplateColumns: '1fr 1fr', gap:'10px'}}>
                  <input type="number" placeholder="Corte $" className="lorentina-input" value={precioManualCorte} onChange={e=>setPrecioManualCorte(e.target.value)} required />
                  <input type="number" placeholder="Armado $" className="lorentina-input" value={precioManualArmado} onChange={e=>setPrecioManualArmado(e.target.value)} required />
                  <input type="number" placeholder="Costura $" className="lorentina-input" value={precioManualCostura} onChange={e=>setPrecioManualCostura(e.target.value)} required />
                  <input type="number" placeholder="Soladura $" className="lorentina-input" value={precioManualSoladura} onChange={e=>setPrecioManualSoladura(e.target.value)} required />
                  <input type="number" placeholder="Emplanti. $" className="lorentina-input" value={precioManualEmplantillado} onChange={e=>setPrecioManualEmplantillado(e.target.value)} required />
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

      {showTarifasModal && (
        <div style={{ position: 'fixed', top: 0, left: 0, width: '100%', height: '100%', background: 'rgba(0,0,0,0.6)', display: 'flex', justifyContent: 'center', alignItems: 'center', zIndex: 1000 }}>
          <div style={{ background: 'white', padding: '30px', borderRadius: '20px', width: '95%', maxWidth: '900px', maxHeight: '90vh', overflowY: 'auto', boxShadow: '0 20px 50px rgba(0,0,0,0.3)' }}>

            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px', borderBottom: '2px solid #eee', paddingBottom: '10px' }}>
              <h3 style={{ margin: 0, color: '#582e2e' }}>📋 Tarifario de Mano de Obra</h3>
              {usuario?.rol === 'ADMIN' && <p style={{ margin: 0, fontSize: '0.8rem', color: '#64748b' }}>* Puedes editar los valores directamente en la tabla</p>}
            </div>

            <div style={{ overflowX: 'auto' }}>
              <table className="modern-table">
                <thead>
                  <tr>
                    <th>Categoría</th>
                    <th style={{ textAlign: 'center' }}>Corte</th>
                    <th style={{ textAlign: 'center' }}>Armado</th>
                    <th style={{ textAlign: 'center' }}>Costura</th>
                    <th style={{ textAlign: 'center' }}>Soladura</th>
                    <th style={{ textAlign: 'center' }}>Emplanti.</th>
                  </tr>
                </thead>
                <tbody>
                  {tarifas.map(t => (
                    <tr key={t.id}>
                      <td style={{ fontWeight: 'bold' }}>{t.nombre}</td>
                      <td style={{ textAlign: 'center' }}>
                        <input 
                          type="number" 
                          className="lorentina-input" 
                          style={{ width: '85px', padding: '5px', textAlign: 'center' }}
                          value={t.precioCorte} 
                          onChange={(e) => handleTarifaChange(t.id, 'precioCorte', e.target.value)}
                          disabled={usuario?.rol !== 'ADMIN'}
                        />
                      </td>
                      <td style={{ textAlign: 'center' }}>
                        <input 
                          type="number" 
                          className="lorentina-input" 
                          style={{ width: '85px', padding: '5px', textAlign: 'center' }}
                          value={t.precioArmado} 
                          onChange={(e) => handleTarifaChange(t.id, 'precioArmado', e.target.value)}
                          disabled={usuario?.rol !== 'ADMIN'}
                        />
                      </td>
                      <td style={{ textAlign: 'center' }}>
                        <input 
                          type="number" 
                          className="lorentina-input" 
                          style={{ width: '85px', padding: '5px', textAlign: 'center' }}
                          value={t.precioCostura} 
                          onChange={(e) => handleTarifaChange(t.id, 'precioCostura', e.target.value)}
                          disabled={usuario?.rol !== 'ADMIN'}
                        />
                      </td>
                      <td style={{ textAlign: 'center' }}>
                        <input 
                          type="number" 
                          className="lorentina-input" 
                          style={{ width: '85px', padding: '5px', textAlign: 'center' }}
                          value={t.precioSoladura} 
                          onChange={(e) => handleTarifaChange(t.id, 'precioSoladura', e.target.value)}
                          disabled={usuario?.rol !== 'ADMIN'}
                        />
                      </td>
                      <td style={{ textAlign: 'center' }}>
                        <input 
                          type="number" 
                          className="lorentina-input" 
                          style={{ width: '85px', padding: '5px', textAlign: 'center' }}
                          value={t.precioEmplantillado} 
                          onChange={(e) => handleTarifaChange(t.id, 'precioEmplantillado', e.target.value)}
                          disabled={usuario?.rol !== 'ADMIN'}
                        />
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <div style={{ display: 'flex', gap: '15px', marginTop: '25px' }}>
              <button 
                onClick={() => setShowTarifasModal(false)}
                style={{ flex: 1, padding: '12px', background: '#e2e8f0', color: '#475569', border: 'none', borderRadius: '10px', fontWeight: 'bold', cursor: 'pointer' }}
              >
                Cerrar
              </button>
              {usuario?.rol === 'ADMIN' && (
                <button 
                  onClick={handleActualizarTarifas}
                  style={{ flex: 2, padding: '12px', background: '#582e2e', color: 'white', border: 'none', borderRadius: '10px', fontWeight: 'bold', cursor: 'pointer', boxShadow: '0 4px 15px rgba(88,46,46,0.2)' }}
                >
                  💾 Guardar Todos los Cambios
                </button>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default Produccion;
