<script setup>
import { computed, onMounted } from 'vue';
import { useCart } from './composables/useCart';
import { useFavorites } from './composables/useFavorites';
import { useUiFeedback } from './composables/useUiFeedback';
import { useStorefrontContext } from './composables/useStorefrontContext';
import './styles/storefront.css';
import StorefrontHeader from './components/storefront/StorefrontHeader.vue';
import HomeSection from './components/storefront/HomeSection.vue';
import CatalogSection from './components/storefront/CatalogSection.vue';
import FavoritesSection from './components/storefront/FavoritesSection.vue';
import ProductDetailSection from './components/storefront/ProductDetailSection.vue';
import CartSection from './components/storefront/CartSection.vue';
import CheckoutSection from './components/storefront/CheckoutSection.vue';
import StorefrontFooter from './components/storefront/StorefrontFooter.vue';
import ToastStack from './components/storefront/ToastStack.vue';
import CartDrawerPreview from './components/storefront/CartDrawerPreview.vue';

const {
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
} = useStorefrontContext();

const {
    cartPreviewOpen,
    toasts,
    openCartPreview,
    closeCartPreview,
    pushToast,
} = useUiFeedback();

const {
    favoriteIds,
    favoritesCount,
    favoritesIsAuthenticated,
    loadFavoriteIds,
    isFavorite,
    toggleFavorite,
} = useFavorites(storefrontIsAuthenticated, pushToast);

const cartPreviewItems = computed(() => cart.value.slice(0, 5));
const cartPreviewRemainingItems = computed(() => Math.max(itemsCount.value - cartPreviewItems.value.reduce((sum, item) => sum + item.quantity, 0), 0));

const {
    cart,
    itemsCount,
    subtotal,
    productDiscountAmount,
    subtotalAfterDiscount,
    shippingAmount,
    total,
    hasItems,
    addItem,
    increase,
    decrease,
    removeItem,
    clearCart,
} = useCart();

function formatCurrency(amount) {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
}

function goToCartPage() {
    closeCartPreview();
    navigateTo(storefrontUrls.value.carrito || '/tienda/carrito');
}

function goToCheckout() {
    if (!hasItems.value) return;
    navigateTo(storefrontUrls.value.checkout || '/tienda/checkout');
}

function handleAddToCart(product) {
    const added = addItem(product);

    if (!added) {
        if (Number(product?.stock ?? 0) <= 0) {
            pushToast(`"${product.name}" esta agotado`);
            return;
        }

        pushToast(`No hay mas unidades disponibles de "${product.name}"`);
        return;
    }

    pushToast(`"${product.name}" agregado al carrito`);
}

onMounted(() => {
    initializeStorefront(pushToast);
    loadFavoriteIds();
});
</script>

<template>
    <div class="page">
        <StorefrontHeader
            :active-view="activeMenuView"
            :items-count="itemsCount"
            :has-items="hasItems"
            :site-settings="siteSettings"
            :is-authenticated="storefrontIsAuthenticated"
            :account-url="storefrontAccountUrl"
            :orders-url="storefrontOrdersUrl"
            :home-url="storefrontUrls.home || '/tienda/home'"
            :catalog-url="storefrontUrls.catalogo || '/tienda/catalogo'"
            :categories="catalogCategories"
            :favorites-url="storefrontUrls.favoritos || '/tienda/favoritos'"
            :favorites-count="favoritesCount"
            :cart-url="storefrontUrls.carrito || '/tienda/carrito'"
            :checkout-url="storefrontUrls.checkout || '/tienda/checkout'"
            @navigate="navigateTo"
            @open-cart="openCartPreview"
        />

        <main>
            <HomeSection
                v-if="activeView === 'home'"
                :format-currency="formatCurrency"
                :favorite-ids="favoriteIds"
                :is-authenticated="favoritesIsAuthenticated"
                @open-product="openProductFromOtherSections"
                @open-promotion="openPromotionCatalog"
                @add-to-cart="handleAddToCart"
                @toggle-favorite="toggleFavorite"
            />

            <CatalogSection
                v-if="activeView === 'catalogo'"
                :format-currency="formatCurrency"
                :favorite-ids="favoriteIds"
                :is-authenticated="favoritesIsAuthenticated"
                @open-product="openProductFromCatalog"
                @add-to-cart="handleAddToCart"
                @toggle-favorite="toggleFavorite"
            />

            <FavoritesSection
                v-if="activeView === 'favoritos'"
                :format-currency="formatCurrency"
                :favorite-ids="favoriteIds"
                :is-authenticated="favoritesIsAuthenticated"
                @open-product="openProductFromOtherSections"
                @add-to-cart="handleAddToCart"
                @toggle-favorite="toggleFavorite"
            />

            <ProductDetailSection
                v-if="activeView === 'producto'"
                :product-id="selectedProductId"
                :show-back-to-catalog="showBackToCatalog"
                :format-currency="formatCurrency"
                :is-favorite="isFavorite(selectedProductId)"
                :is-authenticated="favoritesIsAuthenticated"
                @add-to-cart="handleAddToCart"
                @toggle-favorite="toggleFavorite"
                @back-catalog="navigateTo(returnCatalogUrl || storefrontUrls.catalogo || '/tienda/catalogo')"
            />

            <CartSection
                v-if="activeView === 'carrito'"
                :cart="cart"
                :subtotal="subtotal"
                :product-discount-amount="productDiscountAmount"
                :subtotal-after-discount="subtotalAfterDiscount"
                :total="total"
                :format-currency="formatCurrency"
                @decrease="decrease"
                @increase="increase"
                @remove-item="removeItem"
                @go-checkout="goToCheckout"
            />

            <CheckoutSection
                v-if="activeView === 'checkout'"
                :cart="cart"
                :has-items="hasItems"
                :site-settings="siteSettings"
                :total="total"
                :items-count="itemsCount"
                :subtotal="subtotal"
                :product-discount-amount="productDiscountAmount"
                :subtotal-after-discount="subtotalAfterDiscount"
                :format-currency="formatCurrency"
                @clear-cart="clearCart"
                @toast="pushToast"
                @continue-shopping="navigateTo(storefrontUrls.home || '/tienda/home')"
            />
        </main>

        <StorefrontFooter :site-settings="siteSettings" />

        <CartDrawerPreview
            :open="cartPreviewOpen"
            :has-items="hasItems"
            :items="cartPreviewItems"
            :remaining-items="cartPreviewRemainingItems"
            :subtotal="subtotal"
            :total="total"
            :format-currency="formatCurrency"
            @close="closeCartPreview"
            @go-cart="goToCartPage"
        />

        <a
            v-if="whatsappContactUrl"
            :href="whatsappContactUrl"
            class="floating-whatsapp"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Contactar por WhatsApp"
            title="Contactar por WhatsApp"
        >
            <span class="floating-whatsapp__icon" aria-hidden="true">
                <svg viewBox="0 0 32 32" role="img" focusable="false">
                    <path
                        fill="currentColor"
                        d="M19.11 17.53c-.27-.13-1.58-.78-1.82-.87-.24-.09-.42-.13-.6.14-.18.27-.69.87-.85 1.05-.16.18-.31.2-.58.07-.27-.13-1.14-.42-2.17-1.35-.8-.71-1.34-1.6-1.5-1.87-.16-.27-.02-.42.12-.55.12-.12.27-.31.4-.47.13-.16.18-.27.27-.45.09-.18.04-.34-.02-.47-.07-.13-.6-1.44-.82-1.97-.22-.53-.44-.46-.6-.47h-.51c-.18 0-.47.07-.71.34s-.94.91-.94 2.21.96 2.56 1.1 2.74c.13.18 1.89 2.88 4.58 4.04.64.28 1.15.44 1.54.57.65.21 1.24.18 1.7.11.52-.08 1.58-.64 1.8-1.26.22-.62.22-1.15.16-1.26-.07-.11-.24-.18-.51-.31z"
                    />
                    <path
                        fill="currentColor"
                        d="M16.02 3.2c-7.02 0-12.73 5.71-12.73 12.73 0 2.24.58 4.44 1.68 6.39l-1.79 6.53 6.68-1.75a12.71 12.71 0 0 0 6.16 1.58h.01c7.02 0 12.73-5.71 12.73-12.73 0-3.4-1.32-6.6-3.73-9.01a12.61 12.61 0 0 0-9-3.74zm0 23.3h-.01a10.5 10.5 0 0 1-5.36-1.47l-.38-.23-3.96 1.04 1.06-3.86-.25-.4a10.5 10.5 0 1 1 8.9 4.92z"
                    />
                </svg>
            </span>
        </a>

        <ToastStack :toasts="toasts" />
    </div>
</template>
