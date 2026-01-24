@extends('backend.app')

@section('title', 'List of Package')

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
                        List of Support Tickets </h1>
                    <!--end::Title-->

                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home </a>
                        </li>

                        <li class="breadcrumb-item text-dark">
                            Support Tickets
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
            @include('backend.partials.support_ticket.index_main')
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
                $(function () {
                    const table = $('#package_table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: "{{ route('support-tickets.index') }}",
                        columns: [
                            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                            { data: 'ticket_no', name: 'ticket_no' },
                            { data: 'user', name: 'user' },
                            { data: 'subject', name: 'subject' },
                            { data: 'category', name: 'category' },
                            { data: 'priority', name: 'priority', orderable: false },
                            { data: 'status', name: 'status', orderable: false },
                            { data: 'assigned', name: 'assigned', orderable: false },
                            { data: 'updated_at', name: 'updated_at' },
                            { data: 'attention', orderable: false, searchable: false },
                            { data: 'action', orderable: false, searchable: false },
                        ],
                        order: [[8, 'desc']] // order by updated_at
                    });

                    $('#packageSearch').on('keyup', function () {
                        table.search(this.value).draw();
                    });


                });


                /**
                 * Open edit modal
                 */
                $(document).on('click', '.edit-package', function () {
                    const id = $(this).data('id');

                    $.get("{{ route('package.edit', ':id') }}".replace(':id', id), function (res) {
                        if (!res.status) {
                            Swal.fire('Error', 'Failed to load data', 'error');
                            return;
                        }

                        $('#edit_package_id').val(id);
                        $('#edit_price').val(res.data.price);
                        $('#edit_duration').val(res.data.duration_days);

                        $('#editPackageModal').modal('show');
                    });
                });

                /**
                 * Update package
                 */
                $('#editPackageForm').on('submit', function (e) {
                    e.preventDefault();

                    const id = $('#edit_package_id').val();

                    $.ajax({
                        url: "{{ route('package.update', ':id') }}".replace(':id', id),
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: "PUT",
                            price: $('#edit_price').val(),
                            duration_days: $('#edit_duration').val(),
                        },
                        success: function (res) {
                            Swal.fire('Success', res.message, 'success');
                            $('#editPackageModal').modal('hide');
                            $('#package_table').DataTable().ajax.reload(null, false);
                        },
                        error: function (xhr) {
                            let msg = xhr.responseJSON?.message || 'Update failed';
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                });
            </script>
    @endpush
@endsection
