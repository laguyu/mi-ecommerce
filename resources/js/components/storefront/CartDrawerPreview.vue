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
                        <p class="cart-drawer__eyebrow">Carrito</p>
                        <h2>Productos en tu carrito</h2>
                    </div>

                    <button type="button" class="cart-drawer__close" @click="emit('close')">x</button>
                </header>

                <div v-if="hasItems" class="cart-drawer__body">
                    <div class="cart-drawer__items">
                        <article v-for="item in items" :key="item.id" class="cart-drawer__item">
                            <img v-if="item.image" :src="item.image" :alt="item.name" class="cart-drawer__item-image" />

                            <div class="cart-drawer__item-content">
                                <strong>{{ item.name }}</strong>
                                <p>{{ item.quantity }} x {{ formatCurrency(item.price) }}</p>
                            </div>

                            <div class="cart-drawer__item-total">
                                {{ formatCurrency(item.price * item.quantity) }}
                            </div>
                        </article>

                        <p v-if="remainingItems > 0" class="cart-drawer__more-items">
                            y {{ remainingItems }} producto(s) mas
                        </p>
                    </div>

                    <div class="cart-drawer__summary">
                        <div>
                            <span>Subtotal</span>
                            <strong>{{ formatCurrency(subtotal) }}</strong>
                        </div>

                        <div>
                            <span>Total</span>
                            <strong>{{ formatCurrency(total) }}</strong>
                        </div>
                    </div>

                    <button type="button" class="cart-drawer__action" @click="emit('go-cart')">
                        Ver carrito
                    </button>
                </div>

                <div v-else class="cart-drawer__empty">
                    <p>No hay productos en el carrito.</p>
                    <button type="button" class="cart-drawer__action" @click="emit('go-cart')">
                        Ver carrito
                    </button>
                </div>
            </aside>
        </div>
    </transition>
</template>