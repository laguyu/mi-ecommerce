# Documentacion Tecnica Integral y Detallada - Mi Ecommerce

## 1. Objetivo de este documento
Este documento explica, en detalle y en lenguaje practico, como funciona el proyecto Laravel + Vue.
Esta guia esta pensada para un desarrollador que no es experto en Laravel ni en Vue.

Cubre:
- Controladores
- Modelos
- Form Requests
- Servicios
- Middleware
- Provider
- Support
- Seeders
- Rutas web
- App Vue, componentes Vue y composables
- Flujos end-to-end
- Matriz de validaciones

## 2. Arquitectura general
Backend:
- Laravel gestiona seguridad, validaciones, reglas de negocio, base de datos, pagos, correos y vistas Blade.

Frontend:
- Vue 3 en `resources/js/App.vue` como orquestador de storefront.
- Componentes especializados por pantalla y por widget.
- Composables para estado compartido (carrito, favoritos, contexto, feedback UI).

Persistencia:
- Eloquent Models para entidades (productos, pedidos, promociones, etc.).

Integraciones:
- Stripe y PayPal para pagos.
- Webhooks para confirmar pago de forma asincrona.
- Excel/PDF para exportes y comprobantes.

## 3. Controladores

### 3.1 Cuenta

#### app/Http/Controllers/Account/OrderHistoryController.php
Proposito:
- Gestiona historial de pedidos del cliente autenticado.

Metodos:
- `index(Request $request): View`: lista pedidos del usuario con filtros y paginacion.
- `show(Request $request, Order $order): View`: muestra detalle de un pedido y valida pertenencia del pedido al usuario.
- `export(Request $request): BinaryFileResponse`: exporta pedidos del usuario a Excel.
- `pdf(Request $request, Order $order)`: genera PDF del pedido.

Efectos:
- Lectura de DB de `orders` y `order_items`.
- Generacion de archivo (Excel/PDF).

#### app/Http/Controllers/Account/ProfileController.php
Proposito:
- Editar perfil de usuario autenticado.

Metodos:
- `edit(Request $request): View`: renderiza formulario de perfil.
- `update(Request $request): RedirectResponse`: valida y actualiza datos de perfil.

Efectos:
- Update en tabla `users`.

### 3.2 Admin

#### app/Http/Controllers/Admin/BrandController.php
Proposito:
- CRUD de marcas.

Metodos:
- `index(Request $request): View`: listado con busqueda y paginacion.
- `create(): View`: formulario alta.
- `store(BrandRequest $request): RedirectResponse`: crea marca.
- `edit(Brand $brand): View`: formulario edicion.
- `update(BrandRequest $request, Brand $brand): RedirectResponse`: actualiza marca.
- `destroy(Brand $brand): RedirectResponse`: elimina marca y desacopla productos.
- `uniqueSlug(string $name, ?int $ignoreId = null): string`: genera slug unico.
- `flushCatalogBrandCache(): void`: invalida cache de marcas del catalogo.

#### app/Http/Controllers/Admin/CategoryController.php
Proposito:
- CRUD de categorias con arbol padre/hijo.

Metodos:
- `index(Request $request): View`: listado con busqueda y conteo de productos.
- `create(): View`: formulario alta.
- `store(CategoryRequest $request): RedirectResponse`: crea categoria.
- `edit(Category $category): View`: formulario edicion.
- `update(CategoryRequest $request, Category $category): RedirectResponse`: actualiza categoria.
- `destroy(Category $category): RedirectResponse`: elimina categoria si no tiene productos.
- `uniqueSlug(string $name, ?int $ignoreId = null): string`: slug unico.
- `categoryTreeOptions(array $excludeIds = []): array`: construye opciones jerarquicas para selects.
- `flushCatalogCategoryCache(): void`: invalida cache de categorias.

#### app/Http/Controllers/Admin/CouponController.php
Proposito:
- CRUD de cupones.

Metodos:
- `index(Request $request): View`: listado con busqueda.
- `create(): View`: formulario alta.
- `store(CouponRequest $request): RedirectResponse`: crea cupon.
- `edit(Coupon $coupon): View`: formulario edicion.
- `update(CouponRequest $request, Coupon $coupon): RedirectResponse`: actualiza cupon.
- `destroy(Coupon $coupon): RedirectResponse`: elimina cupon.

#### app/Http/Controllers/Admin/HomeBannerController.php
Proposito:
- CRUD de banner principal home.

Metodos:
- `index(): View`: lista banners.
- `create(): View`: formulario alta.
- `store(HomeBannerRequest $request): RedirectResponse`: crea banner y sube imagen.
- `edit(HomeBanner $homeBanner): View`: formulario edicion.
- `update(HomeBannerRequest $request, HomeBanner $homeBanner): RedirectResponse`: actualiza metadata e imagen opcional.
- `destroy(HomeBanner $homeBanner): RedirectResponse`: elimina banner e imagen.
- `storagePathFromPublicUrl(string $url): ?string`: convierte URL publica a path de disco.

#### app/Http/Controllers/Admin/HomeProductCarouselController.php
Proposito:
- CRUD de carruseles de productos home.

Metodos:
- `index(): View`: lista carruseles.
- `create(): View`: formulario alta.
- `searchProducts(Request $request): JsonResponse`: busqueda de productos para selector del carrusel.
- `store(HomeProductCarouselRequest $request): RedirectResponse`: crea carrusel, valida maximo 3, sube imagen y sincroniza productos.
- `edit(HomeProductCarousel $homeProductCarousel): View`: formulario edicion.
- `update(HomeProductCarouselRequest $request, HomeProductCarousel $homeProductCarousel): RedirectResponse`: actualiza datos, imagen opcional y productos.
- `destroy(HomeProductCarousel $homeProductCarousel): RedirectResponse`: elimina carrusel, relaciones e imagen.
- `storagePathFromPublicUrl(string $url): ?string`: helper de storage.

#### app/Http/Controllers/Admin/HomeSecondaryBannerController.php
Proposito:
- CRUD de banners secundarios home.

Metodos:
- `index(): View`
- `create(): View`
- `store(HomeSecondaryBannerRequest $request): RedirectResponse`
- `edit(HomeSecondaryBanner $homeSecondaryBanner): View`
- `update(HomeSecondaryBannerRequest $request, HomeSecondaryBanner $homeSecondaryBanner): RedirectResponse`
- `destroy(HomeSecondaryBanner $homeSecondaryBanner): RedirectResponse`
- `storagePathFromPublicUrl(string $url): ?string`

#### app/Http/Controllers/Admin/NewsletterSubscriberController.php
Proposito:
- Gestion de suscriptores de newsletter.

Metodos:
- `index(Request $request): View`: listado con filtro por estado y busqueda.
- `toggle(NewsletterSubscriber $newsletterSubscriber): RedirectResponse`: activa/desactiva suscriptor.
- `destroy(NewsletterSubscriber $newsletterSubscriber): RedirectResponse`: elimina suscriptor.
- `export(Request $request): BinaryFileResponse`: exporta a Excel (all/active/inactive).

#### app/Http/Controllers/Admin/ContactMessageController.php
Proposito:
- Gestion de mensajes enviados desde el formulario publico de contacto.

Metodos:
- `index(Request $request): View`: listado con busqueda, filtro por estado y paginacion.
- `show(ContactMessage $contactMessage): View`: detalle de un mensaje con estado y error de envio si existe.
- `destroy(ContactMessage $contactMessage): RedirectResponse`: elimina un mensaje.

#### app/Http/Controllers/Admin/OrderController.php
Proposito:
- Gestion administrativa de pedidos.

Metodos:
- `__construct(...)`: inyeccion de `OrderNotificationService`.
- `index(OrderFilterRequest $request): View`: listado con filtros.
- `updateStatus(OrderStatusRequest $request, Order $order): RedirectResponse`: cambia estado y dispara notificacion si hubo cambio.
- `export(OrderFilterRequest $request): BinaryFileResponse`: exporta pedidos a Excel.
- `show(Order $order): View`: detalle pedido.
- `pdf(Order $order)`: PDF administrativo.

#### app/Http/Controllers/Admin/ProductController.php
Proposito:
- CRUD de productos + gestion de imagenes y visibilidad home.

Metodos:
- `index(Request $request): View`
- `create(): View`
- `store(ProductRequest $request): RedirectResponse`
- `edit(Product $product): View`
- `update(ProductRequest $request, Product $product): RedirectResponse`
- `destroy(Product $product): RedirectResponse`
- `storagePathFromPublicUrl(string $url): ?string`
- `uniqueSlug(string $name, ?int $ignoreId = null): string`
- `uploadProductImages(Product $product, array $uploadedFiles, string $altText): void`

#### app/Http/Controllers/Admin/PromotionController.php
Proposito:
- CRUD promociones y asignacion de productos.

Metodos:
- `index(Request $request): View`
- `create(): View`
- `store(PromotionRequest $request): RedirectResponse`
- `edit(Promotion $promotion): View`
- `searchProducts(Request $request): JsonResponse`
- `update(PromotionRequest $request, Promotion $promotion): RedirectResponse`
- `storagePathFromPublicUrl(string $url): ?string`
- `destroy(Promotion $promotion): RedirectResponse`

#### app/Http/Controllers/Admin/SiteSettingController.php
Proposito:
- Configuracion global del sitio (branding, colores, footer, politicas, cuentas bancarias).

Metodos:
- `edit(): View`
- `update(SiteSettingRequest $request): RedirectResponse`
- `storagePathFromPublicUrl(string $url): ?string`

#### app/Http/Controllers/Admin/UserController.php
Proposito:
- Gestion de usuarios y roles.

Metodos:
- `index(Request $request): View`
- `edit(User $user): View`
- `update(UserUpdateRequest $request, User $user): RedirectResponse`
- `updateRole(UserRoleRequest $request, User $user): RedirectResponse`

### 3.3 Storefront y Auth

#### app/Http/Controllers/AuthController.php
Metodos:
- `showLogin`, `login`, `showRegister`, `register`, `logout`, `showForgotPassword`, `sendResetLink`, `showResetPassword`, `resetPassword`.

Detalle funcional:
- Maneja autenticacion completa y recuperacion de password.
- Aplica validaciones inline en flujos de auth.
- Redirecciones segun resultado de login/registro.

#### app/Http/Controllers/CatalogController.php
Proposito:
- API publica de catalogo, banners, categorias, marcas y detalle de producto.

Metodos:
- `__construct(...)`
- `mainBanner()`: banner principal (tabla banners o fallback a producto).
- `promotionBanner()`: promocion activa para hero promocional.
- `banners()`: items de banner principal.
- `secondaryBanners()`: banners secundarios activos.
- `homeProductCarousels()`: carruseles configurados en admin con productos mapeados.
- `featured()`: productos destacados home.
- `index(Request $request)`: listado paginado + filtros + orden + promocion.
- `categories()`: devuelve categorias aplanadas.
- `brands()`: devuelve marcas para filtros.
- `show(Product $product)`: detalle completo de producto e imagenes.
- `mapProduct(Product $product)`: DTO del producto para frontend.
- `flattenCategories(...)`: construye lista jerarquica lineal.
- `prefixLike(string $value)`: helper para busqueda prefijo.
- `categoryAndDescendantIds(array $categoryIds)`: expande filtro a descendientes.
- `catalogProductRelations()`: define eager loading optimizado para productos de catalogo.

#### app/Http/Controllers/CheckoutController.php
Proposito:
- Preparar pedido antes de cobrar.

Metodos:
- `__construct(...)`
- `prepare(Request $request)`: valida payload, verifica stock, aplica promociones y cupon, calcula totales, crea `orders` + `order_items` en transaccion.
- `validateCoupon(Request $request)`: valida cupon para preview.
- `summary(Order $order)`: resumen de pedido para frontend post-checkout.
- `generateOrderNumber()`: generador de numero de orden.
- `resolveCouponDiscount(...)`: motor de validacion y calculo de cupon.

#### app/Http/Controllers/FavoriteController.php
Metodos:
- `ids(Request $request): JsonResponse`
- `index(Request $request): JsonResponse`
- `toggle(Request $request): JsonResponse`

#### app/Http/Controllers/NewsletterController.php
Metodos:
- `subscribe(Request $request): JsonResponse`

#### app/Http/Controllers/PaymentController.php
Metodos:
- `__construct(...)`
- `createStripeCheckoutSession(Request $request)`
- `stripeSuccess(Request $request, Order $order)`
- `createPaypalOrder(Request $request)`
- `paypalReturn(Request $request, Order $order)`
- `cancel(Order $order)`
- `paypalAccessToken()`
- `paypalBaseUrl()`

#### app/Http/Controllers/PolicyPageController.php
Metodos:
- `show(string $slug): View`

#### app/Http/Controllers/WebhookController.php
Metodos:
- `__construct(...)`
- `stripe(Request $request)`
- `paypal(Request $request)`
- `isValidStripeSignature(...)`
- `isValidPaypalWebhook(...)`
- `paypalAccessToken(...)`
- `paypalBaseUrl()`

## 4. Modelos

### app/Models/Brand.php
Metodos:
- `products(): HasMany`

### app/Models/Category.php
Metodos:
- `parent(): BelongsTo`
- `children(): HasMany`
- `products(): HasMany`
- `getFullNameAttribute(): string`

### app/Models/Coupon.php
Metodos:
- `isActiveNow(?Carbon $at = null): bool`
- `calculateDiscountAmount(float $subtotal): float`

### app/Models/Favorite.php
Metodos:
- `user(): BelongsTo`
- `product(): BelongsTo`

### app/Models/HomeBanner.php
Metodos:
- `product(): BelongsTo`

### app/Models/HomeProductCarousel.php
Metodos:
- `products(): BelongsToMany`

### app/Models/HomeSecondaryBanner.php
Metodos:
- `product(): BelongsTo`

### app/Models/NewsletterSubscriber.php
Metodos propios:
- No define metodos custom, usa Eloquent base.

### app/Models/ContactMessage.php
Metodos propios:
- No define metodos custom, usa Eloquent base.
- Campos relevantes: `status`, `recipient_email`, `sent_at`, `delivery_error`.

### app/Models/Order.php
Metodos:
- `items(): HasMany`
- `user(): BelongsTo`

### app/Models/OrderItem.php
Metodos:
- `order(): BelongsTo`
- `product(): BelongsTo`

### app/Models/Product.php
Metodos:
- `category(): BelongsTo`
- `brand(): BelongsTo`
- `images(): HasMany`
- `primaryImage(): HasOne`
- `favoredByUsers(): BelongsToMany`
- `promotions(): BelongsToMany`

### app/Models/ProductImage.php
Metodos:
- `product(): BelongsTo`

### app/Models/Promotion.php
Metodos:
- `products(): BelongsToMany`
- `scopeActiveNow(Builder $query): Builder`

### app/Models/RoleChangeLog.php
Metodos:
- `changedBy(): BelongsTo`
- `targetUser(): BelongsTo`

### app/Models/SiteSetting.php
Metodos:
- `getLogoUrlAttribute(): ?string`
- Adicional importante: `current()` (singleton de settings) usado globalmente.

### app/Models/User.php
Metodos:
- `casts(): array`
- `orders(): HasMany`
- `favoriteProducts(): BelongsToMany`
- `hasAnyRole(array $roles): bool`
- `hasPermission(string $permission): bool`
- `permissionList(): array`

## 5. Form Requests
Todos los FormRequest de admin tienen:
- `authorize(): bool` -> retorna `true`.
- `rules(): array` -> reglas de validacion especificas.

Archivos:
- app/Http/Requests/Admin/BrandRequest.php
- app/Http/Requests/Admin/CategoryRequest.php
- app/Http/Requests/Admin/CouponRequest.php
- app/Http/Requests/Admin/HomeBannerRequest.php
- app/Http/Requests/Admin/HomeProductCarouselRequest.php
- app/Http/Requests/Admin/HomeSecondaryBannerRequest.php
- app/Http/Requests/Admin/OrderFilterRequest.php
- app/Http/Requests/Admin/OrderStatusRequest.php
- app/Http/Requests/Admin/ProductRequest.php
- app/Http/Requests/Admin/PromotionRequest.php
- app/Http/Requests/Admin/SiteSettingRequest.php
- app/Http/Requests/Admin/UserRoleRequest.php
- app/Http/Requests/Admin/UserUpdateRequest.php
- app/Http/Requests/ContactMessageRequest.php

Notas especiales:
- `CategoryRequest` incluye `descendantIds(...)` para evitar ciclos padre/hijo.
- `SiteSettingRequest` incluye `withValidator(...)` para validar integridad de cuentas bancarias por fila.
- `ContactMessageRequest` valida el formulario publico de contacto y no depende de autenticacion.
- Las imagenes de productos usan un tope de 4 MB y 1600x1600 px maximo.
- Las imagenes de banners, carruseles y promociones usan un tope de 3 MB y 1920x1080 px maximo.

## 6.1. Correos

### app/Mail/ContactMessageMail.php
Proposito:
- Construye el correo que se envia al email configurado en `footer_email` cuando llega un mensaje de contacto.

Detalle:
- Reutiliza `MailBrandingData` para mantener branding consistente.
- Usa `replyTo` con el email del remitente para que el equipo pueda responder directo desde su cliente de correo.

## 6. Servicios

### app/Services/OrderNotificationService.php
Metodos:
- `sendForSuccessfulOrder(Order $order, bool $isPaid): void`
- `sendStatusChangedToCustomer(Order $order, string $previousStatus, string $newStatus): void`

### app/Services/OrderPaymentService.php
Metodos:
- `__construct(...)`
- `markOrderAsPaid(Order $order, string $method, string $reference): bool`

Detalle:
- Protege consistencia con transacciones.
- Evita doble procesamiento de pago.
- Actualiza estado + stock + notificaciones.

### app/Services/ProductPricingService.php
Metodos:
- `pricingForProduct(Product $product, ?CarbonInterface $at = null): array`
- `resolveActivePromotion(Product $product, CarbonInterface $at): ?Promotion`

Detalle:
- Determina precio final segun promociones activas.
- Centraliza calculo de descuento para catalogo y checkout.

## 7. Middleware

### app/Http/Middleware/EnsureAdmin.php
- `handle(...)`: permite solo admins.

### app/Http/Middleware/EnsurePermission.php
- `handle(...)`: valida permisos granulares del usuario.

### app/Http/Middleware/EnsureRole.php
- `handle(...)`: valida rol contra lista permitida.

## 8. Provider

### app/Providers/AppServiceProvider.php
Metodos:
- `register(): void`
- `boot(): void`

Detalle:
- Configura locale `es`.
- Inyecta `siteSettings` en vistas via `View::composer('*', ...)`.

## 9. Support

### app/Support/CatalogData.php
Metodos:
- `all(): array`: dataset de catalogo demo.
- `byId(): array`: indice por id.

### app/Support/MailBrandingData.php
Metodos:
- `fromSettings(): array`: obtiene branding para correos desde settings con fallback seguro.

## 10. Seeders

### database/seeders/CouponSeeder.php
- `run(): void`: crea cupones iniciales.

### database/seeders/DatabaseSeeder.php
- `run(): void`: seeder raiz.

### database/seeders/EcommerceSeeder.php
- `run(): void`: siembra entidades ecommerce base.
- `seedBulkProducts(array $categoryIds, array $brandIds, int $count): void`: crea volumen de productos de prueba.

## 11. Rutas web (routes/web.php)

Bloques clave:
- Redireccion raiz `/` segun rol y query `storefront`.
- Grupo `admin` con `auth` para dashboard.
- Grupo `tienda` para vistas storefront (`home`, `catalogo`, `favoritos`, `carrito`, `checkout`, `producto/{id}`).
- APIs publicas de catalogo y checkout previo.
- APIs autenticadas de favoritos.
- Endpoints de pagos y retornos.
- Endpoints de webhooks.
- Rutas guest (`login`, `register`, recuperacion password).
- Rutas auth de cuenta (`mi-cuenta`, pedidos, export, pdf).
- Grupos admin por permisos:
- `view_admin_orders`
- `manage_categories`
- `manage_products`
- `manage_site_settings`
- `manage_users`

## 12. Frontend Vue

## 12.1 App principal

### resources/js/App.vue
Rol:
- Orquestador de la app storefront.
- Renderiza seccion segun `activeView`.
- Coordina carrito, favoritos, drawer y toasts.

Funciones:
- `formatCurrency(amount)`
- `goToCartPage()`
- `goToCheckout()`
- `handleAddToCart(product)`
- `onMounted(...)`

Estado/computed:
- `cartPreviewItems`
- `cartPreviewRemainingItems`

## 12.2 Componentes storefront

### resources/js/components/storefront/StorefrontHeader.vue
Props/Emits:
- Props de navegacion, estado de auth, conteos, categorias.
- Emits: `navigate`, `open-cart`.

Funciones:
- `submitSearch`, `buildCategoryUrl`, `buildCategoryTree`, `navigateToCategory`, `navigateTo`, `openCartPreview`, `openCategories`, `closeCategories`, `toggleMobileMenu`, `closeMobileMenu`, `toggleMobileCategory`, `isMobileCategoryOpen`, `toggleCategories`, `setHoveredCategory`.

### resources/js/components/storefront/HomeSection.vue
Funciones:
- `handleMainBannerClick`, `handlePromotionBannerClick`, `handleSecondaryBannerClick`, `isSoldOut`, `isFavorite`, `nextSlide`, `prevSlide`, `setSlide`, `stopCarousel`, `stopPromotionCountdown`, `formatDuration`, `startPromotionCountdown`, `startCarousel`.
Hooks:
- `onMounted`, `onUnmounted`.

### resources/js/components/storefront/CatalogSection.vue
Funciones:
- `isSoldOut`, `isFavorite`, `formatDuration`, `stopPromotionCountdown`, `startPromotionCountdown`, `getStoredState`, `getUrlState`, `persistState`, `syncUrl`, `restoreState`, `toggleFilter`, `clearFilters`, `clearPromotionFilter`, `applyFilters`, `scheduleReload`.
Hooks/reactividad:
- `onMounted`, `watch`, `onUnmounted`.

### resources/js/components/storefront/FavoritesSection.vue
Funciones:
- `isSoldOut`, `isFavorite`.
Hooks:
- `onMounted`, `watch` (sincronizacion de favoritos/estado).

### resources/js/components/storefront/ProductDetailSection.vue
Funciones:
- `selectImage(index)`.
Computed:
- `currentProductImages`, `currentProductMainImage`, `isSoldOut`.
Watch:
- recarga detalle cuando cambia `productId`.

### resources/js/components/storefront/CartSection.vue
- Componente presentacional de carrito.
- Emite: `decrease`, `increase`, `remove-item`, `go-checkout`.

### resources/js/components/storefront/CheckoutSection.vue
Funciones clave:
- `getItemTotal`, `getCsrfToken`, `clearErrors`, `validateCheckout`, `clearCoupon`, `handleContinueShopping`.
- Gestiona payload y llamada a preparar pedido + pago.

### resources/js/components/storefront/StorefrontFooter.vue
- Newsletter y render de datos de contacto/politicas.

### resources/js/components/storefront/ToastStack.vue
- Render de cola de toasts (presentacional).

### resources/js/components/storefront/CartDrawerPreview.vue
- Modulo aislado para drawer del carrito.
- Emite `close` y `go-cart`.
- Usa `transition name="cart-drawer"`.

## 12.3 Composables

### resources/js/composables/useCart.js
Estado:
- `cart`, `itemsCount`, `subtotal`, `productDiscountAmount`, `totalDiscountAmount`, `subtotalAfterDiscount`, `shippingAmount`, `total`, `hasItems`.

Funciones:
- `getInitialCart`, `findItem`, `getMaxStock`, `addItem`, `setQuantity`, `increase`, `decrease`, `removeItem`, `clearCart`.
- `watch(cart, ...)` para persistir en `localStorage`.

### resources/js/composables/useFavorites.js
Funciones:
- `getCsrfToken`, `useFavorites`, `loadFavoriteIds`, `isFavorite`, `toggleFavorite`.

### resources/js/composables/useStorefrontContext.js
Funciones:
- `readStorefrontContext`, `readStorefrontLocation`, `readSiteSettings`, `readReturnCatalogUrl`, `saveProductOrigin`, `canShowBackToCatalog`, `useStorefrontContext`, `navigateTo`, `loadCatalogCategories`, `openProductDetail`, `openProductFromCatalog`, `openProductFromOtherSections`, `openPromotionCatalog`, `initializeStorefront`.

### resources/js/composables/useUiFeedback.js
Funciones:
- `useUiFeedback`, `openCartPreview`, `closeCartPreview`, `pushToast`.

## 13. Flujos end-to-end

### 13.1 Catalogo y filtros
1. Front pide categorias/marcas/catalogo.
2. `CatalogController@index` aplica filtros y pagina.
3. `ProductPricingService` calcula precio final por producto.
4. Vue renderiza cards y countdown de promocion activa.

### 13.2 Favoritos
1. Vue carga ids favoritos al montar.
2. Toggle envia POST a `/api/favorites/toggle`.
3. Backend sincroniza tabla `favorites` y devuelve ids.
4. Front actualiza estado y muestra toast.

### 13.3 Checkout y pago
1. Usuario confirma carrito.
2. `CheckoutController@prepare` valida stock, cupon y crea pedido.
3. Front redirige/lanza Stripe o PayPal.
4. Webhook confirma pago y `OrderPaymentService` marca pagado.
5. Se dispara envio de notificaciones por correo.

### 13.4 Promociones y pricing
1. Admin crea promocion y asigna productos.
2. Catalogo trae promociones activas ya ordenadas.
3. `ProductPricingService` define descuento efectivo.
4. UI muestra precio original/final y porcentaje.

### 13.5 CRUD admin
1. Vistas Blade envian formulario.
2. FormRequest valida.
3. Controlador ejecuta persistencia + archivos + cache.
4. Redirect con mensaje.

## 14. Matriz de validacion (resumen)
- Productos: `ProductRequest` controla SKU unico, precio/stock, reglas de banner/carrusel e imagenes.
- Promociones: `PromotionRequest` valida fechas, descuento y `product_ids`.
- Cupones: `CouponRequest` valida tipo y limites de valor.
- Site settings: `SiteSettingRequest` valida branding y estructura bancaria.
- Usuarios: `UserUpdateRequest` y `UserRoleRequest`.
- Ordenes admin: `OrderFilterRequest` y `OrderStatusRequest`.
- Categorias y marcas: `CategoryRequest`, `BrandRequest`.
- Banners: `HomeBannerRequest`, `HomeSecondaryBannerRequest`, `HomeProductCarouselRequest`.

## 15. Notas de mantenibilidad (SOLID)
Fortalezas:
- Validacion separada en FormRequest (SRP).
- Logica critica en Services (OrderPaymentService, ProductPricingService).
- Frontend con composables para estado compartido.

Mejoras recomendadas:
- Crear API client central en Vue para manejar errores y headers comunes.
- Incorporar pruebas unitarias para services de pricing/pago.
- Agregar pruebas de integracion para webhooks y checkout.

## 16. Glosario rapido
- Eloquent: ORM de Laravel para mapear tablas a objetos.
- FormRequest: clase dedicada a validar requests.
- Middleware: filtro que corre antes del controlador.
- Seeder: poblador de datos iniciales/de prueba.
- Composable: funcion reusable de Vue para encapsular estado y logica.
- Webhook: callback de un proveedor externo hacia tu backend.
- Scope (Eloquent): filtro reusable en consultas de un modelo.

## 17. Mapa rapido de dependencias
- `CatalogController` depende de `ProductPricingService`.
- `CheckoutController` depende de `ProductPricingService` y reglas de cupon.
- `PaymentController` y `WebhookController` dependen de `OrderPaymentService`.
- `OrderPaymentService` depende de `OrderNotificationService`.
- `App.vue` depende de composables `useStorefrontContext`, `useFavorites`, `useUiFeedback`, `useCart`.

Fin de documentacion.

## 18. Anexo A - Inventario automatico de metodos y rutas

FILE .\app\Http\Controllers\Account\OrderHistoryController.php
public function index(Request $request): View
public function show(Request $request, Order $order): View
public function export(Request $request): BinaryFileResponse
public function pdf(Request $request, Order $order)

FILE .\app\Http\Controllers\Account\ProfileController.php
public function edit(Request $request): View
public function update(Request $request): RedirectResponse

FILE .\app\Http\Controllers\Admin\BrandController.php
public function index(Request $request): View
public function create(): View
public function store(BrandRequest $request): RedirectResponse
public function edit(Brand $brand): View
public function update(BrandRequest $request, Brand $brand): RedirectResponse
public function destroy(Brand $brand): RedirectResponse
private function uniqueSlug(string $name, ?int $ignoreId = null): string
private function flushCatalogBrandCache(): void

FILE .\app\Http\Controllers\Admin\CategoryController.php
public function index(Request $request): View
public function create(): View
public function store(CategoryRequest $request): RedirectResponse
public function edit(Category $category): View
public function update(CategoryRequest $request, Category $category): RedirectResponse
public function destroy(Category $category): RedirectResponse
private function uniqueSlug(string $name, ?int $ignoreId = null): string
private function categoryTreeOptions(array $excludeIds = []): array
private function flushCatalogCategoryCache(): void

FILE .\app\Http\Controllers\Admin\CouponController.php
public function index(Request $request): View
public function create(): View
public function store(CouponRequest $request): RedirectResponse
public function edit(Coupon $coupon): View
public function update(CouponRequest $request, Coupon $coupon): RedirectResponse
public function destroy(Coupon $coupon): RedirectResponse

FILE .\app\Http\Controllers\Admin\HomeBannerController.php
public function index(): View
public function create(): View
public function store(HomeBannerRequest $request): RedirectResponse
public function edit(HomeBanner $homeBanner): View
public function update(HomeBannerRequest $request, HomeBanner $homeBanner): RedirectResponse
public function destroy(HomeBanner $homeBanner): RedirectResponse
private function storagePathFromPublicUrl(string $url): ?string

FILE .\app\Http\Controllers\Admin\HomeProductCarouselController.php
public function index(): View
public function create(): View
public function searchProducts(Request $request): JsonResponse
public function store(HomeProductCarouselRequest $request): RedirectResponse
public function edit(HomeProductCarousel $homeProductCarousel): View
public function update(HomeProductCarouselRequest $request, HomeProductCarousel $homeProductCarousel): RedirectResponse
public function destroy(HomeProductCarousel $homeProductCarousel): RedirectResponse
private function storagePathFromPublicUrl(string $url): ?string

FILE .\app\Http\Controllers\Admin\HomeSecondaryBannerController.php
public function index(): View
public function create(): View
public function store(HomeSecondaryBannerRequest $request): RedirectResponse
public function edit(HomeSecondaryBanner $homeSecondaryBanner): View
public function update(HomeSecondaryBannerRequest $request, HomeSecondaryBanner $homeSecondaryBanner): RedirectResponse
public function destroy(HomeSecondaryBanner $homeSecondaryBanner): RedirectResponse
private function storagePathFromPublicUrl(string $url): ?string

FILE .\app\Http\Controllers\Admin\NewsletterSubscriberController.php
public function index(Request $request): View
public function toggle(NewsletterSubscriber $newsletterSubscriber): RedirectResponse
public function destroy(NewsletterSubscriber $newsletterSubscriber): RedirectResponse
public function export(Request $request): BinaryFileResponse

FILE .\app\Http\Controllers\Admin\OrderController.php
public function __construct(private readonly OrderNotificationService $orderNotificationService)
public function index(OrderFilterRequest $request): View
public function updateStatus(OrderStatusRequest $request, Order $order): RedirectResponse
public function export(OrderFilterRequest $request): BinaryFileResponse
public function show(Order $order): View
public function pdf(Order $order)

FILE .\app\Http\Controllers\Admin\ProductController.php
public function index(Request $request): View
public function create(): View
public function store(ProductRequest $request): RedirectResponse
public function edit(Product $product): View
public function update(ProductRequest $request, Product $product): RedirectResponse
public function destroy(Product $product): RedirectResponse
private function storagePathFromPublicUrl(string $url): ?string
private function uniqueSlug(string $name, ?int $ignoreId = null): string
private function uploadProductImages(Product $product, array $uploadedFiles, string $altText): void

FILE .\app\Http\Controllers\Admin\PromotionController.php
public function index(Request $request): View
public function create(): View
public function store(PromotionRequest $request): RedirectResponse
public function edit(Promotion $promotion): View
public function searchProducts(Request $request): JsonResponse
public function update(PromotionRequest $request, Promotion $promotion): RedirectResponse
private function storagePathFromPublicUrl(string $url): ?string
public function destroy(Promotion $promotion): RedirectResponse

FILE .\app\Http\Controllers\Admin\SiteSettingController.php
public function edit(): View
public function update(SiteSettingRequest $request): RedirectResponse
private function storagePathFromPublicUrl(string $url): ?string

FILE .\app\Http\Controllers\Admin\UserController.php
public function index(Request $request): View
public function edit(User $user): View
public function update(UserUpdateRequest $request, User $user): RedirectResponse
public function updateRole(UserRoleRequest $request, User $user): RedirectResponse

FILE .\app\Http\Controllers\AuthController.php
public function showLogin(): View
public function login(Request $request): RedirectResponse
public function showRegister(): View
public function register(Request $request): RedirectResponse
public function logout(Request $request): RedirectResponse
public function showForgotPassword(): View
public function sendResetLink(Request $request): RedirectResponse
public function showResetPassword(Request $request, string $token): View
public function resetPassword(Request $request): RedirectResponse

FILE .\app\Http\Controllers\CatalogController.php
public function __construct(private readonly ProductPricingService $productPricingService)
public function mainBanner(): JsonResponse
public function promotionBanner(): JsonResponse
public function banners(): JsonResponse
public function secondaryBanners(): JsonResponse
public function homeProductCarousels(): JsonResponse
public function featured(): JsonResponse
public function index(Request $request): JsonResponse
public function categories(): JsonResponse
public function brands(): JsonResponse
public function show(Product $product): JsonResponse
private function mapProduct(Product $product): array
private function flattenCategories($categories, string $prefix = '', int $depth = 0): array
private function prefixLike(string $value): string
private function categoryAndDescendantIds(array $categoryIds): array
private function catalogProductRelations(): array

FILE .\app\Http\Controllers\CheckoutController.php
public function __construct(
public function prepare(Request $request): JsonResponse
public function validateCoupon(Request $request): JsonResponse
public function summary(Order $order): JsonResponse
private function generateOrderNumber(): string
private function resolveCouponDiscount(string $couponCode, float $subtotal): array

FILE .\app\Http\Controllers\Controller.php

FILE .\app\Http\Controllers\FavoriteController.php
public function ids(Request $request): JsonResponse
public function index(Request $request): JsonResponse
public function toggle(Request $request): JsonResponse

FILE .\app\Http\Controllers\NewsletterController.php
public function subscribe(Request $request): JsonResponse

FILE .\app\Http\Controllers\PaymentController.php
public function __construct(private readonly OrderPaymentService $orderPaymentService)
public function createStripeCheckoutSession(Request $request): JsonResponse
public function stripeSuccess(Request $request, Order $order): RedirectResponse
public function createPaypalOrder(Request $request): JsonResponse
public function paypalReturn(Request $request, Order $order): RedirectResponse
public function cancel(Order $order): RedirectResponse
private function paypalAccessToken(): ?string
private function paypalBaseUrl(): string

FILE .\app\Http\Controllers\PolicyPageController.php
public function show(string $slug): View

FILE .\app\Http\Controllers\WebhookController.php
public function __construct(private readonly OrderPaymentService $orderPaymentService)
public function stripe(Request $request): JsonResponse
public function paypal(Request $request): JsonResponse
private function isValidStripeSignature(string $payload, string $signatureHeader, string $secret): bool
private function isValidPaypalWebhook(Request $request, array $payload, string $clientId, string $secret, string $webhookId): bool
private function paypalAccessToken(string $clientId, string $secret): ?string
private function paypalBaseUrl(): string

FILE .\app\Http\Middleware\EnsureAdmin.php
public function handle(Request $request, Closure $next): Response

FILE .\app\Http\Middleware\EnsurePermission.php
public function handle(Request $request, Closure $next, string $permission): Response

FILE .\app\Http\Middleware\EnsureRole.php
public function handle(Request $request, Closure $next, string ...$roles): Response

FILE .\app\Http\Requests\Admin\BrandRequest.php
public function authorize(): bool
public function rules(): array

FILE .\app\Http\Requests\Admin\CategoryRequest.php
public function authorize(): bool
public function rules(): array
private function descendantIds(Category $category): array

FILE .\app\Http\Requests\Admin\CouponRequest.php
public function authorize(): bool
public function rules(): array

FILE .\app\Http\Requests\Admin\HomeBannerRequest.php
public function authorize(): bool
public function rules(): array

FILE .\app\Http\Requests\Admin\HomeProductCarouselRequest.php
public function authorize(): bool
public function rules(): array

FILE .\app\Http\Requests\Admin\HomeSecondaryBannerRequest.php
public function authorize(): bool
public function rules(): array

FILE .\app\Http\Requests\Admin\OrderFilterRequest.php
public function authorize(): bool
public function rules(): array

FILE .\app\Http\Requests\Admin\OrderStatusRequest.php
public function authorize(): bool
public function rules(): array

FILE .\app\Http\Requests\Admin\ProductRequest.php
public function authorize(): bool
public function rules(): array

FILE .\app\Http\Requests\Admin\PromotionRequest.php
public function authorize(): bool
public function rules(): array

FILE .\app\Http\Requests\Admin\SiteSettingRequest.php
public function authorize(): bool
public function rules(): array
public function withValidator($validator): void

FILE .\app\Http\Requests\Admin\UserRoleRequest.php
public function authorize(): bool
public function rules(): array

FILE .\app\Http\Requests\Admin\UserUpdateRequest.php
public function authorize(): bool
public function rules(): array

FILE .\app\Models\Brand.php
public function products(): HasMany

FILE .\app\Models\Category.php
public function parent(): BelongsTo
public function children(): HasMany
public function products(): HasMany
public function getFullNameAttribute(): string

FILE .\app\Models\Coupon.php
public function isActiveNow(?Carbon $at = null): bool
public function calculateDiscountAmount(float $subtotal): float

FILE .\app\Models\Favorite.php
public function user(): BelongsTo
public function product(): BelongsTo

FILE .\app\Models\HomeBanner.php
public function product(): BelongsTo

FILE .\app\Models\HomeProductCarousel.php
public function products(): BelongsToMany

FILE .\app\Models\HomeSecondaryBanner.php
public function product(): BelongsTo

FILE .\app\Models\NewsletterSubscriber.php

FILE .\app\Models\Order.php
public function items(): HasMany
public function user(): BelongsTo

FILE .\app\Models\OrderItem.php
public function order(): BelongsTo
public function product(): BelongsTo

FILE .\app\Models\Product.php
public function category(): BelongsTo
public function brand(): BelongsTo
public function images(): HasMany
public function primaryImage(): HasOne
public function favoredByUsers(): BelongsToMany
public function promotions(): BelongsToMany

FILE .\app\Models\ProductImage.php
public function product(): BelongsTo

FILE .\app\Models\Promotion.php
public function products(): BelongsToMany
public function scopeActiveNow(Builder $query): Builder

FILE .\app\Models\RoleChangeLog.php
public function changedBy(): BelongsTo
public function targetUser(): BelongsTo

FILE .\app\Models\SiteSetting.php
public function getLogoUrlAttribute(): ?string

FILE .\app\Models\User.php
protected function casts(): array
public function orders(): HasMany
public function favoriteProducts(): BelongsToMany
public function hasAnyRole(array $roles): bool
public function hasPermission(string $permission): bool
public function permissionList(): array

FILE .\app\Providers\AppServiceProvider.php
public function register(): void
public function boot(): void

FILE .\app\Services\OrderNotificationService.php
public function sendForSuccessfulOrder(Order $order, bool $isPaid): void
public function sendStatusChangedToCustomer(Order $order, string $previousStatus, string $newStatus): void

FILE .\app\Services\OrderPaymentService.php
public function __construct(private readonly OrderNotificationService $orderNotificationService)
public function markOrderAsPaid(Order $order, string $method, string $reference): bool

FILE .\app\Services\ProductPricingService.php
public function pricingForProduct(Product $product, ?CarbonInterface $at = null): array
private function resolveActivePromotion(Product $product, CarbonInterface $at): ?Promotion

FILE .\app\Support\CatalogData.php

FILE .\app\Support\MailBrandingData.php

FILE .\database\seeders\CouponSeeder.php
public function run(): void

FILE .\database\seeders\DatabaseSeeder.php
public function run(): void

FILE .\database\seeders\EcommerceSeeder.php
public function run(): void
private function seedBulkProducts(array $categoryIds, array $brandIds, int $count): void

FILE routes/web.php
Route::get('/', function () {
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
Route::get('/dashboard', fn () => backoffice_dashboard_view())->name('dashboard');
Route::prefix('tienda')->name('storefront.')->group(function () {
Route::get('/', fn () => redirect()->route('storefront.home'));
Route::get('/home', fn () => storefront_view('home'))->name('home');
Route::get('/catalogo', fn () => storefront_view('catalogo'))->name('catalogo');
Route::get('/favoritos', fn () => storefront_view('favoritos'))->name('favoritos');
Route::get('/carrito', fn () => storefront_view('carrito'))->name('carrito');
Route::get('/checkout', fn () => storefront_view('checkout'))->name('checkout');
Route::get('/producto/{product}', function (Product $product) {
Route::get('/api/home-products', [CatalogController::class, 'featured']);
Route::get('/api/home-banners', [CatalogController::class, 'banners']);
Route::get('/api/home-main-banner', [CatalogController::class, 'mainBanner']);
Route::get('/api/home-secondary-banners', [CatalogController::class, 'secondaryBanners']);
Route::get('/api/home-product-carousels', [CatalogController::class, 'homeProductCarousels']);
Route::get('/api/home-promotion-banner', [CatalogController::class, 'promotionBanner']);
Route::get('/api/categories', [CatalogController::class, 'categories']);
Route::get('/api/brands', [CatalogController::class, 'brands']);
Route::get('/api/catalog', [CatalogController::class, 'index']);
Route::get('/api/products/{product}', [CatalogController::class, 'show']);
Route::get('/api/checkout/coupon', [CheckoutController::class, 'validateCoupon']);
Route::post('/api/newsletter/subscribe', [NewsletterController::class, 'subscribe']);
Route::post('/api/checkout/prepare', [CheckoutController::class, 'prepare']);
Route::get('/api/orders/{order}/summary', [CheckoutController::class, 'summary']);
Route::get('/politicas/{slug}', [PolicyPageController::class, 'show'])->name('storefront.policy.show');
Route::middleware('auth')->group(function () {
Route::get('/api/favorites', [FavoriteController::class, 'index']);
Route::get('/api/favorites/ids', [FavoriteController::class, 'ids']);
Route::post('/api/favorites/toggle', [FavoriteController::class, 'toggle']);
Route::post('/api/payments/stripe/checkout-session', [PaymentController::class, 'createStripeCheckoutSession']);
Route::post('/api/payments/paypal/order', [PaymentController::class, 'createPaypalOrder']);
Route::get('/checkout/stripe/success/{order}', [PaymentController::class, 'stripeSuccess'])->name('checkout.stripe.success');
Route::get('/checkout/paypal/return/{order}', [PaymentController::class, 'paypalReturn'])->name('checkout.paypal.return');
Route::get('/checkout/cancel/{order}', [PaymentController::class, 'cancel'])->name('checkout.cancel');
Route::post('/webhooks/stripe', [WebhookController::class, 'stripe'])->name('webhooks.stripe');
Route::post('/webhooks/paypal', [WebhookController::class, 'paypal'])->name('webhooks.paypal');
Route::middleware('guest')->group(function () {
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware('auth')->group(function () {
Route::get('/mi-cuenta', [AccountProfileController::class, 'edit'])->name('account.profile.edit');
Route::put('/mi-cuenta', [AccountProfileController::class, 'update'])->name('account.profile.update');
Route::get('/mi-cuenta/pedidos/exportar', [OrderHistoryController::class, 'export'])->name('account.orders.export');
Route::get('/mi-cuenta/pedidos/{order}/pdf', [OrderHistoryController::class, 'pdf'])->name('account.orders.pdf');
Route::get('/mi-cuenta/pedidos', [OrderHistoryController::class, 'index'])->name('account.orders.index');
Route::get('/mi-cuenta/pedidos/{order}', [OrderHistoryController::class, 'show'])->name('account.orders.show');
Route::middleware(['auth', 'permission:view_admin_orders'])->prefix('admin')->name('admin.')->group(function () {
Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
Route::get('/orders/exportar', [AdminOrderController::class, 'export'])->name('orders.export');
Route::get('/orders/{order}/pdf', [AdminOrderController::class, 'pdf'])->name('orders.pdf');
Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
Route::middleware(['auth', 'permission:manage_categories'])->prefix('admin')->name('admin.')->group(function () {
Route::resource('categories', AdminCategoryController::class)->except(['show']);
Route::middleware(['auth', 'permission:manage_products'])->prefix('admin')->name('admin.')->group(function () {
Route::get('/promotions/search-products', [AdminPromotionController::class, 'searchProducts'])->name('promotions.search-products');
Route::get('/home-product-carousels/search-products', [AdminHomeProductCarouselController::class, 'searchProducts'])->name('home-product-carousels.search-products');
Route::resource('products', AdminProductController::class)->except(['show']);
Route::resource('promotions', AdminPromotionController::class)->except(['show']);
Route::resource('coupons', AdminCouponController::class)->except(['show']);
Route::resource('home-banners', AdminHomeBannerController::class)->except(['show']);
Route::resource('home-secondary-banners', AdminHomeSecondaryBannerController::class)
Route::resource('home-product-carousels', AdminHomeProductCarouselController::class)
Route::resource('brands', \App\Http\Controllers\Admin\BrandController::class)->except(['show']);
Route::middleware(['auth', 'permission:manage_site_settings'])->prefix('admin')->name('admin.')->group(function () {
Route::get('/site-settings', [AdminSiteSettingController::class, 'edit'])->name('site-settings.edit');
Route::put('/site-settings', [AdminSiteSettingController::class, 'update'])->name('site-settings.update');
Route::get('/newsletter-subscribers', [AdminNewsletterSubscriberController::class, 'index'])->name('newsletter-subscribers.index');
Route::get('/newsletter-subscribers/exportar', [AdminNewsletterSubscriberController::class, 'export'])->name('newsletter-subscribers.export');
Route::patch('/newsletter-subscribers/{newsletterSubscriber}/toggle', [AdminNewsletterSubscriberController::class, 'toggle'])->name('newsletter-subscribers.toggle');
Route::delete('/newsletter-subscribers/{newsletterSubscriber}', [AdminNewsletterSubscriberController::class, 'destroy'])->name('newsletter-subscribers.destroy');
Route::middleware(['auth', 'permission:manage_users'])->prefix('admin')->name('admin.')->group(function () {
Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.update-role');

FILE .\resources\js\App.vue
const cartPreviewItems = computed(() => cart.value.slice(0, 5));
const cartPreviewRemainingItems = computed(() => Math.max(itemsCount.value - cartPreviewItems.value.reduce((sum, item) => sum + item.quantity, 0), 0));
function formatCurrency(amount) {
function goToCartPage() {
function goToCheckout() {
function handleAddToCart(product) {
onMounted(() => {

FILE .\resources\js\components\storefront\CartDrawerPreview.vue
const emit = defineEmits(['close', 'go-cart']);

FILE .\resources\js\components\storefront\CartSection.vue
const emit = defineEmits(['decrease', 'increase', 'remove-item', 'go-checkout']);

FILE .\resources\js\components\storefront\CatalogSection.vue
const props = defineProps({
const emit = defineEmits(['open-product', 'add-to-cart', 'toggle-favorite']);
const search = ref('');
const selectedCategories = ref([]);
const selectedBrands = ref([]);
const selectedSort = ref('nuevos');
const selectedPromotionId = ref(null);
const promotionLocked = ref(false);
const activePromotion = ref(null);
const activePromotionCountdown = ref('');
const products = ref([]);
const categories = ref([]);
const brands = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, per_page: 24, total: 0 });
const productsLoading = ref(false);
const productsError = ref('');
function isSoldOut(product) {
function isFavorite(productId) {
function formatDuration(ms) {
function stopPromotionCountdown() {
function startPromotionCountdown() {
function getStoredState() {
function getUrlState() {
function persistState(page = pagination.value.current_page) {
function syncUrl(page = pagination.value.current_page) {
function restoreState() {
function toggleFilter(listRef, value) {
function clearFilters() {
function clearPromotionFilter() {
function applyFilters() {
function scheduleReload() {
onMounted(() => {
watch(
onUnmounted(() => {

FILE .\resources\js\components\storefront\CheckoutSection.vue
const props = defineProps({
const emit = defineEmits(['clear-cart', 'toast', 'continue-shopping']);
const checkoutForm = reactive({
const checkoutErrors = reactive({
const paymentMethod = ref('stripe');
const shippingMethod = ref('delivery');
const checkoutApiError = ref('');
const placingOrder = ref(false);
const placedOrder = ref(null);
const couponCode = ref('');
const couponDiscountAmount = ref(0);
const couponMeta = ref(null);
const couponError = ref('');
const couponInfo = ref('');
const applyingCoupon = ref(false);
const paymentMethodModel = computed({
const shippingMethodModel = computed({
const deliveryFee = computed(() => {
const freeShippingThreshold = computed(() => {
const checkoutSubtotalAfterAllDiscounts = computed(() => {
const checkoutShippingAmount = computed(() => {
const checkoutTotal = computed(() => {
const isOnlinePayment = computed(() => {
const bankTransferAccounts = computed(() => {
const hasBankTransferData = computed(() => {
const canCheckout = computed(() => {
function getItemTotal(item) {
function getCsrfToken() {
function clearErrors() {
function validateCheckout() {
function clearCoupon() {
function handleContinueShopping() {
onMounted(() => {

FILE .\resources\js\components\storefront\FavoritesSection.vue
const props = defineProps({
const emit = defineEmits(['open-product', 'add-to-cart', 'toggle-favorite']);
const favorites = ref([]);
const loading = ref(false);
const error = ref('');
function isSoldOut(product) {
function isFavorite(productId) {
onMounted(() => {
watch(
watch(

FILE .\resources\js\components\storefront\HomeSection.vue
const props = defineProps({
const emit = defineEmits(['open-product', 'open-promotion', 'add-to-cart', 'toggle-favorite']);
function handleMainBannerClick() {
function handlePromotionBannerClick() {
function handleSecondaryBannerClick(banner) {
const mainBanner = ref(null);
const promotionBanner = ref(null);
const homeProducts = ref([]);
const secondaryBanners = ref([]);
const homeProductCarousels = ref([]);
const homeLoading = ref(false);
const homeError = ref('');
const carouselIndex = ref(0);
const promotionCountdown = ref('');
const currentSlide = computed(() => homeProducts.value[carouselIndex.value] ?? null);
function isSoldOut(product) {
function isFavorite(productId) {
function nextSlide() {
function prevSlide() {
function setSlide(index) {
function stopCarousel() {
function stopPromotionCountdown() {
function formatDuration(ms) {
function startPromotionCountdown() {
function startCarousel() {
onMounted(() => {
onUnmounted(() => {

FILE .\resources\js\components\storefront\ProductDetailSection.vue
const props = defineProps({
const emit = defineEmits(['add-to-cart', 'back-catalog', 'toggle-favorite']);
const selectedProduct = ref(null);
const selectedImageIndex = ref(0);
const productError = ref('');
const productLoading = ref(false);
const currentProductImages = computed(() => {
const currentProductMainImage = computed(() => {
const isSoldOut = computed(() => Number(selectedProduct.value?.stock ?? 0) <= 0);
function selectImage(index) {
watch(

FILE .\resources\js\components\storefront\StorefrontFooter.vue
const props = defineProps({
const bankTransferAccounts = computed(() => {
const newsletterEmail = ref('');
const newsletterLoading = ref(false);
const newsletterMessage = ref('');
const newsletterError = ref('');

FILE .\resources\js\components\storefront\StorefrontHeader.vue
const props = defineProps({
const emit = defineEmits(['navigate', 'open-cart']);
const quickSearch = ref('');
const categoriesOpen = ref(false);
const mobileMenuOpen = ref(false);
const mobileCategoriesOpen = ref(false);
const hoveredCategoryId = ref(null);
const mobileOpenCategoryIds = ref([]);
const categoryTree = computed(() => buildCategoryTree(props.categories ?? []));
const hasCartItems = computed(() => props.itemsCount > 0);
const hasFavoriteItems = computed(() => props.favoritesCount > 0);
const activeCategory = computed(() => {
function submitSearch() {
function buildCategoryUrl(categoryId) {
function buildCategoryTree(flatCategories) {
function navigateToCategory(categoryId) {
function navigateTo(url) {
function openCartPreview() {
function openCategories() {
function closeCategories() {
function toggleMobileMenu() {
function closeMobileMenu() {
function toggleMobileCategory(categoryId) {
function isMobileCategoryOpen(categoryId) {
function toggleCategories() {
function setHoveredCategory(categoryId) {

FILE .\resources\js\components\storefront\ToastStack.vue

FILE .\resources\js\composables\useCart.js
function getInitialCart() {
export function useCart() {
const cart = ref(getInitialCart());
const itemsCount = computed(() => cart.value.reduce((sum, item) => sum + item.quantity, 0));
const subtotal = computed(() => cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0));
const productDiscountAmount = computed(() => cart.value.reduce((sum, item) => {
const totalDiscountAmount = computed(() => productDiscountAmount.value);
const subtotalAfterDiscount = computed(() => subtotal.value);
const shippingAmount = computed(() => {
const total = computed(() => subtotalAfterDiscount.value);
const hasItems = computed(() => cart.value.length > 0);
function findItem(productId) {
function getMaxStock(itemOrProduct) {
function addItem(product) {
function setQuantity(productId, quantity) {
function increase(productId) {
function decrease(productId) {
function removeItem(productId) {
function clearCart() {
watch(

FILE .\resources\js\composables\useFavorites.js
function getCsrfToken() {
export function useFavorites(storefrontIsAuthenticated, onToast) {
const favoriteIds = ref([]);
const favoritesAuthenticatedByApi = ref(false);
const favoritesIsAuthenticated = computed(() => Boolean(storefrontIsAuthenticated.value || favoritesAuthenticatedByApi.value));
const favoritesCount = computed(() => favoriteIds.value.length);
function isFavorite(productOrId) {

FILE .\resources\js\composables\useStorefrontContext.js
function readStorefrontContext() {
function readStorefrontLocation() {
function readSiteSettings() {
function readReturnCatalogUrl() {
function saveProductOrigin(origin, productId) {
function canShowBackToCatalog(currentProductId) {
export function useStorefrontContext() {
const storefrontContext = ref(readStorefrontContext());
const activeView = ref(storefrontContext.value?.initialView || initialLocation.initialView || 'home');
const selectedProductId = ref(storefrontContext.value?.initialProductId ?? initialLocation.initialProductId ?? null);
const returnCatalogUrl = ref(readReturnCatalogUrl());
const showBackToCatalog = ref(false);
const siteSettings = ref(readSiteSettings());
const catalogCategories = ref([]);
const storefrontUrls = computed(() => storefrontContext.value?.urls ?? {});
const storefrontAccountUrl = computed(() => storefrontContext.value?.accountUrl || '/login');
const storefrontOrdersUrl = computed(() => storefrontContext.value?.ordersUrl || '/login');
const storefrontIsAuthenticated = computed(() => Boolean(storefrontContext.value?.isAuthenticated));
const activeMenuView = computed(() => (activeView.value === 'producto' ? 'catalogo' : activeView.value));
const whatsappContactUrl = computed(() => {
function navigateTo(url) {
function openProductDetail(productOrId, origin = 'otros') {
function openProductFromCatalog(productOrId) {
function openProductFromOtherSections(productOrId) {
function openPromotionCatalog(promotionId) {
function initializeStorefront(onToast) {

FILE .\resources\js\composables\useUiFeedback.js
export function useUiFeedback() {
const cartPreviewOpen = ref(false);
const toasts = ref([]);
function openCartPreview() {
function closeCartPreview() {
function pushToast(message) {
