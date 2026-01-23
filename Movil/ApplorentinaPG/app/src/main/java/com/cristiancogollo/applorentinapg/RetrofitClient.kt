package com.cristiancogollo.applorentinapg

import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory

object RetrofitClient {

    // ⚠️ IMPORTANTE: CAMBIA ESTO POR LA IP DE TU COMPUTADORA
    // Si usas el emulador de Android Studio, usa "http://10.0.2.2:4000/"
    // Si usas tu celular real por Wi-Fi, usa tu IP (ej: "http://192.168.1.15:4000/")
    private const val BASE_URL = "http://10.0.2.2:4000/"

    val instance: ApiService by lazy {
        val retrofit = Retrofit.Builder()
            .baseUrl(BASE_URL)
            .addConverterFactory(GsonConverterFactory.create())
            .build()

        retrofit.create(ApiService::class.java)
    }
}