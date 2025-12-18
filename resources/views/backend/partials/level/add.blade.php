<div class="modal fade" id="kt_modal_edit_level" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered mw-600px">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Level</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"></i>
                </div>
            </div>

            <div class="modal-body">
                <form id="editLevelForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <input type="hidden" id="edit_id">

                    <div class="mb-5">
                        <label class="form-label">Level Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name">
                    </div>

                    <div class="mb-5">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" id="edit_slug" name="slug">
                    </div>

                    <div class="mb-5">
                        <label class="form-label">Required XP</label>
                        <input type="number" class="form-control" id="edit_required_xp" name="required_xp">
                    </div>

                    <div class="mb-5">
                        <label class="form-label">Daily ROI (%)</label>
                        <input type="number" step="0.01" class="form-control" id="edit_daily_roi" name="daily_roi">
                    </div>

                    <div class="mb-5">
                        <label class="form-label">Icon</label>
                        <input type="file" class="form-control" name="icon">
                        <img id="edit_icon_preview" class="mt-3" width="80">
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                        <label class="form-check-label">Active</label>
                    </div>

                    <div class="form-check mb-5">
                        <input class="form-check-input" type="checkbox" id="edit_is_coming_soon" name="is_coming_soon">
                        <label class="form-check-label">Coming Soon</label>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
