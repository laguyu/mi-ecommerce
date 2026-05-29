# Guia: version movil Android sin romper la web

Objetivo:
- Mantener el ecommerce web exactamente como esta.
- Crear una app movil Android en paralelo.
- Reutilizar el backend Laravel actual.

## 1) Estrategia recomendada

Usar dos fases:

1. Fase MVP rapida (2 a 7 dias):
- App Android tipo contenedor (WebView con Capacitor) apuntando a tu web publicada.
- Casi cero cambios en backend.
- La web sigue intacta.

2. Fase app movil robusta (3 a 8 semanas):
- App Flutter o React Native consumiendo APIs.
- UX movil nativa, mejor performance, push notifications.
- La web sigue intacta porque el frontend movil es otro proyecto.

## 2) Arquitectura final (sin tocar la web)

- Backend unico: Laravel (actual).
- Frontend web: el actual (sin cambios funcionales).
- Frontend movil: proyecto nuevo separado.

Regla de oro:
- No mezclar codigo movil dentro de la web existente.
- Crear un repo o carpeta separada para movil.

## 3) Fase MVP: Android con Capacitor (la mas rapida)

## 3.1 Prerrequisitos

- Tener la web ya desplegada (ejemplo: Render).
- Node.js LTS.
- Android Studio instalado.
- Java 17 (recomendado para Android actual).

## 3.2 Crear proyecto movil separado

En una carpeta nueva (fuera o al lado del repo web):

```bash
npm create vite@latest mi-ecommerce-mobile -- --template vanilla
cd mi-ecommerce-mobile
npm install
npm install @capacitor/core @capacitor/cli @capacitor/android
npx cap init "Mi Ecommerce" "com.miecommerce.app"
```

## 3.3 Configurar para abrir tu web publica

Editar capacitor.config.ts o capacitor.config.json con:

- appId: com.miecommerce.app
- appName: Mi Ecommerce
- webDir: dist
- server.url: https://TU-DOMINIO-PUBLICO
- server.cleartext: false

Nota:
- Con server.url, la app carga tu ecommerce desplegado.
- Tu web queda exactamente como esta.

## 3.4 Generar Android

```bash
npm run build
npx cap add android
npx cap copy
npx cap open android
```

Desde Android Studio:
- Ejecutar en emulador o celular.
- Generar APK debug para pruebas.

## 3.5 Ajustes utiles del WebView

En Android Studio (WebView app):
- Activar soporte de camara/archivos solo si los necesitas.
- Habilitar deep links si luego usas notificaciones.
- Definir icono, splash y nombre final de app.

## 4) Seguridad y sesiones en modo WebView

Como estas cargando tu sitio real:
- Login, carrito, checkout y favoritos funcionan igual que en web.
- Se mantiene la sesion por cookies del dominio.

Recomendaciones:
- Forzar HTTPS.
- APP_URL correcta en produccion.
- Revisar CSRF y session domain si cambia subdominio.

## 5) Publicacion Android (MVP)

1. Crear cuenta de Google Play Console.
2. Crear keystore de firma.
3. Generar AAB desde Android Studio (mejor que APK para store).
4. Subir ficha, politicas y capturas.
5. Publicar en canal interno/cerrado primero.

## 6) Fase robusta: app nativa real (sin romper web)

Cuando quieras mejor UX movil:
- Crear app Flutter o React Native aparte.
- Mantener la web actual igual.

## 6.1 Cambios backend recomendados (no rompen web)

Agregar soporte API token para movil:
- Laravel Sanctum para autenticacion de app nativa.
- Endpoints versionados: /api/v1/mobile/*.
- Reusar servicios actuales (pricing, pagos, ordenes).

Esto no afecta rutas web existentes.

## 6.2 Modulos minimos de app nativa

1. Auth (login, registro, reset password).
2. Home (banners, destacados, carousels).
3. Catalogo + filtros + detalle.
4. Favoritos.
5. Carrito.
6. Checkout + pagos.
7. Perfil + historial de pedidos.

## 6.3 Plan de migracion sin riesgo

1. Primero publicar MVP WebView.
2. Medir uso real en Android.
3. Construir app nativa por modulo.
4. Liberar cada modulo por feature flags.
5. Mantener WebView como fallback temporal.

## 7) Que NO hacer

- No rehacer todo el backend.
- No duplicar reglas de negocio en el movil.
- No mezclar cambios grandes en web y movil al mismo tiempo.

## 8) Cronograma sugerido

Semana 1:
- Sacar APK/AAB WebView funcional.
- Probar login, catalogo, carrito y checkout.

Semana 2:
- Publicar beta cerrada en Play Console.
- Corregir issues de dispositivo real.

Semanas 3 a 8 (opcional):
- Empezar app nativa por modulos.

## 9) Checklist de salida rapida (tu caso)

1. Desplegar web estable en dominio HTTPS.
2. Crear proyecto Capacitor separado.
3. Configurar server.url con tu dominio.
4. Generar Android y probar flujo completo.
5. Firmar AAB y publicar beta cerrada.

## 10) Decision recomendada para ti hoy

- No empezar desde cero.
- Publicar primero version movil con Capacitor.
- Mantener web actual intacta.
- Luego evolucionar a nativa solo si el negocio lo pide.

## 11) Entregables listos en este repositorio

- Starter movil Android con Capacitor: mobile-app/
- Plan exacto de endpoints para version movil: MOBILE_API_ENDPOINTS_V1_PLAN.md
- Auth movil Sanctum ya implementado en backend:
	- routes/api.php
	- app/Http/Controllers/Api/V1/Mobile/AuthController.php
	- database/migrations/2026_05_28_200000_create_personal_access_tokens_table.php

Comandos iniciales del starter:

```bash
cd mobile-app
npm install
npm run cap:add:android
npm run android:prepare
npm run cap:open:android
```

Activar tabla de tokens en tu entorno:

```bash
php artisan migrate --force
```
