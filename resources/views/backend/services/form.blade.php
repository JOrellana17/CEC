<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ $action }}">
            @csrf
            @if($method !== 'POST')
            @method($method)
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $service->name) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" required>
                        @foreach(['food' => 'Restaurant', 'laundry' => 'Laundry', 'room' => 'Room service', 'transport' => 'Transportation', 'beverage' => 'Mini bar', 'other' => 'Custom'] as $key => $label)
                        <option value="{{ $key }}" {{ old('category', $service->category) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Price</label>
                    <input type="number" step="0.01" min="0" name="price" class="form-control" required value="{{ old('price', $service->price) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Price Type</label>
                    <select name="price_type" class="form-select">
                        @foreach(['fixed', 'per_person', 'per_hour', 'per_unit'] as $type)
                        <option value="{{ $type }}" {{ old('price_type', $service->price_type ?? 'per_unit') === $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Unit</label>
                    <input type="text" name="unit" class="form-control" value="{{ old('unit', $service->unit) }}" placeholder="item, trip, load">
                </div>
                <div class="col-md-4 d-flex align-items-end gap-3">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('is_active', $service->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label">Active</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_available_24h" value="1" class="form-check-input" {{ old('is_available_24h', $service->is_available_24h) ? 'checked' : '' }}>
                        <label class="form-check-label">24h</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $service->description) }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary">Save Service</button>
                <a href="{{ route('backend.services.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
