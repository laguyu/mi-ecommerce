# Plan de endpoints moviles v1 (sin romper web)

Objetivo:
- Mantener las rutas web actuales funcionando igual.
- Definir versionado para app movil futura.
- Evitar regresiones en frontend web.

## 1) Estrategia de versionado recomendada

Crear nuevas rutas bajo prefijo:

- /api/v1/mobile/*

Reglas:
- No eliminar endpoints actuales de /api/* usados por la web.
- Reusar controladores/servicios existentes cuando sea posible.
- Adaptar solo contrato de respuesta cuando haga falta para movil.

## 2) Endpoints actuales que ya puedes reutilizar (sin cambios)

Publicos:
- GET /api/home-products
- GET /api/home-banners
- GET /api/home-main-banner
- GET /api/home-secondary-banners
- GET /api/home-product-carousels
- GET /api/home-promotion-banner
- GET /api/categories
- GET /api/brands
- GET /api/catalog
- GET /api/products/{product}
- GET /api/checkout/coupon
- POST /api/newsletter/subscribe
- POST /api/checkout/prepare
- GET /api/orders/{order}/summary

Autenticados:
- GET /api/favorites
- GET /api/favorites/ids
- POST /api/favorites/toggle

Pagos:
- POST /api/payments/stripe/checkout-session
- POST /api/payments/paypal/order

## 3) Propuesta de endpoints moviles v1

## 3.1 Auth movil (recomendado con Sanctum)

- POST /api/v1/mobile/auth/login
- POST /api/v1/mobile/auth/register
- POST /api/v1/mobile/auth/logout
- GET  /api/v1/mobile/auth/me
- POST /api/v1/mobile/auth/forgot-password
- POST /api/v1/mobile/auth/reset-password

## 3.2 Home y catalogo

- GET /api/v1/mobile/home
  - Puede agrupar banners + destacados + carruseles en una sola llamada.
- GET /api/v1/mobile/catalog
- GET /api/v1/mobile/products/{product}
- GET /api/v1/mobile/categories
- GET /api/v1/mobile/brands

## 3.3 Favoritos

- GET  /api/v1/mobile/favorites
- POST /api/v1/mobile/favorites/toggle

## 3.4 Carrito y checkout

- POST /api/v1/mobile/checkout/validate-coupon
- POST /api/v1/mobile/checkout/prepare
- GET  /api/v1/mobile/orders/{order}/summary

## 3.5 Pagos

- POST /api/v1/mobile/payments/stripe/checkout-session
- POST /api/v1/mobile/payments/paypal/order

## 3.6 Cuenta

- GET /api/v1/mobile/account/profile
- PUT /api/v1/mobile/account/profile
- GET /api/v1/mobile/account/orders
- GET /api/v1/mobile/account/orders/{order}

## 4) Contratos de respuesta sugeridos (estables)

Estandarizar respuesta:

Exito:

```json
{
  "ok": true,
  "message": "Operacion exitosa",
  "data": {},
  "meta": {}
}
```

Error:

```json
{
  "ok": false,
  "message": "Error de validacion",
  "errors": {
    "field": ["mensaje"]
  }
}
```

Beneficio:
- App movil y web pueden manejar errores de manera consistente.

## 5) Orden de implementacion recomendado

1. Auth movil con Sanctum.
2. Endpoint agregado de home (/api/v1/mobile/home).
3. Catalogo + detalle de producto.
4. Favoritos.
5. Checkout + pagos.
6. Cuenta y pedidos.

## 6) Compatibilidad hacia atras

Para no romper nada:
- Mantener los endpoints actuales para Vue web.
- Implementar endpoints mobile como capa adicional.
- Reusar App\Services actuales (pricing, pagos, notificaciones).

## 7) Seguridad minima para API movil

- Rate limit en auth y endpoints sensibles.
- Tokens con expiracion y revocacion.
- Verificacion de firma en webhooks.
- Logging sin datos sensibles.

## 8) Resultado esperado

- Web actual intacta.
- App movil evoluciona en paralelo.
- Backend unico y mantenible.
