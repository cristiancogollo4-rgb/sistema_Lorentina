package com.cristiancogollo.applorentinapg.screens

import androidx.compose.material.icons.Icons
// IMPORTANTE: Importar la versión AutoMirrored
import androidx.compose.material.icons.automirrored.filled.Assignment
import androidx.compose.material.icons.automirrored.outlined.Assignment
// O si la versión Outlined no es mirrored en tu versión, mantén la normal:
import androidx.compose.material.icons.outlined.Assignment
import androidx.compose.material.icons.filled.Home
import androidx.compose.material.icons.outlined.Home
import androidx.compose.material.icons.outlined.Paid
import androidx.compose.material.icons.outlined.Person
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.graphics.Color
import androidx.navigation.NavController
import androidx.navigation.NavGraph.Companion.findStartDestination
import androidx.navigation.compose.currentBackStackEntryAsState

// Colores (asegúrate de tenerlos definidos o impórtalos)
val LorentinaBrown = Color(0xFF5D4037)
val LorentinaBeige = Color(0xFFD7CCC8)
val LorentinaBg = Color(0xFFF5F5F1)
val LorentinaGreen = Color(0xFF388E3C)
val LorentinaRed = Color(0xFFD32F2F)
val LorentinaTextPrimary = Color(0xFF212121)
val LorentinaTextSecondary = Color(0xFF757575)

@Composable
fun BottomNavigationBar(navController: NavController, userId: Int, userName: String, userRol: String) {
    NavigationBar(containerColor = Color.White) {
        val navBackStackEntry by navController.currentBackStackEntryAsState()
        val currentRoute = navBackStackEntry?.destination?.route

        // CREACIÓN ÚNICA DE ARGUMENTOS: Si esto está bien aquí, está bien en todos los botones
        // Usamos variables locales para asegurar que no se mezclen durante la recomposición
        val cId = userId
        val cName = userName
        val cRol = userRol
        val args = "$cId/$cName/$cRol"

        // 1. INICIO
        NavigationBarItem(
            icon = { Icon(if (currentRoute?.startsWith("home") == true) Icons.Filled.Home else Icons.Outlined.Home, "Inicio") },
            label = { Text("Inicio") },
            selected = currentRoute?.startsWith("home") == true,
            onClick = {
                navController.navigate("home/$args") {
                    popUpTo(navController.graph.findStartDestination().id) { saveState = true }
                    launchSingleTop = true
                    // restoreState = true // Comentado para limpiar errores de caché
                }
            },
            colors = NavigationBarItemDefaults.colors(selectedIconColor = LorentinaBrown, indicatorColor = LorentinaBeige)
        )

        // 2. TAREAS
        NavigationBarItem(
            icon = { Icon(Icons.AutoMirrored.Filled.Assignment, "Tareas") },
            label = { Text("Tareas") },
            selected = currentRoute?.contains("mis_tareas") == true,
            onClick = {
                navController.navigate("mis_tareas_screen/$args") {
                    popUpTo(navController.graph.findStartDestination().id) { saveState = true }
                    launchSingleTop = true
                }
            },
            colors = NavigationBarItemDefaults.colors(selectedIconColor = LorentinaBrown, indicatorColor = LorentinaBeige)
        )

        // 3. NÓMINA
        val nominaArgs = "$userId/$userName/$userRol"
        NavigationBarItem(
            icon = { Icon(Icons.Outlined.Paid, contentDescription = "Nómina") },
            label = { Text("Nómina") },
            selected = currentRoute?.startsWith("nomina") == true,
            onClick = {
                // Forzamos el orden correcto: ID -> NAME -> ROL
                navController.navigate("nomina_screen/$nominaArgs") {
                    popUpTo(navController.graph.findStartDestination().id) {
                        saveState = true
                    }
                    launchSingleTop = true
                    // Importante: No uses restoreState hasta que estemos seguros de que la ruta es limpia
                }
            }
        )

        // 4. PERFIL
        NavigationBarItem(
            icon = { Icon(Icons.Outlined.Person, "Perfil") },
            label = { Text("Perfil") },
            selected = currentRoute?.startsWith("profile") == true,
            onClick = {
                navController.navigate("profile/$args") {
                    popUpTo(navController.graph.findStartDestination().id) { saveState = true }
                    launchSingleTop = true
                }
            },
            colors = NavigationBarItemDefaults.colors(selectedIconColor = LorentinaBrown, indicatorColor = LorentinaBeige)
        )
    }
}