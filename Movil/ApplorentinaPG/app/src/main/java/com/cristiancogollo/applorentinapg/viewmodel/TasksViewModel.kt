package com.cristiancogollo.applorentinapg.viewmodel


import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cristiancogollo.applorentinapg.model.OrdenProduccion
import com.cristiancogollo.applorentinapg.network.RetrofitClient
import com.cristiancogollo.applorentinapg.network.TerminarTareaRequest
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

// Estados de la pantalla
sealed class TasksUiState {
    object Loading : TasksUiState()
    data class Success(val tareas: List<OrdenProduccion>) : TasksUiState()
    data class Error(val mensaje: String) : TasksUiState()
    object Empty : TasksUiState() // Estado para cuando no hay tareas
}

class TasksViewModel : ViewModel() {

    private val api = RetrofitClient.apiService

    private val _uiState = MutableStateFlow<TasksUiState>(TasksUiState.Loading)
    val uiState: StateFlow<TasksUiState> = _uiState.asStateFlow()

    fun cargarTareas(userId: Int) {
        viewModelScope.launch {
            _uiState.value = TasksUiState.Loading
            try {
                // Llamamos al endpoint que ya tienes en el backend
                val response = api.obtenerMisTareas(userId)

                if (response.isSuccessful && response.body() != null) {
                    val lista = response.body()!!
                    if (lista.isEmpty()) {
                        _uiState.value = TasksUiState.Empty
                    } else {
                        _uiState.value = TasksUiState.Success(lista)
                    }
                } else {
                    _uiState.value = TasksUiState.Error("No se pudieron cargar las tareas")
                }
            } catch (e: Exception) {
                _uiState.value = TasksUiState.Error("Error de conexión: ${e.message}")
            }
        }
    }

    // NUEVA FUNCIÓN: CONFIRMAR CORTE Y ENVIAR A ARMADO
    // Reemplaza tu función confirmarTareaCorte por esta:

    fun confirmarTarea(ordenId: Int, userId: Int, rol: String) {
        viewModelScope.launch {
            android.util.Log.d("API_TEST", "Iniciando envío: Orden $ordenId, Rol $rol")
            try {
                val request = TerminarTareaRequest(ordenId, rol, userId)
                val response = api.terminarTarea(request)

                if (response.isSuccessful) {
                    android.util.Log.d("API_TEST", "✅ Servidor recibió la tarea correctamente")
                    cargarTareas(userId) // Recarga la lista para que desaparezca la tarea
                } else {
                    android.util.Log.e("API_TEST", "❌ Error del servidor: ${response.code()} - ${response.errorBody()?.string()}")
                    _uiState.value = TasksUiState.Error("El servidor rechazó la entrega")
                }
            } catch (e: Exception) {
                android.util.Log.e("API_TEST", "💥 Fallo de conexión: ${e.message}")
                _uiState.value = TasksUiState.Error("Error de conexión al servidor")
            }
        }
    }
}

