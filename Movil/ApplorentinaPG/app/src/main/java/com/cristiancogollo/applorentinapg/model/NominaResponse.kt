package com.cristiancogollo.applorentinapg.model


data class NominaResponse(
    val empleadoId: String,
    val totalGanado: Double, // El gran total
    val detalle: List<DetalleNominaItem>
)

data class DetalleNominaItem(
    val id: Int,
    val numeroOrden: String,
    val referencia: String,
    val categoria: String,
    val pares: Int,
    val precio: Double,   // A cómo le pagaron el par
    val subtotal: Double, // pares * precio
    val fecha: String
)