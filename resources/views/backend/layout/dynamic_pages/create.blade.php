
@extends('backend.app')

@section('title', 'Dynamic Page Create')

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
                        Dynamic Page </h1>
                    <!--end::Title-->

                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home </a>
                        </li>

                        <li class="breadcrumb-item text-dark">
                            Dynamic Page Create
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
            @include('backend.partials.dynamic_pages.create_main')
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


                });
            </script>
    @endpush
@endsection
