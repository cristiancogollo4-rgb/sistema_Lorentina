package com.cristiancogollo.applorentinapg

// Archivo: ApiService.kt
import retrofit2.Call
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Path

interface ApiService {
    @POST("/api/login") // Debes crear esta ruta en tu backend Node.js
    fun login(@Body request: LoginRequest): Call<UserResponse>

    // 👇 AGREGA ESTA PARTE NUEVA 👇
    @GET("/api/mis-tareas/{empleadoId}")
    fun obtenerTareas(@Path("empleadoId") id: Int): Call<List<OrdenProduccion>>
}