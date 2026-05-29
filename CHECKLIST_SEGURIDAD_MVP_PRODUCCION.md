# Checklist minimo de seguridad: MVP a produccion

Esta lista prioriza impacto alto con bajo costo para un ecommerce pequeno en fase inicial.

## 1) Configuracion de entorno

- APP_ENV=production.
- APP_DEBUG=false.
- APP_KEY unica y secreta.
- No subir .env al repositorio.
- Rotar claves si una variable sensible fue expuesta.

## 2) HTTPS y dominio

- Forzar HTTPS en el proveedor.
- Verificar APP_URL con https real.
- Revisar mixed content (imagenes/scripts en http).

## 3) Acceso administrativo

- Cambiar passwords debiles de cuentas admin.
- Activar password policy minima (largo y complejidad).
- Revisar permisos por rol (principio de menor privilegio).
- Eliminar usuarios inactivos con rol alto.

## 4) Sesiones y CSRF

- SESSION_DRIVER en database o redis (no file en produccion).
- Confirmar token CSRF en formularios y peticiones autenticadas.
- Revisar expiracion de sesion acorde al negocio.

## 5) Base de datos

- Backups automaticos diarios (Neon o proveedor).
- Probar restauracion al menos una vez.
- Usuario de BD con permisos minimos (solo DB de la app).
- DB_SSLMODE=require para conexion cifrada.

## 6) Secretos y terceros

- Guardar secretos solo en variables de entorno.
- No hardcodear claves de Stripe/PayPal.
- Regenerar claves de webhook si hubo sospecha de filtracion.
- Limitar acceso a paneles de proveedores con 2FA.

## 7) Pagos y webhooks

- Verificar firma de webhook (Stripe/PayPal) activa.
- Registrar eventos clave de pago (creado, pagado, fallido).
- Evitar doble procesamiento de orden (idempotencia).
- Probar flujo de pago en sandbox antes de pasar a live.

## 8) Archivos e imagenes

- Evitar depender de disco local para archivos criticos.
- Validar mime/type y tamano maximo en uploads.
- Bloquear ejecucion de scripts en directorios de carga.

## 9) Cabeceras y hardening web

- Activar cabeceras: X-Frame-Options, X-Content-Type-Options, Referrer-Policy.
- Configurar Content-Security-Policy cuando sea posible.
- Limitar CORS solo a dominios necesarios.

## 10) Monitoreo y logging

- Revisar logs de aplicacion de forma periodica.
- Alertar sobre errores 500 repetidos.
- No registrar datos sensibles (tokens, passwords, tarjetas).

## 11) Dependencias y actualizaciones

- Actualizar parches de Laravel/PHP/dependencias.
- Correr escaneo de vulnerabilidades de dependencias.
- Revisar changelog antes de cambios mayores.

## 12) Pruebas minimas antes de publicar

- Login, registro, recuperar password.
- Catalogo, favoritos, carrito, checkout.
- Webhooks y cambio de estado de orden.
- Exportes (Excel/PDF) y panel admin.

## 13) Plan de respuesta a incidentes (basico)

- Definir quien responde ante caida o hackeo.
- Tener procedimiento: aislar, restaurar, rotar secretos, comunicar.
- Mantener contacto tecnico y checklist de recuperacion.

## 14) Prioridad sugerida (primera semana)

1. APP_DEBUG=false + APP_KEY segura + .env protegido.
2. HTTPS + APP_URL correcto.
3. Backups + prueba de restauracion.
4. Webhooks firmados + logs de pago.
5. Reforzar cuentas admin + 2FA en proveedores.
