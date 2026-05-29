import { computed, ref, watch } from 'vue';

const STORAGE_KEY = 'portfolio-cart-v2';

function getInitialCart() {
    try {
        const raw = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');

        if (!Array.isArray(raw)) {
            return [];
        }

        return raw.map((item) => ({
            ...item,
            price: Number(item?.price ?? 0),
            originalPrice: Number(item?.originalPrice ?? item?.price ?? 0),
            hasDiscount: Boolean(item?.hasDiscount),
            discountPercentage: Number(item?.discountPercentage ?? 0),
            quantity: Number(item?.quantity ?? 1),
        }));
    } catch {
        return [];
    }
}

export function useCart() {
    const cart = ref(getInitialCart());

    const storefrontSettings = typeof window !== 'undefined' ? (window.siteSettings || {}) : {};
    const configuredDeliveryFee = Number(storefrontSettings?.delivery_fee);
    const configuredFreeShippingThreshold = Number(storefrontSettings?.free_shipping_threshold);

    const freeShippingThreshold = Number.isFinite(configuredFreeShippingThreshold) ? Math.max(configuredFreeShippingThreshold, 0) : 120;
    const deliveryFee = Number.isFinite(configuredDeliveryFee) ? Math.max(configuredDeliveryFee, 0) : 7.99;

    const itemsCount = computed(() => cart.value.reduce((sum, item) => sum + item.quantity, 0));

    const subtotal = computed(() => cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0));

    const productDiscountAmount = computed(() => cart.value.reduce((sum, item) => {
        const originalUnitPrice = Number(item.originalPrice ?? item.price);
        const finalUnitPrice = Number(item.price);
        const quantity = Number(item.quantity);

        if (!Number.isFinite(originalUnitPrice) || !Number.isFinite(finalUnitPrice) || !Number.isFinite(quantity)) {
            return sum;
        }

        const lineDiscount = Math.max(originalUnitPrice - finalUnitPrice, 0) * quantity;
        return sum + lineDiscount;
    }, 0));

    const totalDiscountAmount = computed(() => productDiscountAmount.value);
    const subtotalAfterDiscount = computed(() => subtotal.value);

    const shippingAmount = computed(() => {
        if (itemsCount.value === 0) return 0;
        return subtotalAfterDiscount.value >= freeShippingThreshold ? 0 : deliveryFee;
    });

    const total = computed(() => subtotalAfterDiscount.value);

    const hasItems = computed(() => cart.value.length > 0);

    function findItem(productId) {
        return cart.value.find((item) => item.id === productId);
    }

    function getMaxStock(itemOrProduct) {
        const stock = Number(itemOrProduct?.stock);

        if (!Number.isFinite(stock)) {
            return Number.POSITIVE_INFINITY;
        }

        return Math.max(0, stock);
    }

    function addItem(product) {
        const maxStock = getMaxStock(product);

        if (maxStock <= 0) {
            return false;
        }

        const exists = findItem(product.id);

        if (exists) {
            if (exists.quantity >= getMaxStock(exists)) {
                return false;
            }

            exists.price = Number(product.price);
            exists.originalPrice = Number(product.original_price ?? product.price);
            exists.hasDiscount = Boolean(product.has_discount);
            exists.discountPercentage = Number(product.discount_percentage ?? 0);
            exists.quantity += 1;
            return true;
        }

        cart.value.push({
            id: product.id,
            sku: product.sku,
            name: product.name,
            price: product.price,
            originalPrice: Number(product.original_price ?? product.price),
            hasDiscount: Boolean(product.has_discount),
            discountPercentage: Number(product.discount_percentage ?? 0),
            image: product.image,
            stock: maxStock,
            quantity: 1,
        });

        return true;
    }

    function setQuantity(productId, quantity) {
        const item = findItem(productId);
        if (!item) return;

        if (quantity <= 0) {
            removeItem(productId);
            return;
        }

        const maxStock = getMaxStock(item);
        item.quantity = Math.min(quantity, maxStock);
    }

    function increase(productId) {
        const item = findItem(productId);
        if (!item) return;

        if (item.quantity >= getMaxStock(item)) {
            return;
        }

        item.quantity += 1;
    }

    function decrease(productId) {
        const item = findItem(productId);
        if (!item) return;

        if (item.quantity <= 1) {
            removeItem(productId);
            return;
        }

        item.quantity -= 1;
    }

    function removeItem(productId) {
        cart.value = cart.value.filter((item) => item.id !== productId);
    }

    function clearCart() {
        cart.value = [];
    }

    watch(
        cart,
        (value) => {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(value));
        },
        { deep: true }
    );

    return {
        cart,
        itemsCount,
        subtotal,
        productDiscountAmount,
        totalDiscountAmount,
        subtotalAfterDiscount,
        shippingAmount,
        total,
        hasItems,
        addItem,
        setQuantity,
        increase,
        decrease,
        removeItem,
        clearCart,
    };
}
