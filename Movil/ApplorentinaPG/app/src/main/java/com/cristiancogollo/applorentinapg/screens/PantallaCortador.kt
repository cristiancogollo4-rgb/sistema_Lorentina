package com.cristiancogollo.applorentinapg

import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.Assignment
import androidx.compose.material.icons.filled.Home
import androidx.compose.material.icons.filled.Notifications
import androidx.compose.material.icons.outlined.Assignment
import androidx.compose.material.icons.outlined.Home
import androidx.compose.material.icons.outlined.Paid
import androidx.compose.material.icons.outlined.Person
import androidx.compose.material.icons.outlined.ShoppingBag
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.tooling.preview.Preview
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import androidx.navigation.NavGraph.Companion.findStartDestination
import androidx.navigation.compose.currentBackStackEntryAsState
import com.cristiancogollo.applorentinapg.viewmodel.HomeUiState
import com.cristiancogollo.applorentinapg.viewmodel.HomeViewModel

// --- Constantes de Color Lorentina ---
val LorentinaBrown = Color(0xFF5D4037)
val LorentinaBeige = Color(0xFFD7CCC8)
val LorentinaBg = Color(0xFFF5F5F1)
val LorentinaGreen = Color(0xFF388E3C)
val LorentinaRed = Color(0xFFD32F2F)
val LorentinaTextPrimary = Color(0xFF212121)
val LorentinaTextSecondary = Color(0xFF757575)

@Composable
fun HomeScreenUi(
    navController: NavController, // Necesario para la navegación
    usuarioId: Int,
    nombreUsuario: String,
    viewModel: HomeViewModel = viewModel()
) {
    // 1. Cargar datos al entrar
    LaunchedEffect(Unit) {
        viewModel.cargarDatos(usuarioId)
    }

    // 2. Observar estado
    val uiState by viewModel.uiState.collectAsState()

    Scaffold(
        topBar = { LorentinaTopAppBar() },
        bottomBar = { LorentinaBottomNavBar(navController, usuarioId, nombreUsuario) },
        containerColor = LorentinaBg
    ) { paddingValues ->

        Box(
            modifier = Modifier
                .padding(paddingValues)
                .fillMaxSize()
        ) {
            when (val state = uiState) {
                is HomeUiState.Loading -> {
                    CircularProgressIndicator(
                        modifier = Modifier.align(Alignment.Center),
                        color = LorentinaBrown
                    )
                }
                is HomeUiState.Error -> {
                    Column(
                        modifier = Modifier.align(Alignment.Center),
                        horizontalAlignment = Alignment.CenterHorizontally
                    ) {
                        Icon(Icons.Default.Notifications, contentDescription = "Error", tint = LorentinaRed)
                        Spacer(modifier = Modifier.height(8.dp))
                        Text(
                            text = state.mensaje,
                            color = LorentinaRed,
                            textAlign = TextAlign.Center,
                            modifier = Modifier.padding(16.dp)
                        )
                        Button(
                            onClick = { viewModel.cargarDatos(usuarioId) },
                            colors = ButtonDefaults.buttonColors(containerColor = LorentinaBrown)
                        ) {
                            Text("Reintentar")
                        }
                    }
                }
                is HomeUiState.Success -> {
                    Column(
                        modifier = Modifier
                            .fillMaxSize()
                            .verticalScroll(rememberScrollState())
                            .padding(16.dp)
                    ) {
                        GreetingSection(nombreUsuario)

                        Spacer(modifier = Modifier.height(24.dp))

                        SummarySection(
                            pendientes = state.pendientesCount,
                            enProceso = state.enProcesoCount,
                            completadas = state.completadasCount
                        )

                        Spacer(modifier = Modifier.height(24.dp))

                        Text(
                            text = "Tus Tareas Asignadas",
                            style = MaterialTheme.typography.titleLarge.copy(fontWeight = FontWeight.Bold),
                            color = LorentinaTextPrimary
                        )
                        Spacer(modifier = Modifier.height(12.dp))

                        if (state.tareas.isEmpty()) {
                            Card(
                                colors = CardDefaults.cardColors(containerColor = Color.White),
                                modifier = Modifier.fillMaxWidth().padding(vertical = 10.dp)
                            ) {
                                Text(
                                    text = "¡Todo al día! No tienes cortes pendientes.",
                                    modifier = Modifier.padding(20.dp),
                                    color = LorentinaTextSecondary
                                )
                            }
                        } else {
                            state.tareas.forEach { orden ->
                                TaskCard(
                                    orderId = "ORDEN #${orden.numeroOrden}",
                                    badgeText = orden.estado,
                                    badgeColor = if (orden.estado == "EN_CORTE") LorentinaRed else LorentinaGreen,
                                    title = "${orden.referencia} - ${orden.color}",
                                    quantity = "${orden.totalPares} Pares",
                                    material = "Curva: ${orden.obtenerResumenCurva()}",
                                    deadline = formatearFechaCorta(orden.fechaInicio),
                                    imagePlaceholderIcon = Icons.Outlined.ShoppingBag,
                                    imagePlaceholderColor = Color(0xFFA1887F)
                                )
                                Spacer(modifier = Modifier.height(16.dp))
                            }
                        }
                        // Espacio extra al final
                        Spacer(modifier = Modifier.height(50.dp))
                    }
                }
            }
        }
    }
}

// --- FUNCIONES AUXILIARES (UI COMPONENTS) ---

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun LorentinaTopAppBar() {
    CenterAlignedTopAppBar(
        title = {
            Text(
                "LORENTINA",
                style = MaterialTheme.typography.titleMedium.copy(
                    fontWeight = FontWeight.Bold,
                    letterSpacing = 2.sp
                ),
                color = LorentinaBrown
            )
        },
        colors = TopAppBarDefaults.centerAlignedTopAppBarColors(
            containerColor = LorentinaBg
        )
    )
}

@Composable
fun GreetingSection(nombre: String) {
    Column {
        Text(
            text = "Hola, $nombre 👋",
            style = MaterialTheme.typography.headlineMedium.copy(fontWeight = FontWeight.Bold),
            color = LorentinaTextPrimary
        )
        Text(
            text = "Aquí tienes tu producción de hoy",
            style = MaterialTheme.typography.bodyLarge,
            color = LorentinaTextSecondary
        )
    }
}

@Composable
fun SummarySection(pendientes: Int, enProceso: Int, completadas: Int) {
    Row(
        modifier = Modifier.fillMaxWidth(),
        horizontalArrangement = Arrangement.SpaceBetween
    ) {
        SummaryCard(
            modifier = Modifier.weight(1f),
            count = pendientes.toString(),
            label = "Pendientes",
            icon = Icons.Default.Assignment,
            accentColor = LorentinaRed
        )
        Spacer(modifier = Modifier.width(12.dp))
        SummaryCard(
            modifier = Modifier.weight(1f),
            count = completadas.toString(),
            label = "Terminadas",
            icon = Icons.Outlined.Assignment,
            accentColor = LorentinaGreen
        )
    }
}

@Composable
fun SummaryCard(
    modifier: Modifier = Modifier,
    count: String,
    label: String,
    icon: ImageVector,
    accentColor: Color
) {
    Card(
        modifier = modifier,
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(
            modifier = Modifier.padding(16.dp),
            verticalArrangement = Arrangement.Center
        ) {
            Icon(
                imageVector = icon,
                contentDescription = null,
                tint = accentColor,
                modifier = Modifier.size(28.dp)
            )
            Spacer(modifier = Modifier.height(8.dp))
            Text(
                text = count,
                style = MaterialTheme.typography.headlineMedium.copy(fontWeight = FontWeight.Bold),
                color = LorentinaTextPrimary
            )
            Text(
                text = label,
                style = MaterialTheme.typography.bodySmall,
                color = LorentinaTextSecondary
            )
        }
    }
}

@Composable
fun TaskCard(
    orderId: String,
    badgeText: String,
    badgeColor: Color,
    title: String,
    quantity: String,
    material: String,
    deadline: String,
    imagePlaceholderIcon: ImageVector,
    imagePlaceholderColor: Color
) {
    Card(
        shape = RoundedCornerShape(20.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 0.dp),
        border = BorderStroke(1.dp, Color(0xFFEEEEEE)),
        modifier = Modifier.fillMaxWidth()
    ) {
        Column(modifier = Modifier.padding(16.dp)) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = orderId,
                    style = MaterialTheme.typography.labelMedium.copy(fontWeight = FontWeight.Bold),
                    color = LorentinaTextSecondary
                )
                StatusBadge(text = badgeText, color = badgeColor)
            }

            Spacer(modifier = Modifier.height(16.dp))

            Row(verticalAlignment = Alignment.CenterVertically) {
                Surface(
                    modifier = Modifier.size(60.dp),
                    shape = RoundedCornerShape(12.dp),
                    color = imagePlaceholderColor.copy(alpha = 0.1f)
                ) {
                    Icon(
                        imageVector = imagePlaceholderIcon,
                        contentDescription = null,
                        modifier = Modifier.padding(12.dp),
                        tint = imagePlaceholderColor
                    )
                }

                Spacer(modifier = Modifier.width(16.dp))

                Column {
                    Text(
                        text = title,
                        style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.Bold),
                        color = LorentinaTextPrimary
                    )
                    Spacer(modifier = Modifier.height(4.dp))
                    Text(
                        text = quantity,
                        style = MaterialTheme.typography.bodyMedium,
                        color = LorentinaBrown
                    )
                }
            }

            Spacer(modifier = Modifier.height(16.dp))

            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .background(LorentinaBg, RoundedCornerShape(8.dp))
                    .padding(12.dp),
                horizontalArrangement = Arrangement.SpaceBetween
            ) {
                Column(modifier = Modifier.weight(1f)) {
                    Text("Detalle", style = MaterialTheme.typography.labelSmall, color = LorentinaTextSecondary)
                    Text(material, style = MaterialTheme.typography.bodySmall, color = LorentinaTextPrimary)
                }
                Column(horizontalAlignment = Alignment.End) {
                    Text("Fecha Inicio", style = MaterialTheme.typography.labelSmall, color = LorentinaTextSecondary)
                    Text(deadline, style = MaterialTheme.typography.bodySmall, color = LorentinaTextPrimary)
                }
            }
        }
    }
}

@Composable
fun StatusBadge(text: String, color: Color) {
    Surface(
        color = color.copy(alpha = 0.1f),
        shape = RoundedCornerShape(50),
        border = BorderStroke(1.dp, color.copy(alpha = 0.2f))
    ) {
        Text(
            text = text,
            modifier = Modifier.padding(horizontal = 10.dp, vertical = 4.dp),
            style = MaterialTheme.typography.labelSmall.copy(fontWeight = FontWeight.Bold),
            color = color
        )
    }
}

@Composable
fun LorentinaBottomNavBar(
    navController: NavController,
    userId: Int,
    userName: String
) {
    val navBackStackEntry by navController.currentBackStackEntryAsState()
    val currentRoute = navBackStackEntry?.destination?.route

    NavigationBar(
        containerColor = Color.White,
        tonalElevation = 8.dp
    ) {
        NavigationBarItem(
            icon = { Icon(Icons.Filled.Home, contentDescription = "Inicio") },
            label = { Text("Inicio") },
            selected = currentRoute?.startsWith("home") == true,
            onClick = {
                navController.navigate("home/$userId/$userName") {
                    popUpTo(navController.graph.findStartDestination().id) { saveState = true }
                    launchSingleTop = true
                    restoreState = true
                }
            },
            colors = NavigationBarItemDefaults.colors(
                selectedIconColor = LorentinaBrown,
                indicatorColor = LorentinaBeige
            )
        )
        NavigationBarItem(
            icon = { Icon(Icons.Outlined.Assignment, contentDescription = "Tareas") },
            label = { Text("Tareas") },
            selected = currentRoute?.startsWith("tasks") == true,
            onClick = {
                navController.navigate("tasks/$userId/$userName") {
                    popUpTo(navController.graph.findStartDestination().id) { saveState = true }
                    launchSingleTop = true
                    restoreState = true
                }
            },
            colors = NavigationBarItemDefaults.colors(
                selectedIconColor = LorentinaBrown,
                indicatorColor = LorentinaBeige
            )
        )
        NavigationBarItem(
            icon = { Icon(Icons.Outlined.Paid, contentDescription = "Nómina") },
            label = { Text("Nómina") },
            selected = currentRoute?.startsWith("payroll") == true,
            onClick = {
                navController.navigate("payroll/$userId/$userName") {
                    popUpTo(navController.graph.findStartDestination().id) { saveState = true }
                    launchSingleTop = true
                    restoreState = true
                }
            },
            colors = NavigationBarItemDefaults.colors(
                selectedIconColor = LorentinaBrown,
                indicatorColor = LorentinaBeige
            )
        )
        NavigationBarItem(
            icon = { Icon(Icons.Outlined.Person, contentDescription = "Perfil") },
            label = { Text("Perfil") },
            selected = currentRoute?.startsWith("profile") == true,
            onClick = {
                navController.navigate("profile/$userId/$userName") {
                    popUpTo(navController.graph.findStartDestination().id) { saveState = true }
                    launchSingleTop = true
                    restoreState = true
                }
            },
            colors = NavigationBarItemDefaults.colors(
                selectedIconColor = LorentinaBrown,
                indicatorColor = LorentinaBeige
            )
        )
    }
}

fun formatearFechaCorta(fechaIso: String): String {
    return try {
        fechaIso.take(10)
    } catch (e: Exception) {
        "Sin fecha"
    }
}