package com.cristiancogollo.applorentinapg

import androidx.compose.runtime.Composable
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import androidx.navigation.navArgument
import com.cristiancogollo.applorentinapg.screens.MisTareasScreen
import com.cristiancogollo.applorentinapg.screens.NominaScreen


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
                    navController.navigate("home/${usuario.id}/${usuario.nombre}") {
                        popUpTo("login") { inclusive = true } // Borra historial para no volver al login con "atrás"
                    }
                }
            )
        }

        // Definimos los argumentos comunes para reutilizar código
        val userArguments = listOf(
            navArgument("userId") { type = NavType.IntType },
            navArgument("userName") { type = NavType.StringType }
        )

        // 2. HOME (PantallaCortador)
        composable("home/{userId}/{userName}", arguments = userArguments) { entry ->
            val userId = entry.arguments?.getInt("userId") ?: 0
            val userName = entry.arguments?.getString("userName") ?: ""
            HomeScreenUi(navController, userId, userName)
        }

        // 3. MIS TAREAS
        composable("tasks/{userId}/{userName}", arguments = userArguments) { entry ->
            val userId = entry.arguments?.getInt("userId") ?: 0
            val userName = entry.arguments?.getString("userName") ?: ""
            MisTareasScreen(navController, userId, userName)
        }

        // 4. NÓMINA
        composable("payroll/{userId}/{userName}", arguments = userArguments) { entry ->
            val userId = entry.arguments?.getInt("userId") ?: 0
            val userName = entry.arguments?.getString("userName") ?: ""
            NominaScreen(navController, userId, userName)
        }

        // 5. PERFIL
        composable("profile/{userId}/{userName}", arguments = userArguments) { entry ->
            val userId = entry.arguments?.getInt("userId") ?: 0
            val userName = entry.arguments?.getString("userName") ?: ""
            PerfilScreen(navController, userId, userName)
        }
    }
}