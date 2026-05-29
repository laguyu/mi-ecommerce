<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    formatCurrency: { type: Function, required: true },
    favoriteIds: { type: Array, default: () => [] },
    isAuthenticated: { type: Boolean, default: false },
});

const emit = defineEmits(['open-product', 'open-promotion', 'add-to-cart', 'toggle-favorite']);

function handleMainBannerClick() {
    if (mainBanner.value?.productId) {
        emit('open-product', mainBanner.value.productId);
    }
}

function handlePromotionBannerClick() {
    if (promotionBanner.value?.promotionId) {
        emit('open-promotion', promotionBanner.value.promotionId);
    }
}

function handleSecondaryBannerClick(banner) {
    if (banner?.productId) {
        emit('open-product', banner.productId);
        return;
    }

    if (banner?.linkUrl) {
        window.open(banner.linkUrl, '_blank', 'noopener');
    }
}

const mainBanner = ref(null);
const promotionBanner = ref(null);
const homeProducts = ref([]);
const secondaryBanners = ref([]);
const homeProductCarousels = ref([]);
const homeLoading = ref(false);
const homeError = ref('');
const carouselIndex = ref(0);
const promotionCountdown = ref('');
let promotionCountdownTimer = null;
let carouselTimer = null;

const currentSlide = computed(() => homeProducts.value[carouselIndex.value] ?? null);

function isSoldOut(product) {
    return Number(product?.stock ?? 0) <= 0;
}

function isFavorite(productId) {
    return props.favoriteIds.includes(Number(productId));
}

function nextSlide() {
    if (homeProducts.value.length <= 1) return;

    carouselIndex.value = (carouselIndex.value + 1) % homeProducts.value.length;
}

function prevSlide() {
    if (homeProducts.value.length <= 1) return;

    carouselIndex.value = (carouselIndex.value - 1 + homeProducts.value.length) % homeProducts.value.length;
}

function setSlide(index) {
    carouselIndex.value = index;
}

function stopCarousel() {
    if (carouselTimer) {
        clearInterval(carouselTimer);
        carouselTimer = null;
    }
}

function stopPromotionCountdown() {
    if (promotionCountdownTimer) {
        clearInterval(promotionCountdownTimer);
        promotionCountdownTimer = null;
    }
}

function formatDuration(ms) {
    const totalSeconds = Math.max(0, Math.floor(ms / 1000));
    const days = Math.floor(totalSeconds / 86400);
    const hours = Math.floor((totalSeconds % 86400) / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    const pad = (value) => String(value).padStart(2, '0');

    if (days > 0) {
        return `${days}d ${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
    }

    return `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
}

function startPromotionCountdown() {
    stopPromotionCountdown();

    const endsAt = promotionBanner.value?.endsAt ? new Date(promotionBanner.value.endsAt) : null;
    const startsAt = promotionBanner.value?.startsAt ? new Date(promotionBanner.value.startsAt) : null;

    const targetDate = endsAt && !Number.isNaN(endsAt.getTime()) ? endsAt : (startsAt && !Number.isNaN(startsAt.getTime()) ? startsAt : null);

    if (!targetDate) {
        promotionCountdown.value = '';
        return;
    }

    const updateCountdown = () => {
        const now = new Date();
        const diff = targetDate.getTime() - now.getTime();

        promotionCountdown.value = diff > 0 ? formatDuration(diff) : '00:00:00';
    };

    updateCountdown();
    promotionCountdownTimer = setInterval(updateCountdown, 1000);
}

function startCarousel() {
    stopCarousel();

    if (homeProducts.value.length <= 1) return;

    carouselTimer = setInterval(() => {
        nextSlide();
    }, 4200);
}

async function loadHomeProducts() {
    homeLoading.value = true;
    homeError.value = '';

    try {
        const [mainBannerResponse, promotionBannerResponse, productsResponse, secondaryBannersResponse, homeCarouselsResponse] = await Promise.all([
            fetch('/api/home-main-banner'),
            fetch('/api/home-promotion-banner'),
            fetch('/api/home-products'),
            fetch('/api/home-secondary-banners'),
            fetch('/api/home-product-carousels'),
        ]);

        if (!mainBannerResponse.ok || !promotionBannerResponse.ok || !productsResponse.ok || !secondaryBannersResponse.ok || !homeCarouselsResponse.ok) {
            throw new Error('No se pudo cargar la home.');
        }

        const mainBannerPayload = await mainBannerResponse.json();
        const promotionBannerPayload = await promotionBannerResponse.json();
        const productsPayload = await productsResponse.json();
        const secondaryBannersPayload = await secondaryBannersResponse.json();
        const homeCarouselsPayload = await homeCarouselsResponse.json();

        mainBanner.value = mainBannerPayload.data ?? null;
        promotionBanner.value = promotionBannerPayload.data ?? null;
        homeProducts.value = productsPayload.data ?? [];
        secondaryBanners.value = secondaryBannersPayload.data ?? [];
        homeProductCarousels.value = homeCarouselsPayload.data ?? [];
        carouselIndex.value = 0;
        startPromotionCountdown();
        startCarousel();
    } catch (error) {
        homeError.value = error.message || 'Error cargando home.';
    } finally {
        homeLoading.value = false;
    }
}

onMounted(() => {
    loadHomeProducts();
});

onUnmounted(() => {
    stopCarousel();
    stopPromotionCountdown();
});
</script>

<template>
    <section class="home">
        <section class="principal-banner" v-if="mainBanner">
            <article
                class="principal-banner-card"
                @click="handleMainBannerClick"
            >
                <img :src="mainBanner.image" :alt="mainBanner.title" />
                <div class="overlay"></div>
                <div class="content">
                    <p>Banner principal</p>
                    <h3>{{ mainBanner.title }}</h3>
                    <small>{{ mainBanner.subtitle }}</small>
                    <a v-if="mainBanner.linkUrl" :href="mainBanner.linkUrl" target="_blank" rel="noopener" @click.stop>
                        Ir a promocion
                    </a>
                </div>
            </article>
        </section>

        <section class="promotion-banner" v-if="promotionBanner">
            <article class="promotion-banner-card" @click="handlePromotionBannerClick">
                <img :src="promotionBanner.image" :alt="promotionBanner.title" />
                <div class="overlay"></div>
                <div class="content">
                    <p>Promocion activa · -{{ promotionBanner.discountPercentage }}%</p>
                    <h3>{{ promotionBanner.title }}</h3>
                    <small>{{ promotionBanner.subtitle }}</small>
                    <span v-if="promotionCountdown" class="promotion-banner-countdown">Termina en {{ promotionCountdown }}</span>
                    <span class="promotion-banner-cta">Ver catalogo en promocion</span>
                </div>
            </article>
        </section>

        <section class="secondary-banners" v-if="secondaryBanners.length > 0">
            <article
                v-for="banner in secondaryBanners"
                :key="`secondary-${banner.id}`"
                class="secondary-banner-card"
                @click="handleSecondaryBannerClick(banner)"
            >
                <img :src="banner.image" :alt="banner.title" />
                <div class="overlay"></div>
                <div class="content">
                    <p>Banner destacado</p>
                    <h4>{{ banner.title }}</h4>
                    <small>{{ banner.subtitle }}</small>
                </div>
            </article>
        </section>

        <p v-if="homeLoading" class="muted">Cargando home...</p>
        <p v-else-if="homeError" class="error-block">{{ homeError }}</p>

        <article v-else-if="currentSlide" class="hero">
            <img :src="currentSlide.image" :alt="currentSlide.name" class="hero-image" />

            <div class="hero-content">
                <p class="category">{{ currentSlide.category_path || currentSlide.category }}</p>
                <h2>{{ currentSlide.name }}</h2>
                <span v-if="currentSlide.has_discount" class="discount-badge">-{{ currentSlide.discount_percentage }}%</span>
                <p v-if="isSoldOut(currentSlide)" class="sold-out">Agotado</p>
                <p class="desc">{{ currentSlide.description }}</p>
                <div class="price-stack hero-price-stack">
                    <small v-if="currentSlide.has_discount" class="price-label">Antes</small>
                    <small v-if="currentSlide.has_discount" class="price-old">{{ formatCurrency(currentSlide.original_price) }}</small>
                    <small class="price-label">Ahora</small>
                    <p class="hero-price price-current">{{ formatCurrency(currentSlide.price) }}</p>
                </div>

                <div class="hero-actions">
                    <button @click="emit('open-product', currentSlide)">Ver ficha</button>
                    <button
                        class="ghost"
                        :disabled="isSoldOut(currentSlide)"
                        @click="emit('add-to-cart', currentSlide)"
                    >
                        {{ isSoldOut(currentSlide) ? 'Agotado' : 'Agregar al carrito' }}
                    </button>
                    <button
                        type="button"
                        class="ghost favorite-toggle-inline"
                        :class="isFavorite(currentSlide.id) && 'favorite-toggle-inline--active'"
                        @click="emit('toggle-favorite', currentSlide)"
                    >
                        {{ isFavorite(currentSlide.id) ? '❤ En favoritos' : '♡ Favorito' }}
                    </button>
                </div>
            </div>

            <button class="carousel-btn left" @click="prevSlide">&#10094;</button>
            <button class="carousel-btn right" @click="nextSlide">&#10095;</button>

            <div class="dots">
                <button
                    v-for="(product, index) in homeProducts"
                    :key="product.id"
                    :class="['dot', carouselIndex === index && 'dot--active']"
                    @click="setSlide(index)"
                ></button>
            </div>
        </article>

        <div class="mini-grid" v-if="homeProducts.length > 0">
            <article v-for="item in homeProducts.slice(0, 4)" :key="`mini-${item.id}`" class="mini-card">
                <img :src="item.image" :alt="item.name" />
                <div>
                    <h4>{{ item.name }}</h4>
                    <span v-if="item.has_discount" class="discount-badge">-{{ item.discount_percentage }}%</span>
                    <p v-if="isSoldOut(item)" class="sold-out">Agotado</p>
                    <div class="price-stack">
                        <small v-if="item.has_discount" class="price-label">Antes</small>
                        <small v-if="item.has_discount" class="price-old">{{ formatCurrency(item.original_price) }}</small>
                        <small class="price-label">Ahora</small>
                        <p class="price-current">{{ formatCurrency(item.price) }}</p>
                    </div>
                    <div class="mini-card-actions">
                        <button class="ghost" @click="emit('open-product', item)">Ver producto</button>
                        <button
                            type="button"
                            class="ghost favorite-toggle-inline"
                            :class="isFavorite(item.id) && 'favorite-toggle-inline--active'"
                            @click="emit('toggle-favorite', item)"
                        >
                            {{ isFavorite(item.id) ? '❤' : '♡' }}
                        </button>
                    </div>
                </div>
            </article>
        </div>

        <section
            v-for="module in homeProductCarousels"
            :key="`carousel-module-${module.id}`"
            class="home-carousel-module"
        >
            <header class="home-carousel-module__header">
                <div v-if="module.image" class="home-carousel-module__media">
                    <img :src="module.image" :alt="module.title" />
                </div>
                <div class="home-carousel-module__copy">
                    <p>Carrusel de productos</p>
                    <h3>{{ module.title }}</h3>
                    <small>{{ module.subtitle }}</small>
                </div>
            </header>

            <div class="home-carousel-module__grid" v-if="Array.isArray(module.products) && module.products.length > 0">
                <article v-for="item in module.products" :key="`module-product-${module.id}-${item.id}`" class="home-carousel-product-card">
                    <img :src="item.image" :alt="item.name" />
                    <div class="home-carousel-product-card__body">
                        <p class="category">{{ item.category_path || item.category }}</p>
                        <h4>{{ item.name }}</h4>
                        <span v-if="item.has_discount" class="discount-badge">-{{ item.discount_percentage }}%</span>
                        <p v-if="isSoldOut(item)" class="sold-out">Agotado</p>
                        <div class="price-stack">
                            <small v-if="item.has_discount" class="price-label">Antes</small>
                            <small v-if="item.has_discount" class="price-old">{{ formatCurrency(item.original_price) }}</small>
                            <small class="price-label">Ahora</small>
                            <p class="price-current">{{ formatCurrency(item.price) }}</p>
                        </div>
                        <div class="home-carousel-actions">
                            <button class="home-carousel-btn home-carousel-btn--primary" @click="emit('open-product', item)">Ver ficha</button>
                            <button
                                type="button"
                                class="home-carousel-btn home-carousel-btn--accent"
                                :disabled="isSoldOut(item)"
                                @click="emit('add-to-cart', item)"
                            >
                                {{ isSoldOut(item) ? 'Agotado' : 'Agregar al carrito' }}
                            </button>
                            <button
                                type="button"
                                class="home-carousel-btn home-carousel-btn--icon favorite-toggle-inline"
                                :class="isFavorite(item.id) && 'favorite-toggle-inline--active'"
                                @click="emit('toggle-favorite', item)"
                                :aria-label="isFavorite(item.id) ? 'Quitar de favoritos' : 'Agregar a favoritos'"
                            >
                                {{ isFavorite(item.id) ? '❤' : '♡' }}
                            </button>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </section>
</template>
