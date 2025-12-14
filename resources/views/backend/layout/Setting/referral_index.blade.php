@extends('backend.app')

@section('title', 'referral settings')

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
                        invite referral points & amount settings </h1>
                    <!--end::Title-->

                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home </a>
                        </li>

                        <li class="breadcrumb-item text-dark">
                             Referral settings
                        </li>

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
                <!--begin::Basic info-->
                <div class="card mb-5 mb-xl-10">
                    <!--begin::Card header-->
                    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
                        <!--begin::Card title-->
                        <div class="card-title m-0">
                            <h3 class="fw-bold m-0">Referral settings</h3>
                        </div>
                        <!--end::Card title-->
                    </div>
                    <!--begin::Card header-->

                    <!--begin::Content-->
                    <div  class="collapse show">
                        <!--begin::Form-->
                        <form action="{{ route('settings.referral.store') }}" method="POST"  class="form fv-plugins-bootstrap5 fv-plugins-framework" novalidate="novalidate">
                            @csrf
                            <!--begin::Card body-->
                            <div class="card-body border-top p-9">
                                <!-- Second Row: SMTP Configuration -->
                                <div class="d-flex flex-column flex-md-row gap-5 mb-6">
                                    <!-- Referral XP -->
                                    <div class="col-md-6 fv-row">
                                        <label class="required form-label">Referral XP (Points)</label>
                                        <input
                                            type="number"
                                            name="referral_xp"
                                            class="form-control form-control-solid @error('referral_xp') is-invalid @enderror"
                                            placeholder="Enter referral XP"
                                            value="{{ old('referral_xp', $setting->referral_xp) }}"
                                            min="0"
                                        >
                                        @error('referral_xp')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Referral Amount -->
                                    <div class="col-md-6 fv-row">
                                        <label class="required form-label">Referral Amount</label>
                                        <input
                                            type="number"
                                            name="referral_amount"
                                            step="0.01"
                                            class="form-control form-control-solid @error('referral_amount') is-invalid @enderror"
                                            placeholder="Enter referral amount"
                                            value="{{ old('referral_amount', $setting->referral_amount) }}"
                                            min="0"
                                        >
                                        @error('referral_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>
                            </div>
                            <!--end::Card body-->

                            <!--begin::Actions-->
                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                                <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit">Save Changes</button>
                            </div>
                            <!--end::Actions-->
                            <input type="hidden"></form>
                        <!--end::Form-->
                    </div>

                    <!--end::Content-->
                </div>
                <!--end::Basic info-->

                <!--end::Container-->
            </div>
            <!--end::Post-->
        </div>
        <!--end::Content-->

        @push('script')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const togglePassword = document.getElementById('togglePassword');
                    const passwordField = document.getElementById('mail_password');
                    const passwordIcon = document.getElementById('passwordIcon');

                    togglePassword.addEventListener('click', function () {
                        if (passwordField.type === 'password') {
                            passwordField.type = 'text';
                            passwordIcon.classList.remove('fa-eye');
                            passwordIcon.classList.add('fa-eye-slash');
                        } else {
                            passwordField.type = 'password';
                            passwordIcon.classList.remove('fa-eye-slash');
                            passwordIcon.classList.add('fa-eye');
                        }
                    });
                });
            </script>
    @endpush
@endsection
