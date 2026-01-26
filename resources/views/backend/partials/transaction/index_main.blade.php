<div class=" container-xxl " data-select2-id="select2-data-198-e71y">
    <!--begin::Card-->
    <div class="card" data-select2-id="select2-data-197-m5it">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6" data-select2-id="select2-data-196-qcil">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span
                            class="path2"></span></i> <input id="packageSearch" type="text" data-kt-user-table-filter="search"
                                                             class="form-control form-control-solid w-250px ps-13" placeholder="Search ...">
                </div>
                <!--end::Search-->
            </div>
            <!--begin::Card title-->

            <!--begin::Card toolbar-->
            <div class="card-toolbar">

                <div class="d-flex flex-wrap align-items-center gap-3 mb-6">


                    {{-- Status --}}
                    <select id="filterStatus" class="form-select form-select-solid w-150px">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="paid">Paid</option>
                        <option value="rejected">Rejected</option>
                    </select>

                    {{-- Type --}}
                    <select id="filterType" class="form-select form-select-solid w-150px">
                        <option value="">All Type</option>
                        <option value="credit">Credit</option>
                        <option value="debit">Debit</option>
                    </select>

                    {{-- Source --}}
                    <select id="filterSource" class="form-select form-select-solid w-200px">
                        <option value="">All Source</option>
                        <option value="deposit">Deposit</option>
                        <option value="withdraw">Withdraw</option>
                        <option value="investment_buy">Investment Buy</option>
                        <option value="investment_profit">Investment Profit</option>
                        <option value="referral_reward">Referral Reward</option>
                    </select>

                    {{-- Quick Date Buttons --}}
                    <button id="todayBtn" class="btn btn-light-primary">
                        Today
                    </button>

                    <button id="monthBtn" class="btn btn-light-info">
                        This Month
                    </button>

                    {{-- Date range --}}
                    <input type="date" id="startDate" class="form-control form-control-solid w-150px">
                    <input type="date" id="endDate" class="form-control form-control-solid w-150px">

                    {{-- Reset --}}
                    <button id="resetFilters" class="btn btn-light">
                        Reset
                    </button>

                    {{-- Export --}}
                    <button id="exportCsv" class="btn btn-primary">
                        <i class="ki-duotone ki-file-down fs-3 me-1"></i>
                        Export CSV
                    </button>

                </div>





                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base"
                     data-select2-id="select2-data-195-9exk">

                    {{--                    <!--begin::Add user-->--}}
                    {{--                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"--}}
                    {{--                            data-bs-target="#kt_modal_add_user">--}}
                    {{--                        <i class="ki-duotone ki-plus fs-2"></i> Add Level--}}
                    {{--                    </button>--}}
                    <!--end::Add user-->
                </div>
                <!--end::Toolbar-->

                <!--begin::Group actions-->
                <div class="d-flex justify-content-end align-items-center d-none"
                     data-kt-user-table-toolbar="selected">
                    <div class="fw-bold me-5">
                        <span class="me-2" data-kt-user-table-select="selected_count"></span> Selected
                    </div>

                    <button type="button" class="btn btn-danger" data-kt-user-table-select="delete_selected">
                        Delete Selected
                    </button>
                </div>
                <!--end::Group actions-->

                <!--begin::Modal - Add task-->
                @include('backend.partials.level.add')
                <!--end::Modal - Add task-->
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body py-4">

            <!--begin::Table-->
            <div id="kt_table_users_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5"
                           id="package_table">
                        <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase">
                            <th>TXN ID</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                    </table>


                </div>

            </div>
            <!--end::Table-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
</div>



<!---show transaction-->

<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>ID:</strong> <span id="trx_id"></span></li>
                    <li class="list-group-item"><strong>From:</strong> <span id="trx_from"></span></li>
                    <li class="list-group-item"><strong>To:</strong> <span id="trx_to"></span></li>
                    <li class="list-group-item"><strong>Amount:</strong> <span id="trx_amount"></span></li>
                    <li class="list-group-item"><strong>Type:</strong> <span id="trx_type"></span></li>
                    <li class="list-group-item"><strong>Source:</strong> <span id="trx_source"></span></li>
                    <li class="list-group-item"><strong>Status:</strong> <span id="trx_status"></span></li>
                    <li class="list-group-item"><strong>Date:</strong> <span id="trx_date"></span></li>
                </ul>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>
