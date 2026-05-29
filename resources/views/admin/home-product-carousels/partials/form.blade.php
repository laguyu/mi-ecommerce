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

    $selectedProductIds = collect(old('product_ids', $carousel?->products?->pluck('id')->all() ?? []))
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
        Titulo del carrusel
        <input class="input" type="text" name="title" value="{{ old('title', $carousel?->title) }}" required>
        @error('title') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Subtitulo del carrusel
        <input class="input" type="text" name="subtitle" value="{{ old('subtitle', $carousel?->subtitle) }}">
        @error('subtitle') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Orden
        <input class="input" type="number" name="sort_order" min="1" max="99" step="1" value="{{ old('sort_order', $carousel?->sort_order ?? 1) }}" required>
        @error('sort_order') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Imagen del modulo
        <input id="carousel-image-file" class="input" type="file" name="image_file" accept="image/*" {{ $carousel ? '' : 'required' }}>
        @error('image_file') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label class="full-row">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $carousel?->is_active ?? true))>
        Carrusel activo
    </label>

    <div id="carousel-image-preview" class="full-row" data-current-image="{{ $carousel?->image_url ?? '' }}" style="border:1px solid #e5e7eb; border-radius:10px; padding:.6rem; display:grid; gap:.45rem;">
        <strong style="font-size:.9rem;">Vista previa del modulo</strong>
        <small id="carousel-image-preview-status" style="color:#64748b;">Selecciona una imagen para previsualizar.</small>
        <img
            id="carousel-image-preview-image"
            alt="Vista previa del carrusel"
            style="display:none; width:100%; max-width:560px; max-height:220px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb;"
        >
    </div>

    <div id="carousel-product-picker" class="full-row" data-search-url="{{ route('admin.home-product-carousels.search-products') }}" data-initial-selected='@json($selectedForWidget)'>
        <strong>Productos del carrusel</strong>
        <small style="display:block; color:#64748b; margin:.25rem 0 .5rem;">Busca por nombre o SKU y agrega los productos en el orden deseado.</small>

        <div style="display:grid; gap:.55rem;">
            <div style="display:grid; grid-template-columns: 1fr auto; gap:.45rem;">
                <input id="carousel-product-search" type="search" class="input" placeholder="Buscar producto por nombre o SKU">
                <button id="carousel-product-search-button" type="button" class="btn btn-outline">Buscar</button>
            </div>

            <div id="carousel-product-search-feedback" style="font-size:.88rem; color:#64748b;"></div>

            <div id="carousel-product-search-results" style="max-height:220px; overflow:auto; border:1px solid #e5e7eb; border-radius:10px; padding:.45rem; display:grid; gap:.35rem;"></div>

            <div>
                <strong style="font-size:.9rem;">Seleccionados</strong>
                <div id="carousel-product-selected" style="margin-top:.35rem; max-height:220px; overflow:auto; border:1px solid #e5e7eb; border-radius:10px; padding:.45rem; display:grid; gap:.35rem;"></div>
            </div>

            <div id="carousel-product-hidden-inputs"></div>
        </div>

        @error('product_ids') <div class="error">{{ $message }}</div> @enderror
        @error('product_ids.*') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div class="actions">
        <button class="btn" type="submit">{{ $button }}</button>
        <a class="btn btn-outline" href="{{ route('admin.home-product-carousels.index') }}">Cancelar</a>
    </div>
</form>

<script>
(() => {
    const root = document.getElementById('carousel-product-picker');
    if (!root) return;

    const searchUrl = root.dataset.searchUrl;
    const initialSelected = JSON.parse(root.dataset.initialSelected || '[]');
    const selected = [...new Map(initialSelected.map((item) => [Number(item.id), item])).values()];

    const searchInput = document.getElementById('carousel-product-search');
    const searchButton = document.getElementById('carousel-product-search-button');
    const feedback = document.getElementById('carousel-product-search-feedback');
    const resultsContainer = document.getElementById('carousel-product-search-results');
    const selectedContainer = document.getElementById('carousel-product-selected');
    const hiddenInputs = document.getElementById('carousel-product-hidden-inputs');
    let typingSearchTimer = null;
    const imagePreviewRoot = document.getElementById('carousel-image-preview');
    const imageFileInput = document.getElementById('carousel-image-file');
    const imagePreviewImage = document.getElementById('carousel-image-preview-image');
    const imagePreviewStatus = document.getElementById('carousel-image-preview-status');

    function renderImagePreview() {
        const selectedFile = imageFileInput?.files?.[0] ?? null;
        const currentImage = (imagePreviewRoot?.dataset.currentImage || '').trim();

        if (selectedFile) {
            const localFileUrl = URL.createObjectURL(selectedFile);
            imagePreviewStatus.textContent = 'Vista previa del archivo seleccionado.';
            imagePreviewImage.style.display = 'block';
            imagePreviewImage.src = localFileUrl;
            return;
        }

        if (!currentImage) {
            imagePreviewImage.style.display = 'none';
            imagePreviewImage.removeAttribute('src');
            imagePreviewStatus.textContent = 'Selecciona una imagen para previsualizar.';
            return;
        }

        imagePreviewStatus.textContent = 'Mostrando imagen actual.';
        imagePreviewImage.style.display = 'block';
        imagePreviewImage.src = currentImage;
    }

    function statusText(status) {
        return status === 'active' ? 'Activo' : (status === 'inactive' ? 'Inactivo' : 'Sin estado');
    }

    function renderSelected() {
        selectedContainer.innerHTML = '';
        hiddenInputs.innerHTML = '';

        if (selected.length === 0) {
            selectedContainer.innerHTML = '<div style="color:#64748b; font-size:.88rem;">No hay productos agregados.</div>';
            return;
        }

        selected.forEach((product, index) => {
            const row = document.createElement('div');
            row.style.display = 'grid';
            row.style.gridTemplateColumns = 'auto 1fr auto';
            row.style.alignItems = 'center';
            row.style.gap = '.6rem';
            row.style.border = '1px solid #f1f5f9';
            row.style.borderRadius = '8px';
            row.style.padding = '.45rem .55rem';

            const order = document.createElement('strong');
            order.textContent = String(index + 1);

            const info = document.createElement('span');
            info.innerHTML = `<strong>${product.name}</strong><small style="display:block; color:#64748b;">SKU: ${product.sku} · ${statusText(product.status)}</small>`;

            const actions = document.createElement('div');
            actions.style.display = 'flex';
            actions.style.gap = '.3rem';

            const upButton = document.createElement('button');
            upButton.type = 'button';
            upButton.className = 'btn btn-outline';
            upButton.textContent = '↑';
            upButton.disabled = index === 0;
            upButton.addEventListener('click', () => {
                [selected[index - 1], selected[index]] = [selected[index], selected[index - 1]];
                renderSelected();
            });

            const downButton = document.createElement('button');
            downButton.type = 'button';
            downButton.className = 'btn btn-outline';
            downButton.textContent = '↓';
            downButton.disabled = index === selected.length - 1;
            downButton.addEventListener('click', () => {
                [selected[index + 1], selected[index]] = [selected[index], selected[index + 1]];
                renderSelected();
            });

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'btn btn-outline';
            removeButton.textContent = 'Quitar';
            removeButton.addEventListener('click', () => {
                selected.splice(index, 1);
                renderSelected();
            });

            actions.append(upButton, downButton, removeButton);
            row.append(order, info, actions);
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

        const selectedIds = new Set(selected.map((item) => Number(item.id)));
        const filtered = products.filter((product) => !selectedIds.has(Number(product.id)));

        if (filtered.length === 0) {
            resultsContainer.innerHTML = '<div style="color:#64748b; font-size:.88rem;">Sin resultados.</div>';
            return;
        }

        filtered.forEach((product) => {
            const row = document.createElement('div');
            row.style.display = 'flex';
            row.style.alignItems = 'center';
            row.style.justifyContent = 'space-between';
            row.style.gap = '.6rem';
            row.style.border = '1px solid #f1f5f9';
            row.style.borderRadius = '8px';
            row.style.padding = '.45rem .55rem';

            row.innerHTML = `<span><strong>${product.name}</strong><small style="display:block; color:#64748b;">SKU: ${product.sku}</small></span>`;

            const addButton = document.createElement('button');
            addButton.type = 'button';
            addButton.className = 'btn btn-outline';
            addButton.textContent = 'Agregar';
            addButton.addEventListener('click', () => {
                selected.push(product);
                renderSelected();
                row.remove();
            });

            row.appendChild(addButton);
            resultsContainer.appendChild(row);
        });
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
        selected.forEach((item) => params.append('exclude_ids[]', String(item.id)));

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
    searchInput.addEventListener('input', () => {
        if (typingSearchTimer) {
            clearTimeout(typingSearchTimer);
        }

        typingSearchTimer = setTimeout(() => {
            runSearch();
        }, 280);
    });

    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            runSearch();
        }
    });

    imageFileInput?.addEventListener('change', renderImagePreview);

    renderSelected();
    renderImagePreview();
})();
</script>
