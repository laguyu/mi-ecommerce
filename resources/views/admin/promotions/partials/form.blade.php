@php
    $selectedFromServer = collect($selectedProducts ?? []);
    $selectedMap = $selectedFromServer->mapWithKeys(fn ($product) => [
        (int) $product->id => [
            'id' => (int) $product->id,
            'name' => (string) $product->name,
            'sku' => (string) $product->sku,
            'status' => (string) $product->status,
        ],
    ])->all();

    $selectedProductIds = collect(old('product_ids', $promotion?->products?->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();

    $selectedForWidget = collect($selectedProductIds)
        ->map(function ($id) use ($selectedMap) {
            return $selectedMap[$id] ?? [
                'id' => $id,
                'name' => 'Producto #'.$id,
                'sku' => '-',
                'status' => 'unknown',
            ];
        })
        ->values()
        ->all();
@endphp

<form method="POST" action="{{ $action }}" class="grid admin-form admin-form--wide" enctype="multipart/form-data">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <label>
        Nombre de promocion
        <input class="input" type="text" name="name" value="{{ old('name', $promotion?->name) }}" required>
        @error('name') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Descuento (%)
        <input class="input" type="number" name="discount_percentage" min="1" max="90" step="1" value="{{ old('discount_percentage', $promotion?->discount_percentage) }}" required>
        @error('discount_percentage') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Estado
        <select class="select" name="status" required>
            <option value="inactive" @selected(old('status', $promotion?->status ?? 'inactive') === 'inactive')>Inactiva</option>
            <option value="active" @selected(old('status', $promotion?->status) === 'active')>Activa</option>
        </select>
        @error('status') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Titulo del banner de promocion
        <input class="input" type="text" name="banner_title" value="{{ old('banner_title', $promotion?->banner_title) }}" placeholder="Ejemplo: Oferta de temporada">
        @error('banner_title') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Subtitulo del banner de promocion
        <input class="input" type="text" name="banner_subtitle" value="{{ old('banner_subtitle', $promotion?->banner_subtitle) }}" placeholder="Ejemplo: Hasta 40% en productos seleccionados">
        @error('banner_subtitle') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label class="full-row">
        Imagen del banner
        <input id="promotion-banner-image-file" class="input" type="file" name="banner_image_file" accept="image/*">
        <small>Sube una imagen desde el panel. Si la promocion esta activa, se mostrara en el home.</small>
        @error('banner_image_file') <div class="error">{{ $message }}</div> @enderror
    </label>

    @if($promotion?->banner_image_url)
        <label class="full-row" style="display:flex; align-items:center; gap:.5rem;">
            <input id="promotion-remove-banner-image" type="checkbox" name="remove_banner_image" value="1" @checked(old('remove_banner_image'))>
            Quitar imagen actual del banner
        </label>
    @endif

    <div id="promotion-banner-preview" class="full-row" data-current-image="{{ $promotion?->banner_image_url ?? '' }}" style="border:1px solid #e5e7eb; border-radius:10px; padding:.6rem; display:grid; gap:.45rem;">
        <strong style="font-size:.9rem;">Vista previa del banner</strong>
        <small id="promotion-banner-preview-status" style="color:#64748b;">Selecciona una imagen para previsualizar.</small>
        <img
            id="promotion-banner-preview-image"
            alt="Vista previa del banner de promocion"
            style="display:none; width:100%; max-width:560px; max-height:220px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb;"
        >
    </div>

    <label>
        Fecha y hora de inicio
        <input
            class="input"
            type="datetime-local"
            name="starts_at"
            value="{{ old('starts_at', optional($promotion?->starts_at)->format('Y-m-d\TH:i')) }}"
            required
        >
        @error('starts_at') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Fecha y hora de fin
        <input
            class="input"
            type="datetime-local"
            name="ends_at"
            value="{{ old('ends_at', optional($promotion?->ends_at)->format('Y-m-d\TH:i')) }}"
            required
        >
        @error('ends_at') <div class="error">{{ $message }}</div> @enderror
    </label>

    <div id="promotion-product-picker" class="full-row" data-search-url="{{ route('admin.promotions.search-products') }}" data-initial-selected='@json($selectedForWidget)'>
        <strong>Productos de la promocion</strong>
        <small style="display:block; color:#64748b; margin:.25rem 0 .5rem;">Busca por nombre o SKU y agrega solo los productos necesarios.</small>

        <div style="display:grid; gap:.55rem;">
            <div style="display:grid; grid-template-columns: 1fr auto; gap:.45rem;">
                <input id="promotion-product-search" type="search" class="input" placeholder="Buscar producto por nombre o SKU">
                <button id="promotion-product-search-button" type="button" class="btn btn-outline">Buscar</button>
            </div>

            <div id="promotion-product-search-feedback" style="font-size:.88rem; color:#64748b;"></div>

            <div id="promotion-product-search-results" style="max-height:220px; overflow:auto; border:1px solid #e5e7eb; border-radius:10px; padding:.45rem; display:grid; gap:.35rem;"></div>

            <div>
                <strong style="font-size:.9rem;">Seleccionados</strong>
                <div id="promotion-product-selected" style="margin-top:.35rem; max-height:220px; overflow:auto; border:1px solid #e5e7eb; border-radius:10px; padding:.45rem; display:grid; gap:.35rem;"></div>
            </div>

            <div id="promotion-product-hidden-inputs"></div>
        </div>

        @error('product_ids') <div class="error">{{ $message }}</div> @enderror
        @error('product_ids.*') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div class="actions">
        <button class="btn" type="submit">{{ $button }}</button>
        <a class="btn btn-outline" href="{{ route('admin.promotions.index') }}">Cancelar</a>
    </div>
</form>

<script>
(() => {
    const root = document.getElementById('promotion-product-picker');
    if (!root) return;

    const searchUrl = root.dataset.searchUrl;
    const initialSelected = JSON.parse(root.dataset.initialSelected || '[]');
    const selected = new Map(initialSelected.map((item) => [Number(item.id), item]));

    const searchInput = document.getElementById('promotion-product-search');
    const searchButton = document.getElementById('promotion-product-search-button');
    const feedback = document.getElementById('promotion-product-search-feedback');
    const resultsContainer = document.getElementById('promotion-product-search-results');
    const selectedContainer = document.getElementById('promotion-product-selected');
    const hiddenInputs = document.getElementById('promotion-product-hidden-inputs');
    const bannerPreviewRoot = document.getElementById('promotion-banner-preview');
    const bannerImageFileInput = document.getElementById('promotion-banner-image-file');
    const removeBannerImageInput = document.getElementById('promotion-remove-banner-image');
    const bannerPreviewImage = document.getElementById('promotion-banner-preview-image');
    const bannerPreviewStatus = document.getElementById('promotion-banner-preview-status');
    let debounceTimer = null;

    function renderBannerPreview() {
        const selectedFile = bannerImageFileInput?.files?.[0] ?? null;
        const currentImage = (bannerPreviewRoot?.dataset.currentImage || '').trim();
        const removeCurrentImage = Boolean(removeBannerImageInput?.checked);

        if (selectedFile) {
            const localFileUrl = URL.createObjectURL(selectedFile);
            bannerPreviewStatus.textContent = 'Vista previa del archivo seleccionado.';
            bannerPreviewImage.style.display = 'none';
            bannerPreviewImage.src = localFileUrl;
            return;
        }

        if (removeCurrentImage) {
            bannerPreviewImage.style.display = 'none';
            bannerPreviewImage.removeAttribute('src');
            bannerPreviewStatus.textContent = 'La imagen actual se eliminara al guardar.';
            return;
        }

        if (!currentImage) {
            bannerPreviewImage.style.display = 'none';
            bannerPreviewImage.removeAttribute('src');
            bannerPreviewStatus.textContent = 'Selecciona una imagen para previsualizar.';
            return;
        }

        bannerPreviewStatus.textContent = 'Mostrando imagen actual.';
        bannerPreviewImage.style.display = 'none';
        bannerPreviewImage.src = currentImage;
    }

    function statusText(status) {
        return status === 'active' ? 'Activo' : (status === 'inactive' ? 'Inactivo' : 'Sin estado');
    }

    function renderSelected() {
        selectedContainer.innerHTML = '';
        hiddenInputs.innerHTML = '';

        if (selected.size === 0) {
            selectedContainer.innerHTML = '<div style="color:#64748b; font-size:.88rem;">No hay productos agregados.</div>';
            return;
        }

        [...selected.values()].forEach((product) => {
            const row = document.createElement('div');
            row.style.display = 'flex';
            row.style.alignItems = 'center';
            row.style.justifyContent = 'space-between';
            row.style.gap = '.6rem';
            row.style.border = '1px solid #f1f5f9';
            row.style.borderRadius = '8px';
            row.style.padding = '.45rem .55rem';

            row.innerHTML = `
                <span>
                    <strong>${product.name}</strong>
                    <small style="display:block; color:#64748b;">SKU: ${product.sku} · ${statusText(product.status)}</small>
                </span>
            `;

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'btn btn-outline';
            removeButton.textContent = 'Quitar';
            removeButton.addEventListener('click', () => {
                selected.delete(Number(product.id));
                renderSelected();
                renderResults([]);
            });

            row.appendChild(removeButton);
            selectedContainer.appendChild(row);

            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'product_ids[]';
            hidden.value = String(product.id);
            hiddenInputs.appendChild(hidden);
        });
    }

    function renderResults(products) {
        resultsContainer.innerHTML = '';

        if (products.length === 0) {
            resultsContainer.innerHTML = '<div style="color:#64748b; font-size:.88rem;">Sin resultados.</div>';
            return;
        }

        products.forEach((product) => {
            if (selected.has(Number(product.id))) {
                return;
            }

            const row = document.createElement('div');
            row.style.display = 'flex';
            row.style.alignItems = 'center';
            row.style.justifyContent = 'space-between';
            row.style.gap = '.6rem';
            row.style.border = '1px solid #f1f5f9';
            row.style.borderRadius = '8px';
            row.style.padding = '.45rem .55rem';

            row.innerHTML = `
                <span>
                    <strong>${product.name}</strong>
                    <small style="display:block; color:#64748b;">SKU: ${product.sku} · ${statusText(product.status)}</small>
                </span>
            `;

            const addButton = document.createElement('button');
            addButton.type = 'button';
            addButton.className = 'btn btn-outline';
            addButton.textContent = 'Agregar';
            addButton.addEventListener('click', () => {
                selected.set(Number(product.id), product);
                renderSelected();
                row.remove();
            });

            row.appendChild(addButton);
            resultsContainer.appendChild(row);
        });

        if (!resultsContainer.children.length) {
            resultsContainer.innerHTML = '<div style="color:#64748b; font-size:.88rem;">Todos los resultados ya estan agregados.</div>';
        }
    }

    async function runSearch() {
        const q = searchInput.value.trim();

        if (q.length < 2) {
            feedback.textContent = 'Escribe al menos 2 caracteres para buscar.';
            renderResults([]);
            return;
        }

        feedback.textContent = 'Buscando...';

        const params = new URLSearchParams({ q });
        [...selected.keys()].forEach((id) => params.append('exclude_ids[]', String(id)));

        try {
            const response = await fetch(`${searchUrl}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('No se pudo buscar productos.');
            }

            const payload = await response.json();
            const products = Array.isArray(payload.data) ? payload.data : [];

            feedback.textContent = `${products.length} resultado(s).`;
            renderResults(products);
        } catch (error) {
            feedback.textContent = error.message || 'Error buscando productos.';
            renderResults([]);
        }
    }

    searchButton.addEventListener('click', runSearch);
    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            runSearch();
        }
    });

    searchInput.addEventListener('input', () => {
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }

        debounceTimer = setTimeout(() => {
            runSearch();
        }, 350);
    });

    bannerPreviewImage.addEventListener('load', () => {
        bannerPreviewImage.style.display = 'block';
        bannerPreviewStatus.textContent = 'Vista previa lista.';
    });

    bannerPreviewImage.addEventListener('error', () => {
        bannerPreviewImage.style.display = 'none';
        bannerPreviewStatus.textContent = 'No se pudo cargar la imagen seleccionada.';
    });

    bannerImageFileInput.addEventListener('change', () => {
        renderBannerPreview();
    });

    if (removeBannerImageInput) {
        removeBannerImageInput.addEventListener('change', () => {
            renderBannerPreview();
        });
    }

    renderSelected();
    renderResults([]);
    renderBannerPreview();
})();
</script>
