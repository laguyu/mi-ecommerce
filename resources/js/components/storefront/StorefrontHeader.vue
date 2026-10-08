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
    contactUrl: { type: String, required: true },
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
        <div class="brand-block" role="button" tabindex="0" @click="navigateTo(homeUrl)" @keydown.enter="navigateTo(homeUrl)">
            <div class="brand-row">
                <img v-if="siteSettings.logo_url" :src="siteSettings.logo_url" :alt="siteSettings.site_name" class="brand-logo" />
                <div class="brand-copy">
                    <p class="eyebrow">{{ siteSettings.site_eyebrow || 'Tienda Oficial' }}</p>
                    <h1>{{ siteSettings.site_name || 'Nova Shop' }}</h1>
                </div>
            </div>
            <p class="subtitle">{{ siteSettings.site_tagline || 'Tu tienda en línea con envíos rápidos y pagos 100% seguros.' }}</p>
        </div>

        <nav class="top-menu" aria-label="Menu principal de la tienda">
            <div class="menu-mobile">
                <button
                    type="button"
                    class="menu-mobile__toggle"
                    :class="mobileMenuOpen && 'menu-mobile__toggle--active'"
                    @click="toggleMobileMenu"
                    aria-label="Abrir menu"
                >
                    <div class="menu-mobile__toggle-content">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line v-if="!mobileMenuOpen" x1="3" y1="12" x2="21" y2="12"></line>
                            <line v-if="!mobileMenuOpen" x1="3" y1="6" x2="21" y2="6"></line>
                            <line v-if="!mobileMenuOpen" x1="3" y1="18" x2="21" y2="18"></line>
                            <line v-if="mobileMenuOpen" x1="18" y1="6" x2="6" y2="18"></line>
                            <line v-if="mobileMenuOpen" x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        <span class="menu-mobile__toggle-label">{{ mobileMenuOpen ? 'Cerrar' : 'Menú' }}</span>
                    </div>
                    <small class="menu-mobile__toggle-hint">{{ mobileMenuOpen ? 'Ocultar navegación' : 'Explorar tienda' }}</small>
                </button>

                <div v-if="mobileMenuOpen" class="menu-mobile__panel">
                    <form class="menu-mobile__search" @submit.prevent="submitSearch">
                        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input
                            v-model="quickSearch"
                            type="search"
                            class="menu-mobile__search-input"
                            placeholder="Buscar productos..."
                            aria-label="Buscar productos"
                        >
                        <button type="submit">Buscar</button>
                    </form>

                    <button type="button" class="menu-mobile__item" @click="navigateTo(homeUrl)">
                        <span class="menu-mobile__item-label">Home</span>
                        <small class="menu-mobile__item-hint">Portada</small>
                    </button>

                    <button type="button" class="menu-mobile__item" @click="navigateTo(catalogUrl)">
                        <span class="menu-mobile__item-label">Catálogo</span>
                        <small class="menu-mobile__item-hint">Ver todos los productos</small>
                    </button>

                    <button type="button" class="menu-mobile__item" @click="navigateTo(contactUrl)">
                        <span class="menu-mobile__item-label">Contacto</span>
                        <small class="menu-mobile__item-hint">Atención y dudas</small>
                    </button>

                    <button type="button" class="menu-mobile__item" @click="mobileCategoriesOpen = !mobileCategoriesOpen">
                        <span class="menu-mobile__item-label">Categorías</span>
                        <small class="menu-mobile__item-hint">{{ mobileCategoriesOpen ? 'Ocultar listado' : 'Explorar por categoría' }}</small>
                    </button>

                    <div v-if="mobileCategoriesOpen" class="menu-mobile__categories">
                        <div v-for="category in categoryTree" :key="category.id" class="menu-mobile__category-group">
                            <button type="button" class="menu-mobile__item menu-mobile__item--category" @click="navigateToCategory(category.id)">
                                <span class="menu-mobile__item-label">{{ category.name }}</span>
                                <small class="menu-mobile__item-hint">{{ category.children.length > 0 ? 'Abrir categoría' : 'Ver productos' }}</small>
                            </button>

                            <div v-if="category.children.length > 0" class="menu-mobile__subcategory-list">
                                <button
                                    v-for="child in category.children"
                                    :key="child.id"
                                    type="button"
                                    class="menu-mobile__item menu-mobile__item--subcategory"
                                    @click="navigateToCategory(child.id)"
                                >
                                    <span class="menu-mobile__item-label">↳ {{ child.name }}</span>
                                    <small class="menu-mobile__item-hint">Subcategoría</small>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="menu-mobile__item" @click="navigateTo(favoritesUrl)">
                        <span class="menu-mobile__item-label">Favoritos</span>
                        <span v-if="hasFavoriteItems" class="menu-count-badge">{{ favoritesCount }}</span>
                        <small v-else class="menu-mobile__item-hint">Sin favoritos aún</small>
                    </button>

                    <button type="button" class="menu-mobile__item" @click="openCartPreview">
                        <span class="menu-mobile__item-label">Carrito</span>
                        <span v-if="hasCartItems" class="menu-count-badge">{{ itemsCount }}</span>
                        <small v-else class="menu-mobile__item-hint">Carrito vacío</small>
                    </button>

                    <button type="button" class="menu-mobile__item" :disabled="!hasItems" @click="navigateTo(checkoutUrl)">
                        <span class="menu-mobile__item-label">Finalizar compra</span>
                        <small class="menu-mobile__item-hint">Checkout seguro</small>
                    </button>
                </div>
            </div>

            <button
                type="button"
                :class="['menu-item', activeView === 'home' && 'menu-item--active']"
                @click="navigateTo(homeUrl)"
            >
                <span class="menu-item__label">Home</span>
            </button>

            <button
                type="button"
                :class="['menu-item', activeView === 'catalogo' && 'menu-item--active']"
                @click="navigateTo(catalogUrl)"
            >
                <span class="menu-item__label">Catálogo</span>
            </button>

            <div class="menu-dropdown" @mouseenter="openCategories" @mouseleave="closeCategories">
                <button
                    type="button"
                    :class="['menu-item', 'menu-item--dropdown', categoriesOpen && 'menu-item--active']"
                    @click="toggleCategories"
                >
                    <span class="menu-item__label">Categorías</span>
                    <svg class="chevron-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>

                <div v-if="categoriesOpen" class="menu-dropdown__panel menu-dropdown__panel--desktop" role="dialog" aria-label="Categorias de la tienda">
                    <div class="menu-dropdown__column menu-dropdown__column--parents">
                        <p class="menu-dropdown__title">Categorías principales</p>

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
                                {{ category.children.length > 0 ? `${category.children.length} subcategorías` : 'Ver catálogo' }}
                            </small>
                        </button>
                    </div>

                    <div class="menu-dropdown__column menu-dropdown__column--children">
                        <template v-if="activeCategory?.children?.length">
                            <p class="menu-dropdown__title">Subcategorías de {{ activeCategory.name }}</p>

                            <button
                                v-for="child in activeCategory.children"
                                :key="child.id"
                                type="button"
                                class="menu-dropdown__item menu-dropdown__item--child"
                                @click="navigateToCategory(child.id)"
                            >
                                <span class="menu-dropdown__item-label">{{ child.name }}</span>
                                <small class="menu-dropdown__item-hint">Ver productos</small>
                            </button>
                        </template>

                        <div v-else class="menu-dropdown__empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:28px;height:28px;margin-bottom:0.4rem;opacity:0.6;" aria-hidden="true">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                            <span>Pasa el cursor sobre una categoría para ver sus subcategorías.</span>
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
                                <small class="menu-dropdown__item-hint">Abrir catálogo</small>
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
                                <small class="menu-dropdown__item-hint">Subcategoría</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <button
                type="button"
                :class="['menu-item', activeView === 'contacto' && 'menu-item--active']"
                @click="navigateTo(contactUrl)"
            >
                <span class="menu-item__label">Contacto</span>
            </button>

            <button
                type="button"
                :class="['menu-item', 'menu-item--pill', activeView === 'favoritos' && 'menu-item--active']"
                @click="navigateTo(favoritesUrl)"
                title="Favoritos"
            >
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
                <span class="menu-item__label">Favoritos</span>
                <span v-if="hasFavoriteItems" class="menu-count-badge">{{ favoritesCount }}</span>
            </button>

            <button
                type="button"
                :class="['menu-item', 'menu-item--pill', 'menu-item--cart', activeView === 'carrito' && 'menu-item--active']"
                @click="openCartPreview"
                title="Carrito de compras"
            >
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
                <span class="menu-item__label">Carrito</span>
                <span v-if="hasCartItems" class="menu-count-badge">{{ itemsCount }}</span>
            </button>

            <button
                type="button"
                :class="['menu-item', 'menu-item--checkout', activeView === 'checkout' && 'menu-item--active']"
                :disabled="!hasItems"
                @click="navigateTo(checkoutUrl)"
            >
                <span class="menu-item__label">Checkout</span>
            </button>

            <form class="site-nav__search" @submit.prevent="submitSearch">
                <svg class="search-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input
                    v-model="quickSearch"
                    type="search"
                    class="site-nav__search-input"
                    placeholder="Buscar productos..."
                    aria-label="Buscar productos"
                >
                <button type="submit" aria-label="Buscar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;" aria-hidden="true">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </form>
        </nav>
    </header>
</template>
