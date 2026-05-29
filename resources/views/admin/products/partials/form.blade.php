<form method="POST" action="{{ $action }}" class="grid admin-form admin-form--wide" enctype="multipart/form-data">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <label>
        Categoria
        <select class="select" name="category_id" required>
            <option value="">Selecciona categoria</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product?->category_id) == $category->id)>
                    {{ $category->full_name }}
                </option>
            @endforeach
        </select>
        <small>Selecciona la categoria mas especifica, por ejemplo Dama / Falda.</small>
        @error('category_id') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Marca
        <select class="select" name="brand_id" required>
            <option value="">Selecciona marca</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->id }}" @selected(old('brand_id', $product?->brand_id) == $brand->id)>
                    {{ $brand->name }}
                </option>
            @endforeach
        </select>
        @error('brand_id') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Estado
        <select class="select" name="status" required>
            <option value="active" @selected(old('status', $product?->status ?? 'active') === 'active')>Activo</option>
            <option value="inactive" @selected(old('status', $product?->status) === 'inactive')>Inactivo</option>
        </select>
        <small>Si esta inactivo, no se mostrara en la tienda.</small>
        @error('status') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        SKU
        <input class="input" type="text" name="sku" value="{{ old('sku', $product?->sku) }}" required>
        @error('sku') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Nombre
        <input class="input" type="text" name="name" value="{{ old('name', $product?->name) }}" required>
        @error('name') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label class="full-row">
        Descripcion
        <textarea class="textarea" name="description" required>{{ old('description', $product?->description) }}</textarea>
        @error('description') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Precio
        <input class="input" type="number" name="price" min="0" step="0.01" value="{{ old('price', $product?->price) }}" required>
        @error('price') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Stock
        <input class="input" type="number" name="stock" min="0" step="1" value="{{ old('stock', $product?->stock) }}" required>
        @error('stock') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label class="full-row">
        Imagenes del producto
        <input class="input" type="file" name="image_files[]" accept="image/*" multiple {{ $product ? '' : 'required' }}>
        <small>Puedes cargar varias imagenes. La primera imagen existente se mantiene como principal.</small>
        @error('image_files') <div class="error">{{ $message }}</div> @enderror
        @error('image_files.*') <div class="error">{{ $message }}</div> @enderror
    </label>

    @if($product?->images?->count())
        <div class="full-row">
            <strong>Imagenes actuales</strong>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(90px, 1fr)); gap:.5rem; margin-top:.5rem;">
                @foreach($product->images->sortBy([['is_primary', 'desc'], ['sort_order', 'asc']]) as $image)
                    <figure style="margin:0;">
                        <img src="{{ $image->url }}" alt="{{ $product->name }}" style="width:100%; height:90px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb;">
                        @if($image->is_primary)
                            <figcaption style="font-size:.72rem; color:#334155; margin-top:.2rem;">Principal</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        </div>
    @endif

    <label class="full-row">
        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product?->is_featured))>
        Marcar como destacado
    </label>

    <hr style="border:none; border-top:1px solid #e5e7eb; margin:.3rem 0;" class="full-row">

    <label class="full-row">
        <input type="checkbox" name="show_in_main_banner" value="1" @checked(old('show_in_main_banner', $product?->show_in_main_banner))>
        Mostrar en banners principales (inicio)
    </label>

    <label>
        Orden en banners (1 = primero)
        <input
            class="input"
            type="number"
            name="main_banner_order"
            min="1"
            max="99"
            step="1"
            value="{{ old('main_banner_order', $product?->main_banner_order) }}"
            placeholder="Opcional"
        >
        @error('main_banner_order') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label class="full-row">
        <input type="checkbox" name="show_in_home_carousel" value="1" @checked(old('show_in_home_carousel', $product?->show_in_home_carousel))>
        Mostrar en carrusel principal
    </label>

    <label>
        Orden en carrusel (1 = primero)
        <input
            class="input"
            type="number"
            name="home_carousel_order"
            min="1"
            max="99"
            step="1"
            value="{{ old('home_carousel_order', $product?->home_carousel_order) }}"
            placeholder="Opcional"
        >
        @error('home_carousel_order') <div class="error">{{ $message }}</div> @enderror
    </label>

    <div class="actions">
        <button class="btn" type="submit">{{ $button }}</button>
        <a class="btn btn-outline" href="{{ route('admin.products.index') }}">Cancelar</a>
    </div>
</form>
