package com.cristiancogollo.applorentinapg

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.AttachMoney
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.cristiancogollo.applorentinapg.model.DetalleNominaItem
import com.cristiancogollo.applorentinapg.model.PagoNominaItem
import com.cristiancogollo.applorentinapg.screens.BottomNavigationBar
import com.cristiancogollo.applorentinapg.screens.LorentinaBg
import com.cristiancogollo.applorentinapg.screens.LorentinaBrown
import com.cristiancogollo.applorentinapg.screens.LorentinaGreen
import com.cristiancogollo.applorentinapg.viewmodel.NominaUiState
import com.cristiancogollo.applorentinapg.viewmodel.NominaViewModel

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun NominaScreen(
    navController: NavController,
    userId: Int,
    userName: String,
    userRol: String,
    viewModel: NominaViewModel = viewModel()
) {
    LaunchedEffect(Unit) {
        viewModel.cargarNomina(userId, userRol)
    }

    val uiState by viewModel.uiState.collectAsState()

    Scaffold(
        containerColor = LorentinaBg,
        topBar = {
            CenterAlignedTopAppBar(
                title = { Text("Mi Nomina", fontWeight = FontWeight.Bold) },
                navigationIcon = {
                    IconButton(onClick = { navController.popBackStack() }) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Atras")
                    }
                },
                actions = {
                    IconButton(onClick = { viewModel.cargarNomina(userId, userRol) }) {
                        Icon(Icons.Default.Refresh, contentDescription = "Recargar")
                    }
                },
                colors = TopAppBarDefaults.centerAlignedTopAppBarColors(containerColor = LorentinaBg)
            )
        },
        bottomBar = { BottomNavigationBar(navController, userId, userName, userRol) }
    ) { padding ->
        Box(modifier = Modifier.padding(padding).fillMaxSize()) {
            when (val state = uiState) {
                is NominaUiState.Loading -> {
                    CircularProgressIndicator(modifier = Modifier.align(Alignment.Center), color = LorentinaBrown)
                }
                is NominaUiState.Error -> {
                    Text(
                        text = state.mensaje,
                        color = Color.Red,
                        modifier = Modifier.align(Alignment.Center).padding(24.dp)
                    )
                }
                is NominaUiState.Success -> {
                    LazyColumn(
                        modifier = Modifier.fillMaxSize().padding(16.dp),
                        verticalArrangement = Arrangement.spacedBy(12.dp)
                    ) {
                        item {
                            Card(
                                colors = CardDefaults.cardColors(containerColor = LorentinaBrown),
                                shape = RoundedCornerShape(20.dp),
                                modifier = Modifier.fillMaxWidth().height(160.dp),
                                elevation = CardDefaults.cardElevation(8.dp)
                            ) {
                                Column(
                                    modifier = Modifier.fillMaxSize().padding(18.dp),
                                    verticalArrangement = Arrangement.Center,
                                    horizontalAlignment = Alignment.CenterHorizontally
                                ) {
                                    Text("Esta semana", color = Color.White.copy(alpha = 0.8f))
                                    Spacer(modifier = Modifier.height(8.dp))
                                    Text(
                                        text = viewModel.formatoDinero(state.datos.totalGanado),
                                        color = Color.White,
                                        fontWeight = FontWeight.Bold,
                                        fontSize = 36.sp
                                    )
                                    Text(
                                        text = "${state.datos.periodo?.inicio ?: ""} a ${state.datos.periodo?.fin ?: ""} - pago sabado",
                                        color = Color.White.copy(alpha = 0.6f),
                                        style = MaterialTheme.typography.labelSmall
                                    )
                                }
                            }
                        }

                        item {
                            Text(
                                text = "Lo que hice esta semana",
                                style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.Bold),
                                color = Color.Black
                            )
                        }

                        if (state.datos.detalle.isEmpty()) {
                            item {
                                Text("Aun no tienes tareas o ventas liquidadas esta semana.", color = Color.Gray)
                            }
                        } else {
                            items(state.datos.detalle) { item ->
                                NominaItemCard(item, viewModel)
                            }
                        }

                        item {
                            Spacer(modifier = Modifier.height(8.dp))
                            Text(
                                text = "Historial de pagos",
                                style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.Bold),
                                color = Color.Black
                            )
                        }

                        if (state.datos.historial.isEmpty()) {
                            item {
                                Text("Aun no tienes pagos anteriores registrados.", color = Color.Gray)
                            }
                        } else {
                            items(state.datos.historial) { pago ->
                                PagoHistorialCard(pago, viewModel)
                            }
                        }

                        item { Spacer(modifier = Modifier.height(40.dp)) }
                    }
                }
            }
        }
    }
}

@Composable
fun NominaItemCard(item: DetalleNominaItem, viewModel: NominaViewModel) {
    Card(
        colors = CardDefaults.cardColors(containerColor = Color.White),
        shape = RoundedCornerShape(12.dp),
        elevation = CardDefaults.cardElevation(2.dp)
    ) {
        Row(
            modifier = Modifier.fillMaxWidth().padding(16.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Surface(
                color = LorentinaGreen.copy(alpha = 0.1f),
                shape = RoundedCornerShape(8.dp),
                modifier = Modifier.size(40.dp)
            ) {
                Icon(
                    imageVector = Icons.Default.AttachMoney,
                    contentDescription = null,
                    tint = LorentinaGreen,
                    modifier = Modifier.padding(8.dp)
                )
            }

            Spacer(modifier = Modifier.width(12.dp))

            Column(modifier = Modifier.weight(1f)) {
                val categoriaTexto = item.categoria?.takeIf { it.isNotBlank() }?.let { " ($it)" } ?: ""
                val titulo = if (item.tipo == "VENTA") {
                    "Venta ${item.cliente ?: ""}".trim()
                } else {
                    "${item.referencia ?: item.numeroOrden ?: "Produccion"}$categoriaTexto"
                }
                Text(text = titulo, fontWeight = FontWeight.Bold, color = Color.Black)
                Text(
                    text = "${item.pares} pares x ${viewModel.formatoDinero(item.valorUnitario ?: item.precio ?: 0.0)}",
                    style = MaterialTheme.typography.bodySmall,
                    color = Color.Gray
                )
            }

            Text(
                text = viewModel.formatoDinero(item.subtotal),
                fontWeight = FontWeight.Bold,
                color = LorentinaBrown,
                style = MaterialTheme.typography.bodyLarge
            )
        }
    }
}

@Composable
fun PagoHistorialCard(pago: PagoNominaItem, viewModel: NominaViewModel) {
    Card(
        colors = CardDefaults.cardColors(containerColor = Color.White),
        shape = RoundedCornerShape(12.dp),
        elevation = CardDefaults.cardElevation(2.dp)
    ) {
        Row(
            modifier = Modifier.fillMaxWidth().padding(16.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Column(modifier = Modifier.weight(1f)) {
                Text(
                    text = "${pago.periodoInicio} a ${pago.periodoFin}",
                    fontWeight = FontWeight.Bold,
                    color = Color.Black
                )
                Text(
                    text = "Pagado el ${pago.fechaPago} - ${pago.totalPares} pares",
                    style = MaterialTheme.typography.bodySmall,
                    color = Color.Gray
                )
            }
            Text(
                text = viewModel.formatoDinero(pago.totalPagado),
                fontWeight = FontWeight.Bold,
                color = LorentinaBrown
            )
        }
    }
}
