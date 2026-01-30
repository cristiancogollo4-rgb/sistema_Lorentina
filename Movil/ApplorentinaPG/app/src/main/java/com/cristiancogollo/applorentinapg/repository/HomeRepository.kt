package com.cristiancogollo.applorentinapg.repository


import com.cristiancogollo.applorentinapg.OrdenProduccion
import com.cristiancogollo.applorentinapg.network.RetrofitClient



class HomeRepository {
    private val api = RetrofitClient.apiService

    suspend fun getTareasAsignadas(empleadoId: Int): List<OrdenProduccion> {
        return api.obtenerMisTareas(empleadoId)
    }
}