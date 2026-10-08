<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    productId: { type: [Number, String], default: null },
    showBackToCatalog: { type: Boolean, default: false },
    formatCurrency: { type: Function, required: true },
    isFavorite: { type: Boolean, default: false },
    isAuthenticated: { type: Boolean, default: false },
});

const emit = defineEmits(['add-to-cart', 'back-catalog', 'toggle-favorite']);

const selectedProduct = ref(null);
const selectedImageIndex = ref(0);
const productError = ref('');
const productLoading = ref(false);

const currentProductImages = computed(() => {
    if (!selectedProduct.value) return [];
    if (selectedProduct.value.images?.length) return selectedProduct.value.images;

    return [
        {
            url: selectedProduct.value.image,
            alt: selectedProduct.value.name,
            isPrimary: true,
        },
    ];
});

const currentProductMainImage = computed(() => {
    const images = currentProductImages.value;

    if (images.length === 0) return '';

    return images[selectedImageIndex.value]?.url ?? images[0].url;
});

const isSoldOut = computed(() => Number(selectedProduct.value?.stock ?? 0) <= 0);

function selectImage(index) {
    selectedImageIndex.value = index;
}

async function loadProductDetail(productId) {
    if (!productId) {
        selectedProduct.value = null;
        productError.value = '';
        productLoading.value = false;
        return;
    }

    selectedImageIndex.value = 0;
    productError.value = '';
    productLoading.value = true;

    try {
        const response = await fetch(`/api/products/${productId}`);

        if (!response.ok) {
            throw new Error('No se pudo cargar la ficha del producto.');
        }

        const payload = await response.json();
        selectedProduct.value = payload.data;
    } catch (error) {
        selectedProduct.value = null;
        productError.value = error.message || 'No se pudo cargar la ficha del producto.';
    } finally {
        productLoading.value = false;
    }
}

watch(
    () => props.productId,
    (value) => {
        loadProductDetail(value);
    },
    { immediate: true }
);
</script>

<template>
    <section class="panel" aria-live="polite">
        <p v-if="productLoading" class="muted">Cargando ficha del producto...</p>
        <p v-if="!selectedProduct && !productError" class="muted">Selecciona un producto desde Home o Catalogo.</p>
        <p v-else-if="productError" class="error-block">{{ productError }}</p>

        <div v-else-if="selectedProduct" class="product-detail-layout">
            <div class="product-detail-media">
                <button v-if="props.showBackToCatalog" class="product-detail__back-link" @click="emit('back-catalog')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;" aria-hidden="true">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    <span>Volver al catálogo</span>
                </button>

                <div class="product-main-image-wrap">
                    <img :src="currentProductMainImage" :alt="selectedProduct.name" class="product-main-image" />
                    <span v-if="selectedProduct.has_discount" class="discount-badge discount-badge--floating">-{{ selectedProduct.discount_percentage }}%</span>
                </div>

                <div v-if="currentProductImages.length > 1" class="thumbs">
                    <button
                        v-for="(image, index) in currentProductImages"
                        :key="`${selectedProduct.id}-${index}`"
                        :class="['thumb', selectedImageIndex === index && 'thumb--active']"
                        @click="selectImage(index)"
                        :aria-label="`Ver imagen ${index + 1}`"
                    >
                        <img :src="image.url" :alt="image.alt || selectedProduct.name" />
                    </button>
                </div>
            </div>

            <article class="product-detail-info">
                <div class="product-detail-meta">
                    <p v-if="selectedProduct.brand_name" class="brand">{{ selectedProduct.brand_name }}</p>
                    <p class="category">{{ selectedProduct.category_path || selectedProduct.category }}</p>
                </div>

                <h1 class="product-detail-title">{{ selectedProduct.name }}</h1>

                <div class="product-detail-specs">
                    <span class="sku">Código: <strong>{{ selectedProduct.sku }}</strong></span>
                    <span v-if="!isSoldOut" class="stock-status stock-status--in-stock">
                        <span class="stock-dot"></span>
                        {{ selectedProduct.stock }} unidades disponibles
                    </span>
                    <span v-else class="stock-status stock-status--out">
                        <span class="stock-dot stock-dot--red"></span>
                        Agotado temporalmente
                    </span>
                </div>

                <div class="price-stack product-detail-price-stack">
                    <div v-if="selectedProduct.has_discount" class="product-detail-discount-row">
                        <small class="price-old">{{ formatCurrency(selectedProduct.original_price) }}</small>
                        <span class="discount-badge">-{{ selectedProduct.discount_percentage }}% DESCUENTO</span>
                    </div>
                    <p class="hero-price product-detail-price price-current">{{ formatCurrency(selectedProduct.price) }}</p>
                </div>

                <div class="product-detail-description-block">
                    <h3 class="product-detail-description-heading">Descripción del producto</h3>
                    <p class="desc product-detail-description">{{ selectedProduct.description }}</p>
                </div>

                <div class="hero-actions product-detail-actions">
                    <button class="product-detail-btn-cart" :disabled="isSoldOut" @click="emit('add-to-cart', selectedProduct)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;" aria-hidden="true">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                        <span>{{ isSoldOut ? 'Agotado' : 'Agregar al carrito' }}</span>
                    </button>
                    <button
                        class="ghost product-detail-btn-fav"
                        :class="props.isFavorite && 'favorite-toggle-inline--active'"
                        @click="emit('toggle-favorite', selectedProduct)"
                    >
                        <svg viewBox="0 0 24 24" :fill="props.isFavorite ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;" aria-hidden="true">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        <span>{{ props.isFavorite ? 'En favoritos' : 'Añadir a favoritos' }}</span>
                    </button>
                </div>
            </article>
        </div>

        <p v-else class="muted">No se encontró el producto solicitado.</p>
    </section>
</template>
