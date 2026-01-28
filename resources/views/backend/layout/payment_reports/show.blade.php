{{--@extends('backend.app')--}}

{{--@section('title', 'Payment Report Details')--}}

{{--@section('content')--}}
{{--    <div class="content fs-6 d-flex flex-column-fluid">--}}
{{--        <div class="container-xxl">--}}

{{--            --}}{{-- Header --}}
{{--            <div class="d-flex justify-content-between align-items-center mb-6">--}}
{{--                <div>--}}
{{--                    <h1 class="fw-bold fs-2">--}}
{{--                        Payment Report--}}
{{--                        <span class="text-muted">--}}
{{--                        #PR-{{ str_pad($paymentReport->id, 6, '0', STR_PAD_LEFT) }}--}}
{{--                    </span>--}}
{{--                    </h1>--}}

{{--                    <ul class="breadcrumb fw-semibold fs-base my-1">--}}
{{--                        <li class="breadcrumb-item text-muted">--}}
{{--                            <a href="{{ route('dashboard') }}">Home</a>--}}
{{--                        </li>--}}
{{--                        <li class="breadcrumb-item text-muted">--}}
{{--                            <a href="{{ route('payment-reports.index') }}">Payment Reports</a>--}}
{{--                        </li>--}}
{{--                        <li class="breadcrumb-item text-dark">View</li>--}}
{{--                    </ul>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            <div class="row g-6">--}}

{{--                --}}{{-- LEFT --}}
{{--                <div class="col-md-8">--}}

{{--                    --}}{{-- Report Details --}}
{{--                    <div class="card shadow-sm border-0 mb-6">--}}
{{--                        <div class="card-header fw-bold">--}}
{{--                            Report Details--}}
{{--                        </div>--}}

{{--                        <div class="card-body">--}}

{{--                            <div class="row mb-4">--}}
{{--                                <div class="col-md-6">--}}
{{--                                    <div class="text-muted small">Ticket</div>--}}
{{--                                    <div class="fw-semibold">--}}
{{--                                        #{{ str_pad($paymentReport->ticket_id, 6, '0', STR_PAD_LEFT) }}--}}
{{--                                    </div>--}}
{{--                                </div>--}}

{{--                                <div class="col-md-6">--}}
{{--                                    <div class="text-muted small">Category</div>--}}
{{--                                    <span class="badge bg-info">--}}
{{--                                    {{ $paymentReport->category?->label() ?? '-' }}--}}
{{--                                </span>--}}
{{--                                </div>--}}
{{--                            </div>--}}

{{--                            <div class="row mb-4">--}}
{{--                                <div class="col-md-6">--}}
{{--                                    <div class="text-muted small">Issue Type</div>--}}
{{--                                    <div class="fw-semibold">--}}
{{--                                        {{ $paymentReport->issue_type?->label() ?? '-' }}--}}
{{--                                    </div>--}}
{{--                                </div>--}}

{{--                                <div class="col-md-6">--}}
{{--                                    <div class="text-muted small">Status</div>--}}

{{--                                    @php--}}
{{--                                        $statusColor = match($paymentReport->status?->value) {--}}
{{--                                            'open' => 'primary',--}}
{{--                                            'in_progress' => 'warning',--}}
{{--                                            'resolved' => 'success',--}}
{{--                                            'rejected' => 'danger',--}}
{{--                                            default => 'secondary'--}}
{{--                                        };--}}
{{--                                    @endphp--}}

{{--                                    <span class="badge bg-{{ $statusColor }}">--}}
{{--                                    {{ $paymentReport->status?->label() ?? '-' }}--}}
{{--                                </span>--}}
{{--                                </div>--}}
{{--                            </div>--}}

{{--                            --}}{{-- Transaction --}}
{{--                            @if($paymentReport->transaction)--}}
{{--                                <div class="row mb-4">--}}
{{--                                    <div class="col-md-6">--}}
{{--                                        <div class="text-muted small">Transaction ID</div>--}}
{{--                                        <div class="fw-semibold">--}}
{{--                                            TXN-{{ $paymentReport->transaction->id }}--}}
{{--                                        </div>--}}
{{--                                    </div>--}}

{{--                                    <div class="col-md-6">--}}
{{--                                        <div class="text-muted small">Amount</div>--}}
{{--                                        <div class="fw-bold text-success">--}}
{{--                                            ${{ number_format($paymentReport->transaction->amount, 2) }}--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            @endif--}}

{{--                            <hr>--}}

{{--                            --}}{{-- Message --}}
{{--                            <div>--}}
{{--                                <div class="text-muted small mb-2">Message</div>--}}
{{--                                <div class="border rounded p-3 bg-light">--}}
{{--                                    {{ $paymentReport->message }}--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    --}}{{-- MANAGER ACTION --}}
{{--                    @if(--}}
{{--                        auth()->id() === $paymentReport->manager_id &&--}}
{{--                        $paymentReport->status !== \App\Enum\Payment\ReportStatus::RESOLVED--}}
{{--                    )--}}
{{--                        <div class="card shadow-sm border-0">--}}
{{--                            <div class="card-header fw-bold">--}}
{{--                                Manager Action--}}
{{--                            </div>--}}

{{--                            <div class="card-body">--}}
{{--                                <form method="POST"--}}
{{--                                      action="{{ route('payment-reports.update-status', $paymentReport) }}">--}}
{{--                                    @csrf--}}
{{--                                    @method('PUT')--}}

{{--                                    <div class="mb-4">--}}
{{--                                        <label class="form-label">Update Status</label>--}}
{{--                                        <select name="status" class="form-select" required>--}}
{{--                                            @foreach(\App\Enum\Payment\ReportStatus::cases() as $status)--}}
{{--                                                <option value="{{ $status->value }}"--}}
{{--                                                    @selected($paymentReport->status === $status)>--}}
{{--                                                    {{ $status->label() }}--}}
{{--                                                </option>--}}
{{--                                            @endforeach--}}
{{--                                        </select>--}}
{{--                                    </div>--}}

{{--                                    <div class="mb-4">--}}
{{--                                        <label class="form-label">Internal Note (Optional)</label>--}}
{{--                                        <textarea--}}
{{--                                            name="note"--}}
{{--                                            class="form-control"--}}
{{--                                            rows="3"--}}
{{--                                            placeholder="Only visible to admin / manager"></textarea>--}}
{{--                                    </div>--}}

{{--                                    <button class="btn btn-primary">--}}
{{--                                        Update Status--}}
{{--                                    </button>--}}
{{--                                </form>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    @endif--}}
{{--                </div>--}}

{{--                --}}{{-- RIGHT --}}
{{--                <div class="col-md-4">--}}
{{--                    <div class="card shadow-sm border-0">--}}
{{--                        <div class="card-body">--}}

{{--                            --}}{{-- USER --}}
{{--                            <div class="d-flex align-items-center mb-4">--}}
{{--                                <img--}}
{{--                                    src="{{ $paymentReport->user->avatar--}}
{{--                                    ? asset($paymentReport->user->avatar)--}}
{{--                                    : asset('/backend/assets/media/avatars/300-1.jpg') }}"--}}
{{--                                    class="rounded-circle me-3"--}}
{{--                                    width="50"--}}
{{--                                    height="50">--}}

{{--                                <div>--}}
{{--                                    <div class="fw-bold">--}}
{{--                                        {{ $paymentReport->user->first_name }}--}}
{{--                                        {{ $paymentReport->user->last_name }}--}}
{{--                                    </div>--}}
{{--                                    <div class="text-muted small">--}}
{{--                                        {{ $paymentReport->user->email }}--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}

{{--                            <hr>--}}

{{--                            --}}{{-- MANAGER --}}
{{--                            <div class="mb-3">--}}
{{--                                <div class="text-muted small">Assigned Manager</div>--}}
{{--                                <div class="fw-semibold">--}}
{{--                                    {{ $paymentReport->manager?->first_name ?? 'Unassigned' }}--}}
{{--                                </div>--}}
{{--                            </div>--}}

{{--                            <div class="mb-3">--}}
{{--                                <div class="text-muted small">Created</div>--}}
{{--                                <div class="fw-semibold">--}}
{{--                                    {{ $paymentReport->created_at->format('d M Y, h:i A') }}--}}
{{--                                </div>--}}
{{--                            </div>--}}

{{--                            @if($paymentReport->resolved_at)--}}
{{--                                <div class="mb-3">--}}
{{--                                    <div class="text-muted small">Resolved At</div>--}}
{{--                                    <div class="fw-semibold">--}}
{{--                                        {{ $paymentReport->resolved_at->format('d M Y, h:i A') }}--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            @endif--}}

{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--@endsection--}}







@extends('backend.app')

@section('title', 'Payment Report Details')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>

    </style>
@endpush

@section('content')
    <div class="content fs-6 d-flex flex-column flex-column-fluid" id="kt_content">

        {{-- Toolbar --}}
        <div class="toolbar" id="kt_toolbar">
            <div class="container-fluid d-flex flex-stack flex-wrap">
                <div class="d-flex flex-column">
                    <h1 class="fw-bold fs-2">Payment Report Details</h1>

                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('payment-reports.index') }}" class="text-muted text-hover-primary">Payment Reports</a>
                        </li>
                        <li class="breadcrumb-item text-dark">Payment Report Details</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="post fs-6 d-flex flex-column-fluid" id="kt_post">
            @include('backend.partials.payment_reports.show_main')
        </div>

    </div>

    @push('script')
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

        <script>

        </script>
    @endpush
@endsection



