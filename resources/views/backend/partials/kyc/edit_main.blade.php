<div class=" container-xxl " data-select2-id="select2-data-198-e71y">
    <!--begin::Card-->
    <div class="card" data-select2-id="select2-data-197-m5it">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6" data-select2-id="select2-data-196-qcil">
            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base"
                     data-select2-id="select2-data-195-9exk">

                    <!--begin::Add user-->
                    <a href="{{ route('kyc.index') }}" class="btn btn-primary">

                        <i class="ki-duotone ki-entrance-right fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>

                        List of KYC Documents
                    </a>
                    <!--end::Add user-->
                </div>
                <!--end::Toolbar-->
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body py-4">

            <!-- USER INFO -->
            <div class="mb-10">
                <h4>User Information</h4>
                <p><strong>Name:</strong> {{ $kyc->user->first_name }} {{ $kyc->user->last_name }}</p>
                <p><strong>KYC Status:</strong> {{ ucfirst($kyc->status) }}</p>
            </div>

            <!-- DOCUMENT FILES -->
            <h4 class="mb-4">Document Files</h4>

            <div class="row">

                <!-- FRONT SIDE -->
                <div class="{{ $kyc->document_type === 'passport' ? 'col-12' : 'col-md-6' }}">
                    <label class="fw-bold">Front Side</label>

                    <img src="{{ $kyc->file_side_1_path }}"
                         class="kyc-image"
                         alt="front">
                </div>

                @if($kyc->document_type !== 'passport')
                    <!-- BACK SIDE -->
                    <div class="col-md-6">
                        <label class="fw-bold">Back Side</label>

                        <img src="{{ $kyc->file_side_2_path }}"
                             class="kyc-image"
                             alt="back">
                    </div>
                @endif

            </div>


            <!-- UPDATE FORM -->
            <form action="{{ route('kyc.update', $kyc->id) }}" method="POST" class="mt-10">
                @csrf
                @method('PATCH')

                <div class="mb-6">
                    <label class="form-label">Status</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="">--- Select Status ---</option>
                        <option value="Approved" {{ $kyc->status == 'Approved' ? 'selected' : '' }}>Approved</option>
                        <option value="Rejected" {{ $kyc->status == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="Pending" {{ $kyc->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>

                <!-- Rejection Reason -->
                <div class="mb-6" id="reasonSection" style="display: {{ $kyc->status == 'rejected' ? 'block' : 'none' }}">
                    <label class="form-label">Rejection Reason</label>
                    <textarea name="rejection_reason" class="form-control" rows="3">{{ $kyc->rejection_reason }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">Update KYC</button>
            </form>


        </div>
        <!--end::Card body-->
        <!-- BIG BOX LIGHTBOX -->
        <div id="lightbox" class="image-lightbox" aria-hidden="true" role="dialog" aria-modal="true">
            <div class="image-lightbox-content" role="document">
                <button class="image-lightbox-close" aria-label="Close (Esc)">&times;</button>
                <img id="lightbox-img" src="" alt="KYC preview">
            </div>
        </div>
    </div>
    <!--end::Card-->


</div>


