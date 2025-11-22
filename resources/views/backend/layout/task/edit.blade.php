@extends('backend.app')

@section('title', 'Edit Task')

@push('style')
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css">
@endpush

@section('content')

    <div class="content fs-6 d-flex flex-column flex-column-fluid">
        <div class="toolbar" id="kt_toolbar">
            <div class="container-fluid d-flex flex-stack flex-wrap flex-sm-nowrap">
                <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                    <h1 class="text-dark fw-bold my-1 fs-2">Edit Task</h1>

                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>

                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('tasks.index') }}" class="text-muted text-hover-primary">Tasks</a>
                        </li>

                        <li class="breadcrumb-item text-dark">Edit Task</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="post fs-6 d-flex flex-column-fluid">
            @include('backend.partials.task.edit_main')
        </div>
    </div>

@endsection
@push('script')
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>

    <script>
        $(document).ready(function () {

            // CLICK EXISTING MEDIA → OPEN FULLSCREEN PREVIEW
            $('.existing-media-preview').on('click', function () {
                let type = $(this).data('type');
                let src  = $(this).data('src');

                let html = "";

                if (type === "image") {
                    html = `<img src="${src}" style="max-width:90%; max-height:90%;">`;
                }

                if (type === "video") {
                    html = `<video src="${src}" controls autoplay
                     style="max-width:90%; max-height:90%;"></video>`;
                }

                $('#previewContent').html(html);
                $('#mediaPreviewModal').fadeIn(200);
            });

            // CLOSE MODAL
            $('#closePreview, #mediaPreviewModal').on('click', function (e) {
                if (e.target.id === 'mediaPreviewModal' || e.target.id === 'closePreview') {
                    $('#mediaPreviewModal').fadeOut(200);
                    $('#previewContent').html("");
                }
            });



            function toggleTaskFields() {
                $('#duration_wrapper').toggle($('#task_type').val() === 'watch_ad');
            }

            function toggleMediaFields() {
                let type = $('#media_type').val();

                $('#media_file_wrapper').hide();
                $('#media_url_wrapper').hide();

                if (['video','image'].includes(type)) {
                    $('#media_file_wrapper').show();
                }

                if (type === 'url') {
                    $('#media_url_wrapper').show();
                }
            }

            toggleTaskFields();
            toggleMediaFields();

            $('#task_type').on('change', toggleTaskFields);
            $('#media_type').on('change', toggleMediaFields);


            // UPDATE AJAX
            $('#update-task-form').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                // ADD CSRF + METHOD
                formData.append('_token', "{{ csrf_token() }}");
                formData.append('_method', "PUT");

                // ADD Dropzone file
                if (dz.getAcceptedFiles().length > 0) {
                    let file = dz.getAcceptedFiles()[0];
                    formData.append("media", file, file.name);
                }

                $.ajax({
                    url: "{{ route('tasks.update', $task->id) }}",
                    type: "POST", // <-- keep POST because we send _method=PUT
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        toastr.success("Task updated successfully!");

                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    },
                    error: function(xhr) {
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

        // DROPZONE
        Dropzone.autoDiscover = false;
        let dz = new Dropzone("#kt_edit_media_dropzone", {
            url: "#",                   // Not used (manual upload via AJAX)
            autoProcessQueue: false,
            maxFiles: 1,
            maxFilesize: 100,           // MB
            acceptedFiles: "image/*,video/*",
            addRemoveLinks: true,
            previewsContainer: "#kt_edit_media_dropzone",
            clickable: "#kt_edit_media_dropzone .dz-message",
            dictDefaultMessage: ""
        });

        dz.on("addedfile", function () {
            if (dz.files[1]) dz.removeFile(dz.files[0]);
        });
    </script>
@endpush
