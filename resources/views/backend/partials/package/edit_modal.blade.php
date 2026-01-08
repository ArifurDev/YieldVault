<div class="modal fade" id="editPackageModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editPackageForm">
            @csrf
            @method('PUT')

            <input type="hidden" id="edit_package_id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Package</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number"
                               step="0.01"
                               class="form-control"
                               id="edit_price"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Duration (Days)</label>
                        <input type="number"
                               class="form-control"
                               id="edit_duration"
                               required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        Update
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
