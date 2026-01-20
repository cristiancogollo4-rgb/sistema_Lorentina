import { useState, useEffect } from 'react';
import axios from 'axios';

function Stock() {
  const [inventario, setInventario] = useState([]);
  const [inventarioFiltrado, setInventarioFiltrado] = useState([]);
  
  // Estados de interfaz
  const [sucursalVisual, setSucursalVisual] = useState('CABECERA');
  const [filtroTipo, setFiltroTipo] = useState('TODOS'); 
  const [busqueda, setBusqueda] = useState(''); // <--- EL ESTADO DEL BUSCADOR
  const [mostrarCarga, setMostrarCarga] = useState(false);

  // Estados de archivo
  const [archivo, setArchivo] = useState(null);
  const [mensaje, setMensaje] = useState('');
  const [cargando, setCargando] = useState(false);

  // 1. Cargar datos iniciales
  useEffect(() => {
    cargarInventario();
  }, [sucursalVisual]);

  // 2. Lógica de Filtrado (Mejorada)
  useEffect(() => {
    let resultado = inventario;

    // Filtro Tipo
    if (filtroTipo !== 'TODOS') {
        resultado = resultado.filter(item => {
            const tipoItem = item.tipo ? item.tipo.toUpperCase() : '';
            return tipoItem === filtroTipo;
        });
    }

    // Filtro Buscador
    if (busqueda) {
        const term = busqueda.toLowerCase().trim();
        resultado = resultado.filter(item => {
            const ref = item.referencia ? item.referencia.toLowerCase() : '';
            const col = item.color ? item.color.toLowerCase() : '';
            return ref.includes(term) || col.includes(term);
        });
    }
    setInventarioFiltrado(resultado);
  }, [inventario, filtroTipo, busqueda]);

  const cargarInventario = async () => {
    try {
      const res = await axios.get(`http://127.0.0.1:4000/api/stock/zapatos?sucursal=${sucursalVisual}`);
      setInventario(res.data);
    } catch (error) {
      console.error("Error cargando data:", error);
    }
  };

  const handleUpload = async () => {
    if (!archivo) { alert("⚠️ Selecciona un archivo."); return; }
    if (archivo.name.startsWith('~$')) { alert("⛔ Error: Archivo temporal detectado. Cierra el Excel."); return; }

    const formData = new FormData();
    formData.append('file', archivo); 

    setCargando(true);
    setMensaje("Iniciando carga...");

    try {
      const response = await axios.post('http://127.0.0.1:4000/api/stock/masivo', formData);
      setMensaje(`✅ ¡Éxito! ${response.data.detalles}`);
      alert("¡Inventario actualizado correctamente! 🎉");
      cargarInventario(); 
      setMostrarCarga(false); 
      setArchivo(null); 
    } catch (error) {
      if (error.response) setMensaje(`❌ Error Servidor: ${error.response.data.error}`);
      else setMensaje("❌ Error de Red. Revisa la terminal negra.");
    } finally {
      setCargando(false);
    }
  };

  const totalParesVisibles = inventarioFiltrado.reduce((acc, item) => acc + item.total, 0);

  return (
    <div style={{ padding: '20px', maxWidth: '1200px', margin: '0 auto' }}>
      
      {/* HEADER */}
      <div style={{display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px'}}>
        <div>
            <h2 style={{ color: '#582e2e', margin: 0 }}>📦 Inventario: {sucursalVisual}</h2>
            <p style={{color: '#888', margin: '5px 0 0 0'}}>Gestión de stock en tiempo real</p>
        </div>
        <button 
            onClick={() => setMostrarCarga(!mostrarCarga)}
            style={{
                background: mostrarCarga ? '#d32f2f' : '#582e2e',
                color: 'white', border: 'none', padding: '10px 20px', borderRadius: '30px',
                cursor: 'pointer', fontWeight: 'bold', boxShadow: '0 4px 10px rgba(0,0,0,0.2)',
                display: 'flex', alignItems: 'center', gap: '8px'
            }}
        >
            {mostrarCarga ? '✖ Cerrar Carga' : '☁️ Actualizar Stock'}
        </button>
      </div>

      {/* ZONA DE CARGA */}
      {mostrarCarga && (
        <div style={styles.uploadBox}>
            <h3 style={{marginTop:0, color: '#444'}}>Actualización Masiva</h3>
            <div style={{display: 'flex', gap: '10px', alignItems: 'center'}}>
                <input type="file" onChange={(e) => setArchivo(e.target.files[0])} style={styles.input} />
                <button onClick={handleUpload} disabled={cargando} style={styles.button}>
                    {cargando ? '⏳ Procesando...' : 'Subir Archivo'}
                </button>
            </div>
            {mensaje && <p style={{fontWeight: 'bold', marginTop: '10px', color: '#582e2e'}}>{mensaje}</p>}
        </div>
      )}

      {/* --- HERRAMIENTAS Y BUSCADOR --- */}
      <div style={styles.toolbar}>
        {/* Pestañas */}
        <div style={{display: 'flex', gap: '5px'}}>
            {['CABECERA', 'FABRICA', 'TOTAL'].map(suc => (
                <button 
                    key={suc}
                    onClick={() => setSucursalVisual(suc)}
                    style={sucursalVisual === suc ? styles.tabActive : styles.tab}
                >
                    {suc === 'TOTAL' ? '📊' : suc === 'FABRICA' ? '🏭' : '🏢'} {suc}
                </button>
            ))}
        </div>

        {/* BUSCADOR CORREGIDO */}
        <div style={{position: 'relative', flex: 1, maxWidth: '300px'}}>
            {/* Lupa con pointerEvents: none para que no bloquee el click */}
            <span style={{
                position:'absolute', left:'12px', top:'50%', transform: 'translateY(-50%)', 
                color: '#888', pointerEvents: 'none', fontSize: '1.1rem'
            }}>🔍</span>
            
            <input 
                type="text" 
                placeholder="Buscar referencia..." 
                value={busqueda}
                onChange={(e) => setBusqueda(e.target.value)}
                style={{
                    ...styles.input, 
                    paddingLeft: '40px', // Espacio para la lupa
                    paddingRight: '30px', 
                    width: '100%',
                    height: '40px', // Altura fija para evitar problemas
                    border: '1px solid #aaa',
                    color: '#000',          // Fuerza texto negro
                    backgroundColor: '#fff'
                }}
            />

            {busqueda && (
                <button 
                    onClick={() => setBusqueda('')}
                    style={{
                        position: 'absolute', right: '10px', top: '50%', transform: 'translateY(-50%)',
                        background: 'transparent', border: 'none', 
                        fontSize: '1.2rem', color: '#999', cursor: 'pointer'
                    }}
                >
                    &times;
                </button>
            )}
        </div>
      </div>

      {/* FILTROS Y TABLA */}
      <div style={{display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '15px', background: '#fff', padding: '15px', borderRadius: '12px', boxShadow: '0 2px 5px rgba(0,0,0,0.05)'}}>
         <div style={{display: 'flex', gap: '10px', alignItems: 'center'}}>
            <span style={{fontWeight: 'bold', color: '#555', fontSize: '0.9rem'}}>Filtrar por:</span>
            <button onClick={() => setFiltroTipo('TODOS')} style={filtroTipo === 'TODOS' ? styles.chipActive : styles.chip}>Todos</button>
            <button onClick={() => setFiltroTipo('PLANA')} style={filtroTipo === 'PLANA' ? styles.chipActive : styles.chip}>👡 Planas</button>
            <button onClick={() => setFiltroTipo('PLATAFORMA')} style={filtroTipo === 'PLATAFORMA' ? styles.chipActive : styles.chip}>👠 Plataformas</button>
         </div>
         <div style={{textAlign: 'right'}}>
            <span style={{display: 'block', fontSize: '0.8rem', color: '#888'}}>TOTAL PARES</span>
            <span style={{fontSize: '1.5rem', fontWeight: 'bold', color: '#582e2e'}}>{totalParesVisibles}</span>
         </div>
      </div>

      <div className="content-card" style={{ overflowX: 'auto' }}>
        <table className="modern-table" style={{width: '100%', minWidth: '800px'}}>
          <thead>
            <tr style={{background: '#f8f9fa'}}>
              <th style={{padding: '15px'}}>Referencia</th>
              <th>Color</th>
              <th>Tipo</th>
              {[35,36,37,38,39,40,41,42].map(t => <th key={t} className="th-talla">{t}</th>)}
              <th style={{textAlign: 'right', padding: '15px'}}>Total</th>
            </tr>
          </thead>
          <tbody>
            {inventarioFiltrado.map((item) => (
              <tr key={item.id} style={{borderBottom: '1px solid #f0f0f0'}}>
                <td style={{ fontWeight: 'bold', color: '#333', paddingLeft: '15px' }}>{item.referencia}</td>
                <td style={{fontSize: '0.9rem', color: '#666'}}>{item.color}</td>
                <td>
                    <span style={item.tipo === 'PLATAFORMA' ? styles.badgePlataforma : styles.badgePlana}>
                        {item.tipo}
                    </span>
                </td>
                {[item.t35, item.t36, item.t37, item.t38, item.t39, item.t40, item.t41, item.t42].map((cant, idx) => (
                    <td key={idx} style={{ 
                        textAlign: 'center', 
                        fontWeight: cant > 0 ? 'bold' : 'normal',
                        color: cant > 0 ? '#000' : '#ddd',
                        background: cant > 0 ? 'rgba(88, 46, 46, 0.05)' : 'transparent'
                    }}>{cant}</td>
                ))}
                <td style={{ textAlign: 'right', fontWeight: 'bold', color: '#d32f2f', paddingRight: '15px' }}>
                    {item.total}
                </td>
              </tr>
            ))}
             {inventarioFiltrado.length === 0 && (
              <tr><td colSpan="12" style={{ textAlign: 'center', padding: '40px', color: '#999' }}>No se encontraron zapatos.</td></tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}

const styles = {
  uploadBox: { background: '#fff', padding: '20px', borderRadius: '15px', border: '2px dashed #582e2e', marginBottom: '20px', animation: 'fadeIn 0.3s' },
  input: { padding: '10px', border: '1px solid #ccc', borderRadius: '8px', background: 'white', flex: 1 },
  button: { padding: '10px 20px', background: '#582e2e', color: 'white', border: 'none', borderRadius: '8px', cursor: 'pointer' },
  toolbar: { display: 'flex', gap: '15px', marginBottom: '20px', flexWrap: 'wrap', justifyContent: 'space-between', alignItems: 'center' },
  tab: { background: 'white', border: '1px solid #ddd', padding: '10px 20px', cursor: 'pointer', borderRadius: '8px', color: '#666' },
  tabActive: { background: '#582e2e', color: '#fff', border: 'none', padding: '10px 20px', borderRadius: '8px', cursor: 'pointer', fontWeight: 'bold' },
  chip: { background: '#eee', border: 'none', padding: '5px 15px', borderRadius: '20px', cursor: 'pointer', fontSize: '0.85rem', color: '#555' },
  chipActive: { background: '#e5d3c3', color: '#582e2e', border: '1px solid #582e2e', padding: '5px 15px', borderRadius: '20px', cursor: 'pointer', fontWeight: 'bold', fontSize: '0.85rem' },
  badgePlana: { fontSize: '0.7rem', background: '#e3f2fd', color: '#1565c0', padding: '3px 8px', borderRadius: '10px' },
  badgePlataforma: { fontSize: '0.7rem', background: '#fce4ec', color: '#c2185b', padding: '3px 8px', borderRadius: '10px' }
};

export default Stock;