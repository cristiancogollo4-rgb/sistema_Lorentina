package com.cristiancogollo.applorentinapg

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.tooling.preview.Preview
import com.cristiancogollo.applorentinapg.ui.theme.ApplorentinaPGTheme

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        // En tu MainActivity.kt

        setContent {
            var currentScreen by remember { mutableStateOf("LOGIN") }
            var userRole by remember { mutableStateOf("") }

            if (currentScreen == "LOGIN") {
                LorentinaLoginScreen(
                    onLoginSuccess = { rolDetectado ->
                        userRole = rolDetectado
                        // Navegamos al dashboard correspondiente según el rol que vino de la Base de Datos
                        when (rolDetectado) {
                            "ADMIN" -> currentScreen = "HOME_ADMIN"
                            "CORTE" -> currentScreen = "HOME_CORTADOR"
                            "VENTAS" -> currentScreen = "HOME_VENDEDOR"
                            else -> currentScreen = "HOME_GENERICO"
                        }
                    }
                )
            } else {
                // Mostrar la pantalla correspondiente
                when (currentScreen) {
                    "HOME_ADMIN" -> Text("Pantalla del Jefe") // Reemplaza con tu Composable real
                    "HOME_CORTADOR" -> PantallaCortador() // La que creamos antes con las tareas
                    "HOME_VENDEDOR" -> Text("Pantalla de Ventas")
                    else -> Text("Rol desconocido: $userRole")
                }
            }
        }
    }
}

@Composable
fun Greeting(name: String, modifier: Modifier = Modifier) {
    Text(
        text = "Hello $name!",
        modifier = modifier
    )
}

@Preview(showBackground = true)
@Composable
fun GreetingPreview() {
    ApplorentinaPGTheme {
        Greeting("Android")
    }
}