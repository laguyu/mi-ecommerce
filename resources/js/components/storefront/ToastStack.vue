<script setup>
defineProps({
    toasts: { type: Array, required: true },
});
</script>

<template>
    <section class="toast-stack" v-if="toasts.length > 0">
        <article v-for="toast in toasts" :key="toast.id" class="toast">
            {{ toast.message }}
        </article>
    </section>
</template>
