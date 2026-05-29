<form method="POST" action="{{ $action }}" class="grid admin-form admin-form--single">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <label>
        Categoria padre
        <select class="select" name="parent_id">
            <option value="">Sin categoria padre</option>
            @foreach($categories as $option)
                <option value="{{ $option['id'] }}" @selected(old('parent_id', $category?->parent_id) == $option['id'])>
                    {{ $option['label'] }}
                </option>
            @endforeach
        </select>
        @error('parent_id') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Nombre
        <input class="input" type="text" name="name" value="{{ old('name', $category?->name) }}" required>
        @error('name') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Descripcion
        <textarea class="textarea" name="description">{{ old('description', $category?->description) }}</textarea>
        @error('description') <div class="error">{{ $message }}</div> @enderror
    </label>

    <div class="actions">
        <button class="btn" type="submit">{{ $button }}</button>
        <a class="btn btn-outline" href="{{ route('admin.categories.index') }}">Cancelar</a>
    </div>
</form>
