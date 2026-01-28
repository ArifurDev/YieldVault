@extends('backend.app')

@section('title', 'Payment Reports')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .dataTables_filter {
            display: none !important;
        }
    </style>
@endpush

@section('content')
    <div class="content fs-6 d-flex flex-column flex-column-fluid" id="kt_content">

        {{-- Toolbar --}}
        <div class="toolbar" id="kt_toolbar">
            <div class="container-fluid d-flex flex-stack flex-wrap">
                <div class="d-flex flex-column">
                    <h1 class="fw-bold fs-2">Payment Reports</h1>

                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>
                        <li class="breadcrumb-item text-dark">Payment Reports</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="post fs-6 d-flex flex-column-fluid" id="kt_post">
            @include('backend.partials.payment_reports.index_main')
        </div>

    </div>

    @push('script')
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

        <script>
            $(function () {
                const table = $('#payment_report_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('payment-reports.index') }}",
                    columns: [
                        { data: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'report_id', name: 'id' },
                        { data: 'ticket_no', name: 'ticket_id' },
                        { data: 'user', name: 'user' },
                        { data: 'category', name: 'category' },
                        { data: 'issue_type', name: 'issue_type' },
                        { data: 'amount', name: 'amount', orderable: false },
                        { data: 'manager', name: 'manager', orderable: false },
                        { data: 'status', name: 'status', orderable: false },
                        { data: 'created_at', name: 'created_at' },
                        { data: 'action', orderable: false, searchable: false },
                    ],
                    order: [[9, 'desc']]
                });

                $('#paymentReportSearch').on('keyup', function () {
                    table.search(this.value).draw();
                });
            });
        </script>
    @endpush
@endsection
