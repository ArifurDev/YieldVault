@extends('backend.app')

@section('title', 'Wallet Management')

@push('style')
    <link rel="stylesheet"
          href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')

    <div class="content fs-6 d-flex flex-column-fluid">
        <div class="container-xxl">

            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"></i>
                            <input type="text"
                                   data-kt-wallet-table-filter="search"
                                   class="form-control form-control-solid w-250px ps-13"
                                   placeholder="Search wallet">
                        </div>
                    </div>
                </div>

                <div class="card-body py-4">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5"
                               id="wallet_table">
                            <thead>
                            <tr class="text-muted fw-bold fs-7 text-uppercase">
                                <th>User</th>
                                <th>Role</th>
                                <th>Balance</th>
                                <th>Status</th>
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

            let table = $('#wallet_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('wallet.index') }}",
                columns: [
                    {data: 'user', name: 'user'},
                    {data: 'role', name: 'role'},
                    {data: 'balance', name: 'balance'},
                    {data: 'status', name: 'status'},
                    {data: 'actions', orderable: false, searchable: false},
                ],
                drawCallback: function () {
                    if (typeof KTMenu !== 'undefined') {
                        KTMenu.createInstances();
                    }
                }
            });

            $('input[data-kt-wallet-table-filter="search"]').keyup(function () {
                table.search(this.value).draw();
            });

        });
    </script>
@endpush
