package com.cristiancogollo.applorentinapg



import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

@Composable
fun PantallaCortador(
    usuarioId: Int = 1, // Por ahora usamos el ID 1 por defecto para probar
    onLogout: () -> Unit = {}
) {
    // Estado para guardar las órdenes que vienen del servidor
    var listaOrdenes by remember { mutableStateOf<List<OrdenProduccion>>(emptyList()) }
    var isLoading by remember { mutableStateOf(true) }
    var mensajeError by remember { mutableStateOf<String?>(null) }

    // Colores corporativos (mismos del Login)
    val colorHeader = Color(0xFF1565C0) // Azul
    val colorFondo = Color(0xFFF0F0F0)

    // EFECTO: Se ejecuta una vez al abrir la pantalla para cargar datos
    LaunchedEffect(Unit) {
        cargarTareas(usuarioId) { ordenes, error ->
            isLoading = false
            if (error != null) {
                mensajeError = error
            } else {
                listaOrdenes = ordenes ?: emptyList()
            }
        }
    }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(colorFondo)
    ) {
        // --- ENCABEZADO ---
        Box(
            modifier = Modifier
                .fillMaxWidth()
                .background(colorHeader)
                .padding(20.dp)
        ) {
            Column {
                Text(
                    text = "✂️ Área de Corte",
                    color = Color.White,
                    fontSize = 22.sp,
                    fontWeight = FontWeight.Bold
                )
                Text(
                    text = "Tareas pendientes: ${listaOrdenes.size}",
                    color = Color.White.copy(alpha = 0.8f),
                    fontSize = 14.sp
                )
            }
        }

        // --- CONTENIDO ---
        if (isLoading) {
            Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                CircularProgressIndicator(color = colorHeader)
            }
        } else if (mensajeError != null) {
            Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                Text(text = "Error: $mensajeError", color = Color.Red, modifier = Modifier.padding(20.dp))
            }
        } else {
            // LISTA DE TARJETAS
            LazyColumn(
                contentPadding = PaddingValues(16.dp),
                verticalArrangement = Arrangement.spacedBy(10.dp)
            ) {
                items(listaOrdenes) { orden ->
                    CardOrdenCorte(orden)
                }
            }
        }
    }
}

@Composable
fun CardOrdenCorte(orden: OrdenProduccion) {
    Card(
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 4.dp),
        shape = RoundedCornerShape(10.dp),
        modifier = Modifier.fillMaxWidth()
    ) {
        Column(modifier = Modifier.padding(16.dp)) {
            // Fila superior: Referencia y #Orden
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween
            ) {
                Text(
                    text = "REF: ${orden.referencia}",
                    fontWeight = FontWeight.Bold,
                    fontSize = 18.sp,
                    color = Color.Black
                )
                Text(
                    text = "#${orden.numeroOrden}",
                    fontSize = 12.sp,
                    color = Color.Gray
                )
            }

            Spacer(modifier = Modifier.height(5.dp))

            // Color y Materiales
            Text(text = "Color: ${orden.color}", fontWeight = FontWeight.SemiBold, color = Color(0xFF1565C0))
            Text(text = orden.materiales, fontSize = 13.sp, color = Color.DarkGray, lineHeight = 15.sp)

            Spacer(modifier = Modifier.height(10.dp))

            // LA CURVA DE TALLAS (Usamos la función auxiliar del modelo)
            ContainerCurva(orden.obtenerResumenCurva())

            Spacer(modifier = Modifier.height(10.dp))

            Button(
                onClick = { /* Lógica para terminar tarea */ },
                colors = ButtonDefaults.buttonColors(containerColor = Color(0xFF2E7D32)), // Verde
                modifier = Modifier.fillMaxWidth().height(40.dp)
            ) {
                Text("✅ Terminar Lote")
            }
        }
    }
}

@Composable
fun ContainerCurva(textoCurva: String) {
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .background(Color(0xFFE3F2FD), RoundedCornerShape(5.dp))
            .padding(8.dp)
    ) {
        Column {
            Text(text = "Curva a cortar:", fontSize = 10.sp, color = Color.Gray)
            Text(text = textoCurva, fontWeight = FontWeight.Bold, fontSize = 14.sp)
        }
    }
}

// Función auxiliar para llamar a Retrofit
fun cargarTareas(userId: Int, onResult: (List<OrdenProduccion>?, String?) -> Unit) {
    RetrofitClient.instance.obtenerTareas(userId).enqueue(object : Callback<List<OrdenProduccion>> {
        override fun onResponse(call: Call<List<OrdenProduccion>>, response: Response<List<OrdenProduccion>>) {
            if (response.isSuccessful) {
                onResult(response.body(), null)
            } else {
                onResult(null, "Error servidor: ${response.code()}")
            }
        }
        override fun onFailure(call: Call<List<OrdenProduccion>>, t: Throwable) {
            onResult(null, "Fallo conexión: ${t.message}")
        }
    })
}