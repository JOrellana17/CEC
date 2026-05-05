@csrf
@if(isset($roomType))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-12 col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name', $roomType->name ?? '') }}" required></div>
    <div class="col-12 col-md-3"><label class="form-label">Base price</label><input type="number" step="0.01" class="form-control" name="base_price" value="{{ old('base_price', $roomType->base_price ?? '') }}" required></div>
    <div class="col-6 col-md-3"><label class="form-label">Bed type</label><input class="form-control" name="bed_type" value="{{ old('bed_type', $roomType->bed_type ?? '') }}"></div>
    <div class="col-6 col-md-3"><label class="form-label">Capacity</label><input type="number" class="form-control" name="capacity" value="{{ old('capacity', $roomType->capacity ?? 2) }}" required></div>
    <div class="col-6 col-md-3"><label class="form-label">Max capacity</label><input type="number" class="form-control" name="max_capacity" value="{{ old('max_capacity', $roomType->max_capacity ?? 4) }}" required></div>
    <div class="col-6 col-md-3"><label class="form-label">Room size</label><input type="number" class="form-control" name="room_size" value="{{ old('room_size', $roomType->room_size ?? '') }}"></div>
    <div class="col-12 col-md-3 d-flex align-items-end">
        <div class="form-check"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="rt_active" @checked(old('is_active', $roomType->is_active ?? true))><label class="form-check-label" for="rt_active">Active</label></div>
    </div>
    <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3">{{ old('description', $roomType->description ?? '') }}</textarea></div>
    <div class="col-12"><label class="form-label">Amenities</label><input class="form-control" name="amenities[]" value="{{ old('amenities.0', isset($roomType) ? implode(', ', $roomType->amenities ?? []) : '') }}" placeholder="WiFi, TV, Desk"></div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary" data-loading>Save</button>
    <a class="btn btn-outline-secondary" href="{{ route('backend.room-types.index') }}">Cancel</a>
</div>
