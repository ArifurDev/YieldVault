@extends('backend.app')

@section('title', 'Role list')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <style>
        .dataTables_filter {
            display: none !important;
        }
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
                        List of User </h1>
                    <!--end::Title-->

                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home </a>
                        </li>

                        <li class="breadcrumb-item text-dark">
                            Role
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
            @include('backend.partials.Role.index_main')
            <!--end::Post-->
        </div>
        <!--end::Content-->

        @push('script')
            <script src="{{ asset('backend/assets') . '/js/custom/apps/user-management/users/list/table.js' }}"></script>
            <script src="{{ asset('backend/assets') . '/js/custom/apps/user-management/users/list/export-users.js' }}"></script>
            <script src="{{ asset('backend/assets') . '/js/custom/apps/user-management/users/list/add.js' }}"></script>
            <script src="{{ asset('backend/assets') . '/js/custom/utilities/modals/users-search.js' }}"></script>

            {{--  Include DataTables --}}
            <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

            <script>
                // search
                $('input[data-kt-user-table-filter="search"]').on('keyup', function() {
                    $('#kt_table_users').DataTable().search(this.value).draw();
                });

                // fetch data
                $(document).ready(function() {
                    $('#kt_table_users').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ route('roles.index') }}",
                            type: "get",
                        },
                        columns: [
                            // {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                            {
                                data: 'name',
                                name: 'name'
                            },
                            {
                                data: 'permissions',
                                name: 'permissions'
                            },
                            {
                                data: 'actions',
                                name: 'actions',
                                orderable: false,
                                searchable: false
                            }
                        ],
                        drawCallback: function() {
                            if (typeof KTMenu !== 'undefined') {
                                KTMenu.createInstances();
                            }
                        }
                    });
                });


                // permissions
                $(document).ready(function () {
                    loadPermissions();

                    function loadPermissions() {
                        $.ajax({
                            url: "{{ route('permissions.index') }}",
                            type: "GET",
                            success: function (response) {
                                if (response.success) {

                                    let permissionsHtml = '';
                                    response.permissions.forEach(function (permission) {
                                        permissionsHtml += `
                                    <label class="inline-flex items-center mr-3">
                                        <input type="checkbox" name="permissions[]" value="${permission.id}" class="mr-2">
                                        ${permission.name}
                                    </label>
                                    <br>
                                `;
                                    });

                                    $("#permissions-container").html(permissionsHtml);
                                }
                            }
                        });
                    }
                });



                // Submit form via AJAX
                $('#add-role-form').on('submit', function (e) {
                    e.preventDefault();

                    const formData = $(this).serialize();

                    $.ajax({
                        url: "{{ route('role.store') }}",
                        type: "POST",
                        data: formData,
                        success: function (response) {
                            if (response.success) {
                                toastr.success("Role created successfully!");
                                $('#add-role-form')[0].reset();
                                $('#kt_modal_add_role').modal('hide'); // if using Bootstrap modal
                                $('#kt_table_users').DataTable().ajax.reload();
                            } else {
                                toastr.error(response.message || "Something went wrong!");
                            }
                        },
                        error: function (xhr) {
                            // Show Laravel validation errors
                            let errors = xhr.responseJSON.errors;
                            let errorMessages = "";
                            $.each(errors, function (key, value) {
                                errorMessages += value[0] + "\n";
                            });
                            toastr.error(errorMessages || "Something went wrong!");
                        }
                    })
                });
            </script>
    @endpush
@endsection
