@extends('backend.app')

@section('title', 'ROI Logs')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
    <div class="content fs-6 d-flex flex-column-fluid">
        <div class="container-xxl">

            <div class="card mb-6">
                <div class="card-body">
                    <h3 class="fw-bold">
                        ROI Logs – Investment #{{ $investment->id }}
                    </h3>
                    <span class="text-muted">
                    {{ $investment->user->first_name }} {{ $investment->user->last_name }}
                    ({{ $investment->user->email }})
                </span>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <table class="table align-middle table-row-dashed fs-6 gy-5"
                           id="kt_table_roi_logs">
                        <thead>
                        <tr class="text-muted fw-bold fs-7 text-uppercase">
                            <th>Date</th>
                            <th>Profit</th>
                            <th>Status</th>
                            <th>Credited At</th>
                        </tr>
                        </thead>
                    </table>
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
            $('#kt_table_roi_logs').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('roi.logs.data', $investment->id) }}",
                columns: [
                    { data: 'date', name: 'date' },
                    { data: 'profit', name: 'profit' },
                    { data: 'status', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' }
                ]
            });
        });
    </script>
@endpush
