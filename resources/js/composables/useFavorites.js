import { computed, ref } from 'vue';

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

export function useFavorites(storefrontIsAuthenticated, onToast) {
    const favoriteIds = ref([]);
    const favoritesAuthenticatedByApi = ref(false);

    const favoritesIsAuthenticated = computed(() => Boolean(storefrontIsAuthenticated.value || favoritesAuthenticatedByApi.value));
    const favoritesCount = computed(() => favoriteIds.value.length);

    async function loadFavoriteIds() {
        try {
            const response = await fetch('/api/favorites/ids', {
                headers: {
                    Accept: 'application/json',
                },
            });

            if (response.status === 401 || response.status === 419) {
                favoriteIds.value = [];
                favoritesAuthenticatedByApi.value = false;
                return;
            }

            if (!response.ok) {
                throw new Error('No se pudieron cargar los favoritos.');
            }

            const payload = await response.json();
            favoriteIds.value = (payload.data ?? []).map((id) => Number(id)).filter(Boolean);
            favoritesAuthenticatedByApi.value = true;
        } catch {
            favoriteIds.value = [];
            favoritesAuthenticatedByApi.value = false;
        }
    }

    function isFavorite(productOrId) {
        const id = typeof productOrId === 'object' ? Number(productOrId?.id) : Number(productOrId);
        return favoriteIds.value.includes(id);
    }

    async function toggleFavorite(productOrId) {
        const product = typeof productOrId === 'object' ? productOrId : { id: productOrId };

        if (!product?.id) {
            return;
        }

        try {
            const response = await fetch('/api/favorites/toggle', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ product_id: product.id }),
            });

            if (response.status === 401 || response.status === 419) {
                favoritesAuthenticatedByApi.value = false;
                onToast?.('Inicia sesion para guardar favoritos');
                return;
            }

            if (!response.ok) {
                throw new Error('No se pudo actualizar favoritos.');
            }

            const payload = await response.json();
            const ids = payload?.data?.favorite_ids ?? [];

            favoriteIds.value = ids.map((id) => Number(id)).filter(Boolean);
            favoritesAuthenticatedByApi.value = true;

            const wasAdded = Boolean(payload?.data?.is_favorite);
            const name = product?.name ? `"${product.name}"` : 'Producto';
            onToast?.(wasAdded ? `${name} agregado a favoritos` : `${name} eliminado de favoritos`);
        } catch {
            onToast?.('No se pudo actualizar tus favoritos');
        }
    }

    return {
        favoriteIds,
        favoritesCount,
        favoritesIsAuthenticated,
        loadFavoriteIds,
        isFavorite,
        toggleFavorite,
    };
}