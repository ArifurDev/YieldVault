<div class="container-xxl">
    <div class="card">

        {{-- HEADER --}}
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h2 class="fw-bold mb-0">
                    Payment Report
                    <span class="text-muted">
                        #PR-{{ str_pad($paymentReport->id, 6, '0', STR_PAD_LEFT) }}
                    </span>
                </h2>
            </div>
        </div>

        {{-- BODY --}}
        <div class="card-body py-4">

            <div class="row g-6">

                {{-- ================= LEFT : REPORT DETAILS ================= --}}
                <div class="col-md-8">

                    <div class="card shadow-sm border-0 mb-6">
                        <div class="card-body">

                            {{-- BASIC INFO --}}
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="text-muted small">Ticket ID</div>
                                    <div class="fw-semibold">
                                        #{{ str_pad($paymentReport->ticket_id, 6, '0', STR_PAD_LEFT) }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted small">Category</div>
                                    <span class="badge bg-info">
                                        {{ $paymentReport->category?->label() }}
                                    </span>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="text-muted small">Issue Type</div>
                                    <div class="fw-semibold">
                                        {{ $paymentReport->issue_type?->label() }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted small">Status</div>
                                    <span class="badge bg-primary">
                                        {{ $paymentReport->status?->label() }}
                                    </span>
                                </div>
                            </div>

                            {{-- TRANSACTION --}}
                            @if($paymentReport->transaction)
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="text-muted small">Transaction ID</div>
                                        <div class="fw-semibold">
                                            TXN-{{ $paymentReport->transaction->id }}
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="text-muted small">Amount</div>
                                        <div class="fw-bold text-success">
                                            {{ number_format($paymentReport->transaction->amount, 2) }}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <hr>

                            {{-- MESSAGE --}}
                            <div>
                                <div class="text-muted small mb-2">Message</div>
                                <div class="border rounded p-3 bg-light">
                                    {{ $paymentReport->message }}
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- MANAGER ACTION --}}
                    @if(auth()->id() === $paymentReport->manager_id)
                        <div class="card shadow-sm border-0">
                            <div class="card-header fw-bold">
                                Manager Action
                            </div>

                            <div class="card-body">
                                <form method="POST"
                                      action="{{ route('payment-reports.update-status', $paymentReport) }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="mb-4">
                                        <label class="form-label">Update Status</label>
                                        <select name="status" class="form-select" required>
                                            @foreach(\App\Enum\Payment\ReportStatus::cases() as $status)
                                                <option value="{{ $status->value }}"
                                                    @selected($paymentReport->status === $status)>
                                                    {{ $status->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <button class="btn btn-primary">
                                        Update Status
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- ================= RIGHT : USER & META ================= --}}
                <div class="col-md-4">

                    <div class="card shadow-sm border-0">
                        <div class="card-body">

                            {{-- USER --}}
                            <div class="d-flex align-items-center mb-4">
                                <img
                                    src="{{ $paymentReport->user?->avatar
                                        ? asset($paymentReport->user->avatar)
                                        : asset('/backend/assets/media/avatars/300-1.jpg') }}"
                                    class="rounded-circle me-3"
                                    width="55"
                                    height="55"
                                >

                                <div>
                                    <div class="fw-bold">
                                        {{ $paymentReport->user?->first_name }}
                                        {{ $paymentReport->user?->last_name }}
                                    </div>
                                    <div class="text-muted small">
                                        {{ $paymentReport->user?->email }}
                                    </div>
                                </div>
                            </div>

                            <hr>

                            {{-- MANAGER --}}
                            <div class="mb-3">
                                <div class="text-muted small">Assigned Manager</div>
                                <div class="fw-semibold">
                                    {{ trim(($paymentReport->manager?->first_name ?? '') . ' ' . ($paymentReport->manager?->last_name ?? '')) ?: 'Unassigned' }}
                                </div>
                            </div>

                            {{-- TIME --}}
                            <div class="mb-3">
                                <div class="text-muted small">Created</div>
                                <div class="fw-semibold">
                                    {{ $paymentReport->created_at->format('d M Y, h:i A') }}
                                </div>
                            </div>

                            @if($paymentReport->resolved_at)
                                <div class="mb-3">
                                    <div class="text-muted small">Resolved At</div>
                                    <div class="fw-semibold">
                                        {{ $paymentReport->resolved_at->format('d M Y, h:i A') }}
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>
