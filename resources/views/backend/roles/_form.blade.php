@csrf
@if(isset($role))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-12 col-md-6">
        <label class="form-label">Nombre</label>
        <input class="form-control" name="name" value="{{ old('name', $role->name ?? '') }}" required>
    </div>
    <div class="col-12">
        <label class="form-label">Descripción</label>
        <textarea class="form-control" name="description" rows="3">{{ old('description', $role->description ?? '') }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label">Permisos</label>
        <div class="row g-2">
            @foreach($permissions->groupBy('module') as $module => $items)
                <div class="col-12 col-lg-4">
                    <div class="border rounded p-3 h-100">
                        <div class="fw-semibold mb-2">{{ Str::headline($module) }}</div>
                        @foreach($items as $permission)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm-{{ $permission->id }}"
                                    @checked(in_array($permission->name, old('permissions', isset($role) ? $role->permissions->pluck('name')->all() : [])))>
                                <label class="form-check-label" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary" data-loading>Guardar</button>
    <a class="btn btn-outline-secondary" href="{{ route('backend.roles.index') }}">Cancelar</a>
</div>
