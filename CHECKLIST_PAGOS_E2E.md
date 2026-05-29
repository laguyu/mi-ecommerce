# Checklist E2E de Pagos (Stripe + PayPal)

Fecha: 2026-05-26
Proyecto: mi-ecommerce

## 1) Preparacion

1. Verifica variables en .env:
   - STRIPE_SECRET_KEY
   - STRIPE_WEBHOOK_SECRET
   - STRIPE_CURRENCY
   - PAYPAL_CLIENT_ID
   - PAYPAL_CLIENT_SECRET
   - PAYPAL_MODE
   - PAYPAL_CURRENCY
   - PAYPAL_WEBHOOK_ID

2. Limpia cache de Laravel:
   - php artisan optimize:clear

3. Inicia app y frontend:
   - php artisan serve
   - npm run dev

4. Expone URL publica HTTPS con ngrok o Cloudflare Tunnel.

## 2) Configuracion Webhooks

### Stripe

1. Endpoint:
   - https://TU_DOMINIO_PUBLICO/webhooks/stripe

2. Eventos:
   - checkout.session.completed
   - checkout.session.async_payment_succeeded

3. Copia el Signing Secret a STRIPE_WEBHOOK_SECRET.

### PayPal

1. Endpoint:
   - https://TU_DOMINIO_PUBLICO/webhooks/paypal

2. Evento:
   - PAYMENT.CAPTURE.COMPLETED

3. Copia el Webhook ID a PAYPAL_WEBHOOK_ID.

## 3) Flujo Exitoso Stripe

1. Entra al ecommerce, agrega productos al carrito.
2. Ve a checkout y selecciona Stripe.
3. Completa datos del cliente y paga en Stripe.
4. Verifica al volver:
   - Mensaje de pedido confirmado.
   - Orden en DB con status = paid.
   - payment_method = stripe.
   - payment_reference con id de Stripe.
   - paid_at con fecha.
   - stock descontado.

## 4) Flujo Exitoso PayPal

1. Entra al ecommerce, agrega productos al carrito.
2. Ve a checkout y selecciona PayPal.
3. Aprueba el pago en PayPal.
4. Verifica al volver:
   - Mensaje de pedido confirmado.
   - Orden en DB con status = paid.
   - payment_method = paypal.
   - payment_reference con id de orden/captura PayPal.
   - paid_at con fecha.
   - stock descontado.

## 5) Escenarios Negativos

### Cancelacion por usuario

1. Inicia pago Stripe y cancela en pantalla de Stripe.
2. Inicia pago PayPal y cancela en pantalla de PayPal.
3. Verifica:
   - UI muestra pago cancelado.
   - Orden queda en pending_payment.
   - stock no se descuenta.

### Credenciales invalidas

1. Coloca STRIPE_SECRET_KEY incorrecta.
2. Intenta pagar con Stripe.
3. Verifica error controlado en checkout.
4. Repite con PAYPAL_CLIENT_SECRET incorrecta.

### Firma webhook invalida

1. Cambia temporalmente STRIPE_WEBHOOK_SECRET.
2. Reintenta evento webhook.
3. Verifica rechazo con status 400.
4. Repite en PayPal con PAYPAL_WEBHOOK_ID incorrecto.

## 6) Idempotencia y Duplicados

1. Reenvia manualmente el mismo webhook de Stripe desde dashboard/CLI.
2. Reenvia manualmente el mismo webhook de PayPal.
3. Verifica:
   - La orden sigue en paid.
   - No se vuelve a descontar stock.
   - No se crean items duplicados.

## 7) Reconciliacion Basica

1. Compara ordenes paid en DB contra transacciones en Stripe/PayPal.
2. Verifica montos:
   - subtotal
   - discount_amount
   - shipping_amount
   - total
3. Verifica moneda coherente entre app y proveedor.

## 8) Checklist de Salida a Produccion

1. PAYPAL_MODE=live en produccion.
2. Claves live de Stripe/PayPal cargadas por entorno.
3. APP_URL correcto (HTTPS real).
4. Webhooks apuntando al dominio final.
5. Logs monitoreados para errores de webhook.
6. Politica de reintentos y alertas definida.

## 9) Consultas SQL utiles

Ordenes recientes:
SELECT id, order_number, status, payment_method, payment_reference, total, paid_at, created_at
FROM orders
ORDER BY id DESC
LIMIT 20;

Items por orden:
SELECT order_id, product_id, sku, product_name, quantity, unit_price, line_total
FROM order_items
WHERE order_id = ?;

Stock de productos:
SELECT id, sku, name, stock
FROM products
ORDER BY id DESC
LIMIT 20;
