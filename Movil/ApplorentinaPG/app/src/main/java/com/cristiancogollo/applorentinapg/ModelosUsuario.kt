package com.cristiancogollo.applorentinapg


// Esta es la clase que faltaba:
data class LoginRequest(
    val username: String,
    val password: String
)

// Esta también la necesitarás para la respuesta del servidor:
data class UserResponse(
    val id: Int,
    val nombre: String,
    val apellido: String?, // Puede venir nulo
    val username: String,
    val rol: String?,
    val cedula: String?,
    val telefono: String?,
    val activo: Boolean = true
)