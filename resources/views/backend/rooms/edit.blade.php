@extends('layouts.backend')

@section('title', 'Edit Room')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('backend.rooms.update', $room) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-12 col-md-4"><label class="form-label">Room number</label><input class="form-control" name="room_number" value="{{ old('room_number', $room->room_number) }}" required></div>
                <div class="col-12 col-md-4"><label class="form-label">Floor</label><select class="form-select" name="floor_id" required>@foreach($floors as $floor)<option value="{{ $floor->id }}" @selected(old('floor_id', $room->floor_id) == $floor->id)>Floor {{ $floor->number }} - {{ $floor->name }}</option>@endforeach</select></div>
                <div class="col-12 col-md-4"><label class="form-label">Room type</label><select class="form-select" name="room_type_id" required>@foreach($roomTypes as $type)<option value="{{ $type->id }}" @selected(old('room_type_id', $room->room_type_id) == $type->id)>{{ $type->name }}</option>@endforeach</select></div>
                <div class="col-12 col-md-3"><label class="form-label">Rate</label><input type="number" step="0.01" class="form-control" name="price_per_night" value="{{ old('price_per_night', $room->price_per_night) }}" required></div>
                <div class="col-12 col-md-3"><label class="form-label">Capacity</label><input type="number" class="form-control" name="capacity" value="{{ old('capacity', $room->capacity) }}" required></div>
                <div class="col-12 col-md-3"><label class="form-label">Status</label><select class="form-select" name="status">@foreach(['available','occupied','reserved','maintenance','blocked'] as $status)<option value="{{ $status }}" @selected(old('status', $room->status) === $status)>{{ Str::headline($status) }}</option>@endforeach</select></div>
                <div class="col-12 col-md-3"><label class="form-label">Cleaning</label><select class="form-select" name="room_status">@foreach(['clean','dirty','inspected'] as $status)<option value="{{ $status }}" @selected(old('room_status', $room->room_status) === $status)>{{ Str::headline($status) }}</option>@endforeach</select></div>
                <div class="col-12 col-md-6"><label class="form-label">Floor/Block</label><input class="form-control" name="floor" value="{{ old('floor', $room->floor) }}"></div>
                <div class="col-12 col-md-6"><label class="form-label">Building</label><input class="form-control" name="building" value="{{ old('building', $room->building) }}"></div>
                <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3">{{ old('description', $room->description) }}</textarea></div>
                <div class="col-12 d-flex gap-4">
                    <input type="hidden" name="is_active" value="0"><input type="hidden" name="is_smoking" value="0"><input type="hidden" name="has_balcony" value="0">
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="room_active" @checked(old('is_active', $room->is_active))><label class="form-check-label" for="room_active">Active</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="is_smoking" value="1" id="room_smoking" @checked(old('is_smoking', $room->is_smoking))><label class="form-check-label" for="room_smoking">Smoking</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="has_balcony" value="1" id="room_balcony" @checked(old('has_balcony', $room->has_balcony))><label class="form-check-label" for="room_balcony">Balcony</label></div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary" data-loading>Save</button>
                <a class="btn btn-outline-secondary" href="{{ route('backend.rooms.index') }}">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
