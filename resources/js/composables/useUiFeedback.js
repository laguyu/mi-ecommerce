import { ref } from 'vue';

export function useUiFeedback() {
    const cartPreviewOpen = ref(false);
    const toasts = ref([]);

    function openCartPreview() {
        cartPreviewOpen.value = true;
    }

    function closeCartPreview() {
        cartPreviewOpen.value = false;
    }

    function pushToast(message) {
        const id = Date.now() + Math.random();
        toasts.value.push({ id, message });

        setTimeout(() => {
            toasts.value = toasts.value.filter((toast) => toast.id !== id);
        }, 2800);
    }

    return {
        cartPreviewOpen,
        toasts,
        openCartPreview,
        closeCartPreview,
        pushToast,
    };
}