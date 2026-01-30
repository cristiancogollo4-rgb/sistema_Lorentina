package com.cristiancogollo.applorentinapg.screens


import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.*
import androidx.compose.material.icons.outlined.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.tooling.preview.Preview
import androidx.compose.ui.unit.dp
import androidx.navigation.NavController
import com.cristiancogollo.applorentinapg.LorentinaBottomNavBar
import com.cristiancogollo.applorentinapg.LorentinaBrown
import com.cristiancogollo.applorentinapg.LorentinaGreen
import com.cristiancogollo.applorentinapg.LorentinaRed
import com.cristiancogollo.applorentinapg.LorentinaTextPrimary
import com.cristiancogollo.applorentinapg.LorentinaTextSecondary


// --- PALETA DE COLORES LORENTINA ---
val LorentinaBeige = Color(0xFFF5F5F1) // Fondo pantalla
val LorentinaBeigeDark = Color(0xFFEFEBE9) // Fondo chips inactivos
val LorentinaOrange = Color(0xFFFFA000) // Amarillo/Naranja para "En proceso"


// --- COMPOSABLE PRINCIPAL: PANTALLA MIS TAREAS ---
@Composable
fun MisTareasScreen(
    navController: NavController, // <--- NUEVO
    userId: Int,
    userName: String) {
    Scaffold(
        containerColor = LorentinaBeige,
        topBar = { TaskTopBar(navController) },
        bottomBar = { LorentinaBottomNavBar(navController, userId, userName)},
        floatingActionButton = { TaskFloatingActionButton() }
    ) { paddingValues ->

        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(paddingValues)
        ) {
            // Sección de Filtros (Chips horizontales)
            FilterSection()

            Spacer(modifier = Modifier.height(8.dp))

            // Lista Vertical de Tareas
            TaskList()
        }
    }
}

// --- 1. BARRA SUPERIOR (APP BAR) ---
@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun TaskTopBar(navController: NavController) {
    TopAppBar(
        title = {
            Column {
                Text(
                    text = "Mis Tareas",
                    style = MaterialTheme.typography.titleLarge.copy(fontWeight = FontWeight.Bold),
                    color = LorentinaTextPrimary
                )
                Text(
                    text = "Gestión de trabajos asignados",
                    style = MaterialTheme.typography.bodySmall,
                    color = LorentinaTextSecondary
                )
            }
        },
        navigationIcon = {
            IconButton(onClick = {}) {
                Icon(
                    imageVector = Icons.AutoMirrored.Filled.ArrowBack,
                    contentDescription = "Volver",
                    tint = LorentinaTextPrimary
                )
            }
        },
        actions = {
            IconButton(onClick = {}) {
                Icon(
                    imageVector = Icons.Outlined.Notifications,
                    contentDescription = "Notificaciones",
                    tint = LorentinaTextPrimary
                )
            }
        },
        colors = TopAppBarDefaults.topAppBarColors(containerColor = Color.White)
    )
}

// --- 2. FILTROS (CHIPS) ---
@Composable
fun FilterSection() {
    val filters = listOf("Todas", "Pendientes", "En proceso", "Completadas")
    val selectedFilter = "Todas" // Hardcodeado para UI

    LazyRow(
        contentPadding = PaddingValues(horizontal = 16.dp, vertical = 12.dp),
        horizontalArrangement = Arrangement.spacedBy(8.dp)
    ) {
        items(filters) { filter ->
            val isSelected = filter == selectedFilter
            val containerColor = if (isSelected) LorentinaBrown else LorentinaBeigeDark
            val contentColor = if (isSelected) Color.White else LorentinaTextPrimary

            Surface(
                color = containerColor,
                contentColor = contentColor,
                shape = RoundedCornerShape(50), // Pill shape
                modifier = Modifier.height(32.dp),
                onClick = {}
            ) {
                Box(
                    contentAlignment = Alignment.Center,
                    modifier = Modifier.padding(horizontal = 16.dp)
                ) {
                    Text(
                        text = filter,
                        style = MaterialTheme.typography.labelMedium.copy(fontWeight = FontWeight.Medium)
                    )
                }
            }
        }
    }
}

// --- 3. LISTA DE TAREAS ---
@Composable
fun TaskList() {
    LazyColumn(
        contentPadding = PaddingValues(start = 16.dp, end = 16.dp, bottom = 80.dp), // Espacio extra abajo para el FAB
        verticalArrangement = Arrangement.spacedBy(16.dp)
    ) {
        // Card 1: Pendiente y Alta Prioridad
        item {
            TaskCard(
                orderId = "ORDEN #4599",
                status = "Pendiente",
                statusColor = LorentinaRed,
                priority = "Alta Prioridad",
                priorityIcon = Icons.Default.LocalFireDepartment, // Requiere extended icons o usar Icons.Default.Warning
                productName = "Bota Táctica - Ref. Ranger Xtreme",
                quantity = "50 Pares",
                material = "Cuero Hidrofugado Negro",
                deadline = "Hoy, 17:00"
            )
        }

        // Card 2: En Proceso y Prioridad Media
        item {
            TaskCard(
                orderId = "ORDEN #4601",
                status = "En Proceso",
                statusColor = LorentinaOrange,
                priority = "Prioridad Media",
                priorityIcon = Icons.Default.Bolt,
                productName = "Sandalia 'Verano Azul' - Ref. V20",
                quantity = "100 Pares",
                material = "Sintético Glitter",
                deadline = "Mañana, 10:00"
            )
        }

        // Card 3: Completada (Ejemplo extra para scroll)
        item {
            TaskCard(
                orderId = "ORDEN #4588",
                status = "Completada",
                statusColor = LorentinaGreen,
                priority = "Prioridad Baja",
                priorityIcon = Icons.Default.LowPriority,
                productName = "Mocasín Clásico - Ref. M10",
                quantity = "30 Pares",
                material = "Gamuza Café",
                deadline = "Ayer, 14:00"
            )
        }
    }
}

// --- COMPONENTE REUTILIZABLE: CARD DE TAREA ---
@Composable
fun TaskCard(
    orderId: String,
    status: String,
    statusColor: Color,
    priority: String,
    priorityIcon: ImageVector,
    productName: String,
    quantity: String,
    material: String,
    deadline: String
) {
    Card(
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
        shape = RoundedCornerShape(16.dp),
        modifier = Modifier.fillMaxWidth()
    ) {
        Column(modifier = Modifier.padding(16.dp)) {

            // Fila Superior: ID Orden y Badge Estado
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = orderId,
                    style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.Bold),
                    color = LorentinaTextPrimary
                )

                // Badge de Estado
                Surface(
                    color = statusColor.copy(alpha = 0.1f),
                    shape = RoundedCornerShape(50),
                ) {
                    Row(
                        verticalAlignment = Alignment.CenterVertically,
                        modifier = Modifier.padding(horizontal = 8.dp, vertical = 4.dp)
                    ) {
                        Box(modifier = Modifier.size(8.dp).background(statusColor, CircleShape))
                        Spacer(modifier = Modifier.width(6.dp))
                        Text(
                            text = status,
                            color = statusColor,
                            style = MaterialTheme.typography.labelSmall.copy(fontWeight = FontWeight.Bold)
                        )
                    }
                }
            }

            Spacer(modifier = Modifier.height(8.dp))

            // Badge de Prioridad
            Surface(
                color = Color(0xFFFFECB3), // Fondo amarillento suave
                shape = RoundedCornerShape(4.dp)
            ) {
                Row(
                    modifier = Modifier.padding(horizontal = 6.dp, vertical = 2.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Icon(priorityIcon, contentDescription = null, tint = Color(0xFFEF6C00), modifier = Modifier.size(14.dp))
                    Spacer(modifier = Modifier.width(4.dp))
                    Text(
                        text = priority,
                        color = Color(0xFFEF6C00),
                        style = MaterialTheme.typography.labelSmall.copy(fontWeight = FontWeight.Bold)
                    )
                }
            }

            Spacer(modifier = Modifier.height(12.dp))

            // Nombre del Producto e Icono
            Row(verticalAlignment = Alignment.Top) {
                // Icono del zapato (usamos Hiking como aproximación de bota)
                Icon(
                    imageVector = Icons.Default.Hiking,
                    contentDescription = null,
                    tint = LorentinaBrown,
                    modifier = Modifier.size(24.dp)
                )
                Spacer(modifier = Modifier.width(12.dp))
                Text(
                    text = productName,
                    style = MaterialTheme.typography.bodyLarge.copy(fontWeight = FontWeight.Medium),
                    color = LorentinaTextPrimary,
                    maxLines = 2,
                    overflow = TextOverflow.Ellipsis
                )
            }

            Spacer(modifier = Modifier.height(12.dp))

            // Detalles (Grid simple)
            DetailRow(icon = Icons.Outlined.Inventory2, label = "Cantidad:", value = quantity)
            DetailRow(icon = Icons.Outlined.ContentCut, label = "Material:", value = material)
            DetailRow(icon = Icons.Outlined.Event, label = "Límite:", value = deadline)

            Spacer(modifier = Modifier.height(16.dp))

            // Botón de Acción
            Button(
                onClick = {},
                colors = ButtonDefaults.buttonColors(containerColor = LorentinaBrown),
                shape = RoundedCornerShape(8.dp),
                modifier = Modifier.fillMaxWidth().height(45.dp)
            ) {
                Text("VER DETALLE", fontWeight = FontWeight.Bold)
            }
        }
    }
}

// Componente auxiliar para filas de detalles
@Composable
fun DetailRow(icon: ImageVector, label: String, value: String) {
    Row(
        modifier = Modifier.padding(vertical = 2.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Icon(icon, contentDescription = null, tint = LorentinaTextSecondary, modifier = Modifier.size(16.dp))
        Spacer(modifier = Modifier.width(8.dp))
        Text(
            text = label,
            style = MaterialTheme.typography.bodyMedium.copy(fontWeight = FontWeight.Bold),
            color = LorentinaTextPrimary
        )
        Spacer(modifier = Modifier.width(4.dp))
        Text(
            text = value,
            style = MaterialTheme.typography.bodyMedium,
            color = LorentinaTextSecondary
        )
    }
}

// --- 4. BOTÓN FLOTANTE (FAB) ---
@Composable
fun TaskFloatingActionButton() {
    ExtendedFloatingActionButton(
        onClick = {},
        containerColor = LorentinaBrown,
        contentColor = Color.White,
        icon = { Icon(Icons.Default.Add, contentDescription = null) },
        text = { Text("INICIAR TAREA", fontWeight = FontWeight.Bold) },
        // Expanded = true por defecto para mostrar texto
    )
}

// --- 5. BARRA DE NAVEGACIÓN INFERIOR ---
@Composable
fun TaskBottomNavBar() {
    NavigationBar(
        containerColor = Color.White,
        tonalElevation = 8.dp
    ) {
        NavigationBarItem(
            icon = { Icon(Icons.Outlined.Home, contentDescription = "Inicio") },
            label = { Text("Inicio") },
            selected = false,
            onClick = {},
            colors = NavigationBarItemDefaults.colors(
                unselectedIconColor = LorentinaTextSecondary,
                unselectedTextColor = LorentinaTextSecondary
            )
        )
        NavigationBarItem(
            icon = { Icon(Icons.Filled.Assignment, contentDescription = "Tareas") },
            label = { Text("Tareas") },
            selected = true, // Ítem Activo
            onClick = {},
            colors = NavigationBarItemDefaults.colors(
                selectedIconColor = LorentinaBrown,
                selectedTextColor = LorentinaBrown,
                indicatorColor = LorentinaBeige // Fondo del icono seleccionado
            )
        )
        NavigationBarItem(
            icon = { Icon(Icons.Outlined.Paid, contentDescription = "Nómina") },
            label = { Text("Nómina") },
            selected = false,
            onClick = {},
            colors = NavigationBarItemDefaults.colors(
                unselectedIconColor = LorentinaTextSecondary,
                unselectedTextColor = LorentinaTextSecondary
            )
        )
        NavigationBarItem(
            icon = { Icon(Icons.Outlined.Person, contentDescription = "Perfil") },
            label = { Text("Perfil") },
            selected = false,
            onClick = {},
            colors = NavigationBarItemDefaults.colors(
                unselectedIconColor = LorentinaTextSecondary,
                unselectedTextColor = LorentinaTextSecondary
            )
        )
    }
}

// --- PREVIEW ---
//@Preview(showBackground = true, device = "id:pixel_7", showSystemUi = true)
//@Composable
//fun MisTareasPreview() {
//    MisTareasScreen()
//}