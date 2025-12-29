@extends('backend.app')

@section('title', 'Wallet Transaction History')

@push('style')
    <link rel="stylesheet"
          href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')

    <div class="content fs-6 d-flex flex-column-fluid">
        <div class="container-xxl">

            {{-- admin fund action --}}
            <div class="card mb-6">
                <div class="card-header">
                    <h3 class="card-title">Admin Wallet Action</h3>
                </div>

                <div class="card-body">
                    <form id="adminWalletActionForm">
                        @csrf

                        {{-- TARGET USER (CURRENT WALLET OWNER) --}}
                        <input type="hidden" name="user_id" value="{{ $user->id }}">

                        <div class="row g-4">

                            {{-- Amount --}}
                            <div class="col-md-3">
                                <label class="form-label">Amount</label>
                                <input type="number" step="0.01" name="amount"
                                       class="form-control" required>
                            </div>

                            {{-- Action --}}
                            <div class="col-md-3">
                                <label class="form-label">Action</label>
                                <select name="action" class="form-select" required>
                                    <option value="{{ \App\Enum\Wallet\TransactionType::CREDIT->value }}">
                                        Give Money
                                    </option>
                                    <option value="{{ \App\Enum\Wallet\TransactionType::DEBIT->value }}">
                                        Take Money
                                    </option>
                                </select>
                            </div>

                            {{-- Submit --}}
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    Submit
                                </button>
                            </div>

                        </div>
                    </form>

                </div>
            </div>


            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"></i>
                            <input type="text"
                                   data-kt-wallet-transaction-filter="search"
                                   class="form-control form-control-solid w-300px ps-13"
                                   placeholder="Search transaction">
                        </div>
                    </div>
                </div>

                <div class="card-body py-4">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5"
                               id="wallet_transaction_table">
                            <thead>
                            <tr class="text-muted fw-bold fs-7 text-uppercase">
                                <th>From</th>
                                <th>To</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Source</th>
                                <th>Date</th>
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

            let table = $('#wallet_transaction_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('wallet.show', $user->id) }}",
                columns: [
                    { data: 'from', name: 'from_users.first_name' },
                    { data: 'to', name: 'to_users.first_name' },
                    { data: 'amount', name: 'wallet_transactions.amount' },
                    { data: 'flow', orderable: false, searchable: false },
                    { data: 'source', name: 'wallet_transactions.source' },
                    { data: 'date', name: 'wallet_transactions.created_at' },
                ],
            });

            $('input[data-kt-wallet-transaction-filter="search"]').keyup(function () {
                table.search(this.value).draw();
            });

            // Admin Wallet Action Form
            $('#adminWalletActionForm').on('submit', function (e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ route('wallet.admin.action') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function (res) {
                        toastr.success(res.message);

                        // reload datatable
                        table.ajax.reload(null, false);

                        // reset form
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
