{{--<div class=" container-xxl " data-select2-id="select2-data-198-e71y">--}}
{{--    <!--begin::Card-->--}}
{{--    <div class="card" data-select2-id="select2-data-197-m5it">--}}
{{--        <!--begin::Card header-->--}}
{{--        <div class="card-header border-0 pt-6" data-select2-id="select2-data-196-qcil">--}}
{{--            <!--begin::Card title-->--}}
{{--            <div class="card-title">--}}
{{--                <!--begin::Search-->--}}
{{--                <div class="d-flex align-items-center position-relative my-1">--}}
{{--                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span--}}
{{--                            class="path2"></span></i> <input id="packageSearch" type="text" data-kt-user-table-filter="search"--}}
{{--                                                             class="form-control form-control-solid w-250px ps-13" placeholder="Search...">--}}
{{--                </div>--}}
{{--                <!--end::Search-->--}}
{{--            </div>--}}
{{--            <!--begin::Card title-->--}}

{{--            <!--begin::Card toolbar-->--}}
{{--            <div class="card-toolbar">--}}
{{--                <!--begin::Toolbar-->--}}
{{--                <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base"--}}
{{--                     data-select2-id="select2-data-195-9exk">--}}
{{--                    --}}{{--                    <a href="{{ route('payment-reports.create') }}"--}}
{{--                    --}}{{--                       class="btn btn-primary">--}}
{{--                    --}}{{--                        <i class="ki-duotone ki-plus fs-2"></i> Create Payment Report--}}
{{--                    --}}{{--                    </a>--}}

{{--                </div>--}}
{{--                <!--end::Toolbar-->--}}

{{--                <!--begin::Group actions-->--}}
{{--                <div class="d-flex justify-content-end align-items-center d-none"--}}
{{--                     data-kt-user-table-toolbar="selected">--}}
{{--                    <div class="fw-bold me-5">--}}
{{--                        <span class="me-2" data-kt-user-table-select="selected_count"></span> Selected--}}
{{--                    </div>--}}

{{--                    <button type="button" class="btn btn-danger" data-kt-user-table-select="delete_selected">--}}
{{--                        Delete Selected--}}
{{--                    </button>--}}
{{--                </div>--}}
{{--                <!--end::Group actions-->--}}

{{--            </div>--}}
{{--            <!--end::Card toolbar-->--}}
{{--        </div>--}}
{{--        <!--end::Card header-->--}}

{{--        <!--begin::Card body-->--}}
{{--        <div class="card-body py-4">--}}

{{--            <!--begin::Table-->--}}
{{--                    <form method="POST" action="{{ route('payment-reports.store') }}">--}}
{{--                        @csrf--}}
{{--                        <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">--}}

{{--                         Ticket Info--}}
{{--                        <div class="card mb-4">--}}
{{--                            <div class="card-header fw-bold">Ticket Information</div>--}}
{{--                            <div class="card-body">--}}
{{--                                <div class="row mb-2">--}}
{{--                                    <div class="col-md-6">--}}
{{--                                        <strong>Ticket ID:</strong>--}}
{{--                                        #{{ str_pad($ticket->id, 6, '0', STR_PAD_LEFT) }}--}}
{{--                                    </div>--}}
{{--                                    <div class="col-md-6">--}}
{{--                                        <strong>User:</strong>--}}
{{--                                        {{ $ticket->user->first_name }} {{ $ticket->user->last_name }}--}}
{{--                                    </div>--}}
{{--                                </div>--}}

{{--                                <div class="row mb-2">--}}
{{--                                    <div class="col-md-6">--}}
{{--                                        <strong>Category:</strong>--}}
{{--                                        {{ ucfirst($ticket->category->value) }}--}}
{{--                                    </div>--}}
{{--                                    <div class="col-md-6">--}}
{{--                                        <strong>Subject:</strong>--}}
{{--                                        {{ $ticket->subject }}--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                         Payment Report Details--}}
{{--                        <div class="card">--}}
{{--                            <div class="card-header fw-bold">Payment Report Details</div>--}}
{{--                            <div class="card-body">--}}

{{--                                 Transaction--}}
{{--                                <div class="mb-3">--}}
{{--                                    <label class="form-label">Related Transaction (Optional)</label>--}}
{{--                                    <select name="transaction_id" class="form-select">--}}
{{--                                        <option value="">— No Transaction —</option>--}}
{{--                                        @foreach($transactions as $txn)--}}
{{--                                            <option value="{{ $txn->id }}">--}}
{{--                                                TXN-{{ $txn->id }} |--}}
{{--                                                {{ $txn->amount }} |--}}
{{--                                                {{ $txn->created_at->format('d M Y') }}--}}
{{--                                            </option>--}}
{{--                                        @endforeach--}}
{{--                                    </select>--}}
{{--                                </div>--}}

{{--                                 Issue Type--}}
{{--                                <div class="mb-3">--}}
{{--                                    <label class="form-label">Issue Type <span class="text-danger">*</span></label>--}}
{{--                                    <select name="issue_type" class="form-select" required>--}}
{{--                                        <option value="">Select Issue</option>--}}
{{--                                        <option value="delay">Delay</option>--}}
{{--                                        <option value="failed">Failed</option>--}}
{{--                                        <option value="mismatch">Amount Mismatch</option>--}}
{{--                                    </select>--}}
{{--                                </div>--}}

{{--                                 Message--}}
{{--                                <div class="mb-3">--}}
{{--                                    <label class="form-label">Message</label>--}}
{{--                                    <textarea--}}
{{--                                        name="message"--}}
{{--                                        rows="4"--}}
{{--                                        class="form-control"--}}
{{--                                        required>{{ old('message', $ticket->messages->first()->message ?? '') }}</textarea>--}}
{{--                                </div>--}}

{{--                                 MANAGER (AUTO ASSIGNED)--}}
{{--                                <div class="mb-3">--}}
{{--                                    <label class="form-label fw-semibold">--}}
{{--                                        Manager--}}
{{--                                    </label>--}}

{{--                                    @if($manager)--}}
{{--                                        <input type="text"--}}
{{--                                               class="form-control"--}}
{{--                                               value="{{ $manager->first_name }} {{ $manager->last_name }} ({{ $manager->email }})"--}}
{{--                                               disabled>--}}

{{--                                         Hidden input for submit--}}
{{--                                        <input type="hidden" name="manager_id" value="{{ $manager->id }}">--}}
{{--                                    @else--}}
{{--                                        <div class="alert alert-warning mb-0">--}}
{{--                                            No manager assigned to this user.--}}
{{--                                        </div>--}}
{{--                                    @endif--}}
{{--                                </div>--}}


{{--                            </div>--}}

{{--                             Submit--}}
{{--                            <div class="card-footer text-end">--}}
{{--                                <button type="submit" class="btn btn-primary px-4">--}}
{{--                                    Create Payment Report--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </form>--}}
{{--            <!--end::Table-->--}}
{{--        </div>--}}
{{--        <!--end::Card body-->--}}
{{--    </div>--}}
{{--    <!--end::Card-->--}}
{{--</div>--}}



<div class="container-xxl">

    <div class="card">
        <div class="card-body py-4">

            <form method="POST" action="{{ route('payment-reports.store') }}">
                @csrf

                <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">

                {{-- ========================= --}}
                {{-- TICKET INFORMATION --}}
                {{-- ========================= --}}
                <div class="card mb-4">
                    <div class="card-header fw-bold">
                        Ticket Information
                    </div>

                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <strong>Ticket ID:</strong>
                                #{{ str_pad($ticket->id, 6, '0', STR_PAD_LEFT) }}
                            </div>

                            <div class="col-md-6">
                                <strong>User:</strong>
                                {{ $ticket->user->first_name }} {{ $ticket->user->last_name }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6">
                                <strong>Category:</strong>
                                {{ ucfirst($ticket->category->value) }}
                            </div>

                            <div class="col-md-6">
                                <strong>Subject:</strong>
                                {{ $ticket->subject }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========================= --}}
                {{-- PAYMENT REPORT DETAILS --}}
                {{-- ========================= --}}
                <div class="card">
                    <div class="card-header fw-bold">
                        Payment Report Details
                    </div>

                    <div class="card-body">

                        {{-- TRANSACTION --}}
                        <div class="mb-3">
                            <label class="form-label">
                                Related Transaction (Optional)
                            </label>

                            <select name="transaction_id" class="form-select">
                                <option value="">— No Transaction —</option>

                                @foreach($transactions as $txn)
                                    <option value="{{ $txn->id }}">
                                        TXN-{{ str_pad($txn->id, 6, '0', STR_PAD_LEFT) }}
                                        | {{ $txn->amount }}
                                        | {{ ucfirst($txn->type) }}
                                        | {{ $txn->created_at->format('d M Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ISSUE TYPE --}}
                        <div class="mb-3">
                            <label class="form-label">
                                Issue Type <span class="text-danger">*</span>
                            </label>

                            <select name="issue_type" class="form-select" required>
                                <option value="">Select Issue</option>
                                <option value="delay">Delay</option>
                                <option value="failed">Failed</option>
                                <option value="mismatch">Amount Mismatch</option>
                            </select>
                        </div>

                        {{-- CATEGORY (AUTO FROM TICKET) --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Category
                            </label>

                            <input type="text"
                                   class="form-control"
                                   value="{{ \App\Enum\Payment\Category::from($ticket->category->value)->label() }}"
                                   disabled>

                            {{-- Hidden value for submit --}}
                            <input type="hidden"
                                   name="category"
                                   value="{{ $ticket->category->value }}">
                        </div>


                        {{-- MESSAGE --}}
                        <div class="mb-3">
                            <label class="form-label">
                                Message <span class="text-danger">*</span>
                            </label>

                            <textarea
                                name="message"
                                rows="4"
                                class="form-control"
                                required>{{ old('message', optional($ticket->messages->first())->message) }}</textarea>
                        </div>

                        {{-- MANAGER (AUTO ASSIGNED) --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Manager
                            </label>

                            @if($manager)
                                <input type="text"
                                       class="form-control"
                                       value="{{ $manager->first_name }} {{ $manager->last_name }} ({{ $manager->email }})"
                                       disabled>

                                <input type="hidden" name="manager_id" value="{{ $manager->id }}">
                            @else
                                <div class="alert alert-warning mb-0">
                                    No manager assigned to this user.
                                </div>
                            @endif
                        </div>

                    </div>

                    {{-- SUBMIT --}}
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            Create Payment Report
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>
</div>
