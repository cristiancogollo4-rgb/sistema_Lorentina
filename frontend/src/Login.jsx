import { useState, useEffect } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';

function Login() {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const navigate = useNavigate();

  // --- CONFIGURACIÓN DEL CARRUSEL DE FONDO ---
  const backgroundImages = [
    '/fondo1.jpeg',
    '/fondo2.jpg',
    '/fondo3.jpg'
  ];

  const [currentImageIndex, setCurrentImageIndex] = useState(0);

  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentImageIndex((prev) => (prev + 1) % backgroundImages.length);
    }, 5000); 
    return () => clearInterval(interval);
  }, [backgroundImages.length]);

  // --- LÓGICA DE LOGIN ---
  const handleLogin = async (e) => {
    e.preventDefault();
    setError('');

    try {
      const respuesta = await axios.post('http://localhost:4000/api/login', {
        username,
        password
      });

      localStorage.setItem('usuarioLorentina', JSON.stringify(respuesta.data.usuario));
      navigate('/dashboard');

    } catch (err) {
      console.error(err);
      if (err.response) {
        setError(err.response.data.error);
      } else {
        setError('Error al conectar con el servidor');
      }
    }
  };

  return (
    <div className="login-wrapper">
      
      {/* --- FONDO (Carrusel) --- */}
      {/* QUITAMOS los estilos inline aquí para que el CSS funcione */}
      <div className="background-carousel">
        {backgroundImages.map((img, index) => (
          <div
            key={index}
            className={`bg-image ${index === currentImageIndex ? 'active' : ''}`}
            // Mantenemos solo lo dinámico (la URL de la imagen y la opacidad)
            style={{ 
                backgroundImage: `url(${img})`
            }}
          />
        ))}
        {/* El degradado ahora lo controla 100% el CSS */}
        <div className="bg-overlay"></div>
      </div>

      {/* --- CAJA DE LOGIN --- */}
      <div className="login-box">
        
        <div className="logo-container">
            {/* Corrección: En Vite las img de public van sin '/public' */}
            <img src="/public/logolorentina.png" alt="Lorentina Logo" className="logo-img" />
        </div>
        
        <h1>Bienvenido</h1>
        <p className="subtitle">Sistema de Gestión LORENTINA</p>

        <form onSubmit={handleLogin}>
          <div className="input-group">
            <label>Usuario</label>
            <input
              type="text"
              placeholder="Ej: admin"
              value={username}
              onChange={(e) => setUsername(e.target.value)}
              required
              autoFocus
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
      </div>
    </div>
  );
}

export default Login;