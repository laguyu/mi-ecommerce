<script setup>
defineProps({
    cart: { type: Array, required: true },
    subtotal: { type: Number, required: true },
    productDiscountAmount: { type: Number, required: true },
    subtotalAfterDiscount: { type: Number, required: true },
    total: { type: Number, required: true },
    formatCurrency: { type: Function, required: true },
});

const emit = defineEmits(['decrease', 'increase', 'remove-item', 'go-checkout']);
</script>

<template>
    <section class="panel cart-page">
        <header class="cart-page__header">
            <div>
                <h2>Tu carrito de compras</h2>
                <p class="muted" v-if="cart.length > 0">Tienes {{ cart.reduce((sum, i) => sum + i.quantity, 0) }} artículos en tu pedido.</p>
            </div>
        </header>

        <div v-if="cart.length === 0" class="cart-empty-state">
            <svg class="cart-empty-state__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            <h3>Tu carrito está vacío</h3>
            <p class="muted">Explora nuestras categorías y agrega lo que más te guste para realizar tu pedido.</p>
        </div>

        <div v-else class="cart-page__layout">
            <div class="cart-page__items">
                <article v-for="item in cart" :key="item.id" class="cart-item">
                    <img :src="item.image" :alt="item.name" />

                    <div class="item-info">
                        <h4>{{ item.name }}</h4>
                        <div class="item-pricing">
                            <template v-if="item.hasDiscount">
                                <span class="price-old">{{ formatCurrency(item.originalPrice) }}</span>
                                <strong class="price-current">{{ formatCurrency(item.price) }}</strong>
                                <span class="discount-badge discount-badge--inline">-{{ item.discountPercentage }}%</span>
                            </template>
                            <span v-else class="muted">{{ formatCurrency(item.price) }} c/u</span>
                        </div>
                    </div>

                    <div class="qty" aria-label="Cantidad">
                        <button type="button" @click="emit('decrease', item.id)" :aria-label="`Disminuir cantidad de ${item.name}`">−</button>
                        <span>{{ item.quantity }}</span>
                        <button type="button" @click="emit('increase', item.id)" :aria-label="`Aumentar cantidad de ${item.name}`">+</button>
                    </div>

                    <div class="cart-item__subtotal">
                        <strong>{{ formatCurrency(item.price * item.quantity) }}</strong>
                    </div>

                    <button type="button" class="cart-item__remove" @click="emit('remove-item', item.id)" :aria-label="`Eliminar ${item.name}`" title="Eliminar">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:17px;height:17px;" aria-hidden="true">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </article>
            </div>

            <aside class="resume cart-page__resume">
                <h3>Resumen del pedido</h3>
                <div class="resume-lines">
                    <p><span>Subtotal</span><strong>{{ formatCurrency(subtotal) }}</strong></p>
                    <p v-if="productDiscountAmount > 0" class="resume-discount">
                        <span>Descuentos aplicados</span>
                        <strong class="text-emerald">-{{ formatCurrency(productDiscountAmount) }}</strong>
                    </p>
                    <p><span>Subtotal neto</span><strong>{{ formatCurrency(subtotalAfterDiscount) }}</strong></p>
                    <p class="total"><span>Total estimado</span><strong>{{ formatCurrency(total) }}</strong></p>
                </div>
                <button class="full cart-checkout-btn" @click="emit('go-checkout')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;" aria-hidden="true">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <span>Proceder al Checkout</span>
                </button>
                <div class="cart-page__security-notice">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;" aria-hidden="true">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    <span>Compra 100% protegida y encriptada</span>
                </div>
            </aside>
        </div>
    </section>
</template>
