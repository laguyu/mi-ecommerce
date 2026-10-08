<script setup>
import { onMounted, ref, watch } from 'vue';

const props = defineProps({
    formatCurrency: { type: Function, required: true },
    favoriteIds: { type: Array, default: () => [] },
    isAuthenticated: { type: Boolean, default: false },
});

const emit = defineEmits(['open-product', 'add-to-cart', 'toggle-favorite']);

const favorites = ref([]);
const loading = ref(false);
const error = ref('');

function isSoldOut(product) {
    return Number(product?.stock ?? 0) <= 0;
}

function isFavorite(productId) {
    return props.favoriteIds.includes(Number(productId));
}

async function loadFavorites() {
    if (!props.isAuthenticated) {
        favorites.value = [];
        loading.value = false;
        error.value = '';
        return;
    }

    loading.value = true;
    error.value = '';

    try {
        const response = await fetch('/api/favorites');

        if (!response.ok) {
            throw new Error('No se pudo cargar tu catalogo de favoritos.');
        }

        const payload = await response.json();
        favorites.value = payload.data ?? [];
    } catch (loadError) {
        favorites.value = [];
        error.value = loadError.message || 'No se pudo cargar tu catalogo de favoritos.';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadFavorites();
});

watch(
    () => props.isAuthenticated,
    () => {
        loadFavorites();
    }
);

watch(
    () => props.favoriteIds.join(','),
    () => {
        if (!props.isAuthenticated) {
            return;
        }

        loadFavorites();
    }
);
</script>

<template>
    <section class="catalogo">
        <div class="favorites-banner">
            <div class="favorites-banner__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="currentColor" stroke="none" style="width:28px;height:28px;color:#ef4444;">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
            </div>
            <div>
                <h2>Mis Productos Favoritos</h2>
                <p>Guarda tus artículos preferidos para encontrarlos rápido cuando decidas comprarlos.</p>
            </div>
        </div>

        <div v-if="!isAuthenticated" class="favorites-empty-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:40px;height:40px;margin-bottom:0.5rem;color:#94a3b8;">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <p class="muted">Inicia sesión con tu cuenta para ver y guardar tus productos favoritos.</p>
        </div>
        <p v-else-if="loading" class="muted">Cargando favoritos...</p>
        <p v-else-if="error" class="error-block">{{ error }}</p>
        <div v-else-if="favorites.length === 0" class="favorites-empty-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:40px;height:40px;margin-bottom:0.5rem;color:#94a3b8;">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
            <p class="muted">Aún no tienes productos en tu lista de favoritos.</p>
            <small>Haz clic en el corazón de cualquier producto para guardarlo aquí.</small>
        </div>

        <div v-else class="grid">
            <article v-for="product in favorites" :key="product.id" class="card">
                <div class="card__image-wrap" @click="emit('open-product', product)">
                    <img :src="product.image" :alt="product.name" />
                    <span v-if="product.has_discount" class="discount-badge discount-badge--floating">-{{ product.discount_percentage }}%</span>
                    <span v-if="isSoldOut(product)" class="sold-out sold-out--floating">Agotado</span>
                    <button
                        type="button"
                        class="favorite-floating-btn favorite-floating-btn--active"
                        title="Quitar de favoritos"
                        aria-label="Quitar de favoritos"
                        @click.stop="emit('toggle-favorite', product)"
                    >
                        <svg viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;" aria-hidden="true">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </button>
                </div>

                <div class="card-body">
                    <div class="card-head-actions">
                        <p v-if="product.brand_name" class="brand">{{ product.brand_name }}</p>
                        <p class="category">{{ product.category_path || product.category }}</p>
                    </div>

                    <h3 class="card-title" @click="emit('open-product', product)">{{ product.name }}</h3>
                    <p class="sku">SKU: {{ product.sku }}</p>
                    <p class="desc">{{ product.description }}</p>

                    <div class="row">
                        <div class="price-stack">
                            <small v-if="product.has_discount" class="price-old">{{ formatCurrency(product.original_price) }}</small>
                            <strong class="price-current">{{ formatCurrency(product.price) }}</strong>
                        </div>
                        <div class="row-actions">
                            <button class="ghost" @click="emit('open-product', product)">Ficha</button>
                            <button class="action-add-btn" :disabled="isSoldOut(product)" @click="emit('add-to-cart', product)">
                                {{ isSoldOut(product) ? 'Agotado' : '+ Agregar' }}
                            </button>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </section>
</template>
