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
    <section class="panel">
        <h2>Tu carrito</h2>

        <div v-if="cart.length === 0" class="favorites-banner cart-empty-banner">
            <p>Tu carrito esta vacio. Agrega productos desde el catalogo.</p>
        </div>

        <div v-else>
            <article v-for="item in cart" :key="item.id" class="cart-item">
                <img :src="item.image" :alt="item.name" />

                <div class="item-info">
                    <h4>{{ item.name }}</h4>
                    <p class="muted" v-if="item.hasDiscount">
                        <span class="price-old">{{ formatCurrency(item.originalPrice) }}</span>
                        <span> → </span>
                        <strong class="price-current">{{ formatCurrency(item.price) }}</strong>
                        <small> (-{{ item.discountPercentage }}%)</small>
                    </p>
                    <p class="muted" v-else>{{ formatCurrency(item.price) }} c/u</p>
                </div>

                <div class="qty">
                    <button @click="emit('decrease', item.id)">-</button>
                    <span>{{ item.quantity }}</span>
                    <button @click="emit('increase', item.id)">+</button>
                </div>

                <strong>{{ formatCurrency(item.price * item.quantity) }}</strong>
                <button class="danger" @click="emit('remove-item', item.id)">Quitar</button>
            </article>

            <div class="resume">
                <p><span>Subtotal</span><strong>{{ formatCurrency(subtotal) }}</strong></p>
                <p><span>Descuento promociones</span><strong>-{{ formatCurrency(productDiscountAmount) }}</strong></p>
                <p><span>Subtotal final</span><strong>{{ formatCurrency(subtotalAfterDiscount) }}</strong></p>
                <p class="total"><span>Total (sin envio)</span><strong>{{ formatCurrency(total) }}</strong></p>
                <button class="full" @click="emit('go-checkout')">Ir a checkout</button>
            </div>
        </div>
    </section>
</template>
