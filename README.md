<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="360" alt="Laravel Logo"></a></p>

## mi-ecommerce — Tienda demo (Proyecto de Portafolio)

Proyecto e-commerce construido con Laravel y una arquitectura orientada a servicios. Este repositorio contiene la aplicación backend/API, assets públicos y configuraciones para despliegue.

Revisa la documentación técnica y el detalle funcional en [DOCUMENTACION_ECOMMERCE.md](DOCUMENTACION_ECOMMERCE.md).

**Resumen rápido (para no programadores y personas interesadas)**
- **Qué es**: Una tienda en línea completa (catálogo, carrito, checkout, órdenes, notificaciones y panel administrativo).
-- **Objetivo**: Demostrar buenas prácticas de diseño, una arquitectura clara y habilidades para construir aplicaciones web listas para producción.
- **Hosting**: Frontend y/o sitio público desplegado en Vercel (añadir URL pública si está disponible). La base de datos se aloja en TiDB para escalabilidad.

**Stack tecnológico**
- **Backend**: PHP 8+ con Laravel (rutas, controladores, Eloquent ORM).
- **Frontend / Assets**: Vite, Node.js, paquetes en `package.json`.
- **Base de datos**: TiDB (MySQL compatible) en producción; local usa MySQL/MariaDB según configuración.
- **Contenedores / Infra**: `Dockerfile` incluido para empaquetar la app.

**Aspectos relevantes**
- **Código organizado**: Separación por capas (`app/Http/Controllers`, `app/Services`, `app/Models`, `app/Providers`).
- **Prácticas de diseño**: Implementación basada en principios SOLID (ver sección abajo). Esto facilita mantenimiento, pruebas y escalado.
- **Entrega real**: Configuraciones de despliegue muestran capacidad para entregar producto, no solo prototipos.

**Credenciales de demo (seeders)**
La base de datos incluye usuarios de ejemplo para pruebas automáticas y acceso rápido. Ver [database/seeders/AuthUsersSeeder.php](database/seeders/AuthUsersSeeder.php#L1-L120).
- **admin@miecommerce.test** / `password` — cuenta administrativa
- **editor@miecommerce.test** / `password` — rol editor
- **soporte@miecommerce.test** / `password` — rol soporte
- **cliente@miecommerce.test** / `password` — cliente de demostración

**Principios SOLID (explicación breve y aplicada)**
- **S (Single Responsibility)**: Cada clase tiene una única responsabilidad — por ejemplo, los controladores gestionan HTTP, los servicios la lógica de negocio.
- **O (Open/Closed)**: Las funciones se diseñan para extenderse sin modificar el código existente (ej. inyección de dependencias para distintos proveedores).
- **L (Liskov Substitution)**: Subclases y contratos permiten intercambiar implementaciones sin romper el sistema (interfaces y proveedores).
- **I (Interface Segregation)**: Las interfaces son pequeñas y específicas para evitar dependencias innecesarias.
- **D (Dependency Inversion)**: El código depende de abstracciones (contratos), no de implementaciones concretas — facilita pruebas unitarias y mocks.

En este proyecto verás estas ideas aplicadas en `app/Services`, `app/Providers` y en cómo los controladores reciben dependencias por constructor.

**Guía rápida: Ejecutar localmente (desarrolladores)**
1. Copia `.env.example` a `.env` y configura variables de entorno (BD, mail, APP_URL).

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build   # o `npm run dev` para desarrollo
```

2. Configurar base de datos (local): ajustar `.env` con credenciales MySQL/MariaDB.

3. Migrar y ejecutar seeders:

```bash
php artisan migrate --seed
```

4. Levantar servidor local:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

O usando Docker (si prefiere contenedores): revisar `Dockerfile` y crear un `docker-compose` según su entorno.

**Tests**
- Ejecutar pruebas PHPUnit:

```bash
./vendor/bin/phpunit
```

**Estructura destacada**
- **`app/Models`**: modelos Eloquent (Product, Order, User, etc.).
- **`app/Http/Controllers`**: controladores HTTP y API.
- **`app/Services`**: lógica de negocio reutilizable (aquí se aplican SOLID).
- **`database/seeders`**: datos de ejemplo y cuentas de prueba.
- **`mobile-app`**: (carpeta presente, no utilizada en esta versión)

**Para personas no técnicas**
- Este proyecto es una tienda online de ejemplo: puede ver productos, añadir al carrito y completar pedidos. Está diseñado para demostrar cómo se construye y mantiene una tienda profesional.
- Si no sabes programar, puedes usar las credenciales de demo para entrar como administrador y explorar el panel sin tocar código.

**Despliegue**
- Archivos de despliegue incluidos: `vercel.json`, `render.yaml` y `Dockerfile`.
- En producción el frontend puede desplegarse en Vercel; la base de datos se aloja en TiDB (asegúrate de configurar las variables de conexión en el entorno de producción).

---

Gracias por revisar el proyecto. Para ver detalles de implementación, revisa la documentación técnica en [DOCUMENTACION_ECOMMERCE.md](DOCUMENTACION_ECOMMERCE.md) y los seeders en [database/seeders/AuthUsersSeeder.php](database/seeders/AuthUsersSeeder.php#L1-L120).

