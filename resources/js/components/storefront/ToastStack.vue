<script setup>
defineProps({
    toasts: { type: Array, required: true },
});
</script>

<template>
    <section class="toast-stack" v-if="toasts.length > 0" aria-live="polite" aria-atomic="true">
        <article v-for="toast in toasts" :key="toast.id" class="toast">
            <span class="toast__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </span>
            <span class="toast__message">{{ toast.message }}</span>
        </article>
    </section>
</template>
