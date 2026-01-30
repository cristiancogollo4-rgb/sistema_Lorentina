package com.cristiancogollo.applorentinapg.viewmodel


import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cristiancogollo.applorentinapg.OrdenProduccion
import com.cristiancogollo.applorentinapg.repository.HomeRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

// Estados posibles de la UI
sealed class HomeUiState {
    object Loading : HomeUiState()
    data class Success(
        val tareas: List<OrdenProduccion>,
        val pendientesCount: Int,
        val enProcesoCount: Int,
        val completadasCount: Int
    ) : HomeUiState()
    data class Error(val mensaje: String) : HomeUiState()
}

class HomeViewModel : ViewModel() {
    private val repository = HomeRepository()

    private val _uiState = MutableStateFlow<HomeUiState>(HomeUiState.Loading)
    val uiState: StateFlow<HomeUiState> = _uiState.asStateFlow()

    fun cargarDatos(usuarioId: Int) {
        viewModelScope.launch {
            _uiState.value = HomeUiState.Loading
            try {
                // 1. Llamamos al backend
                val listaOrdenes = repository.getTareasAsignadas(usuarioId)

                // 2. Calculamos contadores
                // Como el endpoint filtra solo 'EN_CORTE', todas son pendientes.
                val pendientes = listaOrdenes.size

                // Nota: Para obtener completadas, necesitarías otro endpoint en el backend
                val enProceso = 0
                val completadas = 0

                _uiState.value = HomeUiState.Success(
                    tareas = listaOrdenes,
                    pendientesCount = pendientes,
                    enProcesoCount = enProceso,
                    completadasCount = completadas
                )

            } catch (e: Exception) {
                _uiState.value = HomeUiState.Error("No se pudo conectar: ${e.message}")
            }
        }
    }
}