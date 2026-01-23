package com.cristiancogollo.applorentinapg

import com.google.gson.annotations.SerializedName
import java.io.Serializable

data class OrdenProduccion(
    val id: Int,
    val numeroOrden: String,

    // Las fechas vienen como String ISO desde Node.js (ej: "2024-01-20T10:00:00.000Z")
    val fechaInicio: String,
    val estado: String,

    // --- DETALLE ---
    val referencia: String,
    val color: String,
    val fotoUrl: String?, // Puede ser nulo (?)
    val materiales: String,
    val observacion: String?, // Puede ser nulo (?)

    // --- DESTINO ---
    val destino: String,
    val clienteId: Int?, // Si es para Stock, esto será null

    // --- RESPONSABLES (IDs) ---
    val cortadorId: Int?,
    val armadorId: Int?,
    val costureroId: Int?,
    val soladorId: Int?,
    val emplantilladorId: Int?,

    // --- LA CURVA (Tallas) ---
    // Usamos @SerializedName por si acaso, aunque coinciden con el backend
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

    /**
     * FUNCIÓN ÚTIL:
     * Genera un texto resumen para mostrar en la tarjeta de la App.
     * Ejemplo de retorno: "35(2) - 37(1) - 40(5)"
     * Así no tienes que llenar la pantalla de ceros.
     */
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