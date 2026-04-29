package com.cristiancogollo.applorentinapg.screens

import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.Check
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material.icons.outlined.ShoppingBag
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.window.Dialog
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController

import com.cristiancogollo.applorentinapg.model.OrdenProduccion
import com.cristiancogollo.applorentinapg.viewmodel.TasksUiState
import com.cristiancogollo.applorentinapg.viewmodel.TasksViewModel
import java.text.NumberFormat
import java.util.Locale



@Composable
fun MisTareasScreen(
    navController: NavController,
    userId: Int,
    userName: String,
    userRol: String, // Recibe "CORTE", "ARMADO", "COSTURA"...
    viewModel: TasksViewModel = viewModel()
) {
    // 1. TRADUCCIÓN DE ROL (Aseguramos consistencia)
    val rolParaBackend = remember(userRol) {
        when (userRol?.uppercase()) {
            "ARMADO", "ARMADOR" -> "ARMADOR"
            "COSTURA", "COSTURERO" -> "COSTURERO"
            "SOLADURA", "SOLADOR" -> "SOLADOR"
            "CORTE" -> "CORTE"
            "EMPLANTILLADO", "EMPLANTILLADOR" -> "EMPLANTILLADOR"
            else -> userRol?.uppercase() ?: ""
        }
    }

    val estadoResponsable = remember(rolParaBackend) {
        when (rolParaBackend) {
            "ARMADOR" -> "EN_ARMADO"
            "COSTURERO" -> "EN_COSTURA"
            "SOLADOR" -> "EN_SOLADURA"
            "EMPLANTILLADOR" -> "EN_EMPLANTILLADO"
            else -> "EN_CORTE"
        }
    }
    val (verboAccion, siguientePaso) = when (userRol.uppercase()) {
        "ARMADOR", "ARMADO" -> "armado" to "COSTURA"
        "COSTURERO", "COSTURA" -> "cosido" to "SOLADURA"
        "SOLADOR", "SOLADURA" -> "solado" to "EMPLANTILLADO"
        "EMPLANTILLADOR" -> "emplantillado" to "TERMINADO"
        else -> "cortado" to "ARMADO"
    }

    // 1. ESTADOS LOCALES
    var filtroSeleccionado by remember { mutableStateOf("PENDIENTES") } // "TODAS", "PENDIENTES", "LISTAS"
    var tareaSeleccionada by remember { mutableStateOf<OrdenProduccion?>(null) } // Controla el Modal
    var mostrarConfirmacion by remember { mutableStateOf(false) } // Controla la Alerta de seguridad

    // Cargar al inicio
    LaunchedEffect(Unit) { viewModel.cargarTareas(userId) }

    val uiState by viewModel.uiState.collectAsState()

    Scaffold(
        containerColor = LorentinaBg,
        topBar = { TasksTopBar(navController, onRefresh = { viewModel.cargarTareas(userId) }) },
        // Usamos la barra compartida que creamos antes
        bottomBar = { BottomNavigationBar(navController, userId, userName, userRol) }
    ) { paddingValues ->

        Box(modifier = Modifier.padding(paddingValues).fillMaxSize()) {

            when (val state = uiState) {
                is TasksUiState.Loading -> {
                    CircularProgressIndicator(modifier = Modifier.align(Alignment.Center), color = LorentinaBrown)
                }
                is TasksUiState.Error -> {
                    Text(state.mensaje, modifier = Modifier.align(Alignment.Center), color = Color.Red)
                }
                is TasksUiState.Success -> {

                    // LÓGICA DE FILTRADO DINÁMICA
                    val tareasFiltradas = when (filtroSeleccionado) {
                        // Aquí usamos la variable 'estadoResponsable' en vez de texto fijo
                        "PENDIENTES" -> state.tareas.filter { it.estado == estadoResponsable }
                        "LISTAS" -> state.tareas.filter { it.estado != estadoResponsable }
                        else -> state.tareas
                    }

                    Column(modifier = Modifier.fillMaxSize().padding(16.dp)) {

                        // 1. CHIPS DE FILTRO
                        Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                            FilterChipBtn("Pendientes", filtroSeleccionado == "PENDIENTES") { filtroSeleccionado = "PENDIENTES" }
                            FilterChipBtn("Listas", filtroSeleccionado == "LISTAS") { filtroSeleccionado = "LISTAS" }
                            FilterChipBtn("Todas", filtroSeleccionado == "TODAS") { filtroSeleccionado = "TODAS" }
                        }

                        Spacer(modifier = Modifier.height(16.dp))

                        Text(
                            text = "${tareasFiltradas.size} Órdenes encontradas",
                            style = MaterialTheme.typography.titleSmall,
                            color = Color.Gray
                        )
                        Spacer(modifier = Modifier.height(8.dp))

                        // 2. LISTA DE TAREAS
                        if (tareasFiltradas.isEmpty()) {
                            // Estado vacío visual
                            Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                                Text("No hay tareas en esta sección", color = Color.Gray)
                            }
                        } else {
                            LazyColumn(verticalArrangement = Arrangement.spacedBy(12.dp)) {
                                items(tareasFiltradas) { orden ->
                                    TaskItemCard(
                                        orden = orden,
                                        estadoUsuario = estadoResponsable, // Pasamos el estado del usuario actual
                                        onVerDetalle = { tareaSeleccionada = orden }
                                    )
                                }
                                item { Spacer(modifier = Modifier.height(60.dp)) }
                            }
                        }
                    }
                }
                else -> {}
            }
        }

        // --- 3. VENTANA MODAL DE DETALLES ---
        if (tareaSeleccionada != null) {
            DetalleOrdenDialog(
                orden = tareaSeleccionada!!,
                onDismiss = { tareaSeleccionada = null },
                onEnviarSiguiente = {
                    mostrarConfirmacion = true
                }
            )
        }

        // --- 4. ALERTA DE CONFIRMACIÓN (SEGURIDAD) ---
        if (mostrarConfirmacion && tareaSeleccionada != null) {
            val ordenSegura = tareaSeleccionada!! // Copia local para evitar errores de scope

            AlertDialog(
                containerColor = Color.White,
                onDismissRequest = { mostrarConfirmacion = false },
                title = { Text("¿Finalizar Tarea?") },
                text = {
                    Text("Al confirmar, certificas que has $verboAccion los ${ordenSegura.totalPares} pares. La orden pasará a $siguientePaso.")
                },
                confirmButton = {
                    Button(
                        onClick = {
                            // ✅ 1. PRIMERO: Enviar los datos al servidor
                            viewModel.confirmarTarea(ordenSegura.id, userId, rolParaBackend)

                            // ✅ 2. SEGUNDO: Cerrar solo los estados de los modales
                            // NO usar popBackStack aquí, eso causa el error de los logs
                            mostrarConfirmacion = false
                            tareaSeleccionada = null
                        },
                        modifier = Modifier.fillMaxWidth(),
                        colors = ButtonDefaults.buttonColors(containerColor = Color(0xFF4CAF50))
                    ) {
                        Icon(Icons.Default.Check, contentDescription = null)
                        Spacer(Modifier.width(8.dp))
                        Text("Confirmar Entrega")
                    }
                },
                dismissButton = {
                    TextButton(onClick = { mostrarConfirmacion = false }) {
                        Text("Cancelar", color = Color.Gray)
                    }
                }
            )
        }
    }
}

// --- COMPONENTES AUXILIARES ---

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun FilterChipBtn(label: String, selected: Boolean, onClick: () -> Unit) {
    FilterChip(
        selected = selected,
        onClick = onClick,
        label = { Text(label) },
        colors = FilterChipDefaults.filterChipColors(
            selectedContainerColor = LorentinaBrown,
            selectedLabelColor = Color.White
        )
    )
}

@Composable
fun TaskItemCard(orden: OrdenProduccion, estadoUsuario: String, onVerDetalle: () -> Unit) {
    Card(
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(2.dp),
        border = BorderStroke(1.dp, Color(0xFFEEEEEE)),
        modifier = Modifier.fillMaxWidth()
    ) {
        Column(modifier = Modifier.padding(16.dp)) {
            // Cabecera simplificada
            Row(horizontalArrangement = Arrangement.SpaceBetween, modifier = Modifier.fillMaxWidth()) {
                Text("ORDEN #${orden.numeroOrden}", fontWeight = FontWeight.Bold, color = Color.Gray)

                // Badge de estado dinámico
                val esPendiente = orden.estado == estadoUsuario
                val colorFondo = if (esPendiente) Color(0xFFFFF3E0) else Color(0xFFE8F5E9)
                val colorTexto = if (esPendiente) Color(0xFFEF6C00) else Color(0xFF2E7D32)

                Surface(color = colorFondo, shape = RoundedCornerShape(50)) {
                    Text(
                        text = if(esPendiente) "PENDIENTE" else "ENVIADO",
                        modifier = Modifier.padding(horizontal = 8.dp, vertical = 4.dp),
                        style = MaterialTheme.typography.labelSmall,
                        color = colorTexto
                    )
                }
            }

            Spacer(modifier = Modifier.height(8.dp))

            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(Icons.Outlined.ShoppingBag, null, tint = LorentinaBrown)
                Spacer(modifier = Modifier.width(12.dp))
                Column {
                    Text("${orden.referencia} - ${orden.color}", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.Bold)
                    Text("${orden.totalPares} Pares • ${orden.categoria ?: "Producción"}", style = MaterialTheme.typography.bodyMedium, color = Color.Gray)
                }
            }

            Spacer(modifier = Modifier.height(12.dp))

            Button(
                onClick = onVerDetalle,
                modifier = Modifier.fillMaxWidth(),
                colors = ButtonDefaults.buttonColors(containerColor = LorentinaBrown),
                shape = RoundedCornerShape(8.dp)
            ) {
                Text("Ver Detalles")
            }
        }
    }
}

// --- VENTANA MODAL CON DETALLES COMPLETOS ---
@Composable
fun DetalleOrdenDialog(
    orden: OrdenProduccion,
    onDismiss: () -> Unit,
    onEnviarSiguiente: () -> Unit
) {
    Dialog(onDismissRequest = onDismiss) {
        Card(
            shape = RoundedCornerShape(16.dp),
            modifier = Modifier.fillMaxWidth().heightIn(max = 650.dp),
            colors = CardDefaults.cardColors(containerColor = Color.White)
        ) {
            Column(
                modifier = Modifier
                    .padding(24.dp)
                    .verticalScroll(rememberScrollState())
            ) {
                // TÍTULO
                Text("Detalle de Producción", style = MaterialTheme.typography.titleLarge, fontWeight = FontWeight.Bold)
                Spacer(modifier = Modifier.height(16.dp))

                // INFORMACIÓN CLAVE
                DetalleItem("Orden", "#${orden.numeroOrden}")
                DetalleItem("Referencia", "${orden.referencia} - ${orden.color}")
                DetalleItem("Categoría", orden.categoria ?: "Producción")

                // DINERO (Lógica inteligente mantenida)
                val precioActual = when (orden.estado) {
                    "EN_CORTE" -> orden.precioCorte
                    "EN_ARMADO" -> orden.precioArmado
                    "EN_COSTURA" -> orden.precioCostura
                    "EN_SOLADURA" -> orden.precioSoladura
                    "EN_EMPLANTILLADO" -> orden.precioEmplantillado
                    else -> 0.0
                }

                val precioFinal = if (precioActual > 0) precioActual else orden.precioPactado

                val formatoDinero = NumberFormat.getCurrencyInstance(Locale("es", "CO"))
                formatoDinero.maximumFractionDigits = 0
                val totalPago = orden.totalPares * precioFinal

                Surface(
                    color = LorentinaGreen.copy(alpha = 0.1f),
                    shape = RoundedCornerShape(8.dp),
                    modifier = Modifier.fillMaxWidth().padding(vertical = 8.dp)
                ) {
                    Column(modifier = Modifier.padding(12.dp)) {
                        Text(
                            text = "Pago por esta tarea:",
                            style = MaterialTheme.typography.labelMedium,
                            color = LorentinaGreen
                        )
                        Text(
                            text = formatoDinero.format(totalPago),
                            style = MaterialTheme.typography.headlineSmall,
                            fontWeight = FontWeight.Bold,
                            color = LorentinaGreen
                        )
                        Text(
                            text = "(${orden.totalPares} pares x ${formatoDinero.format(precioFinal)})",
                            style = MaterialTheme.typography.bodySmall,
                            color = LorentinaGreen.copy(alpha = 0.8f)
                        )
                    }
                }

                Divider(modifier = Modifier.padding(vertical = 12.dp))

                // MATERIALES
                Text("Materiales:", fontWeight = FontWeight.Bold)
                Text(orden.materiales ?: "No especificado", style = MaterialTheme.typography.bodyMedium)

                Spacer(modifier = Modifier.height(12.dp))

                // CURVA DE TALLAS
                Text("Curva (${orden.totalPares} pares):", fontWeight = FontWeight.Bold)
                Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                    Text("34:${orden.t34}  35:${orden.t35}  36:${orden.t36} ...", color = Color.Gray)
                }

                Spacer(modifier = Modifier.height(24.dp))

                // Texto del botón dinámico
                val textoBoton = when (orden.estado) {
                    "EN_CORTE" -> "ENVIAR A ARMADO"
                    "EN_ARMADO" -> "ENVIAR A COSTURA" // Ajusta este flujo según tu realidad
                    "EN_COSTURA" -> "ENVIAR A SOLADURA"
                    "EN_SOLADURA" -> "ENVIAR A PLANTILLA"
                    "EN_EMPLANTILLADO" -> "FINALIZAR PEDIDO"
                    else -> "CONFIRMAR"
                }

                // BOTÓN DE ACCIÓN
                if (orden.estado != "TERMINADO") {
                    Button(
                        onClick = onEnviarSiguiente,
                        modifier = Modifier.fillMaxWidth().height(50.dp),
                        colors = ButtonDefaults.buttonColors(containerColor = LorentinaBrown)
                    ) {
                        Icon(Icons.Default.Check, contentDescription = null)
                        Spacer(modifier = Modifier.width(8.dp))
                        Text(textoBoton)
                    }
                } else {
                    OutlinedButton(
                        onClick = {},
                        enabled = false,
                        modifier = Modifier.fillMaxWidth()
                    ) {
                        Text("Ya entregado")
                    }
                }

                Spacer(modifier = Modifier.height(8.dp))
                TextButton(onClick = onDismiss, modifier = Modifier.fillMaxWidth()) {
                    Text("Cerrar")
                }
            }
        }
    }
}

@Composable
fun DetalleItem(titulo: String, valor: String) {
    Row(
        modifier = Modifier.fillMaxWidth().padding(vertical = 4.dp),
        horizontalArrangement = Arrangement.SpaceBetween
    ) {
        Text(titulo, color = Color.Gray)
        Text(valor, fontWeight = FontWeight.SemiBold, color = Color.Black)
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun TasksTopBar(navController: NavController, onRefresh: () -> Unit) {
    CenterAlignedTopAppBar(
        title = { Text("Mis Tareas", fontWeight = FontWeight.Bold) },
        navigationIcon = {
            IconButton(onClick = { navController.popBackStack() }) {
                Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = null)
            }
        },
        actions = {
            IconButton(onClick = onRefresh) {
                Icon(Icons.Default.Refresh, contentDescription = null)
            }
        },
        colors = TopAppBarDefaults.centerAlignedTopAppBarColors(containerColor = LorentinaBg)
    )
}
