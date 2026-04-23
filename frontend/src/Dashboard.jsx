import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Stock from './Stock';
import Empleados from './Empleados';
import Produccion from './Produccion';
import GestionProduccion from './GestionProduccion';

function Dashboard() {
  const navigate = useNavigate();
  const [usuario, setUsuario] = useState(null);
  const [activeTab, setActiveTab] = useState('dashboard');

  useEffect(() => {
    const usuarioGuardado = localStorage.getItem('usuarioLorentina');
    if (!usuarioGuardado) {
      navigate('/');
    } else {
      setUsuario(JSON.parse(usuarioGuardado));
    }
  }, [navigate]);

  const handleLogout = () => {
    localStorage.removeItem('usuarioLorentina');
    navigate('/');
  };

  if (!usuario) return null;

  return (
    <div className="dashboard-container">
      
      {/* --- SIDEBAR LATERAL --- */}
      <aside className="sidebar">
        <div className="sidebar-header">
          {/* Logo centrado y sin texto */}
          <img src="/LOGOLORENTINA.png" alt="Logo" className="sidebar-logo" />
        </div>

        <ul className="menu-list">
          <li 
            className={`menu-item ${activeTab === 'dashboard' ? 'active' : ''}`}
            onClick={() => setActiveTab('dashboard')}
          >
            <span>🏠</span> Dashboard
          </li>

          <li 
            className={`menu-item ${activeTab === 'empleados' ? 'active' : ''}`}
            onClick={() => setActiveTab('empleados')}
          >
            <span>👥</span> Empleados
          </li>

          <li 
            className={`menu-item ${activeTab === 'stock' ? 'active' : ''}`}
            onClick={() => setActiveTab('stock')}
          >
            <span>📦</span> Stock
          </li>

          <li 
            className={`menu-item ${activeTab === 'produccion' ? 'active' : ''}`}
            onClick={() => setActiveTab('produccion')}
          >
            <span>👞</span> Producción
          </li>

          <li 
            className={`menu-item ${activeTab === 'fabricar' ? 'active' : ''}`}
            onClick={() => setActiveTab('fabricar')}
          >
            <span>🔨</span> Fabricar
          </li>

          <li 
            className={`menu-item ${activeTab === 'ventas' ? 'active' : ''}`}
            onClick={() => setActiveTab('ventas')}
          >
            <span>💰</span> Ventas
          </li>
        </ul>

        <div style={{ marginTop: 'auto' }}>
          <li className="menu-item" onClick={handleLogout}>
            <span>🚪</span> Cerrar Sesión
          </li>
        </div>
      </aside>

      {/* --- CONTENIDO PRINCIPAL --- */}
      <main className="main-content">
        
        {/* HEADER SUPERIOR */}
        <header className="top-header">
          <div className="search-container">
            <span className="search-icon-inside">🔍</span>
            <input type="text" placeholder="Buscar en el sistema..." className="search-input-premium" />
          </div>

          <div className="user-profile">
            <div style={{textAlign: 'right'}}>
              <div style={{fontWeight: 'bold', color: '#333'}}>{usuario.nombre}</div>
              <div style={{fontSize: '0.8rem', color: '#888'}}>Administrador</div>
            </div>
            <div className="avatar-circle">
              {usuario.nombre.charAt(0)}
            </div>
          </div>
        </header>

        {/* AQUÍ MUESTRAS EL CONTENIDO SEGÚN LA PESTAÑA SELECCIONADA 
           (Por ahora dejé un mensaje simple para probar que los botones funcionan)
        */}
        <div className="content-area">
           {activeTab === 'dashboard' && <h2>Vista General del Dashboard</h2>}
           {activeTab === 'empleados' && <Empleados />}
           {activeTab === 'stock' && <Stock />}
           {activeTab === 'produccion' && < GestionProduccion/>}
           {activeTab === 'fabricar' && <Produccion />}
           {activeTab === 'ventas' && <h2>Registro de Ventas</h2>}
        </div>


      </main>
    </div>
  );
}

export default Dashboard;