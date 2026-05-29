<style>
    .site-settings-form {
        max-width: 980px;
        display: grid;
        gap: 1rem;
    }

    .settings-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #ffffff;
        padding: 1rem;
        display: grid;
        gap: 0.8rem;
    }

    .settings-card h3 {
        margin: 0;
        font-size: 1.02rem;
    }

    .settings-card p {
        margin: 0;
        color: #64748b;
        font-size: 0.9rem;
    }

    .settings-grid-2 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 0.7rem;
    }

    .settings-grid-colors {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 0.65rem;
    }

    .settings-logo-grid {
        display: grid;
        grid-template-columns: minmax(240px, 1fr) minmax(220px, 280px);
        gap: 0.8rem;
        align-items: start;
    }

    .settings-logo-preview {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
        padding: 0.5rem;
    }

    .bank-settings-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 1rem;
        background: #f8fafc;
        display: grid;
        gap: 0.75rem;
    }

    .bank-settings-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    .bank-account-item {
        border: 1px solid #dbeafe;
        border-radius: 10px;
        padding: 0.8rem;
        background: #fff;
    }

    .bank-account-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 0.55rem;
    }

    .bank-account-fields {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0.6rem;
    }

    .bank-account-fields .full-row {
        grid-column: 1 / -1;
    }

    .legal-editor-help {
        color: #64748b;
        font-size: 0.84rem;
    }

    .legal-editor {
        border: 1px solid #d1d5db;
        border-radius: 10px;
        background: #fff;
        min-height: 260px;
        overflow: hidden;
    }

    .legal-editor-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        padding: 0.45rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f8fafc;
    }

    .legal-editor-toolbar button {
        border: 1px solid #d1d5db;
        background: #fff;
        color: #0f172a;
        border-radius: 8px;
        padding: 0.3rem 0.5rem;
        font-size: 0.82rem;
        line-height: 1.1;
        cursor: pointer;
    }

    .legal-editor-surface {
        min-height: 260px;
        padding: 0.7rem 0.8rem;
        font-size: 0.95rem;
        line-height: 1.6;
        outline: none;
        overflow-y: auto;
    }

    .legal-editor-surface:empty:before {
        content: attr(data-placeholder);
        color: #94a3b8;
        pointer-events: none;
    }

    .legal-editor-surface h2,
    .legal-editor-surface h3,
    .legal-editor-surface h4 {
        margin: 0.8rem 0 0.45rem;
        color: #0f172a;
    }

    .legal-editor-surface p,
    .legal-editor-surface ul,
    .legal-editor-surface ol,
    .legal-editor-surface blockquote {
        margin: 0.45rem 0;
    }

    .legal-editor-surface ul,
    .legal-editor-surface ol {
        padding-left: 1.2rem;
    }

    .legal-editor-surface blockquote {
        border-left: 3px solid #93c5fd;
        padding-left: 0.65rem;
        color: #334155;
        background: #f8fafc;
        border-radius: 0.35rem;
    }

    @media (max-width: 860px) {
        .settings-logo-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<form method="POST" action="{{ $action }}" class="site-settings-form" enctype="multipart/form-data">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <section class="settings-card">
        <h3>Identidad Del Sitio</h3>
        <p>Nombre y textos principales visibles en la tienda.</p>

        <div class="settings-grid-2">
            <label>
                Nombre del sitio
                <input class="input" type="text" name="site_name" value="{{ old('site_name', $siteSetting?->site_name) }}" required>
                @error('site_name') <div class="error">{{ $message }}</div> @enderror
            </label>

            <label>
                Texto superior del encabezado
                <input class="input" type="text" name="site_eyebrow" value="{{ old('site_eyebrow', $siteSetting?->site_eyebrow ?? 'Laravel + Vue Ecommerce') }}" required>
                @error('site_eyebrow') <div class="error">{{ $message }}</div> @enderror
            </label>
        </div>

        <label>
            Subtitulo principal
            <input class="input" type="text" name="site_tagline" value="{{ old('site_tagline', $siteSetting?->site_tagline) }}" required>
            @error('site_tagline') <div class="error">{{ $message }}</div> @enderror
        </label>
    </section>

    <section class="settings-card">
        <h3>Apariencia</h3>
        <p>Define los colores del menu, botones y footer.</p>

        <div class="settings-grid-colors">
            <label>
                Color menu fondo
                <input class="input" type="color" name="menu_background_color" value="{{ old('menu_background_color', $siteSetting?->menu_background_color ?? '#ffffff') }}">
            </label>

            <label>
                Color menu texto
                <input class="input" type="color" name="menu_text_color" value="{{ old('menu_text_color', $siteSetting?->menu_text_color ?? '#111827') }}">
            </label>

            <label>
                Color menu activo
                <input class="input" type="color" name="menu_active_background_color" value="{{ old('menu_active_background_color', $siteSetting?->menu_active_background_color ?? '#111827') }}">
            </label>

            <label>
                Texto menu activo
                <input class="input" type="color" name="menu_active_text_color" value="{{ old('menu_active_text_color', $siteSetting?->menu_active_text_color ?? '#ffffff') }}">
            </label>

            <label>
                Boton fondo
                <input class="input" type="color" name="button_background_color" value="{{ old('button_background_color', $siteSetting?->button_background_color ?? '#111827') }}">
            </label>

            <label>
                Boton texto
                <input class="input" type="color" name="button_text_color" value="{{ old('button_text_color', $siteSetting?->button_text_color ?? '#ffffff') }}">
            </label>

            <label>
                Footer fondo
                <input class="input" type="color" name="footer_background_color" value="{{ old('footer_background_color', $siteSetting?->footer_background_color ?? '#111827') }}">
            </label>

            <label>
                Footer texto
                <input class="input" type="color" name="footer_text_color" value="{{ old('footer_text_color', $siteSetting?->footer_text_color ?? '#e2e8f0') }}">
            </label>
        </div>
    </section>

    <section class="settings-card">
        <h3>Logo</h3>
        <p>Sube el logo y verifica su vista previa.</p>

        <div class="settings-logo-grid">
            <label>
                Logo (archivo)
                <input id="site_logo_file" class="input" type="file" name="logo_file" accept="image/*">
                @error('logo_file') <div class="error">{{ $message }}</div> @enderror
                @if($siteSetting?->logo_url)
                    <small>Actual: {{ $siteSetting->logo_url }}</small>
                @endif
            </label>

            <div>
                <p style="margin:.2rem 0 .4rem; font-size:.9rem; color:#334155;">Vista previa logo</p>
                <img
                    id="site_logo_preview"
                    src="{{ $siteSetting?->logo_url ?? '' }}"
                    alt="Vista previa del logo"
                    class="settings-logo-preview"
                    style="width:100%; max-width:280px; height:auto;"
                    @if(! $siteSetting?->logo_url) hidden @endif
                >
            </div>
        </div>
    </section>

    <section class="settings-card">
        <h3>Footer Y Contacto</h3>
        <p>Información de contacto y redes sociales para el pie de página.</p>

        <label>
            Direccion del footer
            <textarea class="textarea" name="footer_address">{{ old('footer_address', $siteSetting?->footer_address) }}</textarea>
            @error('footer_address') <div class="error">{{ $message }}</div> @enderror
        </label>

        <div class="settings-grid-2">
            <label>
                Telefono
                <input class="input" type="text" name="footer_phone" value="{{ old('footer_phone', $siteSetting?->footer_phone) }}">
                @error('footer_phone') <div class="error">{{ $message }}</div> @enderror
            </label>

            <label>
                Email
                <input class="input" type="email" name="footer_email" value="{{ old('footer_email', $siteSetting?->footer_email) }}">
                @error('footer_email') <div class="error">{{ $message }}</div> @enderror
            </label>
        </div>

        <div class="settings-grid-2">
            <label>
                Facebook URL
                <input class="input" type="url" name="footer_facebook_url" value="{{ old('footer_facebook_url', $siteSetting?->footer_facebook_url) }}" placeholder="https://facebook.com/...">
                @error('footer_facebook_url') <div class="error">{{ $message }}</div> @enderror
            </label>

            <label>
                Instagram URL
                <input class="input" type="url" name="footer_instagram_url" value="{{ old('footer_instagram_url', $siteSetting?->footer_instagram_url) }}" placeholder="https://instagram.com/...">
                @error('footer_instagram_url') <div class="error">{{ $message }}</div> @enderror
            </label>

            <label>
                X / Twitter URL
                <input class="input" type="url" name="footer_x_url" value="{{ old('footer_x_url', $siteSetting?->footer_x_url) }}" placeholder="https://x.com/...">
                @error('footer_x_url') <div class="error">{{ $message }}</div> @enderror
            </label>

            <label>
                WhatsApp URL
                <input class="input" type="url" name="footer_whatsapp_url" value="{{ old('footer_whatsapp_url', $siteSetting?->footer_whatsapp_url) }}" placeholder="https://wa.me/...">
                @error('footer_whatsapp_url') <div class="error">{{ $message }}</div> @enderror
            </label>
        </div>

        <label>
            Nota del footer
            <textarea class="textarea" name="footer_note">{{ old('footer_note', $siteSetting?->footer_note) }}</textarea>
            @error('footer_note') <div class="error">{{ $message }}</div> @enderror
        </label>

        <div class="settings-grid-2">
            <label>
                Costo de delivery
                <input class="input" type="number" name="delivery_fee" min="0" step="0.01" value="{{ old('delivery_fee', $siteSetting?->delivery_fee ?? 7.99) }}" required>
                @error('delivery_fee') <div class="error">{{ $message }}</div> @enderror
            </label>

            <label>
                Umbral para envio gratis
                <input class="input" type="number" name="free_shipping_threshold" min="0" step="0.01" value="{{ old('free_shipping_threshold', $siteSetting?->free_shipping_threshold ?? 120) }}" required>
                @error('free_shipping_threshold') <div class="error">{{ $message }}</div> @enderror
            </label>
        </div>
    </section>

    <section class="settings-card">
        <h3>Paginas Legales</h3>
        <p>Contenido visible al cliente para páginas de políticas. Usa formato enriquecido (negrita, listas, encabezados y enlaces).</p>

        <label>
            Políticas de Privacidad
            <input id="privacy_policy_content_input" type="hidden" name="privacy_policy_content" value="{{ old('privacy_policy_content', $siteSetting?->privacy_policy_content) }}">
            <div class="legal-editor js-legal-editor" data-input-id="privacy_policy_content_input">
                <div class="legal-editor-toolbar">
                    <button type="button" data-cmd="bold"><strong>B</strong></button>
                    <button type="button" data-cmd="italic"><em>I</em></button>
                    <button type="button" data-cmd="underline"><u>U</u></button>
                    <button type="button" data-cmd="insertUnorderedList">Lista</button>
                    <button type="button" data-cmd="insertOrderedList">Numerada</button>
                    <button type="button" data-cmd="formatBlock" data-value="h2">H2</button>
                    <button type="button" data-cmd="formatBlock" data-value="h3">H3</button>
                    <button type="button" data-cmd="createLink">Link</button>
                    <button type="button" data-cmd="removeFormat">Limpiar</button>
                </div>
                <div class="legal-editor-surface" contenteditable="true" data-placeholder="Escribe aquí el contenido..."></div>
            </div>
            @error('privacy_policy_content') <div class="error">{{ $message }}</div> @enderror
            <small>Vista pública: {{ route('storefront.policy.show', ['slug' => 'privacidad']) }}</small>
        </label>

        <label>
            Términos del servicio
            <input id="terms_of_service_content_input" type="hidden" name="terms_of_service_content" value="{{ old('terms_of_service_content', $siteSetting?->terms_of_service_content) }}">
            <div class="legal-editor js-legal-editor" data-input-id="terms_of_service_content_input">
                <div class="legal-editor-toolbar">
                    <button type="button" data-cmd="bold"><strong>B</strong></button>
                    <button type="button" data-cmd="italic"><em>I</em></button>
                    <button type="button" data-cmd="underline"><u>U</u></button>
                    <button type="button" data-cmd="insertUnorderedList">Lista</button>
                    <button type="button" data-cmd="insertOrderedList">Numerada</button>
                    <button type="button" data-cmd="formatBlock" data-value="h2">H2</button>
                    <button type="button" data-cmd="formatBlock" data-value="h3">H3</button>
                    <button type="button" data-cmd="createLink">Link</button>
                    <button type="button" data-cmd="removeFormat">Limpiar</button>
                </div>
                <div class="legal-editor-surface" contenteditable="true" data-placeholder="Escribe aquí el contenido..."></div>
            </div>
            @error('terms_of_service_content') <div class="error">{{ $message }}</div> @enderror
            <small>Vista pública: {{ route('storefront.policy.show', ['slug' => 'terminos']) }}</small>
        </label>

        <label>
            Políticas de envíos
            <input id="shipping_policy_content_input" type="hidden" name="shipping_policy_content" value="{{ old('shipping_policy_content', $siteSetting?->shipping_policy_content) }}">
            <div class="legal-editor js-legal-editor" data-input-id="shipping_policy_content_input">
                <div class="legal-editor-toolbar">
                    <button type="button" data-cmd="bold"><strong>B</strong></button>
                    <button type="button" data-cmd="italic"><em>I</em></button>
                    <button type="button" data-cmd="underline"><u>U</u></button>
                    <button type="button" data-cmd="insertUnorderedList">Lista</button>
                    <button type="button" data-cmd="insertOrderedList">Numerada</button>
                    <button type="button" data-cmd="formatBlock" data-value="h2">H2</button>
                    <button type="button" data-cmd="formatBlock" data-value="h3">H3</button>
                    <button type="button" data-cmd="createLink">Link</button>
                    <button type="button" data-cmd="removeFormat">Limpiar</button>
                </div>
                <div class="legal-editor-surface" contenteditable="true" data-placeholder="Escribe aquí el contenido..."></div>
            </div>
            @error('shipping_policy_content') <div class="error">{{ $message }}</div> @enderror
            <small>Vista pública: {{ route('storefront.policy.show', ['slug' => 'envios']) }}</small>
        </label>

        <label>
            Políticas de cambios y reembolsos
            <input id="refund_policy_content_input" type="hidden" name="refund_policy_content" value="{{ old('refund_policy_content', $siteSetting?->refund_policy_content) }}">
            <div class="legal-editor js-legal-editor" data-input-id="refund_policy_content_input">
                <div class="legal-editor-toolbar">
                    <button type="button" data-cmd="bold"><strong>B</strong></button>
                    <button type="button" data-cmd="italic"><em>I</em></button>
                    <button type="button" data-cmd="underline"><u>U</u></button>
                    <button type="button" data-cmd="insertUnorderedList">Lista</button>
                    <button type="button" data-cmd="insertOrderedList">Numerada</button>
                    <button type="button" data-cmd="formatBlock" data-value="h2">H2</button>
                    <button type="button" data-cmd="formatBlock" data-value="h3">H3</button>
                    <button type="button" data-cmd="createLink">Link</button>
                    <button type="button" data-cmd="removeFormat">Limpiar</button>
                </div>
                <div class="legal-editor-surface" contenteditable="true" data-placeholder="Escribe aquí el contenido..."></div>
            </div>
            @error('refund_policy_content') <div class="error">{{ $message }}</div> @enderror
            <small>Vista pública: {{ route('storefront.policy.show', ['slug' => 'reembolsos']) }}</small>
        </label>

        <small class="legal-editor-help">Si no carga el editor enriquecido, los campos seguirán funcionando como texto normal.</small>
    </section>

    @php
        $bankAccounts = old('bank_accounts');

        if (!is_array($bankAccounts)) {
            $bankAccounts = is_array($siteSetting?->bank_accounts) ? $siteSetting->bank_accounts : [];
        }

        if (empty($bankAccounts)) {
            $legacyAnyValue = filled($siteSetting?->bank_name)
                || filled($siteSetting?->bank_account_holder)
                || filled($siteSetting?->bank_account_number)
                || filled($siteSetting?->bank_account_type)
                || filled($siteSetting?->bank_phone)
                || filled($siteSetting?->bank_reference_note);

            $bankAccounts = $legacyAnyValue
                ? [[
                    'bank_name' => $siteSetting?->bank_name,
                    'account_holder' => $siteSetting?->bank_account_holder,
                    'account_number' => $siteSetting?->bank_account_number,
                    'account_type' => $siteSetting?->bank_account_type,
                    'phones' => $siteSetting?->bank_phone,
                    'reference_note' => $siteSetting?->bank_reference_note,
                ]]
                : [[
                    'bank_name' => '',
                    'account_holder' => '',
                    'account_number' => '',
                    'account_type' => '',
                    'phones' => '',
                    'reference_note' => '',
                ]];
        }
    @endphp

    <section class="bank-settings-card">
        <div class="bank-settings-header">
            <div>
                <h3 style="margin:0 0 .25rem; font-size:1rem;">Cuentas para transferencia</h3>
                <p style="margin:0; color:#64748b; font-size:.9rem;">Puedes registrar varios bancos y varios telefonos para pago movil.</p>
            </div>
            <button id="add_bank_account" type="button" class="btn btn-outline">+ Agregar cuenta</button>
        </div>

        @error('bank_accounts') <div class="error">{{ $message }}</div> @enderror

        <div id="bank_accounts_container" style="display:grid; gap:.8rem;">
            @foreach($bankAccounts as $index => $account)
                <article class="bank-account-item">
                    <div class="bank-account-head">
                        <strong>Cuenta #{{ $index + 1 }}</strong>
                        <button type="button" class="btn btn-outline remove-bank-account">Eliminar</button>
                    </div>

                    <div class="bank-account-fields">
                        <label>
                            Banco
                            <input class="input" type="text" name="bank_accounts[{{ $index }}][bank_name]" value="{{ data_get($account, 'bank_name', '') }}" placeholder="Ej: Banco Nacional">
                        </label>

                        <label>
                            Titular
                            <input class="input" type="text" name="bank_accounts[{{ $index }}][account_holder]" value="{{ data_get($account, 'account_holder', '') }}" placeholder="Nombre o razon social">
                        </label>

                        <label>
                            Cuenta
                            <input class="input" type="text" name="bank_accounts[{{ $index }}][account_number]" value="{{ data_get($account, 'account_number', '') }}" placeholder="000-0000000-0">
                        </label>

                        <label>
                            Tipo de cuenta
                            <input class="input" type="text" name="bank_accounts[{{ $index }}][account_type]" value="{{ data_get($account, 'account_type', '') }}" placeholder="Ahorros / Corriente">
                        </label>

                        <label>
                            Telefonos (separados por coma)
                            <input class="input" type="text" name="bank_accounts[{{ $index }}][phones]" value="{{ data_get($account, 'phones', '') }}" placeholder="+58 412-0000000, +58 414-0000000">
                        </label>

                        <label class="full-row">
                            Nota / referencia
                            <textarea class="textarea" name="bank_accounts[{{ $index }}][reference_note]" placeholder="Ej: Enviar comprobante por WhatsApp con numero de orden.">{{ data_get($account, 'reference_note', '') }}</textarea>
                        </label>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <div class="actions">
        <button class="btn" type="submit">{{ $button }}</button>
        <a class="btn btn-outline" href="{{ route('admin.site-settings.edit') }}">Cancelar</a>
    </div>
</form>

<script>
    (function () {
        const fileInput = document.getElementById('site_logo_file');
        const preview = document.getElementById('site_logo_preview');
        const bankContainer = document.getElementById('bank_accounts_container');
        const addBankButton = document.getElementById('add_bank_account');

        function syncBankAccountIndexes() {
            if (!bankContainer) {
                return;
            }

            const items = Array.from(bankContainer.querySelectorAll('.bank-account-item'));

            items.forEach((item, index) => {
                const title = item.querySelector('strong');

                if (title) {
                    title.textContent = `Cuenta #${index + 1}`;
                }

                const fields = item.querySelectorAll('input[name], textarea[name]');

                fields.forEach((field) => {
                    field.name = field.name.replace(/bank_accounts\[\d+\]/, `bank_accounts[${index}]`);
                });
            });
        }

        function createEmptyBankAccount() {
            if (!bankContainer) {
                return;
            }

            const index = bankContainer.querySelectorAll('.bank-account-item').length;
            const wrapper = document.createElement('article');
            wrapper.className = 'bank-account-item';
            wrapper.innerHTML = `
                <div class="bank-account-head">
                    <strong>Cuenta #${index + 1}</strong>
                    <button type="button" class="btn btn-outline remove-bank-account">Eliminar</button>
                </div>
                <div class="bank-account-fields">
                    <label>
                        Banco
                        <input class="input" type="text" name="bank_accounts[${index}][bank_name]" placeholder="Ej: Banco Nacional">
                    </label>
                    <label>
                        Titular
                        <input class="input" type="text" name="bank_accounts[${index}][account_holder]" placeholder="Nombre o razon social">
                    </label>
                    <label>
                        Cuenta
                        <input class="input" type="text" name="bank_accounts[${index}][account_number]" placeholder="000-0000000-0">
                    </label>
                    <label>
                        Tipo de cuenta
                        <input class="input" type="text" name="bank_accounts[${index}][account_type]" placeholder="Ahorros / Corriente">
                    </label>
                    <label>
                        Telefonos (separados por coma)
                        <input class="input" type="text" name="bank_accounts[${index}][phones]" placeholder="+58 412-0000000, +58 414-0000000">
                    </label>
                    <label class="full-row">
                        Nota / referencia
                        <textarea class="textarea" name="bank_accounts[${index}][reference_note]" placeholder="Ej: Enviar comprobante por WhatsApp con numero de orden."></textarea>
                    </label>
                </div>
            `;

            bankContainer.appendChild(wrapper);
            syncBankAccountIndexes();
        }

        if (fileInput && preview) {
            fileInput.addEventListener('change', function (event) {
                const target = event.target;
                const file = target.files && target.files[0] ? target.files[0] : null;

                if (!file) {
                    return;
                }

                const reader = new FileReader();

                reader.onload = function (loadEvent) {
                    preview.src = loadEvent.target && loadEvent.target.result ? loadEvent.target.result : '';
                    preview.hidden = !preview.src;
                };

                reader.readAsDataURL(file);
            });
        }

        if (addBankButton) {
            addBankButton.addEventListener('click', function () {
                createEmptyBankAccount();
            });
        }

        if (bankContainer) {
            bankContainer.addEventListener('click', function (event) {
                const button = event.target.closest('.remove-bank-account');

                if (!button) {
                    return;
                }

                const item = button.closest('.bank-account-item');
                const total = bankContainer.querySelectorAll('.bank-account-item').length;

                if (total <= 1) {
                    const inputs = item.querySelectorAll('input, textarea');
                    inputs.forEach((field) => {
                        field.value = '';
                    });
                    return;
                }

                item.remove();
                syncBankAccountIndexes();
            });
        }

        syncBankAccountIndexes();
    })();
</script>

<script>
    (function () {
        const editors = Array.from(document.querySelectorAll('.js-legal-editor'));
        if (editors.length === 0) {
            return;
        }

        let activeSurface = null;

        function selectSurface(surface) {
            if (!surface) {
                return;
            }

            activeSurface = surface;
            surface.focus();
        }

        editors.forEach((editor) => {
            const inputId = editor.getAttribute('data-input-id');
            const hiddenInput = inputId ? document.getElementById(inputId) : null;
            const surface = editor.querySelector('.legal-editor-surface');

            if (!hiddenInput || !surface) {
                return;
            }

            surface.innerHTML = String(hiddenInput.value || '').trim();

            surface.addEventListener('focus', () => {
                activeSurface = surface;
            });

            surface.addEventListener('input', () => {
                hiddenInput.value = surface.innerHTML;
            });

            const toolbarButtons = Array.from(editor.querySelectorAll('.legal-editor-toolbar button[data-cmd]'));
            toolbarButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    selectSurface(surface);

                    const command = button.getAttribute('data-cmd');
                    const value = button.getAttribute('data-value');

                    if (command === 'createLink') {
                        const url = window.prompt('Ingresa la URL del enlace:', 'https://');
                        if (!url) {
                            return;
                        }
                        document.execCommand(command, false, url);
                    } else if (value) {
                        document.execCommand(command, false, value);
                    } else {
                        document.execCommand(command, false);
                    }

                    hiddenInput.value = surface.innerHTML;
                });
            });
        });

        const form = document.querySelector('form.site-settings-form');
        if (!form) {
            return;
        }

        form.addEventListener('submit', function () {
            editors.forEach((editor) => {
                const inputId = editor.getAttribute('data-input-id');
                const hiddenInput = inputId ? document.getElementById(inputId) : null;
                const surface = editor.querySelector('.legal-editor-surface');

                if (hiddenInput && surface) {
                    hiddenInput.value = surface.innerHTML;
                }
            });
        });
    })();
</script>
