import { useState, useEffect } from 'react';
import api from './api';
import LoadingState from './components/LoadingState';

function Empleados() {
  const [usuarios, setUsuarios] = useState([]);
  const [loading, setLoading] = useState(false);
  const [filtro, setFiltro] = useState('');
  const [filtroRol, setFiltroRol] = useState('TODOS');
  const [filtroEstado, setFiltroEstado] = useState('TODOS');
  const [errorCarga, setErrorCarga] = useState('');

  // --- ESTADOS DEL MODAL Y EDICIÓN ---
  const [showModal, setShowModal] = useState(false);
  const [modoEdicion, setModoEdicion] = useState(false); 
  const [usuarioId, setUsuarioId] = useState(null);

  // --- FORMULARIO ---
  const [form, setForm] = useState({
    nombre: '', apellido: '', username: '', password: '', 
    cedula: '', telefono: '', 
    roles: [], 
    activo: true
  });

  const ROLES_ADMIN = ['ADMIN', 'VENDEDOR'];
  const ROLES_FABRICA = ['CORTE', 'ARMADOR', 'COSTURERO', 'SOLADOR', 'EMPLANTILLADOR', 'BODEGUERO'];

  useEffect(() => {
    cargarUsuarios();
  }, []);

  const cargarUsuarios = async () => {
    setLoading(true);
    setErrorCarga('');
    try {
        const res = await api.get('/usuarios');
        const listaUsuarios = Array.isArray(res.data)
          ? res.data
          : Object.values(res.data || {});
        setUsuarios(listaUsuarios);
    } catch (error) {
        console.error("Error cargando usuarios:", error);
        setUsuarios([]);
        setErrorCarga('No se pudieron cargar los empleados.');
    } finally {
        setLoading(false);
    }
  };

  const abrirModalCrear = () => {
    setModoEdicion(false);
    setForm({ nombre: '', apellido: '', username: '', password: '', cedula: '', telefono: '', roles: [], activo: true });
    setShowModal(true);
  };

  const abrirModalEditar = (usuario) => {
    setModoEdicion(true);
    setUsuarioId(usuario.id);
    const rolesString = usuario.rol || ''; 
    const rolesArray = rolesString.includes(',') ? rolesString.split(',') : (rolesString ? [rolesString] : []);
    
    setForm({
        nombre: usuario.nombre || '',
        apellido: usuario.apellido || '',
        username: usuario.username || '',
        password: '',
        cedula: usuario.cedula || '',
        telefono: usuario.telefono || '',
        roles: rolesArray,
        activo: usuario.activo !== false
    });
    setShowModal(true);
  };

  const toggleRole = (rol) => {
    if (form.roles.includes(rol)) {
        setForm({ ...form, roles: form.roles.filter(r => r !== rol) });
    } else {
        setForm({ ...form, roles: [...form.roles, rol] });
    }
  };

  const guardarUsuario = async (e) => {
    e.preventDefault();
    const datosEnviar = { ...form, rol: form.roles.join(',') };

    try {
      if (modoEdicion) {
        const res = await api.put(`/usuarios/${usuarioId}`, datosEnviar);
        const usuariosActualizados = usuarios.map(u => u.id === usuarioId ? res.data : u);
        setUsuarios(usuariosActualizados);
        alert("✅ Perfil actualizado");
      } else {
        const res = await api.post('/usuarios', datosEnviar);
        setUsuarios([...usuarios, res.data]); 
        alert("✅ Empleado creado");
      }
      setShowModal(false);
      setFiltro('');
    } catch (error) {
      alert("❌ Error: " + (error.response?.data?.error || "Error de conexión"));
    }
  };

  const eliminarUsuario = async (id) => {
    if(!window.confirm("🚨 ¿Seguro que quieres eliminar este usuario?")) return;
    try {
        await api.delete(`/usuarios/${id}`);
        cargarUsuarios();
    } catch (error) { alert("No se pudo eliminar"); }
  };

  const usuariosFiltrados = usuarios.filter(u => {
    const nombreCompleto = `${u.nombre || ''} ${u.apellido || ''}`.toLowerCase();
    const roles = (u.rol || '').toUpperCase();
    const textoFiltro = filtro.toLowerCase();
    
    // Filtro por texto
    const cumpleTexto = nombreCompleto.includes(textoFiltro) || roles.toLowerCase().includes(textoFiltro);
    
    // Filtro por rol
    let cumpleRol = true;
    if (filtroRol === 'ADMIN') cumpleRol = roles.includes('ADMIN');
    else if (filtroRol === 'VENDEDOR') cumpleRol = roles.includes('VENDEDOR');
    else if (filtroRol === 'FABRICA') cumpleRol = ['CORTE', 'ARMADOR', 'COSTURA', 'COSTURERO', 'SOLADURA', 'SOLADOR', 'EMPLANTILLADOR', 'BODEGUERO'].some(r => roles.includes(r));
    
    // Filtro por estado
    let cumpleEstado = true;
    if (filtroEstado === 'ACTIVO') cumpleEstado = u.activo !== false;
    else if (filtroEstado === 'INACTIVO') cumpleEstado = u.activo === false;

    return cumpleTexto && cumpleRol && cumpleEstado;
  });

  return (
    <div className="fade-in" style={{ padding: '30px', maxWidth: '1200px', margin: '0 auto' }}>
      
      <div className="employee-header">
        <div className="employee-title-group">
            <h2>🏭 Gestión de Recursos Humanos</h2>
            <p>Administra roles, perfiles y accesos del personal de Lorentina.</p>
        </div>
        <button onClick={abrirModalCrear} className="btn-gold">
          + Nuevo Empleado
        </button>
      </div>

      <div style={{ marginBottom: '25px', display: 'flex', gap: '15px', flexWrap: 'wrap', alignItems: 'center' }}>
        <div className="search-container" style={{ width: '400px', margin: 0 }}>
          <span className="search-icon-inside">🔍</span>
          <input 
              type="text" 
              placeholder="Buscar por nombre, cargo o C.C..." 
              value={filtro}
              onChange={e => setFiltro(e.target.value)}
              className="search-input-premium"
          />
          {filtro && (
            <button onClick={() => setFiltro('')} className="clear-search-btn">
              &times;
            </button>
          )}
        </div>

        <select 
            value={filtroRol} 
            onChange={e => setFiltroRol(e.target.value)}
            className="search-input-premium"
            style={{ width: '200px', paddingLeft: '15px', fontWeight: 'bold', color: '#475569', margin: 0 }}
        >
            <option value="TODOS">🧑‍💼 Todos los Roles</option>
            <option value="ADMIN">👑 Administradores</option>
            <option value="VENDEDOR">💼 Vendedores</option>
            <option value="FABRICA">🏭 Solo Fábrica</option>
        </select>

        <select 
            value={filtroEstado} 
            onChange={e => setFiltroEstado(e.target.value)}
            className="search-input-premium"
            style={{ width: '160px', paddingLeft: '15px', fontWeight: 'bold', color: '#475569', margin: 0 }}
        >
            <option value="TODOS">🟢 Todos (Act/Inact)</option>
            <option value="ACTIVO">✅ Solo Activos</option>
            <option value="INACTIVO">❌ Solo Inactivos</option>
        </select>
      </div>

      <div className="employee-table-container">
        {loading && <LoadingState mensaje="Cargando empleados..." />}
        {errorCarga && <p style={{textAlign: 'center', color: '#b00020', padding: '20px'}}>{errorCarga}</p>}
        
        <table className="modern-table">
            <thead>
                <tr style={{background: '#f8fafc'}}>
                    <th>Empleado</th>
                    <th>Roles / Puestos</th>
                    <th>Contacto</th>
                    <th>Estado</th>
                    <th style={{textAlign: 'right', paddingRight: '25px'}}>Acciones</th>
                </tr>
            </thead>
            <tbody>
                {usuariosFiltrados.map(u => {
                    const rolesVisibles = u.rol ? u.rol.split(',') : ['SIN ROL'];
                    const iniciales = `${(u.nombre || 'U').charAt(0)}${(u.apellido || '').charAt(0)}`;
                    
                    return (
                        <tr key={u.id}>
                            <td>
                                <div className="employee-name-cell">
                                    <div className="employee-avatar">
                                      {iniciales}
                                    </div>
                                    <div>
                                      <div className="name">{u.nombre} {u.apellido}</div>
                                      <div className="sub">@{u.username} • C.C. {u.cedula || 'N/A'}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style={{display: 'flex', gap: '5px', flexWrap: 'wrap'}}>
                                    {rolesVisibles.map((r, i) => (
                                        <span key={i} style={obtenerEstiloRol(r)}>{r}</span>
                                    ))}
                                </div>
                            </td>
                            <td style={{fontSize: '0.9rem', color: '#64748b', fontWeight: '500'}}>
                                📞 {u.telefono || 'No registrado'}
                            </td>
                            <td>
                                {u.activo !== false ? 
                                    <span className="status-badge active">Activo</span> : 
                                    <span className="status-badge inactive">Inactivo</span>
                                }
                            </td>
                            <td style={{textAlign: 'right', paddingRight: '20px'}}>
                                <button onClick={() => abrirModalEditar(u)} className="action-btn" title="Editar">✏️</button>
                                <button onClick={() => eliminarUsuario(u.id)} className="action-btn delete" title="Eliminar">🗑️</button>
                            </td>
                        </tr>
                    );
                })}
            </tbody>
        </table>
        {!loading && usuariosFiltrados.length === 0 && (
          <div style={{textAlign: 'center', padding: '40px', color: '#94a3b8'}}>
            <div style={{fontSize: '3rem', marginBottom: '10px'}}>🔍</div>
            <p style={{fontSize: '1.1rem', fontWeight: '600'}}>No se encontraron empleados</p>
          </div>
        )}
      </div>

      {showModal && (
        <div className="premium-modal-overlay">
            <div className="premium-modal-content">
                <div className="modal-header">
                  <h3>{modoEdicion ? '✏️ Editar Perfil' : '➕ Nuevo Empleado'}</h3>
                  <button onClick={() => setShowModal(false)} style={{background: 'none', border: 'none', fontSize: '1.5rem', cursor: 'pointer', color: '#94a3b8'}}>&times;</button>
                </div>
                
                <form onSubmit={guardarUsuario}>
                    <div className="modal-body">
                      <div style={{display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px'}}>
                          
                          <div style={{gridColumn: 'span 2', background: '#f8fafc', padding: '20px', borderRadius: '12px', border: '1px solid #e2e8f0'}}>
                              <label style={styles.labelSection}>Datos Personales</label>
                              <div style={{display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '15px'}}>
                                  <div className="lorentina-input-group" style={{margin: 0}}>
                                    <label>Nombres</label>
                                    <input placeholder="Ej. Juan" value={form.nombre} onChange={e=>setForm({...form, nombre: e.target.value})} className="lorentina-input" required />
                                  </div>
                                  <div className="lorentina-input-group" style={{margin: 0}}>
                                    <label>Apellidos</label>
                                    <input placeholder="Ej. Pérez" value={form.apellido} onChange={e=>setForm({...form, apellido: e.target.value})} className="lorentina-input" required />
                                  </div>
                                  <div className="lorentina-input-group" style={{margin: 0}}>
                                    <label>Documento de Identidad (C.C)</label>
                                    <input placeholder="100..." value={form.cedula} onChange={e=>setForm({...form, cedula: e.target.value})} className="lorentina-input" />
                                  </div>
                                  <div className="lorentina-input-group" style={{margin: 0}}>
                                    <label>Teléfono Celular</label>
                                    <input placeholder="300..." value={form.telefono} onChange={e=>setForm({...form, telefono: e.target.value})} className="lorentina-input" />
                                  </div>
                              </div>
                          </div>

                          <div style={{gridColumn: 'span 2', padding: '20px', borderRadius: '12px', border: '1px solid #e2e8f0'}}>
                              <label style={styles.labelSection}>Datos de Acceso al Sistema</label>
                              <div style={{display: 'flex', gap: '15px'}}>
                                  <div className="lorentina-input-group" style={{margin: 0, flex: 1}}>
                                    <label>Nombre de Usuario</label>
                                    <input placeholder="jperez" value={form.username} onChange={e=>setForm({...form, username: e.target.value})} className="lorentina-input" required />
                                  </div>
                                  <div className="lorentina-input-group" style={{margin: 0, flex: 1}}>
                                    <label>Contraseña</label>
                                    <input type="password" placeholder={modoEdicion ? "Dejar vacío para no cambiar" : "Contraseña segura"} value={form.password} onChange={e=>setForm({...form, password: e.target.value})} className="lorentina-input" />
                                  </div>
                              </div>
                          </div>

                          <div style={{gridColumn: 'span 2', padding: '20px', borderRadius: '12px', border: '1px solid #e2e8f0'}}>
                              <label style={styles.labelSection}>Asignación de Roles y Permisos</label>
                              <div style={{marginBottom: '15px'}}>
                                  <small style={{fontWeight: '700', color: '#64748b'}}>Área Administrativa y Ventas:</small>
                                  <div style={{display: 'flex', gap: '10px', marginTop: '8px'}}>
                                      {ROLES_ADMIN.map(rol => (
                                          <div key={rol} onClick={() => toggleRole(rol)} style={form.roles.includes(rol) ? styles.chipActive : styles.chip}>{rol}</div>
                                      ))}
                                  </div>
                              </div>
                              <div>
                                  <small style={{fontWeight: '700', color: '#64748b'}}>Área de Producción / Fábrica:</small>
                                  <div style={{display: 'flex', flexWrap: 'wrap', gap: '8px', marginTop: '8px'}}>
                                      {ROLES_FABRICA.map(rol => (
                                          <div key={rol} onClick={() => toggleRole(rol)} style={form.roles.includes(rol) ? styles.chipFactoryActive : styles.chip}>{rol}</div>
                                      ))}
                                  </div>
                              </div>
                          </div>

                          {modoEdicion && (
                              <div style={{gridColumn: 'span 2', display: 'flex', alignItems: 'center', gap: '12px', padding: '15px 20px', background: '#fef2f2', borderRadius: '10px', border: '1px solid #fecaca'}}>
                                  <input type="checkbox" checked={form.activo} onChange={e => setForm({...form, activo: e.target.checked})} style={{width: '20px', height: '20px', accentColor: '#dc2626'}} />
                                  <div>
                                    <label style={{fontWeight: '800', color: '#991b1b', display: 'block'}}>Cuenta Activa</label>
                                    <span style={{fontSize: '0.8rem', color: '#b91c1c'}}>Si desmarcas esta opción, el empleado no podrá ingresar al sistema.</span>
                                  </div>
                              </div>
                          )}

                      </div>
                    </div>
                    <div className="modal-footer">
                        <button type="button" onClick={() => setShowModal(false)} className="btn-cancel">Cancelar</button>
                        <button type="submit" className="btn-gold">Guardar Empleado</button>
                    </div>
                </form>
            </div>
        </div>
      )}
    </div>
  );
}

const styles = {
  labelSection: { display: 'block', marginBottom: '15px', fontSize: '0.95rem', color: 'var(--color-primario)', fontWeight: '800', textTransform: 'uppercase', letterSpacing: '0.5px' },
  chip: { padding: '8px 16px', borderRadius: '8px', border: '1px solid #cbd5e1', background: 'white', cursor: 'pointer', fontSize: '0.85rem', userSelect: 'none', color: '#475569', fontWeight: '600', transition: 'all 0.2s' },
  chipActive: { padding: '8px 16px', borderRadius: '8px', border: '1px solid #582e2e', background: '#582e2e', color: 'white', cursor: 'pointer', fontSize: '0.85rem', fontWeight: 'bold', boxShadow: '0 4px 10px rgba(88,46,46,0.2)' },
  chipFactoryActive: { padding: '8px 16px', borderRadius: '8px', border: '1px solid #d97706', background: '#f59e0b', color: 'white', cursor: 'pointer', fontSize: '0.85rem', fontWeight: 'bold', boxShadow: '0 4px 10px rgba(245,158,11,0.2)' },
};

const obtenerEstiloRol = (rol) => {
    const base = { padding: '4px 10px', borderRadius: '6px', fontSize: '0.75rem', fontWeight: '700', marginRight: '6px', marginBottom: '6px', display: 'inline-block' };
    if (rol === 'ADMIN') return { ...base, background: '#1e293b', color: '#f8fafc' };
    if (rol === 'VENDEDOR') return { ...base, background: '#eff6ff', color: '#2563eb', border: '1px solid #bfdbfe' };
    if (rol === 'SIN ROL') return { ...base, background: '#f1f5f9', color: '#94a3b8' };
    // Roles de fábrica (Naranja/Marrón)
    return { ...base, background: '#fff7ed', color: '#c2410c', border: '1px solid #ffedd5' };
};

export default Empleados;
