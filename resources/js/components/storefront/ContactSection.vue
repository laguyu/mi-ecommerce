<script setup>
import { reactive, ref } from 'vue';

const props = defineProps({
    siteSettings: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['toast']);

const form = reactive({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
});

const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function clearMessages() {
    successMessage.value = '';
    errorMessage.value = '';
}

async function submitContact() {
    clearMessages();
    loading.value = true;

    try {
        const response = await fetch('/api/contact-messages', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify(form),
        });

        const payload = await response.json();

        if (!response.ok) {
            const firstError = payload?.errors ? Object.values(payload.errors)[0]?.[0] : null;
            throw new Error(payload?.message || firstError || 'No se pudo enviar el mensaje.');
        }

        successMessage.value = payload?.message || 'Tu mensaje fue enviado correctamente.';
        emit('toast', successMessage.value);

        form.name = '';
        form.email = '';
        form.phone = '';
        form.subject = '';
        form.message = '';
    } catch (error) {
        errorMessage.value = error.message || 'No se pudo enviar el mensaje.';
        emit('toast', errorMessage.value);
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <section class="panel">
        <h2>Contacto</h2>
        <p class="muted">Cuéntanos lo que necesitas y te responderemos lo antes posible.</p>

        <div class="checkout-layout" style="margin-top: 1rem;">
            <form class="checkout-form" @submit.prevent="submitContact">
                <input v-model="form.name" type="text" placeholder="Tu nombre" required>
                <input v-model="form.email" type="email" placeholder="Tu correo" required>
                <input v-model="form.phone" type="text" placeholder="Tu teléfono (opcional)">
                <input v-model="form.subject" type="text" placeholder="Asunto" required>
                <textarea v-model="form.message" rows="6" placeholder="Cuéntanos en qué podemos ayudarte" required></textarea>

                <button class="full" type="submit" :disabled="loading">
                    {{ loading ? 'Enviando...' : 'Enviar mensaje' }}
                </button>

                <p v-if="successMessage" class="success" style="text-align:left;">{{ successMessage }}</p>
                <p v-if="errorMessage" class="error-block">{{ errorMessage }}</p>
            </form>

            <aside class="resume">
                <h4>Canales de atención</h4>
                <p><strong>Teléfono:</strong> {{ props.siteSettings.footer_phone || 'No configurado' }}</p>
                <p><strong>Dirección:</strong> {{ props.siteSettings.footer_address || 'No configurada' }}</p>
            </aside>
        </div>
    </section>
</template>