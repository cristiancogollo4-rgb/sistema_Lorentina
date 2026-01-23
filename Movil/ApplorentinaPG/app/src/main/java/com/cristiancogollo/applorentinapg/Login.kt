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
// Importamos Retrofit (asegúrate de tener tus clases creadas)
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

// Enum simple para usar en la navegación post-login
enum class UserRole { VENDEDOR, ADMINISTRADOR, CORTADOR, ARMADOR, COSTURA, SIN_ROL }

@Composable
fun LorentinaLoginScreen(
    // Este callback ahora devuelve el rol detectado y el objeto usuario completo si lo necesitas
    onLoginSuccess: (String) -> Unit = {}
) {
    // --- COLORES ORIGINALES ---
    val ColorLorentinaFilter = Color(0x99C7E534)
    val ColorGris = Color(0xFFa6a6a6)
    val ColorSelector = Color(0xFFC2D706)

    // --- ESTADOS ---
    // Usamos 'username' en lugar de email, ya que tu BD usa username (ej: jorge.perez)
    var username by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var isLoading by remember { mutableStateOf(false) }

    var usernameError by remember { mutableStateOf<String?>(null) }
    var passwordError by remember { mutableStateOf<String?>(null) }

    val context = LocalContext.current

    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(Color(0xFFF0F0F0))
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
                painter = painterResource(id = R.drawable.fondologin), // Asegúrate que la imagen exista
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
                    painter = painterResource(id = R.drawable.lorentinalogo), // Asegúrate que el logo exista
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
                colors = CardDefaults.cardColors(containerColor = Color.White),
                elevation = CardDefaults.cardElevation(defaultElevation = 25.dp)
            ) {
                Column(
                    modifier = Modifier
                        .fillMaxSize()
                        .padding(horizontal = 20.dp)
                        .padding(top = 50.dp),
                    horizontalAlignment = Alignment.CenterHorizontally
                ) {

                    // NOTA: He eliminado el Selector de Rol Visual (Row) porque ahora
                    // el rol se detecta automáticamente desde la Base de Datos.
                    // Para mantener el espacio visual original, dejamos un Spacer grande.
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

                    // --- BOTÓN INGRESAR ---
                    Button(
                        onClick = {
                            // 1. Validaciones Locales
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

                            // 2. Llamada al Backend Local (Retrofit)
                            isLoading = true

                            val loginRequest = LoginRequest(username, password)

                            // Asegúrate de usar tu RetrofitClient configurado con la IP
                            RetrofitClient.instance.login(loginRequest).enqueue(object : Callback<UserResponse> {
                                override fun onResponse(call: Call<UserResponse>, response: Response<UserResponse>) {
                                    isLoading = false
                                    // Dentro de: override fun onResponse(...)

                                    if (response.isSuccessful && response.body() != null) {
                                        val usuario = response.body()!!

                                        // 1. Imprimimos en consola para ver qué está llegando realmente (útil para depurar)
                                        println("LOGIN DEBUG: Usuario: ${usuario.username}, Rol recibido: ${usuario.rol}")

                                        // 2. VERIFICACIÓN DE SEGURIDAD
                                        if (usuario.rol.isNullOrEmpty()) {
                                            // 🛑 CASO PELIGROSO: El usuario entró, pero no tiene ROL.
                                            // No dejamos que la app explote. Mostramos error en pantalla.
                                            passwordError = "Error crítico: Tu usuario '${usuario.username}' no tiene un ROL asignado en la base de datos. Contacta a Cristian."
                                        } else {
                                            // ✅ CASO ÉXITO: Tiene rol, todo correcto.
                                            Toast.makeText(context, "Bienvenido ${usuario.nombre}", Toast.LENGTH_SHORT).show()

                                            // Aquí pasamos el rol seguro, porque ya validamos que no es null
                                            onLoginSuccess(usuario.rol)
                                        }

                                    } else {
                                        // Error de contraseña o usuario no encontrado
                                        passwordError = "Credenciales incorrectas"
                                    }
                                }

                                override fun onFailure(call: Call<UserResponse>, t: Throwable) {
                                    isLoading = false
                                    Toast.makeText(context, "Error de conexión: ${t.message}", Toast.LENGTH_LONG).show()
                                }
                            })
                        },
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(50.dp),
                        shape = RoundedCornerShape(12.dp),
                        colors = ButtonDefaults.buttonColors(containerColor = ColorSelector),
                        enabled = !isLoading // Deshabilitar si está cargando
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

@Preview
@Composable
fun PreviewLorentinaLogin() {
    LorentinaLoginScreen()
}