<div class="fv-row flex-row-fluid">
    <div class="card card-flush py-4">
        <!--begin::Card header-->
        <div class="card-header">
            <div class="card-title">
                <h2>{{ $title }}</h2>
            </div>
        </div>

        <!--begin::Card body-->
        <div class="card-body text-center pt-0">
            <div class="image-input image-input-empty image-input-outline image-input-placeholder mb-3" data-kt-image-input="true">
                <div class="image-input-wrapper w-150px h-150px" style="background-image: url({{ $imageUrl }})"></div>

                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                       data-kt-image-input-action="change" data-bs-toggle="tooltip" aria-label="Change {{ $name }}"
                       data-bs-original-title="Change {{ $name }}">
                    <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                    <input type="file" name="{{ $name }}" accept=".png, .jpg, .jpeg">
                    <input type="hidden" name="{{ $name }}_remove">
                </label>

                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                      data-kt-image-input-action="cancel" data-bs-toggle="tooltip" aria-label="Cancel"
                      data-bs-original-title="Cancel">
                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                </span>

                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                      data-kt-image-input-action="remove" data-bs-toggle="tooltip" aria-label="Remove"
                      data-bs-original-title="Remove">
                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                </span>
            </div>

            <div class="text-muted fs-7">
                {{ $description }}
            </div>
        </div>
    </div>
</div>
