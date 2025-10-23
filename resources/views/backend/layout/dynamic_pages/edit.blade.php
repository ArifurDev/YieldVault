@extends('backend.app')

@section('title', 'Dynamic Page Edit')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.6/quill.snow.css">
    <style>
        .ql-editor {
            min-height: 200px;
        }
    </style>
@endpush

@section('content')
    <!--begin::Content-->
    <div class="content fs-6 d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <div class="container-fluid d-flex flex-stack flex-wrap flex-sm-nowrap">
                <!--begin::Info-->
                <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                    <!--begin::Title-->
                    <h1 class="text-dark fw-bold my-1 fs-2">Dynamic Page</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>
                        <li class="breadcrumb-item text-dark">Dynamic Page Edit</li>
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
            @include('backend.partials.dynamic_pages.edit_main')
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>
    <!--end::Content-->

    @push('script')
        <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
        <script>
            $(document).ready(function () {
                // Initialize Quill editor for Edit Page Modal
                let quillEdit = new Quill('#kt_edit_page_content', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, false] }],
                            ['bold', 'italic', 'underline'],
                            ['image', 'code-block']
                        ]
                    },
                    placeholder: 'Type your content here...'
                });

                // Pre-populate Quill editor with existing content
                quillEdit.root.innerHTML = `{!! $page->content ?? '' !!}`;

                // Sync Quill content to hidden input
                quillEdit.on('text-change', function () {
                    document.getElementById('edit_page_content_hidden').value = quillEdit.root.innerHTML;
                });

                // Edit Page Form Submission
                $('#edit-page-form').on('submit', function (e) {
                    e.preventDefault();

                    var formData = new FormData(this);

                    $.ajax({
                        url: '{{ route('dynamic-pages.update', $page->id) }}',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.success) {
                                toastr.success(response.message);
                                $('#edit-page-form')[0].reset();
                                if (typeof quillEdit !== 'undefined' && quillEdit) {
                                    quillEdit.root.innerHTML = response.page.content || '';
                                }
                                // Optionally close the modal or refresh the page list
                                $('#kt_modal_edit_page').modal('hide');
                                if ($.fn.DataTable.isDataTable('#kt_table_pages')) {
                                    $('#kt_table_pages').DataTable().ajax.reload();
                                }
                            } else {
                                toastr.error(response.message || 'Update failed.');
                            }
                        },
                        error: function (xhr) {
                            let errors = xhr.responseJSON?.errors || {};
                            let errorMsg = xhr.responseJSON?.message || 'An error occurred.';
                            if (Object.keys(errors).length > 0) {
                                errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += `${value[0]}\n`;
                                });
                            }
                            toastr.error(errorMsg);
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
