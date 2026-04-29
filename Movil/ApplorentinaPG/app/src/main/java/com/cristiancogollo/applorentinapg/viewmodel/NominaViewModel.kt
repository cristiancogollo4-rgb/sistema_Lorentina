package com.cristiancogollo.applorentinapg.viewmodel



import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cristiancogollo.applorentinapg.model.NominaResponse
import com.cristiancogollo.applorentinapg.network.RetrofitClient
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import java.text.NumberFormat
import java.util.Locale



sealed class NominaUiState {
    object Loading : NominaUiState()
    data class Success(val datos: NominaResponse) : NominaUiState()
    data class Error(val mensaje: String) : NominaUiState()
}

class NominaViewModel : ViewModel() {
    private val api = RetrofitClient.apiService
    private val _uiState = MutableStateFlow<NominaUiState>(NominaUiState.Loading)
    val uiState: StateFlow<NominaUiState> = _uiState.asStateFlow()

    fun cargarNomina(userId: Int, userRol: String) {
        viewModelScope.launch {
            _uiState.value = NominaUiState.Loading
            try {
                val response = api.obtenerNomina(userId, normalizarRolNomina(userRol))
                if (response.isSuccessful && response.body() != null) {
                    _uiState.value = NominaUiState.Success(response.body()!!)
                } else {
                    _uiState.value = NominaUiState.Error("No se pudo calcular la nómina")
                }
            } catch (e: Exception) {
                _uiState.value = NominaUiState.Error("Error de conexión: ${e.message}")
            }
        }
    }

    // Función auxiliar para formatear dinero (Pesos Colombianos)
    fun formatoDinero(valor: Double): String {
        val format = NumberFormat.getCurrencyInstance(Locale("es", "CO"))
        format.maximumFractionDigits = 0
        return format.format(valor)
    }

    private fun normalizarRolNomina(userRol: String): String {
        return when (userRol.uppercase()) {
            "ARMADOR", "ARMADO" -> "ARMADO"
            "COSTURERO", "COSTURA" -> "COSTURA"
            "SOLADOR", "SOLADURA" -> "SOLADURA"
            "EMPLANTILLADOR", "EMPLANTILLADO" -> "EMPLANTILLADO"
            else -> userRol.uppercase()
        }
    }
}
