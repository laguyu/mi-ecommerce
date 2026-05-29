<form method="POST" action="{{ $action }}" class="grid admin-form">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <label>
        Codigo
        <input class="input" type="text" name="code" value="{{ old('code', $coupon?->code) }}" placeholder="Ejemplo: BIENVENIDA10" required>
        @error('code') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Nombre
        <input class="input" type="text" name="name" value="{{ old('name', $coupon?->name) }}" placeholder="Ejemplo: Cupon de bienvenida" required>
        @error('name') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Tipo
        <select class="select" name="type" required>
            <option value="percentage" @selected(old('type', $coupon?->type ?? 'percentage') === 'percentage')>Porcentaje</option>
            <option value="fixed" @selected(old('type', $coupon?->type) === 'fixed')>Monto fijo</option>
        </select>
        @error('type') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Valor
        <input class="input" type="number" name="value" min="0.01" step="0.01" value="{{ old('value', $coupon?->value) }}" required>
        @error('value') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Estado
        <select class="select" name="status" required>
            <option value="inactive" @selected(old('status', $coupon?->status ?? 'inactive') === 'inactive')>Inactivo</option>
            <option value="active" @selected(old('status', $coupon?->status) === 'active')>Activo</option>
        </select>
        @error('status') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Fecha inicio (opcional)
        <input class="input" type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($coupon?->starts_at)->format('Y-m-d\\TH:i')) }}">
        @error('starts_at') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Fecha fin (opcional)
        <input class="input" type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($coupon?->ends_at)->format('Y-m-d\\TH:i')) }}">
        @error('ends_at') <div class="error">{{ $message }}</div> @enderror
    </label>

    <label>
        Maximo de usos (opcional)
        <input class="input" type="number" name="max_uses" min="1" step="1" value="{{ old('max_uses', $coupon?->max_uses) }}" placeholder="Sin limite">
        @error('max_uses') <div class="error">{{ $message }}</div> @enderror
    </label>

    @if($coupon)
        <label class="full-row">
            Usos actuales
            <input class="input" type="text" value="{{ $coupon->used_count }}" disabled>
        </label>
    @endif

    <div class="actions">
        <button class="btn" type="submit">{{ $button }}</button>
        <a class="btn btn-outline" href="{{ route('admin.coupons.index') }}">Cancelar</a>
    </div>
</form>
