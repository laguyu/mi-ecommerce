<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="site-settings" content="{{ e(optional($siteSettings)->toJson() ?? '{}') }}">
    @php
        $storefrontContext = [
            'initialView' => $initialStorefrontView ?? 'home',
            'initialProductId' => $initialStorefrontProductId ?? null,
            'isAuthenticated' => auth()->check(),
            'accountUrl' => auth()->check() ? route('account.profile.edit') : route('login'),
            'ordersUrl' => auth()->check() ? route('account.orders.index') : route('login'),
            'flashToast' => session('storefront_toast'),
            'urls' => [
                'home' => route('storefront.home'),
                'catalogo' => route('storefront.catalogo'),
                'contacto' => route('storefront.contacto'),
                'favoritos' => route('storefront.favoritos'),
                'carrito' => route('storefront.carrito'),
                'checkout' => route('storefront.checkout'),
            ],
        ];
    @endphp
    <meta name="storefront-context" content="{{ e(json_encode($storefrontContext, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}">
    <script id="site-settings-data" type="application/json">{!! json_encode($siteSettings?->toArray() ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <style>
        :root {
            --site-menu-bg: {{ data_get($siteSettings, 'menu_background_color', '#ffffff') }};
            --site-menu-text: {{ data_get($siteSettings, 'menu_text_color', '#111827') }};
            --site-menu-active-bg: {{ data_get($siteSettings, 'menu_active_background_color', '#111827') }};
            --site-menu-active-text: {{ data_get($siteSettings, 'menu_active_text_color', '#ffffff') }};
            --site-button-bg: {{ data_get($siteSettings, 'button_background_color', '#111827') }};
            --site-button-text: {{ data_get($siteSettings, 'button_text_color', '#ffffff') }};
            --site-footer-bg: {{ data_get($siteSettings, 'footer_background_color', '#111827') }};
            --site-footer-text: {{ data_get($siteSettings, 'footer_text_color', '#e2e8f0') }};
        }
    </style>
    <title>{{ data_get($siteSettings, 'site_name', 'Mi Ecommerce Portfolio') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .site-nav {
            max-width: 1440px;
            margin: 0.75rem auto 0;
            padding: 0.5rem 1.5rem;
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
            border-bottom: 1px solid #f1f5f9;
        }
        .site-nav__menu {
            display: flex;
            gap: 0.35rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .site-nav__menu-item {
            min-width: 0;
            border: 1px solid transparent;
            border-radius: 999px;
            padding: 0.35rem 0.75rem;
            background: transparent;
            color: #475569;
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.82rem;
            font-weight: 600;
            transition: all 0.18s ease;
        }
        .site-nav__menu-item:hover {
            color: #0f172a;
            background: #f1f5f9;
        }
        .site-nav__menu-item strong {
            font-size: 0.82rem;
            font-weight: 600;
            line-height: 1.2;
        }
        .site-nav__menu-item small { display: none; }
        .site-nav__menu-item--active {
            background: #0f172a;
            color: #ffffff !important;
            border-color: #0f172a;
        }
        .site-nav__menu-item--active:hover {
            background: #1e293b;
            color: #ffffff !important;
        }
        .site-nav__actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
            font-size: 0.85rem;
        }
        .site-nav__actions span {
            color: #475569;
            font-weight: 500;
            padding-right: 0.25rem;
        }
        .site-nav__actions a, .site-nav__actions button {
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            background: #ffffff;
            color: #334155;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.18s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }
        .site-nav__actions a:hover, .site-nav__actions button:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
            color: #0f172a;
        }
        .site-nav__actions a:first-of-type {
            border-color: var(--site-button-bg, #0f172a);
            background: var(--site-button-bg, #0f172a);
            color: var(--site-button-text, #ffffff);
        }
        .site-nav__actions a:first-of-type:hover {
            opacity: 0.92;
        }
        .site-nav form { display: inline; }
        .site-nav .row { display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; }
        .admin-shell { max-width: 1440px; margin: 1.5rem auto 3rem; padding: 0 1.5rem; }
        .admin-hero {
            border: 1px solid #e2e8f0;
            background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 50%, #f0fdfa 100%);
            border-radius: 20px;
            padding: 1.75rem;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
        }
        .admin-hero__brand { display: flex; align-items: center; gap: 1rem; margin-bottom: 0.75rem; }
        .admin-hero__logo { width: 56px; height: 56px; border-radius: 14px; object-fit: contain; border: 1px solid #e2e8f0; background: #fff; padding: 0.35rem; box-shadow: 0 2px 6px rgba(0,0,0,0.06); }
        .admin-eyebrow { margin: 0 0 0.25rem; color: #2563eb; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; }
        .admin-hero h1 { margin: 0; color: #0f172a; font-size: 1.6rem; font-weight: 800; letter-spacing: -0.02em; }
        .admin-sub { margin: 0.45rem 0 0; color: #475569; font-size: 0.95rem; }
        .admin-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-top: 1.25rem; }
        .metric {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            padding: 1.1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }
        .metric:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(15, 23, 42, 0.06);
        }
        .metric p { margin: 0; color: #64748b; font-size: 0.82rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
        .metric strong { display: block; margin-top: 0.4rem; font-size: 1.75rem; font-weight: 800; color: #0f172a; letter-spacing: -0.03em; }
        .admin-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 1.25rem; }
        .action-card {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            padding: 1.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }
        .action-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.07);
        }
        .action-card h3 { margin: 0 0 0.4rem; font-size: 1.05rem; font-weight: 700; color: #0f172a; }
        .action-card p { margin: 0 0 1rem; color: #64748b; font-size: 0.88rem; line-height: 1.45; }
        .action-card a {
            display: inline-block;
            align-self: flex-start;
            border: 1px solid var(--site-button-bg, #0f172a);
            border-radius: 999px;
            padding: 0.45rem 0.95rem;
            color: var(--site-button-text, #fff);
            background: var(--site-button-bg, #0f172a);
            text-decoration: none;
            font-size: 0.84rem;
            font-weight: 600;
            transition: opacity 0.18s ease;
        }
        .action-card a:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <nav class="site-nav">
        <div class="site-nav__menu">
            @auth
                @if(auth()->user()->role !== 'customer')
                    <a class="site-nav__menu-item {{ request()->routeIs('admin.dashboard') ? 'site-nav__menu-item--active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <strong>Panel</strong>
                        <small>Dashboard</small>
                    </a>

                    <a class="site-nav__menu-item {{ request()->routeIs('storefront.*') ? 'site-nav__menu-item--active' : '' }}" href="{{ route('storefront.home') }}">
                        <strong>Tienda</strong>
                        <small>Vista publica</small>
                    </a>
                @endif

                @if(auth()->user()->hasPermission('view_admin_orders'))
                    <a class="site-nav__menu-item {{ request()->routeIs('admin.orders.*') ? 'site-nav__menu-item--active' : '' }}" href="{{ route('admin.orders.index') }}">
                        <strong>Pedidos</strong>
                        <small>Seguimiento</small>
                    </a>
                @endif

                @if(auth()->user()->hasPermission('manage_categories'))
                    <a class="site-nav__menu-item {{ request()->routeIs('admin.categories.*') ? 'site-nav__menu-item--active' : '' }}" href="{{ route('admin.categories.index') }}">
                        <strong>Categorias</strong>
                        <small>Organizar catalogo</small>
                    </a>
                @endif

                @if(auth()->user()->hasPermission('manage_products'))
                    <a class="site-nav__menu-item {{ request()->routeIs('admin.products.*') ? 'site-nav__menu-item--active' : '' }}" href="{{ route('admin.products.index') }}">
                        <strong>Productos</strong>
                        <small>Gestion catalogo</small>
                    </a>
                    <a class="site-nav__menu-item {{ request()->routeIs('admin.promotions.*') ? 'site-nav__menu-item--active' : '' }}" href="{{ route('admin.promotions.index') }}">
                        <strong>Promociones</strong>
                        <small>Descuentos por fecha</small>
                    </a>
                    <a class="site-nav__menu-item {{ request()->routeIs('admin.coupons.*') ? 'site-nav__menu-item--active' : '' }}" href="{{ route('admin.coupons.index') }}">
                        <strong>Cupones</strong>
                        <small>Descuento checkout</small>
                    </a>
                    <a class="site-nav__menu-item {{ request()->routeIs('admin.home-banners.*') ? 'site-nav__menu-item--active' : '' }}" href="{{ route('admin.home-banners.index') }}">
                        <strong>Banners</strong>
                        <small>Home principal</small>
                    </a>
                    <a class="site-nav__menu-item {{ request()->routeIs('admin.home-secondary-banners.*') ? 'site-nav__menu-item--active' : '' }}" href="{{ route('admin.home-secondary-banners.index') }}">
                        <strong>Banners 2°</strong>
                        <small>Modulos secundarios</small>
                    </a>
                    <a class="site-nav__menu-item {{ request()->routeIs('admin.home-product-carousels.*') ? 'site-nav__menu-item--active' : '' }}" href="{{ route('admin.home-product-carousels.index') }}">
                        <strong>Carruseles</strong>
                        <small>3 bloques home</small>
                    </a>
                @endif

                @if(auth()->user()->hasPermission('manage_users'))
                    <a class="site-nav__menu-item {{ request()->routeIs('admin.users.*') ? 'site-nav__menu-item--active' : '' }}" href="{{ route('admin.users.index') }}">
                        <strong>Usuarios</strong>
                        <small>Roles y permisos</small>
                    </a>
                @endif

                @if(auth()->user()->hasPermission('manage_site_settings'))
                    <a class="site-nav__menu-item {{ request()->routeIs('admin.site-settings.*') ? 'site-nav__menu-item--active' : '' }}" href="{{ route('admin.site-settings.edit') }}">
                        <strong>Sitio</strong>
                        <small>Logo y footer</small>
                    </a>
                    <a class="site-nav__menu-item {{ request()->routeIs('admin.newsletter-subscribers.*') ? 'site-nav__menu-item--active' : '' }}" href="{{ route('admin.newsletter-subscribers.index') }}">
                        <strong>Newsletter</strong>
                        <small>Suscriptores</small>
                    </a>
                @endif
            @endauth
        </div>

        <div class="site-nav__actions">
            @guest
                <a href="{{ route('login') }}">Iniciar sesion</a>
                <a href="{{ route('register') }}">Crear cuenta</a>
            @endguest

            @auth
                <span>Hola, {{ auth()->user()->name }}</span>
                <a href="{{ route('account.profile.edit') }}">Mi perfil</a>
                <a href="{{ route('account.orders.index') }}">Mis pedidos</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Salir</button>
                </form>
            @endauth
        </div>
    </nav>

    @if(($isBackoffice ?? false) === true)
        <main class="admin-shell">
            <section class="admin-hero">
                <div class="admin-hero__brand">
                    @if(!empty($siteSettings?->logo_url))
                        <img class="admin-hero__logo" src="{{ $siteSettings->logo_url }}" alt="{{ $siteSettings->site_name ?? 'Sitio' }}">
                    @endif
                    <div>
                        <p class="admin-eyebrow">Panel de administracion</p>
                        <h1>{{ $siteSettings->site_name ?? 'Centro operativo' }}</h1>
                    </div>
                </div>
                <p class="admin-sub">Resumen rapido y accesos directos para gestionar la operacion de la tienda.</p>

                <div class="admin-grid">
                    @if(!is_null($stats['orders_today'] ?? null))
                        <article class="metric">
                            <p>Pedidos hoy</p>
                            <strong>{{ $stats['orders_today'] }}</strong>
                        </article>
                    @endif

                    @if(!is_null($stats['orders_pending'] ?? null))
                        <article class="metric">
                            <p>Pedidos pendientes</p>
                            <strong>{{ $stats['orders_pending'] }}</strong>
                        </article>
                    @endif

                    @if(!is_null($stats['products'] ?? null))
                        <article class="metric">
                            <p>Productos</p>
                            <strong>{{ $stats['products'] }}</strong>
                        </article>
                    @endif

                    @if(!is_null($stats['categories'] ?? null))
                        <article class="metric">
                            <p>Categorias</p>
                            <strong>{{ $stats['categories'] }}</strong>
                        </article>
                    @endif

                    @if(!is_null($stats['users'] ?? null))
                        <article class="metric">
                            <p>Usuarios</p>
                            <strong>{{ $stats['users'] }}</strong>
                        </article>
                    @endif
                </div>
            </section>

            <section class="admin-actions">
                @if(auth()->user()->hasPermission('view_admin_orders'))
                    <article class="action-card">
                        <h3>Monitorear pedidos</h3>
                        <p>Revisa estados de pago, incidencias y datos del comprador.</p>
                        <a href="{{ route('admin.orders.index') }}">Ir a pedidos admin</a>
                    </article>
                @endif

                @if(auth()->user()->hasPermission('manage_products'))
                    <article class="action-card">
                        <h3>Gestionar catalogo</h3>
                        <p>Crea, edita y ordena productos publicados en la tienda.</p>
                        <a href="{{ route('admin.products.index') }}">Ir a productos</a>
                    </article>

                    <article class="action-card">
                        <h3>Gestionar marcas</h3>
                        <p>Organiza el catalogo de marcas y reutilizalo en los productos.</p>
                        <a href="{{ route('admin.brands.index') }}">Ir a marcas</a>
                    </article>
                @endif

                @if(auth()->user()->hasPermission('manage_categories'))
                    <article class="action-card">
                        <h3>Organizar categorias</h3>
                        <p>Mantiene la navegacion del catalogo limpia y coherente.</p>
                        <a href="{{ route('admin.categories.index') }}">Ir a categorias</a>
                    </article>
                @endif

                @if(auth()->user()->hasPermission('manage_users'))
                    <article class="action-card">
                        <h3>Administrar usuarios</h3>
                        <p>Asigna roles y revisa trazabilidad de cambios de permisos.</p>
                        <a href="{{ route('admin.users.index') }}">Ir a usuarios</a>
                    </article>
                @endif

                <article class="action-card">
                    <h3>Vista cliente</h3>
                    <p>Puedes entrar a la tienda publica para validar la experiencia del comprador.</p>
                    <a href="{{ route('storefront.home') }}">Abrir tienda</a>
                </article>
            </section>
        </main>
    @else
        <div id="app"></div>
    @endif
</body>
</html>
