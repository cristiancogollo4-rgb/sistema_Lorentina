package com.cristiancogollo.applorentinapg.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.HelpOutline
import androidx.compose.material.icons.filled.ExpandMore
import androidx.compose.material.icons.filled.Paid
import androidx.compose.material.icons.outlined.*
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.tooling.preview.Preview
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavController
import com.cristiancogollo.applorentinapg.LorentinaBottomNavBar
import com.cristiancogollo.applorentinapg.LorentinaBrown
import com.cristiancogollo.applorentinapg.LorentinaGreen
import com.cristiancogollo.applorentinapg.LorentinaTextPrimary
import com.cristiancogollo.applorentinapg.LorentinaTextSecondary
import com.cristiancogollo.applorentinapg.StatusBadge


// --- PALETA DE COLORES LORENTINA (Definida para consistencia) ---

val LorentinaBeigeLight = Color(0xFFEFEBE9) // Fondo de inputs/chips inactivos
val LorentinaYellow = Color(0xFFFBC02D)


// --- COMPOSABLE PRINCIPAL: PANTALLA NÓMINA ---
@Composable
fun NominaScreen(
    navController: NavController,
    userId: Int,
    userName: String
) {
    Scaffold(
        containerColor = LorentinaBeige,
        topBar = { NominaTopBar(navController) },
        bottomBar = { LorentinaBottomNavBar(navController, userId, userName) }
    ) { paddingValues ->
        // Contenedor principal con Scroll Vertical
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(paddingValues)
                .verticalScroll(rememberScrollState()) // Permite el desplazamiento
                .padding(16.dp)
        ) {
            // 2. Card Grande de Resumen Mensual
            MonthlySummaryCard()

            Spacer(modifier = Modifier.height(20.dp))

            // 3. Selector de Mes
            MonthSelector()

            Spacer(modifier = Modifier.height(16.dp))

            // 4. Filtros de Pagos
            PaymentFilters()

            Spacer(modifier = Modifier.height(16.dp))

            // 5. Lista de Pagos (Items individuales)
            PaymentItem(
                taskId = "TAREA #4588",
                date = "24 Ene 2026",
                amount = "$250,000",
                method = "Transferencia Bancaria",
                status = "Pagado",
                statusColor = LorentinaGreen
            )

            Spacer(modifier = Modifier.height(12.dp))

            PaymentItem(
                taskId = "TAREA #4592",
                date = "22 Ene 2026",
                amount = "$150,000",
                method = "Efectivo",
                status = "Pendiente",
                statusColor = LorentinaYellow
            )

            // Espacio extra al final para que el scroll no quede pegado al BottomBar
            Spacer(modifier = Modifier.height(24.dp))
        }
    }
}

// --- 1. APP BAR SUPERIOR ---
@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun NominaTopBar(navController: NavController) {
    CenterAlignedTopAppBar(
        title = {
            Column(horizontalAlignment = Alignment.CenterHorizontally) {
                Text(
                    text = "Nómina",
                    style = MaterialTheme.typography.titleLarge.copy(fontWeight = FontWeight.Bold),
                    color = LorentinaTextPrimary
                )
                Text(
                    text = "Resumen de pagos y liquidaciones",
                    style = MaterialTheme.typography.bodySmall,
                    color = LorentinaTextSecondary
                )
            }
        },
        navigationIcon = {
            IconButton(onClick = {navController.popBackStack()}) {
                Icon(
                    imageVector = Icons.AutoMirrored.Filled.ArrowBack,
                    contentDescription = "Atrás",
                    tint = LorentinaTextPrimary
                )
            }
        },
        actions = {
            IconButton(onClick = {}) {
                Icon(
                    imageVector = Icons.AutoMirrored.Filled.HelpOutline, // Ícono de ayuda ?
                    contentDescription = "Ayuda",
                    tint = LorentinaTextPrimary
                )
            }
        },
        colors = TopAppBarDefaults.centerAlignedTopAppBarColors(
            containerColor = LorentinaBeige // Fondo beige para integrarse o White si prefieres contraste
        )
    )
}

// --- 2. CARD RESUMEN MENSUAL ---
@Composable
fun MonthlySummaryCard() {
    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(
            modifier = Modifier.padding(20.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            Text(
                text = "Resumen Mensual: Enero 2026",
                style = MaterialTheme.typography.bodyMedium,
                color = LorentinaTextSecondary
            )

            Spacer(modifier = Modifier.height(8.dp))

            Text(
                text = "Total Ganado:",
                style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.Medium),
                color = LorentinaTextPrimary
            )

            // Monto Grande Destacado
            Text(
                text = "$1,250,000",
                style = MaterialTheme.typography.displaySmall.copy(fontWeight = FontWeight.ExtraBold),
                color = LorentinaBrown,
                letterSpacing = (-1).sp
            )

            Spacer(modifier = Modifier.height(16.dp))

            // Fila inferior con Tareas liquidadas y Estado
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = "Tareas Liquidadas: 150",
                    style = MaterialTheme.typography.bodyMedium,
                    color = LorentinaTextSecondary
                )

                // Badge de Estado General
                StatusBadge(text = "Estado: Pagado", color = LorentinaGreen)
            }
        }
    }
}

// --- 3. SELECTOR DE MES ---
@Composable
fun MonthSelector() {
    // Simulamos un Dropdown con una Card o Surface clicable
    Card(
        onClick = {},
        shape = RoundedCornerShape(12.dp),
        colors = CardDefaults.cardColors(containerColor = LorentinaBeigeLight), // Fondo beige claro
        modifier = Modifier.fillMaxWidth().height(50.dp)
    ) {
        Row(
            modifier = Modifier
                .fillMaxSize()
                .padding(horizontal = 16.dp),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.SpaceBetween
        ) {
            Text(
                text = "Mes: Enero",
                style = MaterialTheme.typography.bodyLarge.copy(fontWeight = FontWeight.Medium),
                color = LorentinaTextPrimary
            )
            Icon(
                imageVector = Icons.Default.ExpandMore,
                contentDescription = "Seleccionar mes",
                tint = LorentinaTextSecondary
            )
        }
    }
}

// --- 4. FILTROS DE PAGOS ---
@Composable
fun PaymentFilters() {
    Row(
        modifier = Modifier.fillMaxWidth(),
        horizontalArrangement = Arrangement.spacedBy(8.dp)
    ) {
        // Chip Activo (Todos)
        FilterChipUI(text = "Todos", selected = true)
        // Chips Inactivos
        FilterChipUI(text = "Pagados", selected = false)
        FilterChipUI(text = "Pendientes", selected = false)
    }
}

@Composable
fun FilterChipUI(text: String, selected: Boolean) {
    Surface(
        color = if (selected) LorentinaBrown else LorentinaBeigeLight,
        shape = RoundedCornerShape(50),
        modifier = Modifier.height(32.dp),
        onClick = {}
    ) {
        Box(
            contentAlignment = Alignment.Center,
            modifier = Modifier.padding(horizontal = 16.dp)
        ) {
            Text(
                text = text,
                style = MaterialTheme.typography.labelMedium.copy(fontWeight = FontWeight.Bold),
                color = if (selected) Color.White else LorentinaTextSecondary
            )
        }
    }
}

// --- 5. ITEM DE LISTA DE PAGOS (CARD REUTILIZABLE) ---
@Composable
fun PaymentItem(
    taskId: String,
    date: String,
    amount: String,
    method: String,
    status: String,
    statusColor: Color
) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(modifier = Modifier.padding(16.dp)) {
            // Fila superior: ID y Badge
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Column {
                    Text(
                        text = taskId,
                        style = MaterialTheme.typography.titleSmall.copy(fontWeight = FontWeight.Bold),
                        color = LorentinaTextPrimary
                    )
                    Text(
                        text = "Fecha: $date",
                        style = MaterialTheme.typography.bodySmall,
                        color = LorentinaTextSecondary
                    )
                }

                // Badge de estado (Pequeño)
                StatusBadge(text = status, color = statusColor)
            }

            Spacer(modifier = Modifier.height(12.dp))

            // Monto y Método
            Text(
                text = amount,
                style = MaterialTheme.typography.headlineSmall.copy(fontWeight = FontWeight.Bold),
                color = LorentinaBrown
            )
            Spacer(modifier = Modifier.height(4.dp))
            Text(
                text = "Método: $method",
                style = MaterialTheme.typography.bodySmall,
                color = LorentinaTextSecondary
            )
        }
    }
}

// Auxiliar para el Badge de color


// --- 6. BOTTOM NAVIGATION BAR ---
@Composable
fun NominaBottomNavBar() {
    NavigationBar(
        containerColor = Color.White,
        tonalElevation = 8.dp
    ) {
        NavigationBarItem(
            icon = { Icon(Icons.Outlined.Home, contentDescription = "Inicio") },
            label = { Text("Inicio") },
            selected = false,
            onClick = {},
            colors = NavigationBarItemDefaults.colors(unselectedIconColor = LorentinaTextSecondary)
        )
        NavigationBarItem(
            icon = { Icon(Icons.Outlined.Assignment, contentDescription = "Tareas") },
            label = { Text("Tareas") },
            selected = false,
            onClick = {},
            colors = NavigationBarItemDefaults.colors(unselectedIconColor = LorentinaTextSecondary)
        )
        // ITEM ACTIVO: NÓMINA
        NavigationBarItem(
            icon = { Icon(Icons.Filled.Paid, contentDescription = "Nómina") },
            label = { Text("Nómina") },
            selected = true,
            onClick = {},
            colors = NavigationBarItemDefaults.colors(
                selectedIconColor = LorentinaBrown,
                selectedTextColor = LorentinaBrown,
                indicatorColor = LorentinaBeige
            )
        )
        NavigationBarItem(
            icon = { Icon(Icons.Outlined.Person, contentDescription = "Perfil") },
            label = { Text("Perfil") },
            selected = false,
            onClick = {},
            colors = NavigationBarItemDefaults.colors(unselectedIconColor = LorentinaTextSecondary)
        )
    }
}

// --- PREVIEW ---
//@Preview(showBackground = true, device = "id:pixel_7", showSystemUi = true)
//@Composable
//fun NominaScreenPreview() {
//    NominaScreen()
//}