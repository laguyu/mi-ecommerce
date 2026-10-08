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
                    <button class="hero-action-btn hero-action-btn--primary" @click="emit('open-product', currentSlide)">
                        <span>Ver detalles</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;" aria-hidden="true">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </button>
                    <button
                        class="ghost hero-action-btn"
                        :disabled="isSoldOut(currentSlide)"
                        @click="emit('add-to-cart', currentSlide)"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;" aria-hidden="true">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                        <span>{{ isSoldOut(currentSlide) ? 'Agotado' : 'Agregar al carrito' }}</span>
                    </button>
                    <button
                        type="button"
                        class="ghost favorite-toggle-inline"
                        :class="isFavorite(currentSlide.id) && 'favorite-toggle-inline--active'"
                        @click="emit('toggle-favorite', currentSlide)"
                        :aria-label="isFavorite(currentSlide.id) ? 'Quitar de favoritos' : 'Agregar a favoritos'"
                    >
                        <svg class="heart-icon" viewBox="0 0 24 24" :fill="isFavorite(currentSlide.id) ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        <span>{{ isFavorite(currentSlide.id) ? 'En favoritos' : 'Favorito' }}</span>
                    </button>
                </div>
            </div>

            <button class="carousel-btn left" @click="prevSlide" aria-label="Slide anterior">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;" aria-hidden="true">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <button class="carousel-btn right" @click="nextSlide" aria-label="Slide siguiente">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;" aria-hidden="true">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>

            <div class="dots">
                <button
                    v-for="(product, index) in homeProducts"
                    :key="product.id"
                    :class="['dot', carouselIndex === index && 'dot--active']"
                    @click="setSlide(index)"
                    :aria-label="`Ir al slide ${index + 1}`"
                ></button>
            </div>
        </article>

        <div class="mini-grid" v-if="homeProducts.length > 0">
            <article v-for="item in homeProducts.slice(0, 4)" :key="`mini-${item.id}`" class="mini-card">
                <div class="mini-card__image-wrap">
                    <img :src="item.image" :alt="item.name" />
                    <button
                        type="button"
                        class="favorite-floating-btn"
                        :class="isFavorite(item.id) && 'favorite-floating-btn--active'"
                        @click.stop="emit('toggle-favorite', item)"
                        :aria-label="isFavorite(item.id) ? 'Quitar de favoritos' : 'Agregar a favoritos'"
                    >
                        <svg viewBox="0 0 24 24" :fill="isFavorite(item.id) ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;" aria-hidden="true">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </button>
                    <span v-if="item.has_discount" class="discount-badge discount-badge--floating">-{{ item.discount_percentage }}%</span>
                </div>
                <div class="mini-card__body">
                    <p class="category">{{ item.category_path || item.category }}</p>
                    <h4>{{ item.name }}</h4>
                    <p v-if="isSoldOut(item)" class="sold-out">Agotado</p>
                    <div class="price-stack">
                        <small v-if="item.has_discount" class="price-old">{{ formatCurrency(item.original_price) }}</small>
                        <p class="price-current">{{ formatCurrency(item.price) }}</p>
                    </div>
                    <div class="mini-card-actions">
                        <button class="ghost" @click="emit('open-product', item)">Ver ficha</button>
                        <button
                            class="mini-card-actions__cart"
                            :disabled="isSoldOut(item)"
                            @click="emit('add-to-cart', item)"
                            title="Agregar al carrito"
                        >
                            {{ isSoldOut(item) ? 'Agotado' : '+ Agregar' }}
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
                    <p class="home-carousel-module__badge">Colección Destacada</p>
                    <h3>{{ module.title }}</h3>
                    <small>{{ module.subtitle }}</small>
                </div>
            </header>

            <div class="home-carousel-module__grid" v-if="Array.isArray(module.products) && module.products.length > 0">
                <article v-for="item in module.products" :key="`module-product-${module.id}-${item.id}`" class="home-carousel-product-card">
                    <div class="home-carousel-product-card__image-wrap" @click="emit('open-product', item)">
                        <img :src="item.image" :alt="item.name" />
                        <span v-if="item.has_discount" class="discount-badge discount-badge--floating">-{{ item.discount_percentage }}%</span>
                        <span v-if="isSoldOut(item)" class="sold-out sold-out--floating">Agotado</span>
                    </div>
                    <div class="home-carousel-product-card__body">
                        <p class="category">{{ item.category_path || item.category }}</p>
                        <h4>{{ item.name }}</h4>
                        <div class="price-stack">
                            <small v-if="item.has_discount" class="price-old">{{ formatCurrency(item.original_price) }}</small>
                            <p class="price-current">{{ formatCurrency(item.price) }}</p>
                        </div>
                        <div class="home-carousel-actions">
                            <button class="home-carousel-btn home-carousel-btn--primary" @click="emit('open-product', item)">Ficha</button>
                            <button
                                type="button"
                                class="home-carousel-btn home-carousel-btn--accent"
                                :disabled="isSoldOut(item)"
                                @click="emit('add-to-cart', item)"
                            >
                                {{ isSoldOut(item) ? 'Agotado' : 'Agregar' }}
                            </button>
                            <button
                                type="button"
                                class="home-carousel-btn home-carousel-btn--icon favorite-toggle-inline"
                                :class="isFavorite(item.id) && 'favorite-toggle-inline--active'"
                                @click="emit('toggle-favorite', item)"
                                :aria-label="isFavorite(item.id) ? 'Quitar de favoritos' : 'Agregar a favoritos'"
                            >
                                <svg viewBox="0 0 24 24" :fill="isFavorite(item.id) ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:17px;height:17px;" aria-hidden="true">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </section>
</template>
