{{--@extends('backend.app')--}}

{{--@section('title', 'Create Payment Report')--}}

{{--@section('content')--}}
{{--    <div class="container-fluid">--}}

{{--        --}}{{-- Page Header --}}
{{--        <div class="mb-4">--}}
{{--            <h2>Create Payment Report</h2>--}}
{{--            <p class="text-muted">Admin / Create Payment Report</p>--}}
{{--        </div>--}}

{{--        <form method="POST" action="{{ route('payment-reports.store') }}">--}}
{{--            @csrf--}}
{{--            <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">--}}

{{--            --}}{{-- Ticket Info --}}
{{--            <div class="card mb-4">--}}
{{--                <div class="card-header fw-bold">Ticket Information</div>--}}
{{--                <div class="card-body">--}}
{{--                    <div class="row mb-2">--}}
{{--                        <div class="col-md-6">--}}
{{--                            <strong>Ticket ID:</strong>--}}
{{--                            #{{ str_pad($ticket->id, 6, '0', STR_PAD_LEFT) }}--}}
{{--                        </div>--}}
{{--                        <div class="col-md-6">--}}
{{--                            <strong>User:</strong>--}}
{{--                            {{ $ticket->user->first_name }} {{ $ticket->user->last_name }}--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="row mb-2">--}}
{{--                        <div class="col-md-6">--}}
{{--                            <strong>Category:</strong>--}}
{{--                            {{ ucfirst($ticket->category->value) }}--}}
{{--                        </div>--}}
{{--                        <div class="col-md-6">--}}
{{--                            <strong>Subject:</strong>--}}
{{--                            {{ $ticket->subject }}--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            --}}{{-- Payment Report Details --}}
{{--            <div class="card">--}}
{{--                <div class="card-header fw-bold">Payment Report Details</div>--}}
{{--                <div class="card-body">--}}

{{--                    --}}{{-- Transaction --}}
{{--                    <div class="mb-3">--}}
{{--                        <label class="form-label">Related Transaction (Optional)</label>--}}
{{--                        <select name="transaction_id" class="form-select">--}}
{{--                            <option value="">— No Transaction —</option>--}}
{{--                            @foreach($transactions as $txn)--}}
{{--                                <option value="{{ $txn->id }}">--}}
{{--                                    TXN-{{ $txn->id }} |--}}
{{--                                    {{ $txn->amount }} |--}}
{{--                                    {{ $txn->created_at->format('d M Y') }}--}}
{{--                                </option>--}}
{{--                            @endforeach--}}
{{--                        </select>--}}
{{--                    </div>--}}

{{--                    --}}{{-- Issue Type --}}
{{--                    <div class="mb-3">--}}
{{--                        <label class="form-label">Issue Type <span class="text-danger">*</span></label>--}}
{{--                        <select name="issue_type" class="form-select" required>--}}
{{--                            <option value="">Select Issue</option>--}}
{{--                            <option value="delay">Delay</option>--}}
{{--                            <option value="failed">Failed</option>--}}
{{--                            <option value="mismatch">Amount Mismatch</option>--}}
{{--                        </select>--}}
{{--                    </div>--}}

{{--                    --}}{{-- Message --}}
{{--                    <div class="mb-3">--}}
{{--                        <label class="form-label">Message</label>--}}
{{--                        <textarea--}}
{{--                            name="message"--}}
{{--                            rows="4"--}}
{{--                            class="form-control"--}}
{{--                            required>{{ old('message', $ticket->messages->first()->message ?? '') }}</textarea>--}}
{{--                    </div>--}}

{{--                    --}}{{-- MANAGER (AUTO ASSIGNED) --}}
{{--                    <div class="mb-3">--}}
{{--                        <label class="form-label fw-semibold">--}}
{{--                            Manager--}}
{{--                        </label>--}}

{{--                        @if($manager)--}}
{{--                            <input type="text"--}}
{{--                                   class="form-control"--}}
{{--                                   value="{{ $manager->first_name }} {{ $manager->last_name }} ({{ $manager->email }})"--}}
{{--                                   disabled>--}}

{{--                            --}}{{-- Hidden input for submit --}}
{{--                            <input type="hidden" name="manager_id" value="{{ $manager->id }}">--}}
{{--                        @else--}}
{{--                            <div class="alert alert-warning mb-0">--}}
{{--                                No manager assigned to this user.--}}
{{--                            </div>--}}
{{--                        @endif--}}
{{--                    </div>--}}


{{--                </div>--}}

{{--                --}}{{-- Submit --}}
{{--                <div class="card-footer text-end">--}}
{{--                    <button type="submit" class="btn btn-primary px-4">--}}
{{--                        Create Payment Report--}}
{{--                    </button>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </form>--}}

{{--    </div>--}}
{{--@endsection--}}



{{--@extends('backend.app')--}}

{{--@section('title', 'Create Payment Report')--}}

{{--@push('style')--}}
{{--    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">--}}

{{--    <style>--}}

{{--    </style>--}}
{{--@endpush--}}

{{--@section('content')--}}
{{--    <!--begin::Content-->--}}
{{--    <div class="content fs-6 d-flex flex-column flex-column-fluid" id="kt_content">--}}
{{--        <!--begin::Toolbar-->--}}
{{--        <div class="toolbar" id="kt_toolbar">--}}
{{--            <div class=" container-fluid  d-flex flex-stack flex-wrap flex-sm-nowrap">--}}
{{--                <!--begin::Info-->--}}
{{--                <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">--}}
{{--                    <!--begin::Title-->--}}
{{--                    <h1 class="text-dark fw-bold my-1 fs-2">--}}
{{--                        Create Payment Report </h1>--}}
{{--                    <!--end::Title-->--}}

{{--                    <!--begin::Breadcrumb-->--}}
{{--                    <ul class="breadcrumb fw-semibold fs-base my-1">--}}
{{--                        <li class="breadcrumb-item text-muted">--}}
{{--                            <a href="{{ route('support-tickets.index') }}" class="text-muted text-hover-primary">Support Tickets </a>--}}
{{--                        </li>--}}

{{--                        <li class="breadcrumb-item text-dark">--}}
{{--                            Create Payment Report--}}
{{--                        </li>--}}

{{--                    </ul>--}}
{{--                    <!--end::Breadcrumb-->--}}
{{--                </div>--}}
{{--                <!--end::Info-->--}}

{{--            </div>--}}
{{--        </div>--}}
{{--        <!--end::Toolbar-->--}}

{{--        <!--begin::Post-->--}}
{{--        <div class="post fs-6 d-flex flex-column-fluid" id="kt_post">--}}
{{--            <!--begin::Container-->--}}
{{--            @include('backend.partials.payment_reports.create_main')--}}
{{--            <!--end::Post-->--}}
{{--        </div>--}}
{{--        <!--end::Content-->--}}

{{--        @push('script')--}}
{{--            <script src="{{ asset('backend/assets') . '/js/custom/apps/user-management/users/list/table.js' }}"></script>--}}
{{--            <script src="{{ asset('backend/assets') . '/js/custom/apps/user-management/users/list/export-users.js' }}"></script>--}}
{{--            <script src="{{ asset('backend/assets') . '/js/custom/apps/user-management/users/list/add.js' }}"></script>--}}
{{--            <script src="{{ asset('backend/assets') . '/js/custom/utilities/modals/users-search.js' }}"></script>--}}

{{--            --}}{{--  Include DataTables --}}
{{--            <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>--}}
{{--            <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>--}}

{{--            <script>--}}

{{--            </script>--}}
{{--    @endpush--}}
{{--@endsection--}}



@extends('backend.app')

@section('title', 'Create Payment Report')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
    <div class="content fs-6 d-flex flex-column flex-column-fluid" id="kt_content">

        {{-- Toolbar --}}
        <div class="toolbar" id="kt_toolbar">
            <div class="container-fluid d-flex flex-stack flex-wrap flex-sm-nowrap">
                <div class="d-flex flex-column align-items-start justify-content-center me-2">
                    <h1 class="text-dark fw-bold my-1 fs-2">
                        Create Payment Report
                    </h1>

                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('support-tickets.index') }}"
                               class="text-muted text-hover-primary">
                                Support Tickets
                            </a>
                        </li>
                        <li class="breadcrumb-item text-dark">
                            Create Payment Report
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Page Content --}}
        <div class="post fs-6 d-flex flex-column-fluid" id="kt_post">
            @include('backend.partials.payment_reports.create_main')
        </div>

    </div>
@endsection

