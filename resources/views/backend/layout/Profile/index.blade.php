@extends('backend.app')

@section('title', 'Profile')

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
                        Profile </h1>
                    <!--end::Title-->

                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home </a>
                        </li>

                        <li class="breadcrumb-item text-dark">
                            Profile
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

                <div class="d-flex flex-column flex-lg-row">

                    <!--begin::Layout-->
                    <div class="flex-md-row-fluid ms-lg-12">
                        <!--begin::Overview-->
                        <div class="card  mb-5 mb-xl-10" id="kt_account_settings_overview" data-kt-scroll-offset="{default: 100, md: 125}">
                            <!--begin::Card header-->
                            <div class="card-header border-0 ">
                                <div class="card-title">
                                    <h3 class="fw-bold m-0">Overview</h3>
                                </div>
                            </div>
                            <!--end::Card header-->

                            <!--begin::Content-->
                            <div id="kt_account_settings_overview" class="collapse show">
                                <!--begin::Card body-->
                                <div class="card-body border-top p-9">
                                    <div class="d-flex align-items-start flex-wrap">
                                        <div class="d-flex flex-wrap">
                                            <!--begin::Avatar-->
                                            <div class="symbol symbol-125px mb-7 me-7 position-relative">
                                                <img src="{{ $imageUrl ?? asset('backend/assets') . "/media/avatars/300-1.jpg" }}" alt="image">
                                            </div>
                                            <!--end::Avatar-->

                                            <!--begin::Profile-->
                                            <div class="d-flex flex-column">
                                                <div class="fs-2 fw-bold mb-1">{{ $user?->first_name .' '. $user?->last_name }}</div>
                                                <a href="#" class="text-gray-400 text-hover-primary fs-6 fw-semibold mb-5">{{ $user?->email }}</a>
                                                <div class="badge badge-light-success text-success fw-bold fs-7 me-auto mb-3 px-4 py-3">
                                                    {{ $user?->role }}</div>
                                            </div>
                                            <!--end::Profile-->
                                        </div>
                                    </div>
                                </div>
                                <!--end::Card body-->
                            </div>
                            <!--end::Content-->
                        </div>
                        <!--end::Overview-->
                        <!--begin::Sign-in Method-->
                        <div class="card  mb-5 mb-xl-10">
                            <!--begin::Card header-->
                            <div class="card-header border-0">
                                <div class="card-title m-0">
                                    <h3 class="fw-bold m-0">Sign-in Method</h3>
                                </div>
                            </div>
                            <!--end::Card header-->

                            <!--begin::Content-->
                            <div id="kt_account_settings_signin_method" class="collapse show" tabindex="-1" style="outline: none;">
                                <!--begin::Card body-->
                                <div class="card-body border-top p-9">
                                    <!--begin::Email Address-->
                                    <div class="d-flex flex-wrap align-items-center">
                                        <!--begin::Label-->
                                        <div id="kt_signin_email">
                                            <div class="fs-6 fw-bold mb-1">Email Address</div>
                                            <div class="fw-semibold text-gray-600">{{ $user?->email }}</div>
                                        </div>
                                        <!--end::Label-->

                                        <!--begin::Edit-->
                                            @include('backend.partials.profile.email_edit')
                                        <!--end::Edit-->

                                        <!--begin::Action-->
                                        <div id="kt_signin_email_button" class="ms-auto">
                                            <button class="btn btn-light btn-active-light-primary">Change Email</button>
                                        </div>
                                        <!--end::Action-->
                                    </div>
                                    <!--end::Email Address-->

                                    <!--begin::Separator-->
                                    <div class="separator separator-dashed my-6"></div>
                                    <!--end::Separator-->

                                    <!--begin::Password-->
                                    @include('backend.partials.profile.password_change')
                                    <!--end::Password-->
                                </div>
                                <!--end::Card body-->
                            </div>
                            <!--end::Content-->
                        </div>
                        <!--end::Sign-in Method-->

                        <!--begin::Basic info-->
                        <div class="card mb-5 mb-xl-10">
                            <!--begin::Card header-->
                            <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
                                <!--begin::Card title-->
                                <div class="card-title m-0">
                                    <h3 class="fw-bold m-0">Profile Details</h3>
                                </div>
                                <!--end::Card title-->
                            </div>
                            <!--begin::Card header-->

                            <!--begin::Content-->
                            @include('backend.partials.profile.information_change')
                            <!--end::Content-->
                        </div>
                        <!--end::Basic info-->
                    <!--end::Layout-->
                </div>

            </div>
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>
    <!--end::Content-->

    @push('script')
            <!--begin::Javascript(only this page)-->
            <script src="{{ asset('backend/assets') ."/js/custom/account/settings/signin-methods.js"}}"></script>
            <script src="{{ asset('backend/assets') ."/js/custom/account/settings/profile-details.js"}}"></script>
            <script src="{{ asset('backend/assets') ."/js/custom/pages/user-profile/general.js"}}"></script>
            <!--end::Javascript-->

            <script>
                // Toggle eye icon
                document.querySelectorAll('.toggle-password').forEach(toggle => {
                    toggle.addEventListener('click', function () {
                        const input = document.getElementById(this.dataset.target);
                        if (input.type === 'password') {
                            input.type = 'text';
                            this.innerHTML = '<i class="fa fa-eye-slash"></i>';
                        } else {
                            input.type = 'password';
                            this.innerHTML = '<i class="fa fa-eye"></i>';
                        }
                    });
                });

                // Password match check
                const newPass = document.getElementById('password');
                const confirmPass = document.getElementById('password_confirmation');
                const msg = document.getElementById('password-match-msg');

                [newPass, confirmPass].forEach(input => {
                    input.addEventListener('input', () => {
                        if (newPass.value && confirmPass.value) {
                            msg.textContent = newPass.value === confirmPass.value ? "Passwords match ✅" : "Passwords do not match ❌";
                            msg.classList.toggle("text-danger", newPass.value !== confirmPass.value);
                            msg.classList.toggle("text-success", newPass.value === confirmPass.value);
                        } else {
                            msg.textContent = '';
                        }
                    });
                });
            </script>


    @endpush
@endsection
