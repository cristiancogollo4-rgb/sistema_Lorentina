package com.cristiancogollo.applorentinapg.repository

import com.cristiancogollo.applorentinapg.model.OrdenProduccion
import com.cristiancogollo.applorentinapg.network.RetrofitClient
import retrofit2.Response // <--- IMPORTANTE: Necesitas importar esto

class HomeRepository {
    private val api = RetrofitClient.apiService

    suspend fun getTareasAsignadas(empleadoId: Int): Response<List<OrdenProduccion>> {
        return api.obtenerMisTareas(empleadoId)
    }
}