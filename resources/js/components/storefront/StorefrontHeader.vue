<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    activeView: { type: String, required: true },
    itemsCount: { type: Number, required: true },
    hasItems: { type: Boolean, required: true },
    siteSettings: { type: Object, default: () => ({}) },
    isAuthenticated: { type: Boolean, default: false },
    accountUrl: { type: String, default: '' },
    ordersUrl: { type: String, default: '' },
    homeUrl: { type: String, required: true },
    catalogUrl: { type: String, required: true },
    categories: { type: Array, default: () => [] },
    favoritesUrl: { type: String, required: true },
    favoritesCount: { type: Number, default: 0 },
    cartUrl: { type: String, required: true },
    checkoutUrl: { type: String, required: true },
});

const emit = defineEmits(['navigate', 'open-cart']);

const quickSearch = ref('');
const categoriesOpen = ref(false);
const mobileMenuOpen = ref(false);
const mobileCategoriesOpen = ref(false);
const hoveredCategoryId = ref(null);
const mobileOpenCategoryIds = ref([]);

const categoryTree = computed(() => buildCategoryTree(props.categories ?? []));
const hasCartItems = computed(() => props.itemsCount > 0);
const hasFavoriteItems = computed(() => props.favoritesCount > 0);

const activeCategory = computed(() => {
    if (!categoryTree.value.length) {
        return null;
    }

    return categoryTree.value.find((category) => category.id === hoveredCategoryId.value) ?? categoryTree.value[0];
});

function submitSearch() {
    const term = quickSearch.value.trim();

    if (!term) {
        emit('navigate', props.catalogUrl);
        return;
    }

    emit('navigate', `${props.catalogUrl}?q=${encodeURIComponent(term)}`);
}

function buildCategoryUrl(categoryId) {
    const url = new URL(props.catalogUrl, window.location.origin);
    url.searchParams.append('category_ids[]', String(categoryId));
    return `${url.pathname}${url.search}`;
}

function buildCategoryTree(flatCategories) {
    const roots = [];
    const stack = [];

    flatCategories.forEach((category) => {
        const node = {
            ...category,
            children: [],
        };

        while (stack.length > node.depth) {
            stack.pop();
        }

        if (stack.length === 0) {
            roots.push(node);
        } else {
            stack[stack.length - 1].children.push(node);
        }

        stack.push(node);
    });

    return roots;
}

function navigateToCategory(categoryId) {
    closeMobileMenu();
    categoriesOpen.value = false;
    mobileOpenCategoryIds.value = [];
    emit('navigate', buildCategoryUrl(categoryId));
}

function navigateTo(url) {
    closeMobileMenu();
    emit('navigate', url);
}

function openCartPreview() {
    closeMobileMenu();
    emit('open-cart');
}

function openCategories() {
    categoriesOpen.value = true;

    if (!hoveredCategoryId.value && categoryTree.value.length > 0) {
        hoveredCategoryId.value = categoryTree.value[0].id;
    }
}

function closeCategories() {
    categoriesOpen.value = false;
}

function toggleMobileMenu() {
    mobileMenuOpen.value = !mobileMenuOpen.value;

    if (!mobileMenuOpen.value) {
        mobileCategoriesOpen.value = false;
    }
}

function closeMobileMenu() {
    mobileMenuOpen.value = false;
    mobileCategoriesOpen.value = false;
}

function toggleMobileCategory(categoryId) {
    if (mobileOpenCategoryIds.value.includes(categoryId)) {
        mobileOpenCategoryIds.value = mobileOpenCategoryIds.value.filter((id) => id !== categoryId);
        return;
    }

    mobileOpenCategoryIds.value = [...mobileOpenCategoryIds.value, categoryId];
}

function isMobileCategoryOpen(categoryId) {
    return mobileOpenCategoryIds.value.includes(categoryId);
}

function toggleCategories() {
    categoriesOpen.value = !categoriesOpen.value;

    if (categoriesOpen.value) {
        openCategories();
    }
}

function setHoveredCategory(categoryId) {
    hoveredCategoryId.value = categoryId;
}
</script>

<template>
    <header class="topbar">
        <div class="brand-block">
            <div class="brand-row">
                <img v-if="siteSettings.logo_url" :src="siteSettings.logo_url" :alt="siteSettings.site_name" class="brand-logo" />
                <div>
                    <p class="eyebrow">{{ siteSettings.site_eyebrow || 'Laravel + Vue Ecommerce' }}</p>
                    <h1>{{ siteSettings.site_name || 'Nova Shop' }}</h1>
                </div>
            </div>
            <p class="subtitle">{{ siteSettings.site_tagline || 'Home con carrusel, catalogo, ficha de producto y checkout con Stripe/PayPal.' }}</p>
        </div>

        <nav class="top-menu" aria-label="Menu principal de la tienda">
            <div class="menu-mobile">
                <button
                    type="button"
                    class="menu-mobile__toggle"
                    :class="mobileMenuOpen && 'menu-mobile__toggle--active'"
                    @click="toggleMobileMenu"
                >
                    <span class="menu-mobile__toggle-label">Menu</span>
                    <small class="menu-mobile__toggle-hint">Ver opciones</small>
                </button>

                <div v-if="mobileMenuOpen" class="menu-mobile__panel">
                    <button type="button" class="menu-mobile__item" @click="navigateTo(homeUrl)">
                        <span class="menu-mobile__item-label">Home</span>
                        <small class="menu-mobile__item-hint">Portada</small>
                    </button>

                    <button type="button" class="menu-mobile__item" @click="navigateTo(catalogUrl)">
                        <span class="menu-mobile__item-label">Catalogo</span>
                        <small class="menu-mobile__item-hint">Ver productos</small>
                    </button>

                    <button type="button" class="menu-mobile__item" @click="mobileCategoriesOpen = !mobileCategoriesOpen">
                        <span class="menu-mobile__item-label">Categorias</span>
                        <small class="menu-mobile__item-hint">Padres y subcategorias</small>
                    </button>

                    <div v-if="mobileCategoriesOpen" class="menu-mobile__categories">
                        <div v-for="category in categoryTree" :key="category.id" class="menu-mobile__category-group">
                            <button type="button" class="menu-mobile__item menu-mobile__item--category" @click="navigateToCategory(category.id)">
                                <span class="menu-mobile__item-label">{{ category.name }}</span>
                                <small class="menu-mobile__item-hint">{{ category.children.length > 0 ? 'Abrir categoria' : 'Abrir catalogo' }}</small>
                            </button>

                            <div v-if="category.children.length > 0" class="menu-mobile__subcategory-list">
                                <button
                                    v-for="child in category.children"
                                    :key="child.id"
                                    type="button"
                                    class="menu-mobile__item menu-mobile__item--subcategory"
                                    @click="navigateToCategory(child.id)"
                                >
                                    <span class="menu-mobile__item-label">{{ child.name }}</span>
                                    <small class="menu-mobile__item-hint">Subcategoria</small>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="menu-mobile__item" @click="navigateTo(favoritesUrl)">
                        <span class="menu-mobile__item-label">Favoritos</span>
                        <span v-if="hasFavoriteItems" class="menu-count-badge">{{ favoritesCount }}</span>
                        <small v-else class="menu-mobile__item-hint">Sin favoritos</small>
                    </button>

                    <button type="button" class="menu-mobile__item" @click="openCartPreview">
                        <span class="menu-mobile__item-label">Carrito</span>
                        <span v-if="hasCartItems" class="menu-count-badge">{{ itemsCount }}</span>
                        <small v-else class="menu-mobile__item-hint">Carrito vacio</small>
                    </button>

                    <button type="button" class="menu-mobile__item" :disabled="!hasItems" @click="navigateTo(checkoutUrl)">
                        <span class="menu-mobile__item-label">Checkout</span>
                        <small class="menu-mobile__item-hint">Pagar pedido</small>
                    </button>

                    <form class="menu-mobile__search" @submit.prevent="submitSearch">
                        <input
                            v-model="quickSearch"
                            type="search"
                            class="menu-mobile__search-input"
                            placeholder="Buscar producto"
                            aria-label="Buscar productos"
                        >
                        <button type="submit">Buscar</button>
                    </form>
                </div>
            </div>

            <button
                type="button"
                :class="['menu-item', activeView === 'home' && 'menu-item--active']"
                @click="navigateTo(homeUrl)"
            >
                <span class="menu-item__label">Home</span>
                <small class="menu-item__hint">Portada</small>
            </button>

            <button
                type="button"
                :class="['menu-item', activeView === 'catalogo' && 'menu-item--active']"
                @click="navigateTo(catalogUrl)"
            >
                <span class="menu-item__label">Catalogo</span>
                <small class="menu-item__hint">Ver productos</small>
            </button>

            <div class="menu-dropdown" @mouseenter="openCategories" @mouseleave="closeCategories">
                <button
                    type="button"
                    :class="['menu-item', categoriesOpen && 'menu-item--active']"
                    @click="toggleCategories"
                >
                    <span class="menu-item__label">Categorias</span>
                    <small class="menu-item__hint">Padres y subcategorias</small>
                </button>

                <div v-if="categoriesOpen" class="menu-dropdown__panel menu-dropdown__panel--desktop" role="dialog" aria-label="Categorias de la tienda">
                    <div class="menu-dropdown__column menu-dropdown__column--parents">
                        <p class="menu-dropdown__title">Categorias</p>

                        <button
                            v-for="category in categoryTree"
                            :key="category.id"
                            type="button"
                            class="menu-dropdown__item menu-dropdown__item--parent"
                            :class="activeCategory?.id === category.id && 'menu-dropdown__item--active'"
                            @mouseenter="setHoveredCategory(category.id)"
                            @focus="setHoveredCategory(category.id)"
                            @click="navigateToCategory(category.id)"
                        >
                            <span class="menu-dropdown__item-label">{{ category.name }}</span>
                            <small class="menu-dropdown__item-hint">
                                {{ category.children.length > 0 ? 'Ver subcategorias' : 'Abrir catalogo' }}
                            </small>
                        </button>
                    </div>

                    <div class="menu-dropdown__column menu-dropdown__column--children">
                        <template v-if="activeCategory?.children?.length">
                            <p class="menu-dropdown__title">Subcategorias de {{ activeCategory.name }}</p>

                            <button
                                v-for="child in activeCategory.children"
                                :key="child.id"
                                type="button"
                                class="menu-dropdown__item menu-dropdown__item--child"
                                @click="navigateToCategory(child.id)"
                            >
                                <span class="menu-dropdown__item-label">{{ child.name }}</span>
                                <small class="menu-dropdown__item-hint">Subcategoria</small>
                            </button>
                        </template>

                        <div v-else class="menu-dropdown__empty">
                            Pasa el cursor sobre una categoria con hijas para ver sus subcategorias.
                        </div>
                    </div>
                </div>

                <div v-if="categoriesOpen" class="menu-dropdown__panel menu-dropdown__panel--mobile" aria-label="Categorias de la tienda">
                    <div v-for="category in categoryTree" :key="category.id" class="menu-dropdown__mobile-group">
                        <div class="menu-dropdown__mobile-row">
                            <button
                                type="button"
                                class="menu-dropdown__item menu-dropdown__item--parent menu-dropdown__item--mobile-parent"
                                @click="navigateToCategory(category.id)"
                            >
                                <span class="menu-dropdown__item-label">{{ category.name }}</span>
                                <small class="menu-dropdown__item-hint">Abrir catalogo</small>
                            </button>

                            <button
                                v-if="category.children.length > 0"
                                type="button"
                                class="menu-dropdown__mobile-toggle"
                                :aria-label="isMobileCategoryOpen(category.id) ? 'Ocultar subcategorias' : 'Mostrar subcategorias'"
                                @click.stop="toggleMobileCategory(category.id)"
                            >
                                <span aria-hidden="true">{{ isMobileCategoryOpen(category.id) ? '−' : '+' }}</span>
                            </button>
                        </div>

                        <div v-if="category.children.length > 0 && isMobileCategoryOpen(category.id)" class="menu-dropdown__mobile-children">
                            <button
                                v-for="child in category.children"
                                :key="child.id"
                                type="button"
                                class="menu-dropdown__item menu-dropdown__item--child menu-dropdown__item--mobile-child"
                                @click="navigateToCategory(child.id)"
                            >
                                <span class="menu-dropdown__item-label">{{ child.name }}</span>
                                <small class="menu-dropdown__item-hint">Subcategoria</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <button
                type="button"
                :class="['menu-item', activeView === 'favoritos' && 'menu-item--active']"
                @click="navigateTo(favoritesUrl)"
            >
                <span class="menu-item__label">Favoritos</span>
                <span v-if="hasFavoriteItems" class="menu-count-badge">{{ favoritesCount }}</span>
                <small v-else class="menu-item__hint">Vacio</small>
            </button>

            <button
                type="button"
                :class="['menu-item', activeView === 'carrito' && 'menu-item--active']"
                @click="openCartPreview"
            >
                <span class="menu-item__label">Carrito</span>
                <span v-if="hasCartItems" class="menu-count-badge">{{ itemsCount }}</span>
                <small v-else class="menu-item__hint">Vacio</small>
            </button>

            <button
                type="button"
                :class="['menu-item', activeView === 'checkout' && 'menu-item--active']"
                :disabled="!hasItems"
                @click="navigateTo(checkoutUrl)"
            >
                <span class="menu-item__label">Checkout</span>
                <small class="menu-item__hint">Pagar pedido</small>
            </button>

            <form class="site-nav__search" @submit.prevent="submitSearch">
                <input
                    v-model="quickSearch"
                    type="search"
                    class="site-nav__search-input"
                    placeholder="Buscar por producto, marca o categoria"
                    aria-label="Buscar productos"
                >
                <button type="submit">Buscar</button>
            </form>
        </nav>
    </header>
</template>
