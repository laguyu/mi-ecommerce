<form method="POST" action="{{ $action }}" class="grid admin-form admin-form--single">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <label>
        Nombre
        <input class="input" type="text" name="name" value="{{ old('name', $brand?->name) }}" required>
        @error('name') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Descripcion
        <textarea class="textarea" name="description">{{ old('description', $brand?->description) }}</textarea>
        @error('description') <div class="error">{{ $message }}</div> @enderror
    </label>

    <div class="actions">
        <button class="btn" type="submit">{{ $button }}</button>
        <a class="btn btn-outline" href="{{ route('admin.brands.index') }}">Cancelar</a>
    </div>
</form>
