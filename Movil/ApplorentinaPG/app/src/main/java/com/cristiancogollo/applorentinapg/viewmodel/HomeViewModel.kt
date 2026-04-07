package com.cristiancogollo.applorentinapg.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cristiancogollo.applorentinapg.model.OrdenProduccion
import com.cristiancogollo.applorentinapg.repository.HomeRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

// Definimos los estados de la UI
sealed class HomeUiState {
    object Loading : HomeUiState()
    data class Success(
        val nombreUsuario: String,
        val pendientesCount: Int,
        val enProcesoCount: Int,
        val completadasCount: Int,
        val tareas: List<OrdenProduccion>
    ) : HomeUiState()
    data class Error(val mensaje: String) : HomeUiState()
}

class HomeViewModel : ViewModel() {

    // Instancia del repositorio
    private val repository = HomeRepository()

    private val _uiState = MutableStateFlow<HomeUiState>(HomeUiState.Loading)
    val uiState: StateFlow<HomeUiState> = _uiState.asStateFlow()

    fun cargarDatos(usuarioId: Int) {
        viewModelScope.launch {
            _uiState.value = HomeUiState.Loading
            try {
                // 1. Llamada al Repositorio (Ahora devuelve Response<...>)
                val response = repository.getTareasAsignadas(usuarioId)

                // 2. Verificar si la respuesta fue exitosa
                if (response.isSuccessful && response.body() != null) {
                    val tareas = response.body()!! // Sacamos la lista de la "caja"

                    // 3. Calculamos los contadores
                    // (Nota: Ajusta los filtros según los estados reales de tu BD)
                    val pendientes = tareas.count { it.estado == "EN_CORTE" || it.estado == "ASIGNADA" }
                    val enProceso = 0
                    val completadas = tareas.count { it.estado == "TERMINADO" }

                    // 4. Emitimos el estado de éxito
                    _uiState.value = HomeUiState.Success(
                        nombreUsuario = "Usuario", // Este dato podría venir de otro lado o pasarse por parámetro
                        pendientesCount = pendientes,
                        enProcesoCount = enProceso,
                        completadasCount = completadas,
                        tareas = tareas
                    )
                } else {
                    // Error del servidor (ej. 404, 500)
                    _uiState.value = HomeUiState.Error("Error al cargar tareas: Código ${response.code()}")
                }
            } catch (e: Exception) {
                // Error de conexión
                _uiState.value = HomeUiState.Error("Error de conexión: ${e.message}")
            }
        }
    }
}