import { computed, ref } from 'vue';

const PRODUCT_ORIGIN_KEY = 'storefront-product-origin-v1';

function readStorefrontContext() {
    if (typeof window !== 'undefined' && window.storefrontContext) {
        return window.storefrontContext;
    }

    const meta = document.querySelector('meta[name="storefront-context"]');

    if (!meta?.getAttribute('content')) {
        return {};
    }

    try {
        return JSON.parse(meta.getAttribute('content'));
    } catch {
        return {};
    }
}

function readStorefrontLocation() {
    if (typeof window === 'undefined') {
        return { initialView: 'home', initialProductId: null };
    }

    const pathname = window.location.pathname.replace(/\/+$/, '');
    const productMatch = pathname.match(/^\/tienda\/producto\/(\d+)$/);

    if (productMatch) {
        return {
            initialView: 'producto',
            initialProductId: Number(productMatch[1]),
        };
    }

    if (pathname.endsWith('/tienda/carrito')) {
        return { initialView: 'carrito', initialProductId: null };
    }

    if (pathname.endsWith('/tienda/checkout')) {
        return { initialView: 'checkout', initialProductId: null };
    }

    if (pathname.endsWith('/tienda/catalogo')) {
        return { initialView: 'catalogo', initialProductId: null };
    }

    if (pathname.endsWith('/tienda/contacto')) {
        return { initialView: 'contacto', initialProductId: null };
    }

    if (pathname.endsWith('/tienda/favoritos')) {
        return { initialView: 'favoritos', initialProductId: null };
    }

    return { initialView: 'home', initialProductId: null };
}

function readSiteSettings() {
    if (typeof window !== 'undefined' && window.siteSettings) {
        return window.siteSettings;
    }

    const jsonScript = document.getElementById('site-settings-data');

    if (jsonScript?.textContent) {
        try {
            return JSON.parse(jsonScript.textContent);
        } catch {
            // Continue with other fallbacks.
        }
    }

    const meta = document.querySelector('meta[name="site-settings"]');

    if (!meta?.getAttribute('content')) {
        return {};
    }

    try {
        return JSON.parse(meta.getAttribute('content'));
    } catch {
        return {};
    }
}

function readReturnCatalogUrl() {
    if (typeof window === 'undefined') {
        return '/tienda/catalogo';
    }

    try {
        const stored = sessionStorage.getItem('storefront-catalog-state-v1');

        if (!stored) {
            return '/tienda/catalogo';
        }

        const parsed = JSON.parse(stored);
        return parsed?.returnTo || '/tienda/catalogo';
    } catch {
        return '/tienda/catalogo';
    }
}

function saveProductOrigin(origin, productId) {
    if (typeof window === 'undefined') {
        return;
    }

    try {
        sessionStorage.setItem(
            PRODUCT_ORIGIN_KEY,
            JSON.stringify({
                origin,
                productId,
            })
        );
    } catch {
        // Ignore storage errors.
    }
}

function canShowBackToCatalog(currentProductId) {
    if (typeof window === 'undefined' || !currentProductId) {
        return false;
    }

    try {
        const raw = sessionStorage.getItem(PRODUCT_ORIGIN_KEY);
        if (!raw) {
            return false;
        }

        const payload = JSON.parse(raw);
        return payload?.origin === 'catalogo' && Number(payload?.productId) === Number(currentProductId);
    } catch {
        return false;
    }
}

export function useStorefrontContext() {
    const storefrontContext = ref(readStorefrontContext());
    const initialLocation = readStorefrontLocation();

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
        const fromSetting = String(siteSettings.value?.footer_whatsapp_url || '').trim();
        const fromPhone = String(siteSettings.value?.footer_phone || '').trim();
        const raw = fromSetting || fromPhone;

        if (!raw) {
            return '';
        }

        if (/^https?:\/\//i.test(raw)) {
            return raw;
        }

        if (/^whatsapp:\/\//i.test(raw)) {
            const digits = raw.replace(/\D+/g, '');
            return digits ? `https://wa.me/${digits}` : '';
        }

        if (/^wa\.me\//i.test(raw)) {
            return `https://${raw}`;
        }

        const digits = raw.replace(/\D+/g, '');

        if (!digits) {
            return '';
        }

        return `https://wa.me/${digits}`;
    });

    function navigateTo(url) {
        window.location.assign(url);
    }

    async function loadCatalogCategories() {
        try {
            const response = await fetch('/api/categories', {
                headers: {
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('No se pudieron cargar las categorias.');
            }

            const payload = await response.json();
            catalogCategories.value = payload.data ?? [];
        } catch {
            catalogCategories.value = [];
        }
    }

    function openProductDetail(productOrId, origin = 'otros') {
        selectedProductId.value = typeof productOrId === 'object' ? productOrId.id : productOrId;
        saveProductOrigin(origin, selectedProductId.value);
        showBackToCatalog.value = origin === 'catalogo';

        returnCatalogUrl.value = readReturnCatalogUrl();

        const baseUrl = storefrontUrls.value.product?.(selectedProductId.value) || `/tienda/producto/${selectedProductId.value}`;

        navigateTo(baseUrl);
    }

    function openProductFromCatalog(productOrId) {
        openProductDetail(productOrId, 'catalogo');
    }

    function openProductFromOtherSections(productOrId) {
        openProductDetail(productOrId, 'otros');
    }

    function openPromotionCatalog(promotionId) {
        const id = Number(promotionId);
        if (!id) {
            return;
        }

        const baseCatalogUrl = storefrontUrls.value.catalogo || '/tienda/catalogo';
        navigateTo(`${baseCatalogUrl}?promotion_id=${id}&promotion_locked=1`);
    }

    function initializeStorefront(onToast) {
        if (siteSettings.value?.site_name) {
            document.title = siteSettings.value.site_name;
        }

        const flashToast = String(storefrontContext.value?.flashToast || '').trim();
        if (flashToast !== '') {
            onToast?.(flashToast);
        }

        showBackToCatalog.value = canShowBackToCatalog(selectedProductId.value);
        loadCatalogCategories();
    }

    return {
        activeView,
        selectedProductId,
        returnCatalogUrl,
        showBackToCatalog,
        siteSettings,
        storefrontUrls,
        storefrontAccountUrl,
        storefrontOrdersUrl,
        storefrontIsAuthenticated,
        activeMenuView,
        whatsappContactUrl,
        catalogCategories,
        navigateTo,
        openProductFromCatalog,
        openProductFromOtherSections,
        openPromotionCatalog,
        initializeStorefront,
    };
}