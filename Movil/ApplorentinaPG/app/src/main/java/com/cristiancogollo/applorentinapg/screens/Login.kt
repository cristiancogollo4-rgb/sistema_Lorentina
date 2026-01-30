package com.cristiancogollo.applorentinapg

import android.widget.Toast
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Lock
import androidx.compose.material.icons.filled.Person
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.tooling.preview.Preview
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.cristiancogollo.applorentinapg.network.RetrofitClient
import kotlinx.coroutines.launch // Importante para la lógica nueva

// Asegúrate de que UserResponse esté disponible
// Si no, impórtalo o defínelo en tus modelos

@Composable
fun LorentinaLoginScreen(
    onLoginSuccess: (UserResponse) -> Unit = {} // Callback con el usuario completo
) {
    // --- COLORES ORIGINALES (TU DISEÑO) ---
    val ColorLorentinaFilter = Color(0xCC5D4037)
    val ColorGris = Color(0xFF5D4037)
    val ColorSelector = Color(0xFF5D4037)

    // --- ESTADOS ---
    var username by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var isLoading by remember { mutableStateOf(false) }

    var usernameError by remember { mutableStateOf<String?>(null) }
    var passwordError by remember { mutableStateOf<String?>(null) }

    val context = LocalContext.current

    // Ámbito de corrutina para hacer la llamada de red sin bloquear la UI
    val scope = rememberCoroutineScope()

    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(Color(0xFFF5F5F1))
    ) {
        // ==========================================
        // PARTE SUPERIOR (IMAGEN + LOGO) - INTACTO
        // ==========================================
        Box(
            modifier = Modifier
                .fillMaxWidth()
                .weight(1f)
        ) {
            Image(
                // Asegúrate que R.drawable.fondologin exista en tu proyecto
                painter = painterResource(id = R.drawable.fondologin),
                contentDescription = "Fondo",
                modifier = Modifier
                    .fillMaxSize()
                    .clip(RoundedCornerShape(bottomStart = 35.dp, bottomEnd = 35.dp)),
                contentScale = ContentScale.Crop
            )

            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .clip(RoundedCornerShape(bottomStart = 35.dp, bottomEnd = 35.dp))
                    .background(ColorLorentinaFilter)
            )

            Column(
                modifier = Modifier
                    .align(Alignment.Center)
                    .offset(y = (-20).dp),
                horizontalAlignment = Alignment.CenterHorizontally
            ) {
                Image(
                    // Asegúrate que R.drawable.lorentinalogo exista en tu proyecto
                    painter = painterResource(id = R.drawable.lorentinalogo),
                    contentDescription = "Logo",
                    modifier = Modifier.size(300.dp)
                )
            }
        }

        // ==========================================
        // ÁREA DE LOGIN (TARJETA BLANCA)
        // ==========================================
        Box(
            modifier = Modifier
                .fillMaxWidth()
                .weight(1.5f)
                .offset(y = (-35).dp)
                .padding(bottom = 30.dp)
        ) {
            Card(
                modifier = Modifier.fillMaxSize(),
                shape = RoundedCornerShape(topStart = 35.dp, topEnd = 35.dp),
                colors = CardDefaults.cardColors(containerColor = LorentinaBg),
                elevation = CardDefaults.cardElevation(defaultElevation = 25.dp)
            ) {
                Column(
                    modifier = Modifier
                        .fillMaxSize()
                        .padding(horizontal = 20.dp)
                        .padding(top = 50.dp),
                    horizontalAlignment = Alignment.CenterHorizontally
                ) {

                    Text(
                        text = "Bienvenido a Fábrica",
                        color = ColorSelector,
                        fontWeight = FontWeight.Bold,
                        fontSize = 20.sp
                    )

                    Spacer(modifier = Modifier.height(30.dp))

                    // --- CAMPO USUARIO ---
                    OutlinedTextField(
                        value = username,
                        onValueChange = {
                            username = it
                            usernameError = null
                        },
                        label = { Text("USUARIO", fontSize = 14.sp, color = ColorGris) },
                        placeholder = { Text("Ej: jorge.perez") },
                        trailingIcon = {
                            Icon(
                                imageVector = Icons.Default.Person,
                                contentDescription = "Usuario",
                                tint = ColorSelector,
                                modifier = Modifier.size(32.dp)
                            )
                        },
                        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Text),
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(12.dp),
                        colors = OutlinedTextFieldDefaults.colors(
                            focusedBorderColor = ColorSelector,
                            unfocusedBorderColor = ColorSelector
                        )
                    )
                    if (usernameError != null) {
                        Text(
                            text = usernameError!!,
                            color = Color.Red,
                            fontSize = 12.sp,
                            modifier = Modifier.fillMaxWidth().padding(top = 4.dp, start = 4.dp),
                            textAlign = TextAlign.Start
                        )
                    }

                    Spacer(modifier = Modifier.height(16.dp))

                    // --- CAMPO CONTRASEÑA ---
                    OutlinedTextField(
                        value = password,
                        onValueChange = {
                            password = it
                            passwordError = null
                        },
                        label = { Text("CONTRASEÑA", fontSize = 14.sp, color = ColorGris) },
                        trailingIcon = {
                            Icon(
                                imageVector = Icons.Default.Lock,
                                contentDescription = "Contraseña",
                                tint = ColorSelector,
                                modifier = Modifier.size(32.dp)
                            )
                        },
                        visualTransformation = PasswordVisualTransformation(),
                        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(12.dp),
                        colors = OutlinedTextFieldDefaults.colors(
                            focusedBorderColor = ColorSelector,
                            unfocusedBorderColor = ColorSelector
                        )
                    )
                    if (passwordError != null) {
                        Text(
                            text = passwordError!!,
                            color = Color.Red,
                            fontSize = 12.sp,
                            modifier = Modifier.fillMaxWidth().padding(top = 4.dp, start = 4.dp),
                            textAlign = TextAlign.Start
                        )
                    }

                    Spacer(modifier = Modifier.height(24.dp))

                    // --- BOTÓN INGRESAR (CON LÓGICA ARREGLADA) ---
                    Button(
                        onClick = {
                            // 1. Validaciones Visuales
                            usernameError = null
                            passwordError = null

                            if (username.isEmpty()) {
                                usernameError = "Ingresa tu usuario"
                                return@Button
                            }
                            if (password.isEmpty()) {
                                passwordError = "Ingresa tu contraseña"
                                return@Button
                            }

                            // 2. Inicio de Lógica de Red (Moderno con Corrutinas)
                            isLoading = true

                            scope.launch {
                                try {
                                    val loginRequest = LoginRequest(username, password)
                                    // Llamada asíncrona a la API
                                    val response = RetrofitClient.apiService.login(loginRequest)

                                    if (response.isSuccessful && response.body() != null) {
                                        val usuario = response.body()!!

                                        // Validación de seguridad del Rol
                                        if (usuario.rol.isNullOrEmpty()) {
                                            passwordError = "Tu usuario no tiene un ROL asignado."
                                        } else {
                                            Toast.makeText(context, "Bienvenido ${usuario.nombre}", Toast.LENGTH_SHORT).show()
                                            // ¡ÉXITO! Navegamos al Home
                                            onLoginSuccess(usuario)
                                        }
                                    } else {
                                        // Error del servidor (ej. 401 Credenciales inválidas)
                                        passwordError = "Usuario o contraseña incorrectos"
                                    }
                                } catch (e: Exception) {
                                    // Error de red (sin internet, servidor caído)
                                    passwordError = "Error de conexión: Verifica tu red"
                                    e.printStackTrace()
                                } finally {
                                    isLoading = false
                                }
                            }
                        },
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(50.dp),
                        shape = RoundedCornerShape(12.dp),
                        colors = ButtonDefaults.buttonColors(containerColor = ColorSelector),
                        enabled = !isLoading // Evita doble clic mientras carga
                    ) {
                        if (isLoading) {
                            CircularProgressIndicator(color = Color.White, modifier = Modifier.size(24.dp))
                        } else {
                            Text(
                                text = "INGRESAR",
                                fontSize = 16.sp,
                                fontWeight = FontWeight.Bold,
                                color = Color.White
                            )
                        }
                    }

                }
            }
        }
    }
}

@Preview(showBackground = true)
@Composable
fun PreviewLorentinaLogin() {
    LorentinaLoginScreen()
}