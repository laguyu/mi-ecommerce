# Guia de despliegue gratis: Laravel + Vue en Render con base de datos MySQL gratis

Esta guia esta pensada para este proyecto en particular (Laravel 13 + Vite + Vue) y para hacerlo con costo cero en etapa MVP/demo.
Incluye una opcion real para MySQL compatible en Render: TiDB Cloud Serverless.

## 1) Arquitectura recomendada sin costo

- App web: Render (Web Service, plan Free).
- Base de datos: TiDB Cloud Serverless (Free tier, compatible con protocolo MySQL).
- Archivos/imagenes (opcional recomendado): Cloudinary free o S3 compatible free.

Nota: en planes free la app puede entrar en modo sleep por inactividad.

## 2) Prerrequisitos

- Repositorio en GitHub con este proyecto.
- Cuenta en Render.
- Cuenta en TiDB Cloud.
- Claves de Stripe/PayPal (si vas a probar pagos reales o sandbox).

## 3) Crear base de datos gratis en TiDB Cloud (MySQL compatible)

1. En TiDB Cloud crea un proyecto Serverless.
2. Crea una base de datos (ejemplo: mi_ecommerce).
3. Crea un usuario SQL para la app.
4. En Network Access agrega acceso desde 0.0.0.0/0 para pruebas (luego puedes restringir).
5. Copia los datos de conexion:
   - host
   - puerto
   - usuario
   - password
   - nombre de base
   - CA certificate (si tu cluster requiere SSL con CA)

## 4) Crear servicio web en Render

1. En Render: New + Web Service.
2. Conecta tu repo de GitHub.
3. Configura:
   - Runtime: PHP
   - Branch: main (o la tuya)
   - Build Command:

```bash
composer install --no-dev --optimize-autoloader; npm install; npm run build
```

   - Start Command:

```bash
php artisan serve --host 0.0.0.0 --port $PORT
```

4. Plan: Free.

## 5) Variables de entorno en Render (Environment)

Configura estas variables en tu servicio:

```env
APP_NAME=Mi Ecommerce
APP_ENV=production
APP_DEBUG=false
APP_URL=https://TU-SERVICIO.onrender.com
APP_KEY=base64:GENERADA_LOCALMENTE

DB_CONNECTION=mysql
DB_HOST=TU_HOST_TIDB
DB_PORT=4000
DB_DATABASE=TU_DB_TIDB
DB_USERNAME=TU_USER_TIDB
DB_PASSWORD=TU_PASSWORD_TIDB

# Si el proveedor exige SSL (normalmente si)
MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt

CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

FILESYSTEM_DISK=local

MAIL_MAILER=log
MAIL_FROM_ADDRESS=no-reply@tu-dominio.com
MAIL_FROM_NAME=Mi Ecommerce

STRIPE_SECRET_KEY=
STRIPE_PUBLISHABLE_KEY=
STRIPE_WEBHOOK_SECRET=
STRIPE_CURRENCY=usd
STRIPE_WEBHOOK_TOLERANCE=300

PAYPAL_CLIENT_ID=
PAYPAL_CLIENT_SECRET=
PAYPAL_MODE=sandbox
PAYPAL_CURRENCY=USD
PAYPAL_WEBHOOK_ID=
```

Notas:
- Este proyecto ya soporta MySQL de forma nativa con DB_CONNECTION=mysql.
- En TiDB el puerto habitual es 4000.
- Si no vas a usar correos al inicio, MAIL_MAILER=log evita bloqueos.
- Si no conecta por TLS, revisa la seccion de SSL de config/database.php para validar MYSQL_ATTR_SSL_CA.

## 6) Generar APP_KEY antes del deploy

En tu maquina local (PowerShell):

```powershell
cd C:\laragon\www\mi-ecommerce
php artisan key:generate --show
```

Copia el valor generado y pegalo en APP_KEY de Render.

## 7) Migraciones y seeders en produccion

Cuando el servicio ya este creado, abre Shell en Render y ejecuta:

```bash
php artisan migrate --force
```

Si necesitas datos de prueba iniciales:

```bash
php artisan db:seed --force
```

Si quieres seed especifico:

```bash
php artisan db:seed --class=EcommerceSeeder --force
```

## 8) Comandos de optimizacion recomendados

Ejecuta una sola vez despues de configurar variables:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Si cambias variables de entorno, limpia cache:

```bash
php artisan optimize:clear
```

## 9) Webhooks de pagos en entorno publico

Cuando tengas URL publica de Render:

- Stripe webhook URL:
  - https://TU-SERVICIO.onrender.com/webhooks/stripe
- PayPal webhook URL:
  - https://TU-SERVICIO.onrender.com/webhooks/paypal

Configura estas rutas en los paneles de Stripe y PayPal, y actualiza:
- STRIPE_WEBHOOK_SECRET
- PAYPAL_WEBHOOK_ID

## 10) Manejo de archivos e imagenes en free tier

Con FILESYSTEM_DISK=local, los archivos en algunos entornos free pueden no ser persistentes al redeploy.

Recomendacion MVP:
- Mantenerlo asi para pruebas rapidas.

Recomendacion para estabilidad:
- Migrar imagenes a Cloudinary o S3 compatible cuando pases a etapa productiva.

## 11) Checklist rapido (orden sugerido)

1. Subir proyecto a GitHub.
2. Crear DB en TiDB Cloud.
3. Crear Web Service en Render.
4. Configurar variables ENV.
5. Generar y cargar APP_KEY.
6. Ejecutar migrate --force.
7. Probar home, catalogo, checkout, login, favoritos.
8. Configurar webhooks de Stripe/PayPal.
9. Verificar logs en Render.

## 12) Problemas comunes y solucion

### Error de conexion a DB
- Revisar DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD.
- Confirmar puerto correcto (normalmente 4000).
- Verificar reglas de acceso de red en TiDB Cloud (allowlist).
- Si aplica, validar SSL/CA.

### 419 CSRF en formularios
- Revisar APP_URL exacta (https y dominio correcto).
- Limpiar cache: php artisan optimize:clear.

### Fallo en build de frontend
- Revisar que npm install y npm run build terminen bien.
- Confirmar que package.json este en la raiz del repo.

### Pantalla blanca o error 500
- Ver logs del servicio en Render.
- Revisar APP_DEBUG=false (en prod) y LOG_LEVEL.

## 13) Alternativas tambien gratis

- Koyeb + TiDB Cloud.
- Fly.io (con limites de credito/uso).
- Oracle Cloud Always Free (mas tecnico, mas control).

## 14) Recomendacion final para tu caso

Para salir rapido sin pagar:
- Render + TiDB Cloud + dominio del proveedor.

Cuando tengas primeras ventas:
- pasar a plan pago basico,
- mover archivos a almacenamiento externo,
- separar worker de colas si crece el trafico.

## 15) Archivos listos en este repositorio

- Blueprint para deploy automatico en Render: render.yaml
- Checklist de seguridad MVP a produccion: CHECKLIST_SEGURIDAD_MVP_PRODUCCION.md

Siguiente paso recomendado:
- Importar el blueprint (render.yaml) en Render, cargar las variables sensibles y desplegar.
