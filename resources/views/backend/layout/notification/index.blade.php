@extends('backend.app')

@section('title', 'Notifications')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .dataTables_filter { display: none !important; }
        .unread-row { background: #fff8e1; }
    </style>
@endpush

@section('content')
    <div class="content fs-6 d-flex flex-column-fluid">
        <div class="container-xxl">

            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title d-flex gap-3 align-items-center">

                        <!-- 🔍 Search -->
                        <input type="text"
                               id="notificationSearch"
                               class="form-control form-control-solid w-250px"
                               placeholder="Search notification">

                        <!-- 🔽 Status Filter -->
                        <select id="statusFilter"
                                class="form-select form-select-solid w-150px">
                            <option value="">All</option>
                            <option value="read">Read</option>
                            <option value="unread">Unread</option>
                        </select>

                    </div>
                </div>

                <div class="card-body py-4">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5"
                               id="notification_table">
                            <thead>
                            <tr class="text-muted fw-bold fs-7 text-uppercase">
                                <th>Title</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Time</th>
                                <th class="text-end">Actions</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
@push('script')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(function () {

            let table = $('#notification_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('notification.all') }}",
                    data: function (d) {
                        //  Status filter → column index 2 (status)
                        d.columns[2].search.value = $('#statusFilter').val();
                    }
                },
                columns: [
                    { data: 'title', name: 'title' },
                    { data: 'message', name: 'message' },
                    { data: 'status', name: 'status' },
                    { data: 'time', name: 'created_at' },
                    { data: 'actions', orderable: false, searchable: false },
                ],

                //  Highlight unread rows
                rowCallback: function (row, data) {
                    if (data.status.includes('Unread')) {
                        $(row).addClass('unread-row');
                    }
                }
            });

            /*  Text Search */
            $('#notificationSearch').on('keyup', function () {
                table.search(this.value).draw();
            });

            /*  Status Filter */
            $('#statusFilter').on('change', function () {
                table.draw();
            });

            /*  Mark Single Notification as Read */
            $(document).on('click', '.mark-read', function () {
                let id = $(this).data('id');

                $.ajax({
                    url: "{{ url('admin/notification/read') }}/" + id,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function () {
                        table.ajax.reload(null, false);
                    }
                });
            });

        });
    </script>
@endpush
