<div class="container-xxl">

    {{-- Admin Wallet Action --}}
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="card-title">Admin Wallet Action</h3>
        </div>

        <div class="card-body">
            <form id="adminWalletActionForm">
                @csrf

                <input type="hidden" name="user_id" value="{{ $user->id }}">

                <div class="row g-4 align-items-end">

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Amount</label>
                        <input type="number"
                               step="0.01"
                               name="amount"
                               class="form-control"
                               placeholder="Enter amount"
                               required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Action</label>
                        <select name="action" class="form-select" required>
                            <option value="{{ \App\Enum\Wallet\TransactionType::CREDIT->value }}">
                                Give Money
                            </option>
                            <option value="{{ \App\Enum\Wallet\TransactionType::DEBIT->value }}">
                                Take Money
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit"
                                class="btn btn-primary w-100">
                            Submit
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Transaction Table --}}
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative">
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
                        <th>TXN ID</th>
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
