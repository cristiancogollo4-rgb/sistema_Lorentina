package com.cristiancogollo.applorentinapg.network

import com.cristiancogollo.applorentinapg.LoginRequest
import com.cristiancogollo.applorentinapg.OrdenProduccion
import com.cristiancogollo.applorentinapg.UserResponse
import retrofit2.Response
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Path

interface ApiService {
    // 1. Endpoint para Login (FALTABA ESTO)
    // Usamos 'suspend' para trabajar con Corrutinas
    // Usamos Response<UserResponse> para poder leer códigos de error (ej. 401)
    @POST("api/login")
    suspend fun login(@Body request: LoginRequest): Response<UserResponse>

    // 2. Endpoint para Tareas (Ya lo tenías)
    @GET("api/mis-tareas/{empleadoId}")
    suspend fun obtenerMisTareas(@Path("empleadoId") empleadoId: Int): List<OrdenProduccion>

    @GET("api/usuarios/{id}")
    suspend fun obtenerPerfil(@Path("id") id: Int): Response<UserResponse>
}

object RetrofitClient {
    // ⚠️ CAMBIA ESTO POR TU IP SI USAS CELULAR REAL
    private const val BASE_URL = "http://10.0.2.2:4000/"

    // Unificamos el nombre a 'apiService' para que coincida con el Repositorio
    val apiService: ApiService by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)
    }
}