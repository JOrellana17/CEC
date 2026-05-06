<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ $action }}">
            @csrf
            @if($method !== 'POST')
            @method($method)
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $service->name) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Categoría</label>
                    <select name="category" class="form-select" required>
                        @foreach(['food' => 'Restaurante', 'laundry' => 'Lavandería', 'room' => 'Servicio a la habitación', 'transport' => 'Transporte', 'beverage' => 'Minibar', 'other' => 'Personalizado'] as $key => $label)
                        <option value="{{ $key }}" {{ old('category', $service->category) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Precio</label>
                    <input type="number" step="0.01" min="0" name="price" class="form-control" required value="{{ old('price', $service->price) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tipo de precio</label>
                    <select name="price_type" class="form-select">
                        @foreach(['fixed', 'per_person', 'per_hour', 'per_unit'] as $type)
                        <option value="{{ $type }}" {{ old('price_type', $service->price_type ?? 'per_unit') === $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Unidad</label>
                    <input type="text" name="unit" class="form-control" value="{{ old('unit', $service->unit) }}" placeholder="artículo, viaje, carga">
                </div>
                <div class="col-md-4 d-flex align-items-end gap-3">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('is_active', $service->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label">Activo</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_available_24h" value="1" class="form-check-input" {{ old('is_available_24h', $service->is_available_24h) ? 'checked' : '' }}>
                        <label class="form-check-label">24 h</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $service->description) }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary">Guardar servicio</button>
                <a href="{{ route('backend.services.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
