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
    <section class="panel contact-panel">
        <header class="contact-header">
            <span class="contact-header__eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                </svg>
                Atención al cliente
            </span>
            <h2>Estamos aquí para ayudarte</h2>
            <p>Cuéntanos qué necesitas. Nuestro equipo te responderá lo antes posible.</p>
        </header>

        <div class="contact-layout">
            <form class="checkout-form contact-form" @submit.prevent="submitContact">
                <div class="contact-form__heading">
                    <h3>Envíanos un mensaje</h3>
                    <p>Completa tus datos y nos pondremos en contacto contigo.</p>
                </div>

                <div class="contact-fields">
                    <label class="contact-field">
                        Nombre completo
                        <input v-model="form.name" type="text" placeholder="Tu nombre" autocomplete="name" required>
                    </label>
                    <label class="contact-field">
                        Correo electrónico
                        <input v-model="form.email" type="email" placeholder="tu@correo.com" autocomplete="email" required>
                    </label>
                    <label class="contact-field">
                        Teléfono <span>(opcional)</span>
                        <input v-model="form.phone" type="text" placeholder="Tu teléfono" autocomplete="tel">
                    </label>
                    <label class="contact-field">
                        Asunto
                        <input v-model="form.subject" type="text" placeholder="¿Sobre qué quieres consultar?" required>
                    </label>
                    <label class="contact-field contact-field--full">
                        Mensaje
                        <textarea v-model="form.message" rows="6" placeholder="Cuéntanos en qué podemos ayudarte..." required></textarea>
                    </label>
                </div>

                <button class="full contact-submit" type="submit" :disabled="loading">
                    <span>{{ loading ? 'Enviando...' : 'Enviar mensaje' }}</span>
                    <svg v-if="!loading" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="w-4 h-4">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>

                <p v-if="successMessage" class="contact-feedback contact-feedback--success">{{ successMessage }}</p>
                <p v-if="errorMessage" class="error-block">{{ errorMessage }}</p>
            </form>

            <aside class="contact-info">
                <div class="contact-info__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 3.18 2 2 0 0 1 4.11 1h3a2 2 0 0 1 2 1.72c.12.96.35 1.9.68 2.8a2 2 0 0 1-.45 2.11L8.07 8.93a16 16 0 0 0 6 6l1.3-1.27a2 2 0 0 1 2.11-.45c.9.33 1.84.56 2.8.68A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                </div>
                <p class="contact-info__eyebrow">Estamos para escucharte</p>
                <h3>Información de contacto</h3>
                <p class="contact-info__intro">Elige el canal que prefieras o déjanos tus datos en el formulario.</p>

                <div class="contact-info__items">
                    <div v-if="props.siteSettings.footer_phone" class="contact-info__item">
                        <span class="contact-info__item-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 3.18 2 2 0 0 1 4.11 1h3a2 2 0 0 1 2 1.72c.12.96.35 1.9.68 2.8a2 2 0 0 1-.45 2.11L8.07 8.93a16 16 0 0 0 6 6l1.3-1.27a2 2 0 0 1 2.11-.45c.9.33 1.84.56 2.8.68A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                        </span>
                        <span><small>Teléfono</small><strong>{{ props.siteSettings.footer_phone }}</strong></span>
                    </div>
                    <div v-if="props.siteSettings.footer_email" class="contact-info__item">
                        <span class="contact-info__item-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </span>
                        <span><small>Correo electrónico</small><strong>{{ props.siteSettings.footer_email }}</strong></span>
                    </div>
                    <div v-if="props.siteSettings.footer_address" class="contact-info__item">
                        <span class="contact-info__item-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"></path>
                                <circle cx="12" cy="10" r="2.5"></circle>
                            </svg>
                        </span>
                        <span><small>Dirección</small><strong>{{ props.siteSettings.footer_address }}</strong></span>
                    </div>
                    <p v-if="!props.siteSettings.footer_phone && !props.siteSettings.footer_email && !props.siteSettings.footer_address" class="contact-info__empty">
                        Los datos de contacto estarán disponibles próximamente. Mientras tanto, envíanos un mensaje.
                    </p>
                </div>

                <div class="contact-info__note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span>Te responderemos tan pronto como sea posible.</span>
                </div>
            </aside>
        </div>
    </section>
</template>
