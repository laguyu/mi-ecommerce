# Documentacion del Ecommerce (Laravel + Vue)

Esta guia describe la version actual del proyecto: home con carrusel, catalogo, ficha de producto, carrito, checkout y pagos con Stripe/PayPal.

## Checklist express (5 minutos)

1. Configura .env con credenciales sandbox:
   - STRIPE_SECRET_KEY, STRIPE_PUBLISHABLE_KEY
   - PAYPAL_CLIENT_ID, PAYPAL_CLIENT_SECRET, PAYPAL_MODE=sandbox
2. Limpia cache:
   - php artisan optimize:clear
3. Levanta proyecto:
   - php artisan serve
   - npm run dev
4. Prueba Stripe:
   - Checkout > Stripe
   - Tarjeta 4242 4242 4242 4242
5. Prueba PayPal:
   - Checkout > PayPal
   - Login con cuenta Personal sandbox
6. Valida en base de datos:
   - orders.status = paid
   - orders.payment_method = stripe o paypal
   - stock descontado en products

Si algo falla, revisa la seccion "Troubleshooting rapido" en este mismo documento.

## 1) Arquitectura general

El sistema esta dividido en 2 capas:

- Backend Laravel (API web): catalogo, ordenes y pagos.
- Frontend Vue: vistas y flujo de compra.

Flujo principal:

1. Vue carga home y catalogo desde API.
2. Usuario agrega productos al carrito (localStorage).
3. En checkout, Vue prepara orden pendiente de pago.
4. Vue redirige a Stripe o PayPal.
5. Al regresar, se confirma pago, se marca orden y se descuenta stock.

## 2) Estructura backend

Archivos clave:

- routes/web.php
- app/Http/Controllers/CatalogController.php
- app/Http/Controllers/CheckoutController.php
- app/Http/Controllers/PaymentController.php
- app/Http/Controllers/WebhookController.php
- app/Services/OrderPaymentService.php

Modelos principales:

- app/Models/Category.php
- app/Models/Product.php
- app/Models/ProductImage.php
- app/Models/Order.php
- app/Models/OrderItem.php

Tablas principales:

- categories
- products
- product_images
- orders
- order_items

## 3) Endpoints principales

Catalogo:

- GET /api/home-products
- GET /api/catalog
- GET /api/products/{product}

Checkout y orden:

- POST /api/checkout/prepare
- GET /api/orders/{order}/summary

Pagos:

- POST /api/payments/stripe/checkout-session
- POST /api/payments/paypal/order
- GET /checkout/stripe/success/{order}
- GET /checkout/paypal/return/{order}
- GET /checkout/cancel/{order}

Webhooks:

- POST /webhooks/stripe
- POST /webhooks/paypal

## 4) Variables de entorno

Stripe:

- STRIPE_SECRET_KEY
- STRIPE_PUBLISHABLE_KEY
- STRIPE_WEBHOOK_SECRET
- STRIPE_CURRENCY (ejemplo: usd)
- STRIPE_WEBHOOK_TOLERANCE (ejemplo: 300)

PayPal:

- PAYPAL_CLIENT_ID
- PAYPAL_CLIENT_SECRET
- PAYPAL_MODE (sandbox o live)
- PAYPAL_CURRENCY (ejemplo: USD)
- PAYPAL_WEBHOOK_ID

Despues de editar .env ejecutar:

```bash
php artisan optimize:clear
```

## 5) Guia Stripe Sandbox (sin dominio)

### 5.1 Donde sacar keys

1. Ir a https://dashboard.stripe.com
2. Activar Test mode
3. Ir a Developers > API keys
4. Copiar:
   - Secret key (sk_test...)
   - Publishable key (pk_test...)

### 5.2 Configurar en .env

```env
STRIPE_SECRET_KEY=sk_test_xxx
STRIPE_PUBLISHABLE_KEY=pk_test_xxx
STRIPE_CURRENCY=usd
```

Nota: para prueba local basica, STRIPE_WEBHOOK_SECRET puede quedar vacio si usas retorno del navegador.

### 5.3 Probar compra

1. Iniciar proyecto:
   - php artisan serve
   - npm run dev
2. Ir al checkout y seleccionar Stripe.
3. Usar tarjeta de prueba:
   - 4242 4242 4242 4242
   - fecha futura
   - CVC cualquiera
   - ZIP cualquiera
4. Confirmar que:
   - la orden queda en paid
   - payment_method = stripe
   - stock descontado

## 6) Guia PayPal Sandbox (sin dominio)

### 6.1 Donde crear app y credenciales

1. Ir a https://developer.paypal.com
2. Menu Apps & Credentials
3. En seccion Sandbox, crear una App o usar una existente
4. Copiar:
   - Client ID
   - Secret

### 6.2 Donde crear cuentas sandbox para pruebas

1. En developer.paypal.com ir a Testing Tools > Sandbox Accounts
2. Verificar que existan:
   - 1 cuenta Business (vendedor)
   - 1 cuenta Personal (comprador)
3. En el pago, iniciar sesion con la cuenta Personal sandbox.

### 6.3 Configurar en .env

```env
PAYPAL_CLIENT_ID=xxx
PAYPAL_CLIENT_SECRET=xxx
PAYPAL_MODE=sandbox
PAYPAL_CURRENCY=USD
```

Nota: PAYPAL_WEBHOOK_ID puede quedar vacio para prueba local basica sin webhook publico.

### 6.4 Probar compra

1. Iniciar proyecto:
   - php artisan serve
   - npm run dev
2. Ir al checkout y seleccionar PayPal.
3. Aprobar pago en pantalla de PayPal sandbox.
4. Confirmar que:
   - la orden queda en paid
   - payment_method = paypal
   - stock descontado

## 7) Webhooks (opcional en local, recomendado en produccion)

Stripe webhook:

1. Developers > Webhooks > Add endpoint
2. URL: https://TU_DOMINIO_PUBLICO/webhooks/stripe
3. Eventos:
   - checkout.session.completed
   - checkout.session.async_payment_succeeded
4. Copiar signing secret (whsec...) a STRIPE_WEBHOOK_SECRET

PayPal webhook:

1. En tu App Sandbox/Live, crear webhook
2. URL: https://TU_DOMINIO_PUBLICO/webhooks/paypal
3. Evento recomendado: PAYMENT.CAPTURE.COMPLETED
4. Copiar Webhook ID a PAYPAL_WEBHOOK_ID

## 8) Troubleshooting rapido

Error PayPal 400 MALFORMED_REQUEST_JSON:

- Revisar que PAYPAL_CLIENT_ID y PAYPAL_CLIENT_SECRET sean de la misma app sandbox.
- Revisar PAYPAL_MODE=sandbox.
- Ejecutar php artisan optimize:clear.

Error "PayPal no devolvio URL de aprobacion":

- Verificar credenciales y moneda.
- Revisar provider_error y paypal_debug_id en respuesta.

Error Stripe por key:

- Confirmar que sea sk_test en sandbox.
- Confirmar Test mode activo en dashboard.

## 9) Verificaciones SQL utiles

Ordenes recientes:

```sql
SELECT id, order_number, status, payment_method, payment_reference, total, paid_at, created_at
FROM orders
ORDER BY id DESC
LIMIT 20;
```

Items de una orden:

```sql
SELECT order_id, product_id, sku, product_name, quantity, unit_price, line_total
FROM order_items
WHERE order_id = ?;
```

Stock actual:

```sql
SELECT id, sku, name, stock
FROM products
ORDER BY id DESC
LIMIT 20;
```
