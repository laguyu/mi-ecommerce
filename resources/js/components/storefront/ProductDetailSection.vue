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
            <div>
                <img :src="currentProductMainImage" :alt="selectedProduct.name" class="product-main-image" />
                <div class="thumbs">
                    <button
                        v-for="(image, index) in currentProductImages"
                        :key="`${selectedProduct.id}-${index}`"
                        :class="['thumb', selectedImageIndex === index && 'thumb--active']"
                        @click="selectImage(index)"
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

                <h2 class="product-detail-title">{{ selectedProduct.name }}</h2>
                <span v-if="selectedProduct.has_discount" class="discount-badge">-{{ selectedProduct.discount_percentage }}%</span>

                <div class="product-detail-specs">
                    <p class="sku">SKU: {{ selectedProduct.sku }}</p>
                    <p class="stock">Stock disponible: {{ selectedProduct.stock }} unidades</p>
                </div>

                <p v-if="isSoldOut" class="sold-out">Producto agotado</p>

                <p class="desc product-detail-description">{{ selectedProduct.description }}</p>
                <div class="price-stack product-detail-price-stack">
                    <small v-if="selectedProduct.has_discount" class="price-label">Antes</small>
                    <small v-if="selectedProduct.has_discount" class="price-old">{{ formatCurrency(selectedProduct.original_price) }}</small>
                    <small class="price-label">Ahora</small>
                    <p class="hero-price product-detail-price price-current">{{ formatCurrency(selectedProduct.price) }}</p>
                </div>

                <div class="hero-actions product-detail-actions">
                    <button :disabled="isSoldOut" @click="emit('add-to-cart', selectedProduct)">
                        {{ isSoldOut ? 'Agotado' : 'Agregar al carrito' }}
                    </button>
                    <button class="ghost" @click="emit('toggle-favorite', selectedProduct)">
                        {{ props.isFavorite ? '❤ En favoritos' : '♡ Agregar a favoritos' }}
                    </button>
                    <button v-if="props.showBackToCatalog" class="ghost" @click="emit('back-catalog')">Volver al catalogo</button>
                </div>
            </article>
        </div>

        <p v-else class="muted">No se encontró el producto solicitado.</p>
    </section>
</template>
