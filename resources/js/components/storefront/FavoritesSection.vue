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
            <h2>Catalogo de favoritos</h2>
            <p>Guarda tus productos preferidos para volver rapido cuando quieras comprarlos.</p>
        </div>

        <p v-if="!isAuthenticated" class="muted">Inicia sesion para ver y gestionar tus favoritos.</p>
        <p v-else-if="loading" class="muted">Cargando favoritos...</p>
        <p v-else-if="error" class="error-block">{{ error }}</p>
        <p v-else-if="favorites.length === 0" class="muted">Aun no tienes productos favoritos. Agrega corazones desde Home o Catalogo.</p>

        <div v-else class="grid">
            <article v-for="product in favorites" :key="product.id" class="card">
                <img :src="product.image" :alt="product.name" @click="emit('open-product', product)" />

                <div class="card-body">
                    <div class="card-head-actions">
                        <p v-if="product.brand_name" class="brand">{{ product.brand_name }}</p>
                        <button
                            type="button"
                            class="favorite-toggle favorite-toggle--active"
                            title="Quitar de favoritos"
                            aria-label="Quitar de favoritos"
                            @click="emit('toggle-favorite', product)"
                        >
                            ❤
                        </button>
                    </div>

                    <p class="category">{{ product.category_path || product.category }}</p>
                    <h3>{{ product.name }}</h3>
                    <p class="sku">{{ product.sku }}</p>
                    <p v-if="isSoldOut(product)" class="sold-out">Agotado</p>
                    <p class="desc">{{ product.description }}</p>

                    <div class="row">
                        <strong>{{ formatCurrency(product.price) }}</strong>
                        <div class="row-actions">
                            <button class="ghost" @click="emit('open-product', product)">Ficha</button>
                            <button :disabled="isSoldOut(product)" @click="emit('add-to-cart', product)">
                                {{ isSoldOut(product) ? 'Agotado' : 'Agregar' }}
                            </button>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </section>
</template>
