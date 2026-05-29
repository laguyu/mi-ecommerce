<form method="POST" action="{{ $action }}" class="grid admin-form" enctype="multipart/form-data">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <label>
        Titulo
        <input class="input" type="text" name="title" value="{{ old('title', $banner?->title) }}" required>
        @error('title') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Subtitulo
        <input class="input" type="text" name="subtitle" value="{{ old('subtitle', $banner?->subtitle) }}">
        @error('subtitle') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Producto vinculado (opcional)
        <select class="select" name="product_id">
            <option value="">Sin vincular</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}" @selected(old('product_id', $banner?->product_id) == $product->id)>
                    {{ $product->name }}
                </option>
            @endforeach
        </select>
        @error('product_id') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        URL externa (opcional)
        <input class="input" type="url" name="link_url" value="{{ old('link_url', $banner?->link_url) }}" placeholder="https://...">
        @error('link_url') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Orden de prioridad (1 primero)
        <input class="input" type="number" name="sort_order" min="1" max="99" step="1" value="{{ old('sort_order', $banner?->sort_order ?? 1) }}" required>
        @error('sort_order') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Imagen banner (archivo)
        <input id="image_file" class="input" type="file" name="image_file" accept="image/*" {{ $banner ? '' : 'required' }}>
        @error('image_file') <div class="error">{{ $message }}</div> @enderror
    </label>

    <div class="full-row">
        <p style="margin:.2rem 0 .4rem; font-size:.9rem; color:#334155;">Vista previa</p>
        <img
            id="banner_preview"
            src="{{ $banner?->image_url ?? '' }}"
            alt="Vista previa de banner"
            style="width:100%; max-width:520px; height:auto; border:1px solid #e5e7eb; border-radius:12px;"
            @if(!$banner?->image_url) hidden @endif
        >
    </div>

    <label class="full-row">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner?->is_active ?? true))>
        Banner secundario activo
    </label>

    <div class="actions">
        <button class="btn" type="submit">{{ $button }}</button>
        <a class="btn btn-outline" href="{{ route('admin.home-secondary-banners.index') }}">Cancelar</a>
    </div>
</form>

<script>
    (function () {
        const fileInput = document.getElementById('image_file');
        const preview = document.getElementById('banner_preview');

        if (!fileInput || !preview) {
            return;
        }

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
    })();
</script>
