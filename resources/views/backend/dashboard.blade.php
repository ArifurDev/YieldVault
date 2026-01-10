@extends('backend.app')

@section('title', 'Admin Dashboard')

@section('content')
    <!--begin::Content-->
    <div class="content fs-6 d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <div class=" container-fluid  d-flex flex-stack flex-wrap flex-sm-nowrap">
                <!--begin::Info-->
                <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                    <!--begin::Title-->
                    <h1 class="text-dark fw-bold my-1 fs-2">
                        Dashboard </h1>
                    <!--end::Title-->

                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="" class="text-muted text-hover-primary">Home </a>
                        </li>

                        <li class="breadcrumb-item text-dark"></li>

                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Info-->

            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Post-->
        <div class="post fs-6 d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div class=" container-xxl ">

                <!--begin:row-->
                <div class="row gx-6 gx-xl-9">

                    <div class="col-lg-6 col-xxl-4">
                        <div class="card h-100">
                            <div class="card-body p-9">

                                <!-- Total KYC -->
                                <div class="fs-2hx fw-bold">
                                    {{ $kycPending + $kycApproved + $kycRejected }}
                                </div>
                                <div class="fs-4 fw-semibold text-gray-400 mb-7">KYC Status Overview</div>

                                <div class="d-flex flex-wrap">

                                    <!-- Chart area -->
                                    <div class="d-flex flex-center h-100px w-100px me-9 mb-5">
                                        <canvas id="kt_kyc_chart" width="100" height="100"></canvas>
                                    </div>

                                    <!-- Status Labels -->
                                    <div class="d-flex flex-column justify-content-center flex-row-fluid pe-11 mb-5">

                                        <div class="d-flex fs-6 fw-semibold align-items-center mb-3">
                                            <div class="bullet bg-success me-3"></div>
                                            <div class="text-gray-400 ">Approved</div>
                                            <div class="ms-auto fw-bold text-gray-700">{{ $kycApproved }}</div>
                                        </div>

                                        <div class="d-flex fs-6 fw-semibold align-items-center mb-3">
                                            <div class="bullet bg-danger me-3"></div>
                                            <div class="text-gray-400">Rejected</div>
                                            <div class="ms-auto fw-bold text-gray-700">{{ $kycRejected }}</div>
                                        </div>

                                        <div class="d-flex fs-6 fw-semibold align-items-center">
                                            <div class="bullet bg-warning me-3"></div>
                                            <div class="text-gray-400">Pending</div>
                                            <div class="ms-auto fw-bold text-gray-700">{{ $kycPending }}</div>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-lg-6 col-xxl-4">
                        <div class="card h-100">
                            <div class="card-body p-9">

                                <!-- Title -->
                                <div class="fs-2hx fw-bold text-dark">
                                    Wallet Summary
                                </div>
                                <div class="fs-4 fw-semibold text-gray-400 mb-7">
                                    Wallet Finance
                                </div>

                                <!-- Total Deposit -->
                                <div class="fs-6 d-flex justify-content-between align-items-center mb-4">
                                    <div class="fw-semibold text-gray-600">
                                        Total Deposit
                                    </div>
                                    <div class="d-flex align-items-center fw-bold text-success">
                                        <i class="ki-duotone ki-arrow-up-right fs-3 me-1 text-success">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                        ${{ number_format($totalDeposit, 2) }}
                                    </div>
                                </div>

                                <div class="separator separator-dashed"></div>

                                <!-- Total Withdraw -->
                                <div class="fs-6 d-flex justify-content-between align-items-center my-4">
                                    <div class="fw-semibold text-gray-600">
                                        Total Withdraw
                                    </div>
                                    <div class="d-flex align-items-center fw-bold text-danger">
                                        <i class="ki-duotone ki-arrow-down-left fs-3 me-1 text-danger">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                        ${{ number_format($totalWithdraw, 2) }}
                                    </div>
                                </div>

                                <div class="separator separator-dashed"></div>

                                <!-- Pending Transactions -->
                                <div class="fs-6 d-flex justify-content-between align-items-center my-4">
                                    <div class="fw-semibold text-gray-600">
                                        Pending Transactions
                                    </div>
                                    <div class="fw-bold text-warning">
                                        {{ $pendingTransactions }}
                                    </div>
                                </div>

                                <div class="separator separator-dashed"></div>

                                <!-- Failed Transactions -->
                                <div class="fs-6 d-flex justify-content-between align-items-center mt-4">
                                    <div class="fw-semibold text-gray-600">
                                        Reject Transactions
                                    </div>
                                    <div class="fw-bold text-danger">
                                        {{ $failedTransactions }}
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>


                    <div class="col-md-6 col-xl-4">

                        <!--begin::Card-->
                        <div class="card border-hover-primary">

                            <!--begin::Card header-->
                            <div class="card-header border-0 pt-9">

                                <!--begin::Card Title-->
                                <div class="card-title m-0">
                                    <div class="symbol symbol-50px w-50px bg-light">
                                        <i class="ki-duotone ki-profile-user fs-2x text-primary">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </div>
                                </div>
                                <!--end::Card Title-->

                                <!--begin::Card toolbar-->
                                <div class="card-toolbar">
                <span class="badge badge-light-primary fw-bold me-auto px-4 py-3">
                    In Progress
                </span>
                                </div>
                                <!--end::Card toolbar-->

                            </div>
                            <!--end::Card header-->

                            <!--begin::Card body-->
                            <div class="card-body p-9">

                                <!--begin::Name-->
                                <div class="fs-3 fw-bold text-dark">
                                    Users Overview
                                </div>
                                <!--end::Name-->

                                <!--begin::Description-->
                                <p class="text-gray-400 fw-semibold fs-5 mt-1 mb-7">
                                    Total users & referral-based users
                                </p>
                                <!--end::Description-->

                                <!--begin::Info-->
                                <div class="d-flex flex-wrap mb-5">

                                    <!--begin::Total Users-->
                                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-7 mb-3">
                                        <div class="fs-6 text-gray-800 fw-bold">
                                            {{ $totalUsers }}
                                        </div>
                                        <div class="fw-semibold text-gray-400">
                                            Total Users
                                        </div>
                                    </div>
                                    <!--end::Total Users-->

                                    <!--begin::Referral Users-->
                                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 mb-3">
                                        <div class="fs-6 text-gray-800 fw-bold">
                                            {{ $referralUsers }}
                                        </div>
                                        <div class="fw-semibold text-gray-400">
                                            Referral Users
                                        </div>
                                    </div>
                                    <!--end::Referral Users-->

                                </div>
                                <!--end::Info-->

                                <!--begin::Progress-->
                                <div class="h-4px w-100 bg-light mb-5"
                                     data-bs-toggle="tooltip"
                                     aria-label="{{ $referralProgress }}% users joined via referral"
                                     data-bs-original-title="{{ $referralProgress }}% users joined via referral">

                                    <div class="bg-primary rounded h-4px"
                                         role="progressbar"
                                         style="width: {{ $referralProgress }}%"
                                         aria-valuenow="{{ $referralProgress }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                <!--end::Progress-->

                            </div>
                            <!--end::Card body-->

                        </div>
                        <!--end::Card-->

                    </div>


                </div>
                <!--end:row-->


                <!--begin:2n row-->
                <div class="row gx-6 gx-xl-9 mt-6">
                    {{-- User Growth --}}
                    <div class="col-xl-8">
                        <div class="card h-100">

                            {{-- Header --}}
                            <div class="card-header border-0 d-flex align-items-center justify-content-between py-5">
                                <div>
                                    <h3 class="card-title fw-bold mb-1">User Growth</h3>
                                    <span class="text-muted fs-7">New user registrations over time</span>
                                </div>

                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-primary growth-filter active" data-filter="week">This Week</button>
                                    <button class="btn btn-light growth-filter" data-filter="month">Last Month</button>
                                    <button class="btn btn-light growth-filter" data-filter="year">This Year</button>
                                    <button class="btn btn-light growth-filter" data-filter="all">All</button>
                                </div>
                            </div>

                            {{-- Body --}}
                            <div class="card-body">
                                <div style="height: 320px;">
                                    <canvas id="userGrowthChart"></canvas>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card h-100">

                            <!-- Header -->
                            <div class="card-header border-0">
                                <h3 class="card-title fw-bold">Quick Actions</h3>
                                <span class="text-muted fs-7">Admin shortcuts</span>
                            </div>

                            <!-- Body -->
                            <div class="card-body pt-2">

                                <div class="d-grid gap-4">

                                    <!-- Users -->
                                    <a href="{{ route('user.index') }}"
                                       class="d-flex align-items-center p-4 rounded border border-dashed border-gray-300 hover-elevate-up">
                                        <span class="symbol symbol-40px me-4 bg-light-primary">
                                            <i class="ki-duotone ki-profile-user fs-2 text-primary">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                        </span>
                                        <div>
                                            <div class="fw-bold text-dark">Manage Users</div>
                                            <div class="text-muted fs-7">View & control users</div>
                                        </div>
                                    </a>

                                    <!-- KYC -->
                                    <a href="{{ route('kyc.index') }}"
                                       class="d-flex align-items-center p-4 rounded border border-dashed border-gray-300 hover-elevate-up">
                                        <span class="symbol symbol-40px me-4 bg-light-warning">
                                            <i class="ki-duotone ki-shield-tick fs-2 text-warning">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                        </span>
                                        <div>
                                            <div class="fw-bold text-dark">KYC Requests</div>
                                            <div class="text-muted fs-7">Approve / Reject users</div>
                                        </div>
                                    </a>

                                    <!-- Transactions -->
                                    <a href="{{ route('transaction.index') }}"
                                       class="d-flex align-items-center p-4 rounded border border-dashed border-gray-300 hover-elevate-up">
                                        <span class="symbol symbol-40px me-4 bg-light-success">
                                            <i class="ki-duotone ki-wallet fs-2 text-success">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                        </span>
                                        <div>
                                            <div class="fw-bold text-dark">Transactions</div>
                                            <div class="text-muted fs-7">Deposit & Withdraw logs</div>
                                        </div>
                                    </a>

                                    <!-- Tasks -->
                                    <a href="{{ route('tasks.index') }}"
                                       class="d-flex align-items-center p-4 rounded border border-dashed border-gray-300 hover-elevate-up">
                                        <span class="symbol symbol-40px me-4 bg-light-info">
                                            <i class="ki-duotone ki-check-circle fs-2 text-info">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                        </span>
                                        <div>
                                            <div class="fw-bold text-dark">Manage Tasks</div>
                                            <div class="text-muted fs-7">Daily & user tasks</div>
                                        </div>
                                    </a>

                                </div>

                            </div>
                        </div>
                    </div>

                </div>
                <!--end:2n row-->

                <!-- begin:3rd row-->
                <div class="row gx-6 gx-xl-9 mt-5">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-0">
                                <h3 class="card-title fw-bold">Recent Transactions</h3>

                                <div class="card-toolbar">
                                    <a href="{{ route('transaction.index') }}"
                                       class="btn btn-sm btn-light-primary">
                                        View All
                                    </a>
                                </div>
                            </div>

                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                                        <thead>
                                        <tr class="text-muted fw-bold fs-7 text-uppercase">
                                            <th>From</th>
                                            <th>To</th>
                                            <th>Amount</th>
                                            <th>Type</th>
                                            <th>Source</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody id="recentTransactionTable">
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-5">
                                                Loading...
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!---end:3rd row-->

            </div>
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>
    <!--end::Content-->

    @push('script')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                let growthChart = null;

                function loadUserGrowth(filter = 'week') {
                    $.get("{{ route('dashboard.user-growth') }}", { filter }, function (res) {

                        const ctx = document.getElementById('userGrowthChart').getContext('2d');

                        if (growthChart) {
                            growthChart.destroy();
                        }

                        // Gradient
                        const gradient = ctx.createLinearGradient(0, 0, 0, 320);
                        gradient.addColorStop(0, 'rgba(62,151,255,0.35)');
                        gradient.addColorStop(1, 'rgba(62,151,255,0.02)');

                        growthChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: res.labels.length ? res.labels : ['No Data'],
                                datasets: [{
                                    label: 'Users',
                                    data: res.values.length ? res.values : [0],
                                    borderColor: '#3E97FF',
                                    backgroundColor: gradient,
                                    borderWidth: 3,
                                    tension: 0.45,
                                    fill: true,
                                    pointRadius: 5,
                                    pointHoverRadius: 7,
                                    pointBackgroundColor: '#ffffff',
                                    pointBorderColor: '#3E97FF',
                                    pointBorderWidth: 2,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        backgroundColor: '#1e1e2d',
                                        titleColor: '#ffffff',
                                        bodyColor: '#ffffff',
                                        padding: 12,
                                        cornerRadius: 8,
                                        displayColors: false,
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: { display: false },
                                        ticks: {
                                            color: '#A1A5B7',
                                            font: { size: 12 }
                                        }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        grid: {
                                            borderDash: [5, 5],
                                            color: '#EFF2F5'
                                        },
                                        ticks: {
                                            precision: 0,
                                            color: '#A1A5B7',
                                            font: { size: 12 }
                                        }
                                    }
                                }
                            }
                        });
                    });
                }

                // Initial Load
                loadUserGrowth();

                // Filter Button Click
                $(document).on('click', '.growth-filter', function () {

                    $('.growth-filter')
                        .removeClass('btn-primary active')
                        .addClass('btn-light');

                    $(this)
                        .addClass('btn-primary active')
                        .removeClass('btn-light');

                    loadUserGrowth($(this).data('filter'));
                });
            </script>
            <script>
                function loadRecentTransactions() {
                    $.get("{{ route('dashboard.recent-transactions') }}", function (data) {

                        const table = $('#recentTransactionTable');
                        table.empty();

                        if (data.length === 0) {
                            table.html(`
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                No recent transactions
                            </td>
                        </tr>
                    `);
                            return;
                        }

                        data.forEach(trx => {

                            const statusColor = {
                                approved: 'success',
                                pending: 'warning',
                                rejected: 'danger',
                                paid: 'primary'
                            }[trx.status.toLowerCase()] ?? 'secondary';

                            table.append(`
                        <tr>
                            <td>${trx.from}</td>
                            <td>${trx.to}</td>
                            <td class="fw-bold">$${trx.amount}</td>
                            <td>
                                <span class="badge badge-light-info">
                                    ${trx.type}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-light-primary">
                                    ${trx.source}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-light-${statusColor}">
                                    ${trx.status}
                                </span>
                            </td>
                            <td>${trx.date}</td>
                            <td class="text-end">
                                <a href="/admin/transaction/transactions/${trx.id}"
                                   class="btn btn-sm btn-light">
                                    View
                                </a>
                            </td>
                        </tr>
                    `);
                        });
                    });
                }

                loadRecentTransactions();
            </script>
    @endpush
@endsection
