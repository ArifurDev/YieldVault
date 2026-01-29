
@extends('backend.app')

@section('title', 'Wallet Transaction History')

@push('style')
    <link rel="stylesheet"
          href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

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
                <div class="d-flex flex-column align-items-start">
                    <h1 class="text-dark fw-bold fs-2 mb-1">
                        Wallet Transaction History
                    </h1>

                    <ul class="breadcrumb fw-semibold fs-base">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('wallet.index') }}"
                               class="text-muted text-hover-primary">
                                Wallets
                            </a>
                        </li>
                        <li class="breadcrumb-item text-dark">
                            Transaction History
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="post fs-6 d-flex flex-column-fluid" id="kt_post">
            @include('backend.partials.wallet.history_index_main')
        </div>

    </div>
@endsection

@push('script')
    {{-- DataTables --}}
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {

            let table = $('#wallet_transaction_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('wallet.show', $user->id) }}",
                order: [[5, 'desc']],
                columns: [
                    { data: 'tnx_id', name: 'tnx_id' },
                    { data: 'from', name: 'from_users.first_name' },
                    { data: 'to', name: 'to_users.first_name' },
                    { data: 'amount', name: 'wallet_transactions.amount' },
                    { data: 'flow', orderable: false, searchable: false },
                    { data: 'source', name: 'wallet_transactions.source' },
                    { data: 'date', name: 'wallet_transactions.created_at' },
                ],
            });

            // Search
            $('input[data-kt-wallet-transaction-filter="search"]').on('keyup', function () {
                table.search(this.value).draw();
            });

            // Admin Wallet Action
            $('#adminWalletActionForm').on('submit', function (e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ route('wallet.admin.action') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    success: function (res) {
                        toastr.success(res.message);
                        table.ajax.reload(null, false);
                        $('#adminWalletActionForm')[0].reset();
                    },
                    error: function (xhr) {
                        toastr.error(
                            xhr.responseJSON?.message ?? 'Something went wrong'
                        );
                    }
                });
            });
        });
    </script>
@endpush

