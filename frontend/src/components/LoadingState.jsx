function LoadingState({ mensaje = 'Cargando información...' }) {
  return (
    <div
      style={{
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        gap: '10px',
        padding: '16px',
        margin: '12px 0',
        borderRadius: '12px',
        background: '#fff7ed',
        color: '#9a3412',
        fontWeight: 600,
        border: '1px solid #fed7aa',
      }}
    >
      <span style={{ fontSize: '1.2rem' }}>⏳</span>
      <span>{mensaje}</span>
    </div>
  );
}

export default LoadingState;
