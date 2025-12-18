@extends('backend.app')

@section('title', 'Edit level data')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')

    <!--begin::Content-->
    <div class="content fs-6 d-flex flex-column flex-column-fluid" id="kt_content">

        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <div class="container-fluid d-flex flex-stack flex-wrap flex-sm-nowrap">

                <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                    <h1 class="text-dark fw-bold my-1 fs-2">Level data edit</h1>

                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>

                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('level.index') }}" class="text-muted text-hover-primary">Levels</a>
                        </li>

                        <li class="breadcrumb-item text-dark">Level data edit</li>
                    </ul>

                </div>

            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Post-->
        <div class="post fs-6 d-flex flex-column-fluid" id="kt_post">
            @include('backend.partials.level.edit_main')
        </div>
        <!--end::Post-->

        @endsection


        =
        @push('script')
            <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>

            <script>
                $(document).ready(function () {

                    /* -------------------------------------
                        SHOW/HIDE DURATION BASED ON TASK TYPE
                    -------------------------------------- */
                    function toggleTaskTypeFields() {
                        let type = $('#task_type').val();
                        $('#duration_wrapper').toggle(type === 'watch_ad');
                    }

                    /* -------------------------------------
                        SHOW/HIDE MEDIA FIELDS
                    -------------------------------------- */
                    function toggleMediaFields() {
                        let type = $('#media_type').val();

                        $('#media_file_wrapper').hide();
                        $('#media_url_wrapper').hide();

                        if (type === 'video' || type === 'image') {
                            $('#media_file_wrapper').show();
                        }

                        if (type === 'url') {
                            $('#media_url_wrapper').show();
                        }
                    }

                    // INITIAL LOAD
                    toggleTaskTypeFields();
                    toggleMediaFields();

                    // ON CHANGE
                    $('#task_type').on('change', toggleTaskTypeFields);
                    $('#media_type').on('change', toggleMediaFields);



                    /* -------------------------------------
                        AJAX SUBMIT
                    -------------------------------------- */
                    $('#create-task-form').on('submit', function (e) {
                        e.preventDefault();

                        let formData = new FormData(this);

                        // ADD Dropzone file manually
                        if (dz.getAcceptedFiles().length > 0) {
                            let file = dz.getAcceptedFiles()[0];
                            formData.append("media", file, file.name);
                        }

                        $.ajax({
                            url: "{{ route('tasks.store') }}",
                            type: "POST",
                            data: formData,
                            processData: false,
                            contentType: false,

                            success: function (response) {
                                toastr.success("Task created successfully!");

                                // RESET FORM
                                $('#create-task-form')[0].reset();

                                // RESET DROPZONE
                                dz.removeAllFiles(true);

                                // RESET DYNAMIC FIELDS
                                toggleTaskTypeFields();
                                toggleMediaFields();

                                // optional: scroll to top
                                // $('html, body').animate({ scrollTop: 0 }, 'fast');
                            },
                            error: function (xhr) {
                                let errors = xhr.responseJSON?.errors || {};
                                let msg = "";

                                Object.keys(errors).forEach(key => {
                                    msg += errors[key][0] + "<br>";
                                });

                                toastr.error(msg || "Something went wrong!");
                            }
                        });
                    });

                });

                /* -------------------------------------
                INIT DROPZONE
            -------------------------------------- */
                Dropzone.autoDiscover = false;

                let dz = new Dropzone("#kt_ecommerce_add_product_media", {
                    url: "#",                   // Not used (manual upload via AJAX)
                    autoProcessQueue: false,
                    maxFiles: 1,
                    maxFilesize: 100,           // MB
                    acceptedFiles: "image/*,video/*",
                    addRemoveLinks: true,
                    previewsContainer: "#kt_ecommerce_add_product_media",
                    clickable: "#kt_ecommerce_add_product_media .dz-message",
                    dictDefaultMessage: ""
                });

                dz.on("addedfile", function () {
                    if (dz.files[1]) dz.removeFile(dz.files[0]); // allow only 1 file
                });
            </script>
    @endpush


