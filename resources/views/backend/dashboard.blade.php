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
                        <!--begin::Budget-->
                        <div class="card  h-100">
                            <div class="card-body p-9">
                                <div class="fs-2hx fw-bold">$3,290.00</div>
                                <div class="fs-4 fw-semibold text-gray-400 mb-7">Project Finance</div>

                                <div class="fs-6 d-flex justify-content-between mb-4">
                                    <div class="fw-semibold">Avg. Project Budget</div>
                                    <div class="d-flex fw-bold">
                                        <i class="ki-duotone ki-arrow-up-right fs-3 me-1 text-success"><span
                                                class="path1"></span><span class="path2"></span></i> $6,570
                                    </div>
                                </div>

                                <div class="separator separator-dashed"></div>

                                <div class="fs-6 d-flex justify-content-between my-4">
                                    <div class="fw-semibold">Lowest Project Check</div>

                                    <div class="d-flex fw-bold">
                                        <i class="ki-duotone ki-arrow-down-left fs-3 me-1 text-danger"><span
                                                class="path1"></span><span class="path2"></span></i> $408
                                    </div>
                                </div>

                                <div class="separator separator-dashed"></div>

                                <div class="fs-6 d-flex justify-content-between mt-4">
                                    <div class="fw-semibold">Ambassador Page</div>

                                    <div class="d-flex fw-bold">
                                        <i class="ki-duotone ki-arrow-up-right fs-3 me-1 text-success"><span
                                                class="path1"></span><span class="path2"></span></i> $920
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Budget-->
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

            </div>
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>
    <!--end::Content-->

    @push('script')
    @endpush
@endsection
