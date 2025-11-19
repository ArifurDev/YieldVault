
@extends('backend.app')

@section('title', 'Task Create')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.6/quill.snow.css">
    <style>

    </style>
@endpush

@section('content')
    <!--begin::Content-->
    <div class="content fs-6 d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <div class=" container-fluid  d-flex flex-stack flex-wrap flex-sm-nowrap">
                <!--begin::Info-->
                <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                    <!--begin::Title-->
                    <h1 class="text-dark fw-bold my-1 fs-2">
                        Task Create </h1>
                    <!--end::Title-->

                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home </a>
                        </li>
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('tasks.index') }}" class="text-muted text-hover-primary">Tasks </a>
                        </li>

                        <li class="breadcrumb-item text-dark">
                            Task Create
                        </li>

                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Info-->

            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Post-->
        <div class="post fs-6 d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            @include('backend.partials.task.create_main')
            <!--end::Post-->
        </div>
        <!--end::Content-->

        @push('script')
            <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
            <script>
                // DataTables initialization
                $(document).ready(function() {

                    // Initialize Quill editor for Add Page Modal
                    let quillAdd = new Quill('#kt_add_page_content', {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                [{ 'header': [1, 2, false] }],
                                ['bold', 'italic', 'underline'],
                            ]
                        },
                        placeholder: 'Type your content here...'
                    });

                    // Sync Quill content to hidden input
                    quillAdd.on('text-change', function() {
                        document.getElementById('add_page_content_hidden').value = quillAdd.root.innerHTML;
                    });

                    // Add Page
                    $('#add-page-form').on('submit', function(e) {
                        e.preventDefault();
                        var formData = new FormData(this);

                        $.ajax({
                            url: '{{ route('dynamic-pages.store') }}',
                            type: 'POST',
                            data: formData,
                            contentType: false,
                            processData: false,
                            success: function(response) {
                                if (response.success) {
                                    toastr.success(response.message);
                                    $('#add-page-form')[0].reset();
                                    $('#kt_modal_add_page').modal('hide');
                                    $('#kt_table_users').DataTable().ajax.reload();
                                } else {
                                    toastr.error(response.message);
                                }
                            },
                            error: function(xhr) {
                                let errors = xhr.responseJSON.errors || {};
                                let errorMsg = '';
                                $.each(errors, function(key, value) {
                                    errorMsg += value[0] + '\n';
                                });
                                toastr.error(errorMsg || 'Something went wrong!');
                            }
                        });
                    });








                    /* -------------------------------------
                                            SHOW/HIDE DURATION BASED ON TASK TYPE
                                        -------------------------------------- */
                    function toggleTaskTypeFields() {
                        let type = $('#task_type').val();
                        if (type === 'watch_ad') {
                            $('#duration_wrapper').show();
                        } else {
                            $('#duration_wrapper').hide();
                        }
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
                });
            </script>
    @endpush
@endsection
