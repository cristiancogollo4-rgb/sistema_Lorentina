package com.cristiancogollo.applorentinapg

import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.KeyboardArrowRight
import androidx.compose.material.icons.filled.*
import androidx.compose.material.icons.outlined.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.cristiancogollo.applorentinapg.model.UserResponse
import com.cristiancogollo.applorentinapg.screens.BottomNavigationBar
import com.cristiancogollo.applorentinapg.screens.LorentinaBg
import com.cristiancogollo.applorentinapg.screens.LorentinaBrown
import com.cristiancogollo.applorentinapg.screens.LorentinaGreen
import com.cristiancogollo.applorentinapg.screens.LorentinaTextPrimary
import com.cristiancogollo.applorentinapg.screens.LorentinaTextSecondary
import com.cristiancogollo.applorentinapg.viewmodel.ProfileUiState
import com.cristiancogollo.applorentinapg.viewmodel.ProfileViewModel

// --- PANTALLA PRINCIPAL PERFIL (MVVM) ---
@Composable
fun PerfilScreen(
    navController: NavController,
    userId: Int,
    userName: String,
    userRol: String,
    viewModel: ProfileViewModel = viewModel()
) {
    // 1. Cargar datos al entrar
    LaunchedEffect(Unit) {
        viewModel.cargarPerfil(userId)
    }

    val uiState by viewModel.uiState.collectAsState()

    Scaffold(
        containerColor = LorentinaBg, // Asegúrate de tener este color definido en algún lado o usa Color(0xFFF5F5F1)
        topBar = { ProfileTopBar(navController) },
        bottomBar = { BottomNavigationBar(navController, userId, userName, userRol) }
    ) { paddingValues ->

        Box(modifier = Modifier.padding(paddingValues).fillMaxSize()) {

            when (val state = uiState) {
                is ProfileUiState.Loading -> {
                    CircularProgressIndicator(
                        modifier = Modifier.align(Alignment.Center),
                        color = LorentinaBrown
                    )
                }
                is ProfileUiState.Error -> {
                    Column(
                        modifier = Modifier.align(Alignment.Center).padding(16.dp),
                        horizontalAlignment = Alignment.CenterHorizontally
                    ) {
                        Text("Error: ${state.mensaje}", color = Color.Red)
                        Button(onClick = { viewModel.cargarPerfil(userId) }) {
                            Text("Reintentar")
                        }
                    }
                }
                is ProfileUiState.Success -> {
                    val user = state.usuario

                    // CONTENIDO REAL
                    Column(
                        modifier = Modifier
                            .fillMaxSize()
                            .verticalScroll(rememberScrollState())
                            .padding(horizontal = 16.dp, vertical = 8.dp),
                        verticalArrangement = Arrangement.spacedBy(16.dp)
                    ) {
                        // 2. Card de Información del Usuario
                        UserInfoCard(user)

                        // 3. Card de Información Laboral
                        WorkInfoCard(user)

                        // 4. Card de Accesos Rápidos
                        QuickAccessCard()

                        Spacer(modifier = Modifier.height(8.dp))

                        // 5. Botón Cerrar Sesión
                        Button(
                            onClick = {
                                // Navegar al Login y borrar historial
                                navController.navigate("login") {
                                    popUpTo("login") { inclusive = true }
                                }
                            },
                            colors = ButtonDefaults.buttonColors(containerColor = LorentinaBrown),
                            shape = RoundedCornerShape(50),
                            modifier = Modifier.fillMaxWidth().height(50.dp)
                        ) {
                            Text(
                                text = "Cerrar sesión",
                                style = MaterialTheme.typography.bodyLarge.copy(fontWeight = FontWeight.Bold),
                                color = Color.White
                            )
                        }
                        Spacer(modifier = Modifier.height(24.dp))
                    }
                }
            }
        }
    }
}

// --- COMPONENTES UI ADAPTADOS ---

@OptIn(ExperimentalMaterial3Api::class)
@Composable
private fun ProfileTopBar(navController: NavController) {
    CenterAlignedTopAppBar(
        title = {
            Text(
                text = "Mi Perfil",
                style = MaterialTheme.typography.headlineMedium.copy(fontWeight = FontWeight.Bold),
                color = LorentinaTextPrimary
            )
        },
        navigationIcon = {
            IconButton(onClick = { navController.popBackStack() }) {
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
                    imageVector = Icons.Default.Settings,
                    contentDescription = "Configuración",
                    tint = LorentinaTextPrimary
                )
            }
        },
        colors = TopAppBarDefaults.centerAlignedTopAppBarColors(
            containerColor = LorentinaBg // Color(0xFFF5F5F1)
        )
    )
}

@Composable
private fun UserInfoCard(user: UserResponse) {
    Card(
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
        shape = RoundedCornerShape(16.dp),
        modifier = Modifier.fillMaxWidth()
    ) {
        Row(
            modifier = Modifier.padding(20.dp).fillMaxWidth(),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Surface(
                modifier = Modifier.size(80.dp),
                shape = CircleShape,
                color = Color.LightGray
            ) {
                Icon(
                    imageVector = Icons.Default.Person,
                    contentDescription = "Avatar",
                    modifier = Modifier.padding(16.dp),
                    tint = Color.White
                )
            }
            Spacer(modifier = Modifier.width(20.dp))
            Column {
                Text(
                    text = "${user.nombre} ${user.apellido ?: ""}",
                    style = MaterialTheme.typography.titleLarge.copy(fontWeight = FontWeight.Bold),
                    color = LorentinaTextPrimary
                )
                Spacer(modifier = Modifier.height(4.dp))
                Text(
                    text = "Rol: ${user.rol ?: "Sin Rol"}",
                    style = MaterialTheme.typography.bodyMedium,
                    color = LorentinaTextSecondary
                )
                Text(
                    text = "Usuario: ${user.username}",
                    style = MaterialTheme.typography.bodyMedium,
                    color = LorentinaTextSecondary
                )
            }
        }
    }
}

@Composable
private fun WorkInfoCard(user: UserResponse) {
    Card(
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
        shape = RoundedCornerShape(16.dp),
        modifier = Modifier.fillMaxWidth()
    ) {
        Column(modifier = Modifier.padding(20.dp)) {
            Text(
                text = "Información Laboral",
                style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.Bold),
                color = LorentinaTextPrimary
            )
            Spacer(modifier = Modifier.height(16.dp))

            // DATOS REALES VS HARDCODEADOS
            // Como tu base de datos aun no tiene "Turno" o "FechaIngreso", podemos dejarlos fijos
            // o usar campos opcionales si los agregas después.
            InfoRow(label = "Cédula:", value = user.cedula ?: "No registrada")
            InfoRow(label = "Teléfono:", value = user.telefono ?: "No registrado")
            InfoRow(label = "Área:", value = user.rol ?: "General")

            Row(
                modifier = Modifier.fillMaxWidth().padding(vertical = 6.dp),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text("Estado:", style = MaterialTheme.typography.bodyMedium, color = LorentinaTextSecondary)
                Surface(
                    color = if(user.activo) LorentinaGreen.copy(alpha = 0.15f) else Color.Red.copy(alpha = 0.15f),
                    shape = RoundedCornerShape(50)
                ) {
                    Text(
                        text = if(user.activo) "Activo" else "Inactivo",
                        color = if(user.activo) LorentinaGreen else Color.Red,
                        style = MaterialTheme.typography.labelMedium.copy(fontWeight = FontWeight.Bold),
                        modifier = Modifier.padding(horizontal = 12.dp, vertical = 4.dp)
                    )
                }
            }
        }
    }
}

@Composable
private fun InfoRow(label: String, value: String) {
    Row(
        modifier = Modifier.fillMaxWidth().padding(vertical = 6.dp),
        horizontalArrangement = Arrangement.SpaceBetween
    ) {
        Text(text = label, style = MaterialTheme.typography.bodyMedium, color = LorentinaTextSecondary)
        Text(text = value, style = MaterialTheme.typography.bodyMedium.copy(fontWeight = FontWeight.Medium), color = LorentinaTextPrimary)
    }
}

@Composable
private fun QuickAccessCard() {
    Card(
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
        shape = RoundedCornerShape(16.dp),
        modifier = Modifier.fillMaxWidth()
    ) {
        Column(modifier = Modifier.padding(vertical = 8.dp)) {
            Text(
                text = "Accesos Rápidos",
                style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.Bold),
                color = LorentinaTextPrimary,
                modifier = Modifier.padding(start = 20.dp, top = 12.dp, bottom = 8.dp)
            )

            AccessItem(icon = Icons.Outlined.CheckCircle, text = "Ver tareas completadas")
            HorizontalDivider(modifier = Modifier.padding(horizontal = 20.dp), color = Color.LightGray.copy(alpha = 0.3f))

            AccessItem(icon = Icons.Outlined.Paid, text = "Ver nómina")
            HorizontalDivider(modifier = Modifier.padding(horizontal = 20.dp), color = Color.LightGray.copy(alpha = 0.3f))

            AccessItem(icon = Icons.Outlined.Lock, text = "Cambiar contraseña")
        }
    }
}

@Composable
private fun AccessItem(icon: ImageVector, text: String) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .clickable(onClick = {})
            .padding(horizontal = 20.dp, vertical = 16.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Icon(imageVector = icon, contentDescription = null, tint = LorentinaBrown, modifier = Modifier.size(22.dp))
        Spacer(modifier = Modifier.width(16.dp))
        Text(
            text = text,
            style = MaterialTheme.typography.bodyMedium,
            color = LorentinaTextPrimary,
            modifier = Modifier.weight(1f)
        )
        Icon(
            imageVector = Icons.AutoMirrored.Filled.KeyboardArrowRight,
            contentDescription = null,
            tint = LorentinaTextSecondary
        )
    }
}

