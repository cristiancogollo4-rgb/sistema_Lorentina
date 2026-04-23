import { useState, useEffect, useMemo } from 'react';
import api from './api';
import { useNavigate } from 'react-router-dom';

// CELDA DE ALTA FLUIDEZ (Double-Buffer Cross-Fade)
const CollageCell = ({ imagePool }) => {
  // Inicializamos con una imagen aleatoria inmediatamente
  const [urlA, setUrlA] = useState(() => 
    imagePool.length > 0 ? imagePool[Math.floor(Math.random() * imagePool.length)] : ''
  );
  const [urlB, setUrlB] = useState('');
  const [activeLayer, setActiveLayer] = useState('A'); 
  
  useEffect(() => {
    if (imagePool.length === 0) return;
    let timeoutId;
    
    const triggerSwap = () => {
      const nextPhoto = imagePool[Math.floor(Math.random() * imagePool.length)];
      
      const img = new Image();
      img.src = nextPhoto;
      img.onload = () => {
        setActiveLayer(prev => {
          const next = prev === 'A' ? 'B' : 'A';
          if (next === 'B') setUrlB(nextPhoto);
          else setUrlA(nextPhoto);
          return next;
        });
        
        const waitTime = Math.random() * 4000 + 4000;
        timeoutId = setTimeout(triggerSwap, waitTime);
      };
    };

    const initialWait = Math.random() * 4000 + 1000;
    timeoutId = setTimeout(triggerSwap, initialWait);

    return () => clearTimeout(timeoutId);
  }, [imagePool]);

  return (
    <div className={`collage-cell-container ${activeLayer === 'B' ? 'show-b' : 'show-a'}`}>
      <div className="collage-layer layer-a" style={{ backgroundImage: `url(${urlA})` }}></div>
      <div className="collage-layer layer-b" style={{ backgroundImage: `url(${urlB})` }}></div>
    </div>
  );
};

function Login() {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [legendIndex, setLegendIndex] = useState(0);

  const localImages = import.meta.glob('./assets/collage/*.{png,jpg,jpeg,webp,PNG,JPG,JPEG,WEBP}', { eager: true });
  const imagePool = Object.values(localImages).map(img => img.default);
  
  const navigate = useNavigate();

  const leyendas = [
    "Somos Lorentina, siempre Lorentina",
    "100% Cuero Genuino",
    "Comodidad en tus pies",
    "Artesanía que deja huella",
    "Elegancia en cada paso",
    "Estilo y Distinción"
  ];

  useEffect(() => {
    const interval = setInterval(() => {
      setLegendIndex((prev) => (prev + 1) % leyendas.length);
    }, 5500);
    return () => clearInterval(interval);
  }, []);

  const handleLogin = async (e) => {
    e.preventDefault();
    setError('');
    try {
      const respuesta = await api.post('/login', { username, password });
      localStorage.setItem('usuarioLorentina', JSON.stringify(respuesta.data.usuario));
      navigate('/dashboard');
    } catch (err) {
      setError(err.response?.data?.error || 'Error al conectar con el servidor');
    }
  };

  return (
    <div className="login-page">
      <div className="collage-background">
        {Array.from({ length: 20 }).map((_, index) => (
          <CollageCell key={index} imagePool={imagePool} />
        ))}
      </div>

      <div className="login-content-overlay">
        <div className="login-legend-side">
          <div className="legend-container">
            <h1 className="legend-text slide-up-text" key={legendIndex}>
              {leyendas[legendIndex]}
            </h1>
          </div>
        </div>

        <div className="login-form-side">
          <div className="login-box chocolate-card">
            <img src="/LOGOLORENTINA.png" alt="Logo" className="card-logo" />
            <h1>Bienvenid@</h1>
            <p className="subtitle">Somos Lorentina</p>

            <form onSubmit={handleLogin}>
              <div className="input-group">
                <label>Usuario</label>
                <input
                  type="text"
                  placeholder="Usuario"
                  value={username}
                  onChange={(e) => setUsername(e.target.value)}
                  required
                />
              </div>
              
              <div className="input-group">
                <label>Contraseña</label>
                <input
                  type="password"
                  placeholder="••••••••"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required
                />
              </div>
              {error && <div className="error-msg">{error}</div>}
              <button type="submit" className="btn-login">INGRESAR</button>
            </form>
            <p className="footer-text">© 2026 Lorentina • 100% Cuero</p>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Login;
