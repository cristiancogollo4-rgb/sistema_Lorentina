package com.cristiancogollo.applorentinapg

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.material3.MaterialTheme

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent {
            MaterialTheme {
                // Aquí llamamos a nuestro nuevo archivo de navegación
                AppNavigation()
            }
        }
    }
}