@extends('backend.app')

@section('title', 'List of Transactions')

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
                        List of Transactions </h1>
                    <!--end::Title-->

                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home </a>
                        </li>

                        <li class="breadcrumb-item text-dark">
                            Transactions
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
            @include('backend.partials.transaction.index_main')
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
                        ajax: {
                            url: "{{ route('transaction.index') }}",
                            data: function (d) {
                                d.status     = $('#filterStatus').val();
                                d.type       = $('#filterType').val();
                                d.source     = $('#filterSource').val();
                                d.start_date = $('#startDate').val();
                                d.end_date   = $('#endDate').val();
                            }
                        },
                        columns: [
                            { data: 'from', name: 'from_user_id' },
                            { data: 'to', name: 'to_user_id' },
                            { data: 'amount', name: 'amount' },
                            { data: 'type', name: 'type' },
                            { data: 'source', name: 'source' },
                            { data: 'status', name: 'status' },
                            { data: 'date', name: 'created_at' },
                            { data: 'action', orderable: false, searchable: false },
                        ]
                    });

                    $('#packageSearch').on('keyup', function () {
                        table.search(this.value).draw();
                    });
                    // Filters
                    $('#filterStatus, #filterType, #filterSource, #startDate, #endDate')
                        .on('change', function () {
                            table.draw();
                        });

                    // Today
                    $('#todayBtn').on('click', function () {
                        const today = new Date().toISOString().slice(0,10);
                        $('#startDate').val(today);
                        $('#endDate').val(today);
                        table.draw();
                    });

                    // This Month
                    $('#monthBtn').on('click', function () {
                        const now = new Date();
                        const first = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0,10);
                        const last  = new Date(now.getFullYear(), now.getMonth()+1, 0).toISOString().slice(0,10);

                        $('#startDate').val(first);
                        $('#endDate').val(last);
                        table.draw();
                    });

                    // Reset
                    $('#resetFilters').on('click', function () {
                        $('#filterStatus, #filterType, #filterSource').val('');
                        $('#startDate, #endDate, #packageSearch').val('');
                        table.search('').draw();
                    });

                    $('#exportCsv').on('click', function () {
                        const params = $.param({
                            status: $('#filterStatus').val(),
                            type: $('#filterType').val(),
                            source: $('#filterSource').val(),
                            start_date: $('#startDate').val(),
                            end_date: $('#endDate').val(),
                        });

                        window.location.href = "{{ route('transaction.export') }}?" + params;
                    });


                    // Show transaction modal
                    $(document).on('click', '.show-transaction', function () {
                        const id = $(this).data('id');
                        const url = "{{ route('transaction.show', ':id') }}".replace(':id', id);
                        $.get(url, function (data) {
                            $('#trx_id').text(data.id);
                            $('#trx_from').text(data.from);
                            $('#trx_to').text(data.to);
                            $('#trx_amount').text('$' + data.amount);
                            $('#trx_type').text(data.type);
                            $('#trx_source').text(data.source);
                            $('#trx_status').text(data.status);
                            $('#trx_date').text(data.date);

                            $('#transactionModal').modal('show');
                        });
                    });

                });
            </script>

    @endpush
@endsection
