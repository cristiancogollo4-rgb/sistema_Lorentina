package com.cristiancogollo.applorentinapg

import android.util.Log
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.CheckCircle
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material.icons.outlined.Assignment
import androidx.compose.material.icons.outlined.ShoppingBag
import androidx.compose.material.icons.outlined.TaskAlt
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavController
import com.cristiancogollo.applorentinapg.model.OrdenProduccion
import com.cristiancogollo.applorentinapg.network.RetrofitClient
import com.cristiancogollo.applorentinapg.screens.BottomNavigationBar
import com.cristiancogollo.applorentinapg.screens.LorentinaBeige
import com.cristiancogollo.applorentinapg.screens.LorentinaBg
import com.cristiancogollo.applorentinapg.screens.LorentinaBrown
import com.cristiancogollo.applorentinapg.screens.LorentinaGreen
import com.cristiancogollo.applorentinapg.screens.LorentinaTextPrimary
import com.cristiancogollo.applorentinapg.screens.LorentinaTextSecondary
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable

fun Pantalla(navController: NavController, userId: Int, userName: String, userRol: String) {
    Log.d("NAV", "Nombre: $userName, Rol: $userRol")
    val scope = rememberCoroutineScope()
    var tareas by remember { mutableStateOf<List<OrdenProduccion>>(emptyList()) }
    var isLoading by remember { mutableStateOf(true) }

    val tituloModulo = when (userRol.uppercase()) {
        "ARMADO" -> "Módulo de Armado"
        "COSTURA" -> "Módulo de Costura"
        "SOLADURA" -> "Módulo de Soladura"
        "EMPLANTILLADO" -> "Módulo de Emplantillado"
        else -> "Módulo de Corte"
    }

    fun cargarDatos() {
        scope.launch {
            isLoading = true
            try {
                val response = RetrofitClient.apiService.obtenerMisTareas(userId)
                if (response.isSuccessful) {
                    tareas = response.body() ?: emptyList()
                }
            } catch (e: Exception) { } finally {
                isLoading = false
            }
        }
    }

    LaunchedEffect(Unit) { cargarDatos() }

    Scaffold(
        containerColor = LorentinaBg,
        topBar = {
            CenterAlignedTopAppBar(
                title = {
                    Text(
                        text = tituloModulo,
                        style = MaterialTheme.typography.titleLarge.copy(fontWeight = FontWeight.Bold),
                        color = LorentinaTextPrimary
                    )
                },
                actions = {
                    IconButton(onClick = { cargarDatos() }) {
                        Icon(Icons.Default.Refresh, contentDescription = null, tint = LorentinaTextPrimary)
                    }
                },
                colors = TopAppBarDefaults.centerAlignedTopAppBarColors(containerColor = LorentinaBg)
            )
        },
        bottomBar = { BottomNavigationBar(navController, userId, userName, userRol) }
    ) { paddingValues ->
        Box(modifier = Modifier.padding(paddingValues).fillMaxSize()) {
            if (isLoading) {
                CircularProgressIndicator(modifier = Modifier.align(Alignment.Center), color = LorentinaBrown)
            } else {
                LazyColumn(
                    contentPadding = PaddingValues(16.dp),
                    verticalArrangement = Arrangement.spacedBy(16.dp)
                ) {
                    // --- SECCIÓN DE SALUDO Y RESUMEN ---
                    item {
                        Column {
                            Text(
                                text = "¡Hola, $userName!",
                                style = MaterialTheme.typography.headlineSmall.copy(fontWeight = FontWeight.Bold),
                                color = LorentinaTextPrimary
                            )
                            Text(
                                text = "Este es el resumen de tu actividad de hoy.",
                                style = MaterialTheme.typography.bodyMedium,
                                color = LorentinaTextSecondary
                            )
                            Spacer(modifier = Modifier.height(20.dp))

                            // FILA DE TARJETAS DE ESTADO
                            Row(
                                modifier = Modifier.fillMaxWidth(),
                                horizontalArrangement = Arrangement.spacedBy(12.dp)
                            ) {
                                // Pendientes (Filtramos las que no están terminadas)
                                val pendientes = tareas.count { it.estado != "TERMINADO" }
                                TarjetaEstadoResumen(
                                    titulo = "Pendientes",
                                    cantidad = pendientes.toString(),
                                    icono = Icons.Outlined.Assignment,
                                    colorIcono = Color(0xFFE57373),
                                    modifier = Modifier.weight(1f)
                                )
                                // Cumplidas (Filtramos las terminadas)
                                val cumplidas = tareas.count { it.estado == "TERMINADO" }
                                TarjetaEstadoResumen(
                                    titulo = "Cumplidas",
                                    cantidad = cumplidas.toString(),
                                    icono = Icons.Outlined.TaskAlt,
                                    colorIcono = LorentinaGreen,
                                    modifier = Modifier.weight(1f)
                                )
                            }
                        }
                    }

                    item {
                        Text(
                            text = "Lista de Tareas",
                            style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.Bold),
                            color = LorentinaTextPrimary,
                            modifier = Modifier.padding(top = 8.dp)
                        )
                    }

                    // --- LISTA DE TAREAS (SIN BOTÓN) ---
                    items(tareas) { orden ->
                        TaskCardInformativa(orden)
                    }
                }
            }
        }
    }
}

@Composable
fun TarjetaEstadoResumen(
    titulo: String,
    cantidad: String,
    icono: ImageVector,
    colorIcono: Color,
    modifier: Modifier = Modifier
) {
    Card(
        modifier = modifier,
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
        shape = RoundedCornerShape(16.dp)
    ) {
        Column(modifier = Modifier.padding(16.dp)) {
            Icon(imageVector = icono, contentDescription = null, tint = colorIcono)
            Spacer(modifier = Modifier.height(8.dp))
            Text(text = cantidad, style = MaterialTheme.typography.headlineMedium.copy(fontWeight = FontWeight.Bold), color = LorentinaTextPrimary)
            Text(text = titulo, style = MaterialTheme.typography.labelMedium, color = LorentinaTextSecondary)
        }
    }
}

@Composable
fun TaskCardInformativa(orden: OrdenProduccion) {
    Card(
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
        shape = RoundedCornerShape(16.dp),
        modifier = Modifier.fillMaxWidth()
    ) {
        Column(modifier = Modifier.padding(20.dp)) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Surface(
                        color = LorentinaBrown.copy(alpha = 0.1f),
                        shape = CircleShape,
                        modifier = Modifier.size(40.dp)
                    ) {
                        Icon(Icons.Outlined.ShoppingBag, null, tint = LorentinaBrown, modifier = Modifier.padding(8.dp))
                    }
                    Spacer(modifier = Modifier.width(12.dp))
                    Column {
                        Text("Orden #${orden.numeroOrden}", style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.Bold))
                        Text(orden.categoria ?: "Producción", style = MaterialTheme.typography.bodySmall, color = LorentinaTextSecondary)
                    }
                }

                // Badge de Pares
                Surface(
                    color = LorentinaBeige.copy(alpha = 0.3f),
                    shape = RoundedCornerShape(50),
                ) {
                    Text(
                        text = "${orden.totalPares} Pares",
                        style = MaterialTheme.typography.labelSmall.copy(fontWeight = FontWeight.Bold),
                        color = LorentinaBrown,
                        modifier = Modifier.padding(horizontal = 10.dp, vertical = 6.dp)
                    )
                }
            }

            HorizontalDivider(modifier = Modifier.padding(vertical = 16.dp), color = Color.LightGray.copy(alpha = 0.3f))

            InfoRowStyled("Referencia", orden.referencia)
            InfoRowStyled("Color", orden.color)
            InfoRowStyled("Estado", orden.estado ?: "Asignada")
        }
    }
}
@Composable
fun InfoRowStyled(label: String, value: String) {
    Row(
        modifier = Modifier.fillMaxWidth().padding(vertical = 2.dp),
        horizontalArrangement = Arrangement.SpaceBetween
    ) {
        Text(label, color = LorentinaTextSecondary, style = MaterialTheme.typography.bodyMedium)
        Text(value, fontWeight = FontWeight.Bold, style = MaterialTheme.typography.bodyMedium)
    }
}
