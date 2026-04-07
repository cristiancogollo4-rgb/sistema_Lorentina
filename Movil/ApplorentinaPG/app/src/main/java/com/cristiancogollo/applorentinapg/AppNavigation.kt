package com.cristiancogollo.applorentinapg

import android.util.Log
import androidx.compose.runtime.Composable
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import androidx.navigation.navArgument
import com.cristiancogollo.applorentinapg.screens.MisTareasScreen

@Composable
fun AppNavigation() {
    val navController = rememberNavController()

    NavHost(
        navController = navController,
        startDestination = "login"
    ) {
        // 1. LOGIN
        composable("login") {
            LorentinaLoginScreen(
                onLoginSuccess = { usuario ->
                    // Forzamos el orden exacto: ID, NOMBRE, ROL
                    val id = usuario.id
                    val nombre = usuario.nombre
                    val rol = usuario.rol ?: "SIN_ROL"

                    Log.d("NAV_DEBUG", "Login exitoso -> Navigating with Name: $nombre, Rol: $rol")

                    navController.navigate("home/$id/$nombre/$rol") {
                        popUpTo("login") { inclusive = true }
                    }
                }
            )
        }

        // 2. HOME
        composable(
            route = "home/{userId}/{userName}/{userRol}",
            arguments = listOf(
                navArgument("userId") { type = NavType.IntType },
                navArgument("userName") { type = NavType.StringType },
                navArgument("userRol") { type = NavType.StringType }
            )
        ) { backStackEntry ->
            val id = backStackEntry.arguments?.getInt("userId") ?: 0
            val name = backStackEntry.arguments?.getString("userName") ?: ""
            val rol = backStackEntry.arguments?.getString("userRol") ?: ""

            Log.d("NAV_DEBUG", "En HOME -> Nombre: $name, Rol: $rol")
            Pantalla(navController, id, name, rol)
        }

        // 3. MIS TAREAS
        composable(
            route = "mis_tareas_screen/{userId}/{userName}/{userRol}",
            arguments = listOf(
                navArgument("userId") { type = NavType.IntType },
                navArgument("userName") { type = NavType.StringType },
                navArgument("userRol") { type = NavType.StringType }
            )
        ) { backStackEntry ->
            val id = backStackEntry.arguments?.getInt("userId") ?: 0
            val name = backStackEntry.arguments?.getString("userName") ?: ""
            val rol = backStackEntry.arguments?.getString("userRol") ?: ""

            Log.d("NAV_DEBUG", "En TAREAS -> Nombre: $name, Rol: $rol")
            MisTareasScreen(navController, id, name, rol)
        }

        // 4. NÓMINA
        composable(
            route = "nomina_screen/{userId}/{userName}/{userRol}",
            arguments = listOf(
                navArgument("userId") { type = NavType.IntType },
                navArgument("userName") { type = NavType.StringType },
                navArgument("userRol") { type = NavType.StringType }
            )
        ) { backStackEntry ->
            val id = backStackEntry.arguments?.getInt("userId") ?: 0
            val name = backStackEntry.arguments?.getString("userName") ?: ""
            val rol = backStackEntry.arguments?.getString("userRol") ?: ""

            NominaScreen(navController, id, name, rol)
        }

        // 5. PERFIL
        composable(
            route = "profile/{userId}/{userName}/{userRol}",
            arguments = listOf(
                navArgument("userId") { type = NavType.IntType },
                navArgument("userName") { type = NavType.StringType },
                navArgument("userRol") { type = NavType.StringType }
            )
        ) { backStackEntry ->
            val id = backStackEntry.arguments?.getInt("userId") ?: 0
            val name = backStackEntry.arguments?.getString("userName") ?: ""
            val rol = backStackEntry.arguments?.getString("userRol") ?: ""

            PerfilScreen(navController, id, name, rol)
        }
    }
}