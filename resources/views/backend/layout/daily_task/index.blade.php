@extends('backend.app')

@section('title', 'List of Daily Tasks')

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
                        List of Daily Tasks </h1>
                    <!--end::Title-->

                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home </a>
                        </li>

                        <li class="breadcrumb-item text-dark">
                            Daily Tasks
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
            @include('backend.partials.daily_task.index_main')
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
                $('input[data-kt-user-table-filter="search"]').on('keyup', function() {
                    $('#kt_table_users').DataTable().search(this.value).draw();
                });

                $(document).ready(function() {
                    $('#kt_table_users').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ route('daily.task.index') }}",
                            type: "get",
                        },
                        columns: [
                            // {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                            { data: 'title', name: 'title' },
                            { data: 'type', name: 'type' },
                            { data: 'date', name: 'date' },
                            { data: 'sort_order', name: 'sort_order' },
                            { data: 'status', name: 'status' },
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


                // remove
                $(document).on('click', '.delete-kyc-btn', function(e) {
                    e.preventDefault();
                    let ID = $(this).data('id');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This action cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let deleteUrl = "{{ route('daily.tasks.assign.remove', ['id' => '___id___']) }}".replace('___id___', ID);
                            $.ajax({
                                url: deleteUrl,
                                type: "DELETE",
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    Swal.fire(
                                        'Deleted!',
                                        response.message,
                                        'success'
                                    );
                                    $('#kt_table_users').DataTable().ajax.reload();
                                },
                                error: function(xhr) {
                                    Swal.fire(
                                        'Error!',
                                        xhr.responseJSON?.message || 'Something went wrong.',
                                        'error'
                                    );
                                }
                            });
                        }
                    });
                });


                //Convert row to editable form
                $(document).on("click", ".edit-row", function () {
                    let id = $(this).data("id");
                    let row = $(this).closest("tr");

                    let date = row.find("td:eq(2)").text().trim();
                    let status = row.find("td:eq(3)").text().trim() === "Active" ? 1 : 0;

                    row.find("td:eq(2)").html(`<input type="date" class="form-control edit-date" value="${date}">`);
                    row.find("td:eq(3)").html(`
                        <select class="form-control edit-status">
                            <option value="1" ${status==1?'selected':''}>Active</option>
                            <option value="0" ${status==0?'selected':''}>Inactive</option>
                        </select>
                    `);

                                    row.find("td:eq(4)").html(`
                        <button class="btn btn-sm btn-success save-row" data-id="${id}">Save</button>
                        <button class="btn btn-sm btn-secondary cancel-row" data-id="${id}">Cancel</button>
                    `);
                });

                // ========== SAVE ROW ==========
                $(document).on("click", ".save-row", function () {

                    let id = $(this).data("id");
                    let row = $(this).closest("tr");

                    let date = row.find(".edit-date").val();
                    let status = row.find(".edit-status").val();
                    let updateUrl = "{{ route('daily.tasks.assign.update', ['id' => '___id___']) }}".replace('___id___', id);
                    $.ajax({
                        url: updateUrl,
                        type: "POST",
                        data: {
                            _method: "PUT",
                            _token: "{{ csrf_token() }}",
                            date: date,
                            is_active: status
                        },
                        success: function(res){
                            toastr.success("Task updated!");

                            $("#kt_table_users").DataTable().ajax.reload(null, false);
                        },
                        error:function(){
                            toastr.error("Update failed!");
                        }
                    });

                });

                // ========== CANCEL INLINE EDIT ==========
                $(document).on("click", ".cancel-row", function () {
                    $("#kt_table_users").DataTable().ajax.reload(null, false);
                });


            </script>
    @endpush
@endsection
