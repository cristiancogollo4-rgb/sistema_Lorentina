import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Stock from './Stock';
import Empleados from './Empleados';
import Produccion from './Produccion';
import GestionProduccion from './GestionProduccion';
import DashboardResumen from './DashboardResumen';
import Clientes from './Clientes';
import Ventas from './Ventas';

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
      <aside className="sidebar">
        <div className="sidebar-header">
          <img src="/LOGOLORENTINA.png" alt="Logo" className="sidebar-logo" />
        </div>

        <ul className="menu-list">
          <li
            className={`menu-item ${activeTab === 'dashboard' ? 'active' : ''}`}
            onClick={() => setActiveTab('dashboard')}
          >
            <span>Dashboard</span>
          </li>

          {usuario.rol === 'ADMIN' && (
            <>
              <li
                className={`menu-item ${activeTab === 'clientes' ? 'active' : ''}`}
                onClick={() => setActiveTab('clientes')}
              >
                <span>Clientes</span>
              </li>

              <li
                className={`menu-item ${activeTab === 'empleados' ? 'active' : ''}`}
                onClick={() => setActiveTab('empleados')}
              >
                <span>Empleados</span>
              </li>

              <li
                className={`menu-item ${activeTab === 'stock' ? 'active' : ''}`}
                onClick={() => setActiveTab('stock')}
              >
                <span>Stock</span>
              </li>

              <li
                className={`menu-item ${activeTab === 'produccion' ? 'active' : ''}`}
                onClick={() => setActiveTab('produccion')}
              >
                <span>Produccion</span>
              </li>

              <li
                className={`menu-item ${activeTab === 'fabricar' ? 'active' : ''}`}
                onClick={() => setActiveTab('fabricar')}
              >
                <span>Fabricar</span>
              </li>

              <li
                className={`menu-item ${activeTab === 'ventas' ? 'active' : ''}`}
                onClick={() => setActiveTab('ventas')}
              >
                <span>Ventas</span>
              </li>
            </>
          )}

          {usuario.rol === 'VENDEDOR' && (
            <>
              <li
                className={`menu-item ${activeTab === 'clientes' ? 'active' : ''}`}
                onClick={() => setActiveTab('clientes')}
              >
                <span>Clientes</span>
              </li>

              <li
                className={`menu-item ${activeTab === 'stock' ? 'active' : ''}`}
                onClick={() => setActiveTab('stock')}
              >
                <span>Stock</span>
              </li>

              <li
                className={`menu-item ${activeTab === 'apartados' ? 'active' : ''}`}
                onClick={() => setActiveTab('apartados')}
              >
                <span>Apartados</span>
              </li>

              <li
                className={`menu-item ${activeTab === 'ventas' ? 'active' : ''}`}
                onClick={() => setActiveTab('ventas')}
              >
                <span>Ventas</span>
              </li>
            </>
          )}
        </ul>

        <div style={{ marginTop: 'auto' }}>
          <li className="menu-item" onClick={handleLogout}>
            <span>Cerrar Sesion</span>
          </li>
        </div>
      </aside>

      <main className="main-content">
        <header className="top-header">
          <div className="search-container">
            <span className="search-icon-inside">Buscar</span>
            <input type="text" placeholder="Buscar en el sistema..." className="search-input-premium" />
          </div>

          <div style={{ display: 'flex', alignItems: 'center' }}>
            <a href="http://localhost:8000" target="_blank" rel="noopener noreferrer" className="btn-ecommerce">
              <svg className="ecommerce-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
              </svg>
              Ver Tienda
            </a>

            <div className="user-profile">
              <div style={{ textAlign: 'right' }}>
                <div style={{ fontWeight: 'bold', color: '#333' }}>{usuario.nombre}</div>
                <div style={{ fontSize: '0.8rem', color: '#888' }}>
                  {usuario.rol === 'ADMIN' ? 'Administrador' : 'Vendedor'}
                </div>
              </div>
              <div className="avatar-circle">
                {usuario.nombre.charAt(0)}
              </div>
            </div>
          </div>
        </header>

        <div className="content-area">
          {activeTab === 'dashboard' && <DashboardResumen usuario={usuario} />}
          {activeTab === 'empleados' && <Empleados />}
          {activeTab === 'stock' && <Stock soloLectura={usuario.rol === 'VENDEDOR'} />}
          {activeTab === 'produccion' && <GestionProduccion />}
          {activeTab === 'fabricar' && <Produccion usuario={usuario} />}
          {activeTab === 'clientes' && <Clientes usuario={usuario} />}
          {activeTab === 'apartados' && (
            <h2 style={{ padding: '2rem' }}>
              Gestion de Apartados <span style={{ fontSize: '1rem', color: '#888' }}>(proximamente)</span>
            </h2>
          )}
          {activeTab === 'ventas' && <Ventas usuario={usuario} />}
        </div>
      </main>
    </div>
  );
}

export default Dashboard;
