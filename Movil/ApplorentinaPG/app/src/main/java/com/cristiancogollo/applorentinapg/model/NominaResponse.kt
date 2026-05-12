package com.cristiancogollo.applorentinapg.model


data class NominaResponse(
    val totalGanado: Double,
    val totalPares: Int = 0,
    val totalTareas: Int = 0,
    val totalVentas: Int = 0,
    val periodo: PeriodoNomina? = null,
    val detalle: List<DetalleNominaItem>,
    val historial: List<PagoNominaItem> = emptyList()
)

data class PeriodoNomina(
    val inicio: String,
    val fin: String,
    val diaPago: String? = null,
    val fechaPago: String? = null
)

data class DetalleNominaItem(
    val id: Int,
    val tipo: String? = null,
    val numeroOrden: String? = null,
    val referencia: String? = null,
    val color: String? = null,
    val categoria: String? = null,
    val tarea: String? = null,
    val cliente: String? = null,
    val tipoCliente: String? = null,
    val pares: Int,
    val precio: Double? = null,
    val valorUnitario: Double? = null,
    val subtotal: Double,
    val totalVenta: Double? = null,
    val fecha: String? = null
)

data class PagoNominaItem(
    val id: Int,
    val periodoInicio: String,
    val periodoFin: String,
    val fechaPago: String,
    val estado: String,
    val totalPares: Int,
    val totalTareas: Int,
    val totalPagado: Double,
    val detalle: List<DetalleNominaItem> = emptyList(),
    val notas: String? = null
)
