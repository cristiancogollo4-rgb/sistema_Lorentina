package com.cristiancogollo.applorentinapg.model


data class NominaResponse(
    val totalGanado: Double,
    val detalle: List<DetalleNominaItem>
)

data class DetalleNominaItem(
    val id: Int,
    val numeroOrden: String,
    val referencia: String,
    val categoria: String? = null,
    val pares: Int,
    val precio: Double,
    val subtotal: Double,
    val fecha: String? = null
)
