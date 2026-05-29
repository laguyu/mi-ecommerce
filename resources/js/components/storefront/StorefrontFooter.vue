<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    siteSettings: { type: Object, default: () => ({}) },
});

const bankTransferAccounts = computed(() => {
    const settings = props.siteSettings || {};
    const rawAccounts = Array.isArray(settings.bank_accounts) ? settings.bank_accounts : [];

    const normalizedAccounts = rawAccounts
        .map((account) => {
            const bankName = String(account?.bank_name || '').trim();
            const accountHolder = String(account?.account_holder || '').trim();
            const accountNumber = String(account?.account_number || '').trim();
            const accountType = String(account?.account_type || '').trim();
            const phonesRaw = String(account?.phones || '').trim();
            const referenceNote = String(account?.reference_note || '').trim();

            return {
                bankName,
                accountHolder,
                accountNumber,
                accountType,
                phones: phonesRaw
                    .split(',')
                    .map((phone) => phone.trim())
                    .filter(Boolean),
                referenceNote,
            };
        })
        .filter((account) => {
            return Boolean(
                account.bankName ||
                account.accountHolder ||
                account.accountNumber ||
                account.accountType ||
                account.phones.length > 0 ||
                account.referenceNote
            );
        });

    if (normalizedAccounts.length > 0) {
        return normalizedAccounts;
    }

    const legacyBankName = String(settings.bank_name || '').trim();
    const legacyAccountHolder = String(settings.bank_account_holder || '').trim();
    const legacyAccountNumber = String(settings.bank_account_number || '').trim();
    const legacyAccountType = String(settings.bank_account_type || '').trim();
    const legacyPhone = String(settings.bank_phone || '').trim();
    const legacyReferenceNote = String(settings.bank_reference_note || '').trim();

    if (
        !legacyBankName &&
        !legacyAccountHolder &&
        !legacyAccountNumber &&
        !legacyAccountType &&
        !legacyPhone &&
        !legacyReferenceNote
    ) {
        return [];
    }

    return [{
        bankName: legacyBankName,
        accountHolder: legacyAccountHolder,
        accountNumber: legacyAccountNumber,
        accountType: legacyAccountType,
        phones: legacyPhone ? [legacyPhone] : [],
        referenceNote: legacyReferenceNote,
    }];
});

const newsletterEmail = ref('');
const newsletterLoading = ref(false);
const newsletterMessage = ref('');
const newsletterError = ref('');

async function subscribeNewsletter() {
    newsletterMessage.value = '';
    newsletterError.value = '';

    const email = newsletterEmail.value.trim();
    if (!email) {
        newsletterError.value = 'Ingresa un email valido.';
        return;
    }

    newsletterLoading.value = true;

    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const response = await fetch('/api/newsletter/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ email }),
        });

        const payload = await response.json();

        if (!response.ok) {
            const backendMessage = payload?.message || payload?.errors?.email?.[0];
            throw new Error(backendMessage || 'No se pudo completar la suscripcion.');
        }

        newsletterMessage.value = payload?.message || 'Suscripcion exitosa.';
        newsletterEmail.value = '';
    } catch (error) {
        newsletterError.value = error.message || 'No se pudo completar la suscripcion.';
    } finally {
        newsletterLoading.value = false;
    }
}
</script>

<template>
    <footer class="site-footer">
        <div class="site-footer__brand">
            <img v-if="siteSettings.logo_url" :src="siteSettings.logo_url" :alt="siteSettings.site_name" class="site-footer__logo" />
            <div>
                <p class="site-footer__eyebrow">Ecommerce oficial</p>
                <h3>{{ siteSettings.site_name || 'Nova Shop' }}</h3>
                <p class="site-footer__tagline">{{ siteSettings.site_tagline || 'Home con carrusel, catalogo, ficha de producto y checkout con Stripe/PayPal.' }}</p>
            </div>
        </div>

        <div class="site-footer__grid">
            <section v-if="siteSettings.footer_address" class="site-footer__panel">
                <h4>Direccion</h4>
                <p class="site-footer__value">{{ siteSettings.footer_address }}</p>
            </section>

            <section v-if="siteSettings.footer_phone || siteSettings.footer_email" class="site-footer__panel">
                <h4>Contacto</h4>
                <div class="site-footer__contact-list">
                    <p v-if="siteSettings.footer_phone" class="site-footer__value">Teléfono: {{ siteSettings.footer_phone }}</p>
                    <p v-if="siteSettings.footer_email" class="site-footer__value">Email: {{ siteSettings.footer_email }}</p>
                </div>
            </section>

            <section v-if="bankTransferAccounts.length > 0" class="site-footer__panel">
                <h4>Transferencias</h4>
                <div class="site-footer__bank-list">
                    <article v-for="(account, index) in bankTransferAccounts" :key="`${account.bankName}-${account.accountNumber}-${index}`" class="site-footer__bank-item">
                        <p v-if="account.bankName" class="site-footer__value"><strong>{{ account.bankName }}</strong></p>
                        <p v-if="account.accountHolder" class="site-footer__value">Titular: {{ account.accountHolder }}</p>
                        <p v-if="account.accountNumber" class="site-footer__value">Cuenta: {{ account.accountNumber }}</p>
                        <p v-if="account.accountType" class="site-footer__value">Tipo: {{ account.accountType }}</p>
                        <p v-if="account.phones.length > 0" class="site-footer__value">Telefono(s): {{ account.phones.join(', ') }}</p>
                        <p v-if="account.referenceNote" class="site-footer__value">{{ account.referenceNote }}</p>
                    </article>
                </div>
            </section>

            <section v-if="siteSettings.footer_facebook_url || siteSettings.footer_instagram_url || siteSettings.footer_x_url || siteSettings.footer_whatsapp_url" class="site-footer__panel">
                <h4>Redes</h4>
                <div class="site-footer__links">
                    <a v-if="siteSettings.footer_facebook_url" :href="siteSettings.footer_facebook_url" target="_blank" rel="noopener">Facebook</a>
                    <a v-if="siteSettings.footer_instagram_url" :href="siteSettings.footer_instagram_url" target="_blank" rel="noopener">Instagram</a>
                    <a v-if="siteSettings.footer_x_url" :href="siteSettings.footer_x_url" target="_blank" rel="noopener">X</a>
                    <a v-if="siteSettings.footer_whatsapp_url" :href="siteSettings.footer_whatsapp_url" target="_blank" rel="noopener">WhatsApp</a>
                </div>
            </section>

            <section class="site-footer__panel">
                <h4>Newsletter</h4>
                <p class="site-footer__newsletter-intro">Recibe novedades, lanzamientos y promociones especiales.</p>
                <form class="site-footer__newsletter" @submit.prevent="subscribeNewsletter">
                    <label class="site-footer__newsletter-field" for="newsletter-email">Correo electronico</label>
                    <div class="site-footer__newsletter-row">
                        <input
                            id="newsletter-email"
                            v-model="newsletterEmail"
                            type="email"
                            placeholder="tuemail@dominio.com"
                            autocomplete="email"
                            required
                        >
                        <button class="site-footer__newsletter-submit" type="submit" :disabled="newsletterLoading">
                        {{ newsletterLoading ? 'Enviando...' : 'Suscribirme' }}
                        </button>
                    </div>
                </form>
                <p v-if="newsletterMessage" class="site-footer__newsletter-success">{{ newsletterMessage }}</p>
                <p v-if="newsletterError" class="site-footer__newsletter-error">{{ newsletterError }}</p>
            </section>

            <section class="site-footer__panel">
                <h4>Legal</h4>
                <div class="site-footer__links">
                    <a href="/politicas/privacidad">Políticas de Privacidad</a>
                    <a href="/politicas/terminos">Términos del servicio</a>
                    <a href="/politicas/envios">Políticas de envíos</a>
                    <a href="/politicas/reembolsos">Políticas de cambios y reembolsos</a>
                </div>
            </section>
        </div>

        <div v-if="siteSettings.footer_note" class="site-footer__bottom">
            <p class="site-footer__note">{{ siteSettings.footer_note }}</p>
            <span class="site-footer__copyright">{{ siteSettings.site_name || 'Nova Shop' }} · Todos los derechos reservados</span>
        </div>
    </footer>
</template>
