@extends('backend.app')

@section('title', 'ROI List')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
    <div class="content fs-6 d-flex flex-column-fluid" id="kt_content">



        <div class="container-xxl">

            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"></i>
{{--                            <input type="text" data-kt-roi-table-filter="search"--}}
{{--                                   class="form-control form-control-solid w-250px ps-13"--}}
{{--                                   placeholder="Search user">--}}
                        </div>
                    </div>
                </div>

                <div class="card-body py-4">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5"
                               id="kt_table_roi">
                            <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th>User</th>
                                <th>Amount</th>
                                <th>ROI %</th>
                                <th>Earned</th>
                                <th>Expected</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600"></tbody>
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
        $(document).ready(function () {

            let table = $('#kt_table_roi').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('roi.list') }}",
                columns: [
                    { data: 'user', name: 'user' },
                    { data: 'amount', name: 'amount' },
                    { data: 'roi', name: 'roi_percent' },
                    { data: 'earned', name: 'earned_amount' },
                    { data: 'expected', name: 'expected', orderable: false },
                    { data: 'progress', orderable: false, searchable: false },
                    { data: 'status', name: 'status' },
                    { data: 'actions', orderable: false, searchable: false },
                ],
                drawCallback: function () {
                    if (typeof KTMenu !== 'undefined') {
                        KTMenu.createInstances();
                    }
                }
            });

            $('input[data-kt-roi-table-filter="search"]').on('keyup', function () {
                table.search(this.value).draw();
            });

        });
    </script>
@endpush
