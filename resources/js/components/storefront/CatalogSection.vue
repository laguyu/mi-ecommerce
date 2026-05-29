<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    formatCurrency: { type: Function, required: true },
    favoriteIds: { type: Array, default: () => [] },
    isAuthenticated: { type: Boolean, default: false },
});

const emit = defineEmits(['open-product', 'add-to-cart', 'toggle-favorite']);

const search = ref('');
const selectedCategories = ref([]);
const selectedBrands = ref([]);
const selectedSort = ref('nuevos');
const selectedPromotionId = ref(null);
const promotionLocked = ref(false);
const activePromotion = ref(null);
const activePromotionCountdown = ref('');
const products = ref([]);
const categories = ref([]);
const brands = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, per_page: 24, total: 0 });
const productsLoading = ref(false);
const productsError = ref('');
let reloadTimer = null;
let promotionCountdownTimer = null;
let isHydrating = true;
const STORAGE_KEY = 'storefront-catalog-state-v1';

function isSoldOut(product) {
    return Number(product?.stock ?? 0) <= 0;
}

function isFavorite(productId) {
    return props.favoriteIds.includes(Number(productId));
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

function stopPromotionCountdown() {
    if (promotionCountdownTimer) {
        clearInterval(promotionCountdownTimer);
        promotionCountdownTimer = null;
    }
}

function startPromotionCountdown() {
    stopPromotionCountdown();

    const targetDateRaw = activePromotion.value?.endsAt || activePromotion.value?.startsAt;
    const targetDate = targetDateRaw ? new Date(targetDateRaw) : null;

    if (!targetDate || Number.isNaN(targetDate.getTime())) {
        activePromotionCountdown.value = '';
        return;
    }

    const updateCountdown = () => {
        const diff = targetDate.getTime() - new Date().getTime();
        activePromotionCountdown.value = diff > 0 ? formatDuration(diff) : '00:00:00';
    };

    updateCountdown();
    promotionCountdownTimer = setInterval(updateCountdown, 1000);
}

function getStoredState() {
    if (typeof window === 'undefined') {
        return null;
    }

    try {
        const raw = sessionStorage.getItem(STORAGE_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch {
        return null;
    }
}

function getUrlState() {
    if (typeof window === 'undefined') {
        return null;
    }

    const params = new URLSearchParams(window.location.search);

    if ([...params.keys()].length === 0) {
        return null;
    }

    return {
        search: params.get('q') || '',
        selectedSort: params.get('sort') || 'nuevos',
        selectedCategories: params.getAll('category_ids[]').map((value) => Number(value)).filter(Boolean),
        selectedBrands: params.getAll('brand_ids[]').map((value) => Number(value)).filter(Boolean),
        selectedPromotionId: Number(params.get('promotion_id') || 0) || null,
        promotionLocked: params.get('promotion_locked') === '1',
        page: Number(params.get('page') || 1) || 1,
    };
}

function persistState(page = pagination.value.current_page) {
    if (typeof window === 'undefined') {
        return;
    }

    try {
        sessionStorage.setItem(
            STORAGE_KEY,
            JSON.stringify({
                search: search.value,
                selectedCategories: selectedCategories.value,
                selectedBrands: selectedBrands.value,
                selectedSort: selectedSort.value,
                selectedPromotionId: selectedPromotionId.value,
                promotionLocked: promotionLocked.value,
                page,
                returnTo: `${window.location.pathname}${window.location.search}`,
            })
        );
    } catch {
        // Ignore storage errors.
    }
}

function syncUrl(page = pagination.value.current_page) {
    if (typeof window === 'undefined') {
        return;
    }

    const params = new URLSearchParams();

    if (search.value.trim() !== '') {
        params.set('q', search.value.trim());
    }

    if (selectedSort.value !== 'nuevos') {
        params.set('sort', selectedSort.value);
    }

    selectedCategories.value.forEach((id) => params.append('category_ids[]', String(id)));
    selectedBrands.value.forEach((id) => params.append('brand_ids[]', String(id)));

    if (selectedPromotionId.value) {
        params.set('promotion_id', String(selectedPromotionId.value));

        if (promotionLocked.value) {
            params.set('promotion_locked', '1');
        }
    }

    if (page > 1) {
        params.set('page', String(page));
    }

    const nextUrl = `${window.location.pathname}${params.toString() ? `?${params.toString()}` : ''}`;
    window.history.replaceState({}, '', nextUrl);
}

function restoreState() {
    const urlState = getUrlState();

    if (urlState) {
        search.value = urlState.search;
        selectedCategories.value = urlState.selectedCategories;
        selectedBrands.value = urlState.selectedBrands;
        selectedSort.value = urlState.selectedSort;
        selectedPromotionId.value = urlState.selectedPromotionId;
        promotionLocked.value = Boolean(urlState.promotionLocked);

        return urlState.page;
    }

    const state = getStoredState();

    if (!state) {
        return 1;
    }

    search.value = state.search ?? '';
    selectedCategories.value = Array.isArray(state.selectedCategories) ? state.selectedCategories : [];
    selectedBrands.value = Array.isArray(state.selectedBrands) ? state.selectedBrands : [];
    selectedSort.value = state.selectedSort || 'nuevos';
    selectedPromotionId.value = Number(state.selectedPromotionId || 0) || null;
    promotionLocked.value = Boolean(state.promotionLocked);

    return Number(state.page) > 0 ? Number(state.page) : 1;
}

function toggleFilter(listRef, value) {
    const current = listRef.value;

    if (current.includes(value)) {
        listRef.value = current.filter((item) => item !== value);
        return;
    }

    listRef.value = [...current, value];
}

function clearFilters() {
    search.value = '';
    selectedCategories.value = [];
    selectedBrands.value = [];
    selectedSort.value = 'nuevos';

    if (!promotionLocked.value) {
        selectedPromotionId.value = null;
        activePromotion.value = null;
    }

    persistState(1);
    syncUrl(1);
    loadCatalog(1);
}

function clearPromotionFilter() {
    if (promotionLocked.value) {
        return;
    }

    selectedPromotionId.value = null;
    activePromotion.value = null;
    persistState(1);
    syncUrl(1);
    loadCatalog(1);
}

function applyFilters() {
    persistState(1);
    syncUrl(1);
    loadCatalog(1);
}

function scheduleReload() {
    if (reloadTimer) {
        clearTimeout(reloadTimer);
    }

    reloadTimer = setTimeout(() => {
        loadCatalog(1);
    }, 250);
}

async function loadFacets() {
    const [categoriesResponse, brandsResponse] = await Promise.all([
        fetch('/api/categories'),
        fetch('/api/brands'),
    ]);

    if (!categoriesResponse.ok || !brandsResponse.ok) {
        throw new Error('No se pudieron cargar los filtros del catalogo.');
    }

    const [categoriesPayload, brandsPayload] = await Promise.all([
        categoriesResponse.json(),
        brandsResponse.json(),
    ]);

    categories.value = categoriesPayload.data ?? [];
    brands.value = brandsPayload.data ?? [];
}

async function loadCatalog(page = 1) {
    productsLoading.value = true;
    productsError.value = '';

    try {
        syncUrl(page);

        const params = new URLSearchParams();
        params.set('page', String(page));
        params.set('per_page', '24');

        if (search.value.trim() !== '') {
            params.set('q', search.value.trim());
        }

        if (selectedSort.value !== 'nuevos') {
            params.set('sort', selectedSort.value);
        }

        selectedCategories.value.forEach((id) => params.append('category_ids[]', String(id)));
        selectedBrands.value.forEach((id) => params.append('brand_ids[]', String(id)));

        if (selectedPromotionId.value) {
            params.set('promotion_id', String(selectedPromotionId.value));
        }

        const catalogResponse = await fetch(`/api/catalog?${params.toString()}`);

        if (!catalogResponse.ok) {
            throw new Error('No se pudo cargar el catalogo.');
        }

        const catalogPayload = await catalogResponse.json();

        products.value = catalogPayload.data ?? [];
        activePromotion.value = catalogPayload.meta?.promotion ?? null;
        pagination.value = catalogPayload.meta ?? pagination.value;
        startPromotionCountdown();
        persistState(pagination.value.current_page);
    } catch (error) {
        productsError.value = error.message || 'Error cargando catalogo.';
    } finally {
        productsLoading.value = false;
    }
}

onMounted(() => {
    const initialPage = restoreState();

    Promise.all([loadFacets(), loadCatalog(initialPage)]).catch((error) => {
        productsError.value = error.message || 'Error cargando catalogo.';
    });

    isHydrating = false;
});

watch(
    () => [search.value, selectedSort.value, selectedCategories.value.join(','), selectedBrands.value.join(','), selectedPromotionId.value || ''],
    () => {
        if (isHydrating) {
            return;
        }

        persistState(1);
        scheduleReload();
    }
);

onUnmounted(() => {
    if (reloadTimer) {
        clearTimeout(reloadTimer);
    }

    stopPromotionCountdown();
});
</script>

<template>
    <section class="catalogo">
        <div class="filters">
            <div v-if="activePromotion" class="filters__row" style="margin-bottom: .35rem;">
                <div class="filter-chip" style="display:flex; align-items:center; gap:.45rem;">
                    <span>Promocion activa: {{ activePromotion.name }}</span>
                    <span v-if="activePromotionCountdown" class="promotion-countdown-chip">Termina en {{ activePromotionCountdown }}</span>
                    <button v-if="!promotionLocked" type="button" class="ghost" @click="clearPromotionFilter">Quitar</button>
                </div>
            </div>

            <div class="filters__row filters__row--controls">
                <input v-model="search" type="search" class="filters__search" placeholder="Buscar producto..." />

                <select v-model="selectedSort" class="filters__select">
                    <option value="nuevos">Mas recientes</option>
                    <option value="price-asc">Precio: menor a mayor</option>
                    <option value="price-desc">Precio: mayor a menor</option>
                    <option value="name-asc">Nombre: A-Z</option>
                    <option value="name-desc">Nombre: Z-A</option>
                </select>
            </div>

            <div class="filters__row filters__row--actions">
                <button type="button" class="filters__action filters__action--primary" @click="applyFilters">Filtrar</button>
                <button type="button" class="filters__action filters__action--secondary" @click="clearFilters">Limpiar filtros</button>
            </div>

            <div class="filter-group filter-group--full">
                <span class="filter-group__label">Categorias</span>
                <label v-for="category in categories" :key="category.path" class="filter-chip" :style="{ marginLeft: `${category.depth * 0.75}rem` }">
                    <input v-model="selectedCategories" type="checkbox" :value="category.id">
                    <span>{{ category.path }}</span>
                </label>
            </div>

            <div class="filter-group filter-group--full">
                <span class="filter-group__label">Marcas</span>
                <label v-for="brand in brands" :key="brand.id" class="filter-chip">
                    <input v-model="selectedBrands" type="checkbox" :value="brand.id">
                    <span>{{ brand.name }}</span>
                </label>
            </div>
        </div>

        <p v-if="productsLoading" class="muted">Cargando catalogo...</p>
        <p v-else-if="productsError" class="error-block">{{ productsError }}</p>
        <p v-else-if="products.length === 0" class="muted">No hay productos para ese filtro.</p>

        <div class="grid">
            <article v-for="product in products" :key="product.id" class="card">
                <img :src="product.image" :alt="product.name" @click="emit('open-product', product)" />

                <div class="card-body">
                    <div class="card-head-actions">
                        <p v-if="product.brand_name" class="brand">{{ product.brand_name }}</p>
                        <button
                            type="button"
                            class="favorite-toggle"
                            :class="isFavorite(product.id) && 'favorite-toggle--active'"
                            :title="isFavorite(product.id) ? 'Quitar de favoritos' : 'Agregar a favoritos'"
                            :aria-label="isFavorite(product.id) ? 'Quitar de favoritos' : 'Agregar a favoritos'"
                            @click="emit('toggle-favorite', product)"
                        >
                            {{ isFavorite(product.id) ? '❤' : '♡' }}
                        </button>
                    </div>
                    <p class="category">{{ product.category_path || product.category }}</p>
                    <h3>{{ product.name }}</h3>
                    <span v-if="product.has_discount" class="discount-badge">-{{ product.discount_percentage }}%</span>
                    <p class="sku">{{ product.sku }}</p>
                    <p v-if="isSoldOut(product)" class="sold-out">Agotado</p>
                    <p class="desc">{{ product.description }}</p>

                    <div class="row">
                        <div class="price-stack">
                            <small v-if="product.has_discount" class="price-label">Antes</small>
                            <small v-if="product.has_discount" class="price-old">{{ formatCurrency(product.original_price) }}</small>
                            <small class="price-label">Ahora</small>
                            <strong class="price-current">{{ formatCurrency(product.price) }}</strong>
                        </div>
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

        <div v-if="pagination.last_page > 1" class="catalog-pagination">
            <button type="button" class="ghost" :disabled="pagination.current_page <= 1" @click="loadCatalog(pagination.current_page - 1)">
                Anterior
            </button>

            <span class="catalog-pagination__info">
                Pagina {{ pagination.current_page }} de {{ pagination.last_page }} · {{ pagination.total }} productos
            </span>

            <button
                type="button"
                class="ghost"
                :disabled="pagination.current_page >= pagination.last_page"
                @click="loadCatalog(pagination.current_page + 1)"
            >
                Siguiente
            </button>
        </div>
    </section>
</template>
