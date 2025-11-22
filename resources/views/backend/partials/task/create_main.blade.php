<div class="container-xxl">
    <div class="card">

        <div class="card-header border-0 pt-6">
            <div class="card-toolbar">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('tasks.index') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-entrance-right fs-2"></i>
                        List of Tasks
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body py-4">

            <form id="create-task-form" method="POST" enctype="multipart/form-data"
                  class="form fv-plugins-bootstrap5 fv-plugins-framework">

                @csrf

                <div class="d-flex flex-column scroll-y px-5 px-lg-10">

                    <div class="fv-row mb-6">
                        <label class="required form-label">Title</label>
                        <input type="text" name="title" class="form-control form-control-solid" placeholder="Enter title">
                    </div>

                    <div class="fv-row mb-6">
                        <label class="required form-label">Task Type</label>
                        <select name="type" id="task_type" class="form-control form-control-solid">
                            @foreach($taskTypes as $type)
                                <option value="{{ $type->value }}">{{ ucfirst(str_replace('_',' ', $type->value)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="fv-row mb-6" id="duration_wrapper" style="display:none;">
                        <label class="required form-label">Duration (Seconds)</label>
                        <input type="number" name="duration" class="form-control form-control-solid" placeholder="e.g. 30">
                    </div>

                    <div class="fv-row mb-6">
                        <label class="required form-label">XP Reward</label>
                        <input type="number" name="xp" class="form-control form-control-solid" placeholder="e.g. 5">
                    </div>

                    <div class="fv-row mb-6">
                        <label class="required form-label">Media Type</label>
                        <select name="media_type" id="media_type" class="form-control form-control-solid">
                            @foreach($mediaTypes as $media)
                                <option value="{{ $media->value }}">{{ ucfirst($media->value) }}</option>
                            @endforeach
                        </select>
                    </div>

{{--                    <div class="fv-row mb-6" id="media_file_wrapper" style="display:none;">--}}
{{--                        <label class="form-label">Upload Media</label>--}}
{{--                        <input type="file" name="media" class="form-control form-control-solid">--}}
{{--                    </div>--}}



                    <div class="mb-5" id="media_file_wrapper" style="display:none;">
                        <label class="form-label required">Media</label>

                        <div class="fv-row mb-2">
                            <!-- keep same id so existing CSS/JS works -->
                            <div class="dropzone dz-clickable craft-dz" id="kt_ecommerce_add_product_media">
                                <div class="dz-message needsclick d-flex align-items-center gap-3">
                                    <!-- Icon (Keenthemes icon ok) -->
                                    <i class="ki-duotone ki-file-up text-primary fs-3x">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>

                                    <div class="ms-2">
                                        <h3 class="fs-5 fw-bold text-gray-900 mb-1">Drop files here or click to upload.</h3>
                                        <span class="fs-7 fw-semibold text-gray-500">Upload only 1 image or video</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-muted fs-7">Set the product media.</div>
                    </div>


                    <div class="fv-row mb-6" id="media_url_wrapper" style="display:none;">
                        <label class="form-label">Media URL</label>
                        <input type="text" name="media_url" class="form-control form-control-solid"
                               placeholder="https://example.com/file.mp4">
                    </div>

                </div>

                <div class="text-center pt-10">
                    <button type="reset" class="btn btn-light me-3">Discard</button>

                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <span class="indicator-label">Submit</span>
                        <span class="indicator-progress">Please wait...
                            <span class="spinner-border spinner-border-sm ms-2"></span>
                        </span>
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>








