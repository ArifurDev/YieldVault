<div class="container-xxl">
    <div class="card">
        <div class="card-body">

            <form id="edit-level-form"
                  action="{{ route('level.update', $level->id) }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf
                @method('PUT')

                <div class="fv-row mb-6">
                    <label class="required form-label">Level Name</label>
                    <input type="text" name="name"
                           class="form-control form-control-solid"
                           value="{{ $level->name }}">
                </div>

                <div class="fv-row mb-6">
                    <label class="required form-label">Slug</label>
                    <input type="text" name="slug"
                           class="form-control form-control-solid"
                           value="{{ $level->slug }}">
                </div>

                <div class="fv-row mb-6">
                    <label class="required form-label">Required XP</label>
                    <input type="number" name="required_xp"
                           class="form-control form-control-solid"
                           value="{{ $level->required_xp }}">
                </div>

                <div class="fv-row mb-6">
                    <label class="required form-label">Daily ROI (%)</label>
                    <input type="number" step="0.01" name="daily_roi"
                           class="form-control form-control-solid"
                           value="{{ $level->daily_roi }}">
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox"
                           name="is_active" value="1"
                        {{ $level->is_active ? 'checked' : '' }}>
                    <label class="form-check-label">Active</label>
                </div>

                <div class="form-check form-switch mb-6">
                    <input class="form-check-input" type="checkbox"
                           name="is_coming_soon" value="1"
                        {{ $level->is_coming_soon ? 'checked' : '' }}>
                    <label class="form-check-label">Coming Soon</label>
                </div>

                <div class="text-end">
                    <a href="{{ route('level.index') }}" class="btn btn-light me-3">Back</a>
                    <button type="submit" class="btn btn-primary">Update Level</button>
                </div>

            </form>

        </div>
    </div>
</div>
