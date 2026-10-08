<script setup>
defineProps({
    open: { type: Boolean, default: false },
    hasItems: { type: Boolean, default: false },
    items: { type: Array, default: () => [] },
    remainingItems: { type: Number, default: 0 },
    subtotal: { type: Number, default: 0 },
    total: { type: Number, default: 0 },
    formatCurrency: { type: Function, required: true },
});

const emit = defineEmits(['close', 'go-cart']);
</script>

<template>
    <transition name="cart-drawer">
        <div v-if="open" class="cart-drawer__backdrop" @click.self="emit('close')">
            <aside class="cart-drawer" aria-label="Vista previa del carrito">
                <header class="cart-drawer__header">
                    <div>
                        <p class="cart-drawer__eyebrow">Mi Pedido</p>
                        <h2>Carrito de compras</h2>
                    </div>

                    <button type="button" class="cart-drawer__close" @click="emit('close')" aria-label="Cerrar carrito">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;" aria-hidden="true">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </header>

                <div v-if="hasItems" class="cart-drawer__body">
                    <div class="cart-drawer__items">
                        <article v-for="item in items" :key="item.id" class="cart-drawer__item">
                            <img v-if="item.image" :src="item.image" :alt="item.name" class="cart-drawer__item-image" />

                            <div class="cart-drawer__item-content">
                                <strong>{{ item.name }}</strong>
                                <p>{{ item.quantity }} × {{ formatCurrency(item.price) }}</p>
                            </div>

                            <div class="cart-drawer__item-total">
                                {{ formatCurrency(item.price * item.quantity) }}
                            </div>
                        </article>

                        <p v-if="remainingItems > 0" class="cart-drawer__more-items">
                            + {{ remainingItems }} producto(s) adicionales
                        </p>
                    </div>

                    <div class="cart-drawer__summary">
                        <div>
                            <span>Subtotal</span>
                            <strong>{{ formatCurrency(subtotal) }}</strong>
                        </div>

                        <div>
                            <span>Total estimado</span>
                            <strong>{{ formatCurrency(total) }}</strong>
                        </div>
                    </div>

                    <button type="button" class="cart-drawer__action" @click="emit('go-cart')">
                        Ir al Carrito y Checkout →
                    </button>
                </div>

                <div v-else class="cart-drawer__empty">
                    <svg class="cart-drawer__empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <p>Tu carrito está vacío actualmente.</p>
                    <small>Explora nuestro catálogo para encontrar tus productos favoritos.</small>
                    <button type="button" class="cart-drawer__action" @click="emit('close')">
                        Explorar catálogo
                    </button>
                </div>
            </aside>
        </div>
    </transition>
</template>
