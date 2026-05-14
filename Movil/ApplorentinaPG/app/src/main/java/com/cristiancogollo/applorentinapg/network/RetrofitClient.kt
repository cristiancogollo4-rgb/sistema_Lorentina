package com.cristiancogollo.applorentinapg.network

import com.cristiancogollo.applorentinapg.model.LoginRequest
import com.cristiancogollo.applorentinapg.model.NominaResponse
import com.cristiancogollo.applorentinapg.model.OrdenProduccion
import com.cristiancogollo.applorentinapg.model.UserResponse
import retrofit2.Response
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.PUT
import retrofit2.http.Path
import retrofit2.http.Query


data class TerminarTareaRequest(
    val ordenId: Int,
    val rol: String,
    val empleadoId: Int? = null
)

interface ApiService {

    @POST("api/login")
    suspend fun login(@Body request: LoginRequest): Response<UserResponse>

    // 2. Endpoint para Tareas (Ya lo tenías)
    @GET("api/mis-tareas/{empleadoId}")
    suspend fun obtenerMisTareas(@Path("empleadoId") empleadoId: Int): Response<List<OrdenProduccion>>
    // Busca la función obtenerNomina y cámbiala por esta:
    @GET("api/nomina/{id}")
    suspend fun obtenerNomina(@Path("id") id: Int, @Query("rol") rol: String): Response<NominaResponse>
    @GET("api/usuarios/{id}")
    suspend fun obtenerPerfil(@Path("id") id: Int): Response<UserResponse>
    @POST("api/produccion/terminar-tarea")
    suspend fun terminarTarea(@Body request: TerminarTareaRequest): Response<Any>

}

object RetrofitClient {
    // ⚠️ La URL ahora se lee dinámicamente desde local.properties usando BuildConfig
    private val BASE_URL = com.cristiancogollo.applorentinapg.BuildConfig.BASE_URL

    // Unificamos el nombre a 'apiService' para que coincida con el Repositorio
    val apiService: ApiService by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)
    }
}
