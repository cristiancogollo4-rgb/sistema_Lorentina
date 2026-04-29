package com.cristiancogollo.applorentinapg.model

import java.io.Serializable

data class OrdenProduccion(
    val id: Int,
    val numeroOrden: String,

    // Laravel puede omitir algunos campos heredados del backend anterior.
    val fechaInicio: String? = null,
    val estado: String,

    val referencia: String,
    val color: String,
    val categoria: String? = null,

    val precioCorte: Double = 0.0,
    val precioArmado: Double = 0.0,
    val precioCostura: Double = 0.0,
    val precioSoladura: Double = 0.0,
    val precioEmplantillado: Double = 0.0,
    val precioPactado: Double = 0.0,
    val fotoUrl: String? = null,
    val materiales: String? = null,
    val observacion: String? = null,
    val destino: String? = null,
    val clienteId: Int? = null,
    val cortadorId: Int?,
    val armadorId: Int?,
    val costureroId: Int?,
    val soladorId: Int?,
    val emplantilladorId: Int?,
    val t34: Int = 0,
    val t35: Int = 0,
    val t36: Int = 0,
    val t37: Int = 0,
    val t38: Int = 0,
    val t39: Int = 0,
    val t40: Int = 0,
    val t41: Int = 0,
    val t42: Int = 0,
    val t43: Int = 0,
    val t44: Int = 0,
    val totalPares: Int

) : Serializable {
    fun obtenerResumenCurva(): String {
        val lista = mutableListOf<String>()
        if (t34 > 0) lista.add("34($t34)")
        if (t35 > 0) lista.add("35($t35)")
        if (t36 > 0) lista.add("36($t36)")
        if (t37 > 0) lista.add("37($t37)")
        if (t38 > 0) lista.add("38($t38)")
        if (t39 > 0) lista.add("39($t39)")
        if (t40 > 0) lista.add("40($t40)")
        if (t41 > 0) lista.add("41($t41)")
        if (t42 > 0) lista.add("42($t42)")
        if (t43 > 0) lista.add("43($t43)")
        if (t44 > 0) lista.add("44($t44)")

        return if (lista.isNotEmpty()) lista.joinToString(" - ") else "Sin tallas"
    }
}
