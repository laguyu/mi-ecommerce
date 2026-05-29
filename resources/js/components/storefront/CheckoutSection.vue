<script setup>
import { computed, onMounted, reactive, ref } from 'vue';

const props = defineProps({
    cart: { type: Array, required: true },
    hasItems: { type: Boolean, required: true },
    siteSettings: { type: Object, default: () => ({}) },
    total: { type: Number, required: true },
    itemsCount: { type: Number, required: true },
    subtotal: { type: Number, required: true },
    productDiscountAmount: { type: Number, required: true },
    subtotalAfterDiscount: { type: Number, required: true },
    formatCurrency: { type: Function, required: true },
});

const emit = defineEmits(['clear-cart', 'toast', 'continue-shopping']);

const checkoutForm = reactive({
    fullName: '',
    email: '',
    address: '',
    city: '',
    postalCode: '',
});

const checkoutErrors = reactive({
    fullName: '',
    email: '',
    address: '',
    city: '',
    postalCode: '',
});

const paymentMethod = ref('stripe');
const shippingMethod = ref('delivery');
const checkoutApiError = ref('');
const placingOrder = ref(false);
const placedOrder = ref(null);
const couponCode = ref('');
const couponDiscountAmount = ref(0);
const couponMeta = ref(null);
const couponError = ref('');
const couponInfo = ref('');
const applyingCoupon = ref(false);

const paymentMethodModel = computed({
    get: () => paymentMethod.value,
    set: (value) => {
        paymentMethod.value = value;
    },
});

const shippingMethodModel = computed({
    get: () => shippingMethod.value,
    set: (value) => {
        shippingMethod.value = value;
    },
});

const deliveryFee = computed(() => {
    const value = Number(props.siteSettings?.delivery_fee);
    return Number.isFinite(value) ? Math.max(value, 0) : 7.99;
});

const freeShippingThreshold = computed(() => {
    const value = Number(props.siteSettings?.free_shipping_threshold);
    return Number.isFinite(value) ? Math.max(value, 0) : 120;
});

const checkoutSubtotalAfterAllDiscounts = computed(() => {
    return Math.max(props.subtotalAfterDiscount - couponDiscountAmount.value, 0);
});

const checkoutShippingAmount = computed(() => {
    if (!props.hasItems) return 0;
    if (shippingMethod.value === 'pickup') return 0;
    return checkoutSubtotalAfterAllDiscounts.value >= freeShippingThreshold.value ? 0 : deliveryFee.value;
});

const checkoutTotal = computed(() => {
    return checkoutSubtotalAfterAllDiscounts.value + checkoutShippingAmount.value;
});

const isOnlinePayment = computed(() => {
    return paymentMethod.value === 'stripe' || paymentMethod.value === 'paypal';
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

const hasBankTransferData = computed(() => {
    return bankTransferAccounts.value.length > 0;
});

const canCheckout = computed(() => {
    const requiresShippingAddress = shippingMethod.value === 'delivery';

    return (
        props.hasItems &&
        checkoutForm.fullName &&
        checkoutForm.email &&
        (!requiresShippingAddress || checkoutForm.address) &&
        (!requiresShippingAddress || checkoutForm.city) &&
        (!requiresShippingAddress || checkoutForm.postalCode)
    );
});

function getItemTotal(item) {
    return Number(item.price) * Number(item.quantity);
}

async function copyTransferValue(label, value) {
    const content = String(value || '').trim();

    if (content === '') {
        return;
    }

    try {
        if (navigator?.clipboard?.writeText) {
            await navigator.clipboard.writeText(content);
        } else {
            const textarea = document.createElement('textarea');
            textarea.value = content;
            textarea.setAttribute('readonly', 'readonly');
            textarea.style.position = 'absolute';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        }

        emit('toast', `${label} copiado`);
    } catch {
        emit('toast', `No se pudo copiar ${label.toLowerCase()}`);
    }
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function clearErrors() {
    Object.keys(checkoutErrors).forEach((key) => {
        checkoutErrors[key] = '';
    });

    checkoutApiError.value = '';
}

function validateCheckout() {
    clearErrors();
    const requiresShippingAddress = shippingMethod.value === 'delivery';

    if (!checkoutForm.fullName.trim()) checkoutErrors.fullName = 'Ingresa tu nombre completo';
    if (!checkoutForm.email.includes('@')) checkoutErrors.email = 'Correo invalido';
    if (requiresShippingAddress && !checkoutForm.address.trim()) checkoutErrors.address = 'Ingresa una direccion';
    if (requiresShippingAddress && !checkoutForm.city.trim()) checkoutErrors.city = 'Ingresa una ciudad';
    if (requiresShippingAddress && !checkoutForm.postalCode.trim()) checkoutErrors.postalCode = 'Ingresa codigo postal';

    return Object.values(checkoutErrors).every((value) => value === '');
}

async function applyCoupon() {
    const code = couponCode.value.trim();

    couponError.value = '';
    couponInfo.value = '';
    couponMeta.value = null;
    couponDiscountAmount.value = 0;

    if (code === '') {
        return;
    }

    applyingCoupon.value = true;

    try {
        const params = new URLSearchParams({
            code,
            subtotal: String(props.subtotalAfterDiscount),
        });

        const response = await fetch(`/api/checkout/coupon?${params.toString()}`, {
            headers: {
                Accept: 'application/json',
            },
        });

        const payload = await response.json();

        if (!response.ok || !payload.valid) {
            couponError.value = payload.message || 'No se pudo aplicar el cupon.';
            return;
        }

        couponMeta.value = payload.data;
        couponDiscountAmount.value = Number(payload.data?.discountAmount ?? 0);
        couponInfo.value = payload.message || 'Cupon aplicado correctamente.';
    } catch {
        couponError.value = 'No se pudo validar el cupon en este momento.';
    } finally {
        applyingCoupon.value = false;
    }
}

function clearCoupon() {
    couponCode.value = '';
    couponDiscountAmount.value = 0;
    couponMeta.value = null;
    couponError.value = '';
    couponInfo.value = '';
}

async function fetchOrderSummary(orderId) {
    const response = await fetch(`/api/orders/${orderId}/summary`);

    if (!response.ok) {
        throw new Error('No se pudo leer la orden pagada.');
    }

    const payload = await response.json();

    placedOrder.value = {
        number: payload.data.number,
        date: payload.data.date,
        total: payload.data.total,
        items: payload.data.items,
        lines: payload.data.lines ?? [],
        subtotal: payload.data.subtotal ?? 0,
        discountAmount: payload.data.discountAmount ?? 0,
        promotionDiscountAmount: payload.data.promotionDiscountAmount ?? 0,
        couponDiscountAmount: payload.data.couponDiscountAmount ?? 0,
        totalDiscountAmount: payload.data.totalDiscountAmount ?? (payload.data.discountAmount ?? 0),
        shippingAmount: payload.data.shippingAmount ?? 0,
        paymentMethod: payload.data.paymentMethod,
    };
}

async function handleCheckoutResultFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const checkoutStatus = params.get('checkout');
    const orderId = params.get('order');

    if (!checkoutStatus || !orderId) {
        return;
    }

    if (checkoutStatus === 'success') {
        try {
            await fetchOrderSummary(orderId);
            emit('clear-cart');
            emit('toast', 'Pago confirmado correctamente.');
        } catch {
            checkoutApiError.value = 'Pago aprobado, pero no se pudo recuperar el resumen.';
        }
    }

    if (checkoutStatus === 'failed') {
        checkoutApiError.value = 'El pago no se pudo confirmar. Intenta de nuevo.';
    }

    if (checkoutStatus === 'cancelled') {
        checkoutApiError.value = 'Pago cancelado por el usuario.';
    }

    window.history.replaceState({}, '', '/');
}

async function placeOrder() {
    if (!validateCheckout() || !props.hasItems) return;

    placingOrder.value = true;
    checkoutApiError.value = '';

    const payload = {
        customer: {
            fullName: checkoutForm.fullName,
            email: checkoutForm.email,
            address: checkoutForm.address,
            city: checkoutForm.city,
            postalCode: checkoutForm.postalCode,
        },
        paymentMethod: paymentMethod.value,
        shippingMethod: shippingMethod.value,
        couponCode: couponCode.value.trim(),
        items: props.cart.map((item) => ({
            id: item.id,
            quantity: item.quantity,
        })),
    };

    try {
        const prepareResponse = await fetch('/api/checkout/prepare', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                Accept: 'application/json',
            },
            body: JSON.stringify(payload),
        });

        const prepareData = await prepareResponse.json();

        if (!prepareResponse.ok) {
            checkoutApiError.value = prepareData.message || 'No se pudo preparar el checkout.';
            return;
        }

        const orderId = prepareData.order.id;

        if (!isOnlinePayment.value) {
            await fetchOrderSummary(orderId);
            emit('clear-cart');
            emit('toast', `Pedido registrado con ${paymentMethod.value}.`);
            clearCoupon();
            return;
        }

        const paymentEndpoint = paymentMethod.value === 'stripe'
            ? '/api/payments/stripe/checkout-session'
            : '/api/payments/paypal/order';

        const paymentResponse = await fetch(paymentEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                Accept: 'application/json',
            },
            body: JSON.stringify({ orderId }),
        });

        const paymentData = await paymentResponse.json();

        if (!paymentResponse.ok || !paymentData.redirectUrl) {
            checkoutApiError.value = paymentData.message || 'No se pudo iniciar el pago.';
            return;
        }

        window.location.href = paymentData.redirectUrl;
    } catch (error) {
        checkoutApiError.value = error.message || 'Error iniciando el pago.';
    } finally {
        placingOrder.value = false;
    }
}

function handleContinueShopping() {
    placedOrder.value = null;
    clearCoupon();
    emit('continue-shopping');
}

onMounted(() => {
    handleCheckoutResultFromUrl();
});
</script>

<template>
    <section class="panel">
        <h2>Checkout</h2>

        <div v-if="placedOrder" class="success success-order">
            <div class="success-order__header">
                <h3>Pedido confirmado</h3>
                <p>
                    Orden <strong>{{ placedOrder.number }}</strong> - {{ placedOrder.date }}
                </p>
                <p>Metodo: {{ placedOrder.paymentMethod?.toUpperCase() }}</p>
            </div>

            <div class="success-order__items">
                <article v-for="line in placedOrder.lines" :key="line.id" class="success-order__item">
                    <img
                        v-if="line.image"
                        :src="line.image"
                        :alt="line.name"
                        class="success-order__item-image"
                    />
                    <div v-else class="success-order__item-image success-order__item-image--placeholder">Sin imagen</div>

                    <div class="success-order__item-content">
                        <strong>{{ line.name }}</strong>
                        <small>{{ line.quantity }} x {{ formatCurrency(line.unitPrice) }}</small>
                    </div>

                    <strong class="success-order__item-total">{{ formatCurrency(line.lineTotal) }}</strong>
                </article>
            </div>

            <div class="success-order__totals">
                <p><span>Subtotal</span><strong>{{ formatCurrency(placedOrder.subtotal) }}</strong></p>
                <p><span>Descuento promociones</span><strong>-{{ formatCurrency(placedOrder.promotionDiscountAmount || 0) }}</strong></p>
                <p><span>Descuento cupon</span><strong>-{{ formatCurrency(placedOrder.couponDiscountAmount || 0) }}</strong></p>
                <p><span>Descuento total</span><strong>-{{ formatCurrency(placedOrder.totalDiscountAmount || 0) }}</strong></p>
                <p><span>Envio</span><strong>{{ formatCurrency(placedOrder.shippingAmount) }}</strong></p>
                <p class="success-order__grand-total"><span>Total</span><strong>{{ formatCurrency(placedOrder.total) }}</strong></p>
            </div>

            <button @click="handleContinueShopping">Seguir comprando</button>
        </div>

        <div v-else class="checkout-layout">
            <form class="checkout-form" @submit.prevent="placeOrder">
                <input v-model="checkoutForm.fullName" placeholder="Nombre completo" required />
                <small v-if="checkoutErrors.fullName" class="error">{{ checkoutErrors.fullName }}</small>

                <input v-model="checkoutForm.email" type="email" placeholder="Correo" required />
                <small v-if="checkoutErrors.email" class="error">{{ checkoutErrors.email }}</small>

                <fieldset class="payment-methods">
                    <legend>Metodo de envio</legend>
                    <label>
                        <input v-model="shippingMethodModel" type="radio" value="pickup" />
                        Pickup en tienda
                    </label>
                    <label>
                        <input v-model="shippingMethodModel" type="radio" value="delivery" />
                        Delivery a domicilio
                    </label>
                </fieldset>

                <input
                    v-model="checkoutForm.address"
                    placeholder="Direccion"
                    :required="shippingMethod === 'delivery'"
                    :disabled="shippingMethod === 'pickup'"
                />
                <small v-if="checkoutErrors.address" class="error">{{ checkoutErrors.address }}</small>

                <input
                    v-model="checkoutForm.city"
                    placeholder="Ciudad"
                    :required="shippingMethod === 'delivery'"
                    :disabled="shippingMethod === 'pickup'"
                />
                <small v-if="checkoutErrors.city" class="error">{{ checkoutErrors.city }}</small>

                <input
                    v-model="checkoutForm.postalCode"
                    placeholder="Codigo postal"
                    :required="shippingMethod === 'delivery'"
                    :disabled="shippingMethod === 'pickup'"
                />
                <small v-if="checkoutErrors.postalCode" class="error">{{ checkoutErrors.postalCode }}</small>

                <div style="display:grid; grid-template-columns: 1fr auto auto; gap:.45rem; align-items:center;">
                    <input v-model="couponCode" placeholder="Cupon (ej: BIENVENIDA10)" />
                    <button type="button" class="btn btn-outline" :disabled="applyingCoupon" @click="applyCoupon">
                        {{ applyingCoupon ? 'Validando...' : 'Aplicar' }}
                    </button>
                    <button type="button" class="btn btn-outline" @click="clearCoupon">Quitar</button>
                </div>
                <small v-if="couponInfo" style="color:#166534;">{{ couponInfo }}</small>
                <small v-if="couponError" class="error">{{ couponError }}</small>

                <fieldset class="payment-methods">
                    <legend>Metodo de pago</legend>
                    <label>
                        <input v-model="paymentMethodModel" type="radio" value="stripe" />
                        Stripe
                    </label>
                    <label>
                        <input v-model="paymentMethodModel" type="radio" value="paypal" />
                        PayPal
                    </label>
                    <label>
                        <input
                            v-model="paymentMethodModel"
                            type="radio"
                            value="transferencia"
                            :disabled="!hasBankTransferData"
                        />
                        {{ hasBankTransferData ? 'Transferencia bancaria' : 'Transferencia bancaria (no disponible)' }}
                    </label>
                    <label>
                        <input v-model="paymentMethodModel" type="radio" value="efectivo" />
                        Efectivo contra entrega
                    </label>
                </fieldset>

                <p class="muted" v-if="shippingMethod === 'pickup'">
                    Retiro en tienda seleccionado. No se aplica costo de envio.
                </p>

                <p class="muted" v-if="isOnlinePayment">
                    Seras redirigido a {{ paymentMethod === 'stripe' ? 'Stripe Checkout' : 'PayPal' }} para completar el pago.
                </p>
                <p class="muted" v-else>
                    Se registrara tu pedido y el pago quedara pendiente de confirmacion.
                </p>

                <div v-if="paymentMethod === 'transferencia'" class="transfer-bank-box">
                    <h4>Datos bancarios</h4>

                    <article v-for="(account, index) in bankTransferAccounts" :key="`transfer-account-${index}`" class="transfer-bank-grid">
                        <p><strong>Cuenta {{ index + 1 }}</strong></p>
                        <p v-if="account.bankName">
                            <span>Banco:</span>
                            <span class="transfer-copy-row">
                                <strong>{{ account.bankName }}</strong>
                                <button type="button" class="copy-inline" @click="copyTransferValue('Banco', account.bankName)">Copiar</button>
                            </span>
                        </p>
                        <p v-if="account.accountHolder">
                            <span>Titular:</span>
                            <span class="transfer-copy-row">
                                <strong>{{ account.accountHolder }}</strong>
                                <button type="button" class="copy-inline" @click="copyTransferValue('Titular', account.accountHolder)">Copiar</button>
                            </span>
                        </p>
                        <p v-if="account.accountNumber">
                            <span>Cuenta:</span>
                            <span class="transfer-copy-row">
                                <strong>{{ account.accountNumber }}</strong>
                                <button type="button" class="copy-inline" @click="copyTransferValue('Cuenta', account.accountNumber)">Copiar</button>
                            </span>
                        </p>
                        <p v-if="account.accountType">
                            <span>Tipo:</span>
                            <span class="transfer-copy-row">
                                <strong>{{ account.accountType }}</strong>
                                <button type="button" class="copy-inline" @click="copyTransferValue('Tipo de cuenta', account.accountType)">Copiar</button>
                            </span>
                        </p>
                        <p v-if="account.phones.length > 0">
                            <span>Telefonos (Pago movil):</span>
                            <span class="transfer-copy-row">
                                <strong>{{ account.phones.join(' / ') }}</strong>
                                <button type="button" class="copy-inline" @click="copyTransferValue('Telefonos', account.phones.join(' / '))">Copiar</button>
                            </span>
                        </p>
                        <p v-if="account.referenceNote" class="transfer-bank-note">{{ account.referenceNote }}</p>
                    </article>
                </div>

                <p v-if="checkoutApiError" class="error-block">{{ checkoutApiError }}</p>

                <button class="full" :disabled="!canCheckout || placingOrder">
                    {{ placingOrder ? (isOnlinePayment ? 'Redirigiendo...' : 'Procesando pedido...') : `Pagar ${formatCurrency(checkoutTotal)}` }}
                </button>
            </form>

            <aside class="resume">
                <h4>Resumen</h4>

                <div class="checkout-products" v-if="cart.length > 0">
                    <h5>Productos a comprar</h5>

                    <ul>
                        <li v-for="item in cart" :key="`checkout-item-${item.id}`">
                            <div class="checkout-product-main">
                                <img :src="item.image" :alt="item.name" class="checkout-product-image" />
                                <div>
                                    <strong>{{ item.name }}</strong>
                                    <small v-if="item.hasDiscount">
                                        {{ item.quantity }} x <span class="price-old">{{ formatCurrency(item.originalPrice) }}</span>
                                        <span> → </span>
                                        <span class="price-current">{{ formatCurrency(item.price) }}</span>
                                        (-{{ item.discountPercentage }}%)
                                    </small>
                                    <small v-else>{{ item.quantity }} x {{ formatCurrency(item.price) }}</small>
                                </div>
                            </div>
                            <strong>{{ formatCurrency(getItemTotal(item)) }}</strong>
                        </li>
                    </ul>
                </div>

                <p><span>Productos</span><strong>{{ itemsCount }}</strong></p>
                <p><span>Subtotal</span><strong>{{ formatCurrency(subtotal) }}</strong></p>
                <p><span>Descuento promociones</span><strong>-{{ formatCurrency(productDiscountAmount) }}</strong></p>
                <p><span>Descuento cupon</span><strong>-{{ formatCurrency(couponDiscountAmount) }}</strong></p>
                <p><span>Subtotal final</span><strong>{{ formatCurrency(checkoutSubtotalAfterAllDiscounts) }}</strong></p>
                <p>
                    <span>Envio</span>
                    <strong>{{ checkoutShippingAmount === 0 ? 'Gratis' : formatCurrency(checkoutShippingAmount) }}</strong>
                </p>
                <p class="total"><span>Total</span><strong>{{ formatCurrency(checkoutTotal) }}</strong></p>
            </aside>
        </div>
    </section>
</template>
