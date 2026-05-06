@csrf
@if(isset($floor))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-12 col-md-6">
        <label class="form-label">Nombre</label>
        <input class="form-control" name="name" value="{{ old('name', $floor->name ?? '') }}" required>
    </div>
    <div class="col-12 col-md-3">
        <label class="form-label">Número</label>
        <input type="number" class="form-control" name="number" value="{{ old('number', $floor->number ?? '') }}" required>
    </div>
    <div class="col-12 col-md-3 d-flex align-items-end">
        <div class="form-check">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $floor->is_active ?? true))>
            <label class="form-check-label" for="is_active">Activo</label>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label">Descripción</label>
        <textarea class="form-control" name="description" rows="3">{{ old('description', $floor->description ?? '') }}</textarea>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary" data-loading>Guardar</button>
    <a class="btn btn-outline-secondary" href="{{ route('backend.floors.index') }}">Cancelar</a>
</div>
