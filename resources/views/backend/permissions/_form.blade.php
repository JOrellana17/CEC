@csrf
@if(isset($permission))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-12 col-md-6">
        <label class="form-label">Nombre</label>
        <input class="form-control" name="name" value="{{ old('name', $permission->name ?? '') }}" required>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Módulo</label>
        <input class="form-control" name="module" value="{{ old('module', $permission->module ?? '') }}" required>
    </div>
    <div class="col-12">
        <label class="form-label">Descripción</label>
        <textarea class="form-control" name="description" rows="3">{{ old('description', $permission->description ?? '') }}</textarea>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary" data-loading>Guardar</button>
    <a class="btn btn-outline-secondary" href="{{ route('backend.permissions.index') }}">Cancelar</a>
</div>
