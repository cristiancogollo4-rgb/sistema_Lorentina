import { useState, useEffect } from 'react';
import axios from 'axios';

function Empleados() {
  const [usuarios, setUsuarios] = useState([]);
  const [loading, setLoading] = useState(false);
  const [filtro, setFiltro] = useState('');

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
  // Busca esta línea en Empleados.js y cámbiala:
const ROLES_FABRICA = ['CORTE', 'ARMADOR', 'COSTURERO', 'SOLADOR', 'EMPLANTILLADOR', 'BODEGUERO'];

  useEffect(() => {
    cargarUsuarios();
  }, []);

  const cargarUsuarios = async () => {
    setLoading(true);
    try {
        const res = await axios.get('http://localhost:4000/api/usuarios');
        console.log("Usuarios recibidos:", res.data); // Para depurar en consola
        setUsuarios(res.data);
    } catch (error) {
        console.error("Error cargando usuarios:", error);
    } finally {
        setLoading(false);
    }
  };

  // --- MANEJO DEL FORMULARIO ---
  const abrirModalCrear = () => {
    setModoEdicion(false);
    setForm({ nombre: '', apellido: '', username: '', password: '', cedula: '', telefono: '', roles: [], activo: true });
    setShowModal(true);
  };

  const abrirModalEditar = (usuario) => {
    setModoEdicion(true);
    setUsuarioId(usuario.id);
    
    // --- PROTECCIÓN: Si el rol es nulo, usamos array vacío ---
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
        activo: usuario.activo !== false // Si es null o undefined, asume true (activo)
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
    // ... (tus validaciones) ...

    const datosEnviar = { ...form, rol: form.roles.join(',') };

    try {
      if (modoEdicion) {
        // ... (lógica de editar igual) ...
        const res = await axios.put(`http://localhost:4000/api/usuarios/${usuarioId}`, datosEnviar);
        
        // ACTUALIZAR MANUALMENTE EN LA LISTA LOCAL (Sin recargar)
        const usuariosActualizados = usuarios.map(u => u.id === usuarioId ? res.data : u);
        setUsuarios(usuariosActualizados);
        alert("✅ Perfil actualizado");

      } else {
        const res = await axios.post('http://localhost:4000/api/usuarios', datosEnviar);
        
        // AGREGAR MANUALMENTE A LA LISTA LOCAL
        // res.data es el usuario nuevo que devolvió el backend
        setUsuarios([...usuarios, res.data]); 
        
        alert("✅ Empleado creado");
      }
      
      setShowModal(false);
      setFiltro(''); // Importante limpiar filtro aquí también
      
      // Ya no es estrictamente necesario llamar a cargarUsuarios(), 
      // pero puedes dejarlo como respaldo en segundo plano si quieres.
      // cargarUsuarios(); 

    } catch (error) {
      alert("❌ Error: " + (error.response?.data?.error || "Error de conexión"));
    }
  };

  const eliminarUsuario = async (id) => {
    if(!window.confirm("🚨 ¿Seguro que quieres eliminar este usuario?")) return;
    try {
        await axios.delete(`http://localhost:4000/api/usuarios/${id}`);
        cargarUsuarios();
    } catch (error) { alert("No se pudo eliminar"); }
  };

  // --- FILTRADO SEGURO ---
  const usuariosFiltrados = usuarios.filter(u => {
    const nombreCompleto = `${u.nombre || ''} ${u.apellido || ''}`.toLowerCase();
    const roles = (u.rol || '').toLowerCase();
    const textoFiltro = filtro.toLowerCase();
    return nombreCompleto.includes(textoFiltro) || roles.includes(textoFiltro);
  });

  return (
    <div className="fade-in" style={{ padding: '20px', maxWidth: '1200px', margin: '0 auto' }}>
      
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '30px' }}>
        <div>
            <h2 style={{ color: '#582e2e', margin: 0 }}>🏭 Gestión de Recursos Humanos</h2>
            <p style={{ color: '#666', margin: '5px 0 0 0' }}>Administra roles, perfiles y accesos.</p>
        </div>
        <button onClick={abrirModalCrear} style={styles.btnPrimary}>+ Nuevo Empleado</button>
      </div>

      <div style={{ marginBottom: '20px' }}>
        <input 
            type="text" 
            placeholder="🔍 Buscar por nombre o cargo..." 
            value={filtro}
            onChange={e => setFiltro(e.target.value)}
            style={{ padding: '10px', width: '100%', maxWidth: '400px', borderRadius: '20px', border: '1px solid #ccc' }}
        />
      </div>

      <div className="content-card">
        <table className="modern-table">
            <thead>
                <tr style={{background: '#f8f9fa', textTransform: 'uppercase', fontSize: '0.85rem', color: '#888'}}>
                    <th style={{padding: '15px'}}>Empleado</th>
                    <th>Roles / Puestos</th>
                    <th>Contacto</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                {usuariosFiltrados.map(u => {
                    // --- PROTECCIÓN VISUAL: Convertir rol seguro a array ---
                    const rolesVisibles = u.rol ? u.rol.split(',') : ['SIN ROL'];
                    
                    return (
                        <tr key={u.id} style={{borderBottom: '1px solid #eee'}}>
                            <td style={{padding: '15px'}}>
                                <div style={{fontWeight: 'bold', fontSize: '1rem'}}>{u.nombre} {u.apellido}</div>
                                <div style={{fontSize: '0.8rem', color: '#888'}}>@{u.username} • C.C. {u.cedula || 'N/A'}</div>
                            </td>
                            <td>
                                <div style={{display: 'flex', gap: '5px', flexWrap: 'wrap'}}>
                                    {rolesVisibles.map((r, i) => (
                                        <span key={i} style={obtenerEstiloRol(r)}>{r}</span>
                                    ))}
                                </div>
                            </td>
                            <td style={{fontSize: '0.9rem', color: '#555'}}>
                                📞 {u.telefono || '--'}
                            </td>
                            <td>
                                {u.activo !== false ? 
                                    <span style={{background:'#d4edda', color:'#155724', padding:'3px 8px', borderRadius:'10px', fontSize:'0.8rem'}}>Activo</span> : 
                                    <span style={{background:'#f8d7da', color:'#721c24', padding:'3px 8px', borderRadius:'10px', fontSize:'0.8rem'}}>Inactivo</span>
                                }
                            </td>
                            <td>
                                <button onClick={() => abrirModalEditar(u)} style={styles.btnIcon} title="Editar">✏️</button>
                                <button onClick={() => eliminarUsuario(u.id)} style={{...styles.btnIcon, color: 'red'}} title="Eliminar">🗑️</button>
                            </td>
                        </tr>
                    );
                })}
            </tbody>
        </table>
        {usuariosFiltrados.length === 0 && <p style={{textAlign: 'center', color: '#999', padding: '20px'}}>No se encontraron empleados.</p>}
      </div>

      {/* --- MODAL --- */}
      {showModal && (
        <div style={styles.modalOverlay}>
            <div style={styles.modalContent}>
                <h3 style={{marginTop: 0, color: '#582e2e'}}>{modoEdicion ? '✏️ Editar Perfil' : '➕ Nuevo Ingreso'}</h3>
                
                <form onSubmit={guardarUsuario} style={{display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '15px'}}>
                    
                    <div style={{gridColumn: 'span 2', background: '#f9f9f9', padding: '10px', borderRadius: '8px'}}>
                        <label style={styles.labelSection}>Datos de Identificación</label>
                        <div style={{display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '10px'}}>
                            <input placeholder="Nombre" value={form.nombre} onChange={e=>setForm({...form, nombre: e.target.value})} style={styles.input} required />
                            <input placeholder="Apellido" value={form.apellido} onChange={e=>setForm({...form, apellido: e.target.value})} style={styles.input} required />
                            <input placeholder="Cédula" value={form.cedula} onChange={e=>setForm({...form, cedula: e.target.value})} style={styles.input} />
                            <input placeholder="Teléfono" value={form.telefono} onChange={e=>setForm({...form, telefono: e.target.value})} style={styles.input} />
                        </div>
                    </div>

                    <div style={{gridColumn: 'span 2'}}>
                        <label style={styles.labelSection}>Credenciales</label>
                        <div style={{display: 'flex', gap: '10px'}}>
                            <input placeholder="Usuario" value={form.username} onChange={e=>setForm({...form, username: e.target.value})} style={{...styles.input, flex: 1}} required />
                            <input type="password" placeholder={modoEdicion ? "(Vacío para mantener)" : "Contraseña"} value={form.password} onChange={e=>setForm({...form, password: e.target.value})} style={{...styles.input, flex: 1}} />
                        </div>
                    </div>

                    <div style={{gridColumn: 'span 2'}}>
                        <label style={styles.labelSection}>Roles (Multi-selección)</label>
                        
                        <div style={{marginBottom: '10px'}}>
                            <small style={{fontWeight: 'bold', color: '#555'}}>Administrativos:</small>
                            <div style={{display: 'flex', gap: '10px', marginTop: '5px'}}>
                                {ROLES_ADMIN.map(rol => (
                                    <div key={rol} onClick={() => toggleRole(rol)} style={form.roles.includes(rol) ? styles.chipActive : styles.chip}>{rol}</div>
                                ))}
                            </div>
                        </div>

                        <div>
                            <small style={{fontWeight: 'bold', color: '#555'}}>Planta / Fábrica:</small>
                            <div style={{display: 'flex', flexWrap: 'wrap', gap: '8px', marginTop: '5px'}}>
                                {ROLES_FABRICA.map(rol => (
                                    <div key={rol} onClick={() => toggleRole(rol)} style={form.roles.includes(rol) ? styles.chipFactoryActive : styles.chip}>{rol}</div>
                                ))}
                            </div>
                        </div>
                    </div>

                    {modoEdicion && (
                        <div style={{gridColumn: 'span 2', display: 'flex', alignItems: 'center', gap: '10px', padding: '10px', background: '#fff3cd', borderRadius: '5px'}}>
                            <input type="checkbox" checked={form.activo} onChange={e => setForm({...form, activo: e.target.checked})} style={{width: '20px', height: '20px'}} />
                            <label style={{fontWeight: 'bold', color: '#856404'}}>Empleado Activo</label>
                        </div>
                    )}

                    <div style={{gridColumn: 'span 2', display: 'flex', gap: '10px', marginTop: '10px'}}>
                        <button type="button" onClick={() => setShowModal(false)} style={styles.btnSecondary}>Cancelar</button>
                        <button type="submit" style={styles.btnPrimary}>Guardar</button>
                    </div>

                </form>
            </div>
        </div>
      )}
    </div>
  );
}

const styles = {
  btnPrimary: { background: '#582e2e', color: 'white', border: 'none', padding: '10px 20px', borderRadius: '8px', cursor: 'pointer', fontWeight: 'bold' },
  btnSecondary: { background: '#ccc', color: '#333', border: 'none', padding: '10px 20px', borderRadius: '8px', cursor: 'pointer', fontWeight: 'bold', flex: 1 },
  btnIcon: { background: 'transparent', border: 'none', cursor: 'pointer', fontSize: '1.2rem', padding: '5px' },
  input: { padding: '10px', borderRadius: '6px', border: '1px solid #ccc', width: '100%', boxSizing: 'border-box', color: '#000' },
  labelSection: { display: 'block', marginBottom: '8px', fontSize: '0.9rem', color: '#582e2e', fontWeight: 'bold', textTransform: 'uppercase' },
  chip: { padding: '6px 12px', borderRadius: '20px', border: '1px solid #ccc', background: 'white', cursor: 'pointer', fontSize: '0.85rem', userSelect: 'none', color: '#333' },
  chipActive: { padding: '6px 12px', borderRadius: '20px', border: '1px solid #582e2e', background: '#582e2e', color: 'white', cursor: 'pointer', fontSize: '0.85rem', fontWeight: 'bold', boxShadow: '0 2px 5px rgba(88,46,46,0.3)' },
  chipFactoryActive: { padding: '6px 12px', borderRadius: '20px', border: '1px solid #e65100', background: '#ff9800', color: 'white', cursor: 'pointer', fontSize: '0.85rem', fontWeight: 'bold', boxShadow: '0 2px 5px rgba(230,81,0,0.3)' },
  modalOverlay: { position: 'fixed', top: 0, left: 0, width: '100%', height: '100%', background: 'rgba(0,0,0,0.6)', display: 'flex', justifyContent: 'center', alignItems: 'center', zIndex: 1000 },
  modalContent: { background: 'white', padding: '30px', borderRadius: '15px', width: '90%', maxWidth: '600px', maxHeight: '90vh', overflowY: 'auto', boxShadow: '0 10px 30px rgba(0,0,0,0.2)' }
};

const obtenerEstiloRol = (rol) => {
    const base = { padding: '2px 8px', borderRadius: '10px', fontSize: '0.75rem', fontWeight: 'bold', marginRight: '4px' };
    if (rol === 'ADMIN') return { ...base, background: '#333', color: '#fff' };
    if (rol === 'VENDEDOR') return { ...base, background: '#2196f3', color: '#fff' };
    if (rol === 'SIN ROL') return { ...base, background: '#eee', color: '#999' };
    return { ...base, background: '#fff3e0', color: '#ef6c00', border: '1px solid #ffe0b2' };
};

export default Empleados;