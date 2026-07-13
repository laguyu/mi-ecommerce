<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Mi Ecommerce' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
    <style>
        body { min-height: 100vh; display: flex; flex-direction: column; }
        .page-shell { max-width: 1440px; width: 100%; margin: 0 auto; padding: 1.2rem 1.5rem 2rem; display: flex; flex-direction: column; flex: 1; }
        .top-nav { display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.2rem; align-items: flex-end; }
        .top-nav__brand { min-width: 220px; }
        .top-nav__brand-row { display: flex; align-items: center; gap: .75rem; }
        .top-nav__brand-logo { width: 46px; height: 46px; border-radius: 12px; object-fit: contain; border: 1px solid #e5e7eb; background: #fff; padding: .25rem; }
        .top-nav__menu { display: flex; gap: .4rem; flex-wrap: wrap; align-items: stretch; justify-content: flex-end; border-bottom: 1px solid #e5e7eb; padding-bottom: .2rem; }
        .top-nav__menu-item { min-width: 0; border: 0; border-bottom: 2px solid transparent; border-radius: 0; padding: .4rem .55rem .5rem; background: transparent; color: var(--site-menu-text, #111827); text-decoration: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; box-shadow: none; }
        .top-nav__menu-item strong { font-size: .84rem; line-height: 1.1; }
        .top-nav__menu-item small { display: none; }
        .top-nav__menu-item--active { background: transparent; border-color: var(--site-menu-active-bg, #111827); color: var(--site-menu-text, #111827); box-shadow: none; }
        .top-nav__menu-item--active small { display: none; }
        .top-nav__actions { display: flex; gap: .55rem; align-items: center; flex-wrap: wrap; }
        .top-nav__actions a, .top-nav__actions button { border: 1px solid var(--site-button-bg, #d1d5db); border-radius: 999px; padding: .45rem .85rem; background: var(--site-button-bg, #fff); color: var(--site-button-text, #111827); text-decoration: none; cursor: pointer; }
        .top-nav form { display: inline; }
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 1.1rem 1.15rem; }
        .card h1 { margin: 0 0 .35rem; font-size: 1.28rem; }
        .grid { display: grid; gap: .75rem; }
        .input, .select, .textarea { width: 100%; border: 1px solid #d1d5db; border-radius: 10px; padding: .58rem .72rem; font-size: .93rem; }
        .textarea { min-height: 110px; resize: vertical; }
        .btn { border: 1px solid var(--site-button-bg, #111827); background: var(--site-button-bg, #111827); color: var(--site-button-text, #fff); border-radius: 9px; padding: .45rem .78rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: .35rem; font-size: .86rem; line-height: 1.2; text-decoration: none; }
        .btn-outline { border: 1px solid color-mix(in srgb, var(--site-menu-text, #111827) 18%, #d1d5db 82%); background: var(--site-menu-bg, #fff); color: var(--site-menu-text, #111827); }
        .alert { border: 1px solid #bbf7d0; background: #ecfdf5; color: #065f46; border-radius: 10px; padding: .65rem .8rem; margin-bottom: .9rem; }
        .error { color: #b91c1c; font-size: .87rem; margin-top: .2rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #e5e7eb; text-align: left; padding: .55rem .35rem; vertical-align: top; }
        .actions { display: flex; gap: .45rem; flex-wrap: wrap; align-items: center; }
        .admin-form { max-width: 920px; display: grid; grid-template-columns: repeat(2, minmax(220px, 1fr)); gap: .78rem .8rem; margin-top: .85rem; }
        .admin-form > label { display: grid; gap: .34rem; align-content: start; font-size: .88rem; color: #334155; }
        .admin-form > label > small { color: #64748b; font-size: .8rem; }
        .admin-form > .actions,
        .admin-form > .full-row,
        .admin-form > hr { grid-column: 1 / -1; }
        .admin-form--single { grid-template-columns: 1fr; max-width: 760px; }
        .admin-form--wide { max-width: 1040px; }
        @media (max-width: 860px) {
            .admin-form { grid-template-columns: 1fr; }
        }
        .shared-footer { margin-top: auto; padding: 1.2rem 0 0; }
        .shared-footer__inner { border-radius: 1rem; padding: 1rem 1.1rem; border: 1px solid color-mix(in srgb, var(--site-footer-bg, #111827) 70%, #94a3b8 30%); background: linear-gradient(180deg, var(--site-footer-bg, #111827) 0%, color-mix(in srgb, var(--site-footer-bg, #111827) 86%, #000 14%) 100%); color: var(--site-footer-text, #e2e8f0); display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap; align-items: center; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14); }
        .shared-footer__brand { display: grid; gap: .2rem; }
        .shared-footer__eyebrow { margin: 0; text-transform: uppercase; letter-spacing: .08em; font-size: .72rem; color: color-mix(in srgb, var(--site-footer-text, #e2e8f0) 38%, #93c5fd 62%); }
        .shared-footer__title { margin: 0; font-size: 1rem; }
        .shared-footer__text { margin: 0; color: color-mix(in srgb, var(--site-footer-text, #e2e8f0) 82%, #cbd5e1 18%); font-size: .92rem; }
        .shared-footer__sections { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-start; }
        .shared-footer__section { display: grid; gap: .25rem; min-width: 160px; }
        .shared-footer__section h3 { margin: 0; font-size: .85rem; text-transform: uppercase; letter-spacing: .06em; color: color-mix(in srgb, var(--site-footer-text, #e2e8f0) 38%, #93c5fd 62%); }
        .shared-footer__section p,
        .shared-footer__section a { margin: 0; color: var(--site-footer-text, #e2e8f0); font-size: .92rem; }
        .shared-footer__links { display: flex; flex-wrap: wrap; gap: .45rem; }
        .shared-footer__links a { text-decoration: none; padding: .35rem .65rem; border-radius: 999px; border: 1px solid rgba(255, 255, 255, 0.14); background: rgba(255, 255, 255, 0.06); }
    </style>
</head>
<body>
    <div class="page-shell">
        <nav class="top-nav">
            <div class="top-nav__brand">
                <div class="top-nav__brand-row">
                    @if(!empty($siteSettings?->logo_url))
                        <img class="top-nav__brand-logo" src="{{ $siteSettings->logo_url }}" alt="{{ $siteSettings->site_name ?? 'Sitio' }}">
                    @endif
                    <div>
                        <strong>{{ $siteSettings->site_name ?? 'Panel' }}</strong>
                        <div style="color:#64748b; font-size:.9rem;">Accesos directos de administracion</div>
                    </div>
                </div>
            </div>

            <div class="top-nav__menu">
                @guest
                    <a class="top-nav__menu-item {{ request()->is('/') && request()->boolean('storefront') ? 'top-nav__menu-item--active' : '' }}" href="/?storefront=1">
                        <strong>Tienda</strong>
                        <small>Vista publica</small>
                    </a>

                    <a class="top-nav__menu-item {{ request()->routeIs('login') ? 'top-nav__menu-item--active' : '' }}" href="{{ route('login') }}">
                        <strong>Iniciar sesion</strong>
                        <small>Acceder a tu cuenta</small>
                    </a>

                    <a class="top-nav__menu-item {{ request()->routeIs('register') ? 'top-nav__menu-item--active' : '' }}" href="{{ route('register') }}">
                        <strong>Crear cuenta</strong>
                        <small>Registro rapido</small>
                    </a>
                @endguest

                @auth
                    @if(auth()->user()->role !== 'customer')
                        <a class="top-nav__menu-item {{ request()->routeIs('admin.dashboard') ? 'top-nav__menu-item--active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <strong>Panel</strong>
                            <small>Dashboard</small>
                        </a>
                    @endif

                    <a class="top-nav__menu-item {{ request()->routeIs('account.profile.*') ? 'top-nav__menu-item--active' : '' }}" href="{{ route('account.profile.edit') }}">
                        <strong>Mi perfil</strong>
                        <small>Nombre y contraseña</small>
                    </a>

                    <a class="top-nav__menu-item {{ request()->routeIs('account.orders.*') ? 'top-nav__menu-item--active' : '' }}" href="{{ route('account.orders.index') }}">
                        <strong>Mis pedidos</strong>
                        <small>Historial de compras</small>
                    </a>

                    @if(auth()->user()->role !== 'customer')
                        <a class="top-nav__menu-item {{ request()->is('/') ? 'top-nav__menu-item--active' : '' }}" href="/?storefront=1">
                            <strong>Tienda</strong>
                            <small>Vista publica</small>
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('view_admin_orders'))
                        <a class="top-nav__menu-item {{ request()->routeIs('admin.orders.*') ? 'top-nav__menu-item--active' : '' }}" href="{{ route('admin.orders.index') }}">
                            <strong>Pedidos</strong>
                            <small>Seguimiento</small>
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('manage_categories'))
                        <a class="top-nav__menu-item {{ request()->routeIs('admin.categories.*') ? 'top-nav__menu-item--active' : '' }}" href="{{ route('admin.categories.index') }}">
                            <strong>Categorias</strong>
                            <small>Organizar catalogo</small>
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('manage_products'))
                        <a class="top-nav__menu-item {{ request()->routeIs('admin.products.*') ? 'top-nav__menu-item--active' : '' }}" href="{{ route('admin.products.index') }}">
                            <strong>Productos</strong>
                            <small>Gestion del catalogo</small>
                        </a>
                        <a class="top-nav__menu-item {{ request()->routeIs('admin.promotions.*') ? 'top-nav__menu-item--active' : '' }}" href="{{ route('admin.promotions.index') }}">
                            <strong>Promociones</strong>
                            <small>Descuentos por fecha</small>
                        </a>
                        <a class="top-nav__menu-item {{ request()->routeIs('admin.coupons.*') ? 'top-nav__menu-item--active' : '' }}" href="{{ route('admin.coupons.index') }}">
                            <strong>Cupones</strong>
                            <small>Descuento checkout</small>
                        </a>
                        <a class="top-nav__menu-item {{ request()->routeIs('admin.brands.*') ? 'top-nav__menu-item--active' : '' }}" href="{{ route('admin.brands.index') }}">
                            <strong>Marcas</strong>
                            <small>Catalogo de marcas</small>
                        </a>
                        <a class="top-nav__menu-item {{ request()->routeIs('admin.home-banners.*') ? 'top-nav__menu-item--active' : '' }}" href="{{ route('admin.home-banners.index') }}">
                            <strong>Banners</strong>
                            <small>Home principal</small>
                        </a>
                        <a class="top-nav__menu-item {{ request()->routeIs('admin.home-secondary-banners.*') ? 'top-nav__menu-item--active' : '' }}" href="{{ route('admin.home-secondary-banners.index') }}">
                            <strong>Banners 2°</strong>
                            <small>Modulos secundarios</small>
                        </a>
                        <a class="top-nav__menu-item {{ request()->routeIs('admin.home-product-carousels.*') ? 'top-nav__menu-item--active' : '' }}" href="{{ route('admin.home-product-carousels.index') }}">
                            <strong>Carruseles</strong>
                            <small>3 bloques home</small>
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('manage_users'))
                        <a class="top-nav__menu-item {{ request()->routeIs('admin.users.*') ? 'top-nav__menu-item--active' : '' }}" href="{{ route('admin.users.index') }}">
                            <strong>Usuarios</strong>
                            <small>Roles y permisos</small>
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('manage_site_settings'))
                        <a class="top-nav__menu-item {{ request()->routeIs('admin.site-settings.*') ? 'top-nav__menu-item--active' : '' }}" href="{{ route('admin.site-settings.edit') }}">
                            <strong>Sitio</strong>
                            <small>Logo y footer</small>
                        </a>
                        <a class="top-nav__menu-item {{ request()->routeIs('admin.contact-messages.*') ? 'top-nav__menu-item--active' : '' }}" href="{{ route('admin.contact-messages.index') }}">
                            <strong>Contacto</strong>
                            <small>Mensajes recibidos</small>
                        </a>
                        <a class="top-nav__menu-item {{ request()->routeIs('admin.newsletter-subscribers.*') ? 'top-nav__menu-item--active' : '' }}" href="{{ route('admin.newsletter-subscribers.index') }}">
                            <strong>Newsletter</strong>
                            <small>Suscriptores</small>
                        </a>
                    @endif
                @endauth
            </div>

            <div class="top-nav__actions">
                @auth
                    <span>Hola, {{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Salir</button>
                    </form>
                @endauth
            </div>
        </nav>

        @if(session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif

        @yield('content')

        <footer class="shared-footer">
            <div class="shared-footer__inner">
                <div class="shared-footer__brand">
                    <p class="shared-footer__eyebrow">{{ data_get($siteSettings, 'site_eyebrow', 'Laravel + Vue Ecommerce') }}</p>
                    <h2 class="shared-footer__title">{{ data_get($siteSettings, 'site_name', 'Nova Shop') }}</h2>
                    <p class="shared-footer__text">{{ data_get($siteSettings, 'footer_note', data_get($siteSettings, 'site_tagline', 'Tienda en linea con panel de administracion.')) }}</p>
                </div>

                <div class="shared-footer__sections">
                    @if(data_get($siteSettings, 'footer_address'))
                        <section class="shared-footer__section">
                            <h3>Dirección</h3>
                            <p>{{ data_get($siteSettings, 'footer_address') }}</p>
                        </section>
                    @endif

                    @if(data_get($siteSettings, 'footer_phone') || data_get($siteSettings, 'footer_email'))
                        <section class="shared-footer__section">
                            <h3>Contacto</h3>
                            @if(data_get($siteSettings, 'footer_phone'))
                                <p>{{ data_get($siteSettings, 'footer_phone') }}</p>
                            @endif
                            @if(data_get($siteSettings, 'footer_email'))
                                <p>{{ data_get($siteSettings, 'footer_email') }}</p>
                            @endif
                        </section>
                    @endif

                    @if(data_get($siteSettings, 'footer_facebook_url') || data_get($siteSettings, 'footer_instagram_url') || data_get($siteSettings, 'footer_x_url') || data_get($siteSettings, 'footer_whatsapp_url'))
                        <section class="shared-footer__section">
                            <h3>Redes</h3>
                            <div class="shared-footer__links">
                                @if(data_get($siteSettings, 'footer_facebook_url'))
                                    <a href="{{ data_get($siteSettings, 'footer_facebook_url') }}" target="_blank" rel="noopener">Facebook</a>
                                @endif
                                @if(data_get($siteSettings, 'footer_instagram_url'))
                                    <a href="{{ data_get($siteSettings, 'footer_instagram_url') }}" target="_blank" rel="noopener">Instagram</a>
                                @endif
                                @if(data_get($siteSettings, 'footer_x_url'))
                                    <a href="{{ data_get($siteSettings, 'footer_x_url') }}" target="_blank" rel="noopener">X</a>
                                @endif
                                @if(data_get($siteSettings, 'footer_whatsapp_url'))
                                    <a href="{{ data_get($siteSettings, 'footer_whatsapp_url') }}" target="_blank" rel="noopener">WhatsApp</a>
                                @endif
                            </div>
                        </section>
                    @endif
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
