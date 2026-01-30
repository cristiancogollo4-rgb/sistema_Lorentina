package com.cristiancogollo.applorentinapg.viewmodel


import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cristiancogollo.applorentinapg.UserResponse
import com.cristiancogollo.applorentinapg.network.RetrofitClient
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

// Estados de la UI del Perfil
sealed class ProfileUiState {
    object Loading : ProfileUiState()
    data class Success(val usuario: UserResponse) : ProfileUiState()
    data class Error(val mensaje: String) : ProfileUiState()
}

class ProfileViewModel : ViewModel() {
    // Llamada directa a la API (si quieres usar Repositorio también es válido, pero así es más rápido)
    private val api = RetrofitClient.apiService

    private val _uiState = MutableStateFlow<ProfileUiState>(ProfileUiState.Loading)
    val uiState: StateFlow<ProfileUiState> = _uiState.asStateFlow()

    fun cargarPerfil(userId: Int) {
        viewModelScope.launch {
            _uiState.value = ProfileUiState.Loading
            try {
                val response = api.obtenerPerfil(userId)
                if (response.isSuccessful && response.body() != null) {
                    _uiState.value = ProfileUiState.Success(response.body()!!)
                } else {
                    _uiState.value = ProfileUiState.Error("Usuario no encontrado")
                }
            } catch (e: Exception) {
                _uiState.value = ProfileUiState.Error("Error de conexión: ${e.message}")
            }
        }
    }
}