<div class="d-flex align-items-center ms-1 ms-lg-2">
    <!--begin::Menu wrapper-->
    <div class="btn btn-icon btn-active-light-primary w-30px h-30px w-md-40px h-md-40px"
        data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent"
        data-kt-menu-placement="bottom-end">
        <i class="ki-duotone ki-element-11 fs-1"><span class="path1"></span><span class="path2"></span><span
                class="path3"></span><span class="path4"></span></i>
    </div>

    <!--begin::Menu-->
    <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px" data-kt-menu="true"
        id="kt_menu_notifications" style="">
        <!--begin::Heading-->
        <div class="d-flex flex-column bgi-no-repeat rounded-top"
            style="background-image: url({{ asset('backend/assets/media/misc/menu-header-bg.jpg') }})">
            <!--begin::Title-->
            <h3 class="text-white fw-semibold px-9 mt-10 mb-6">
                Notifications <span class="fs-8 opacity-75 ps-3">24 reports</span>
            </h3>
            <!--end::Title-->

            <!--begin::Tabs-->
            <ul class="nav nav-line-tabs nav-line-tabs-2x nav-stretch fw-semibold px-9" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link text-white opacity-75 opacity-state-100 pb-4 active" data-bs-toggle="tab"
                        href="#kt_topbar_notifications_1" aria-selected="false" tabindex="-1" role="tab">Alerts</a>
                </li>
            </ul>
            <!--end::Tabs-->
        </div>
        <!--end::Heading-->

        <!--begin::Tab content-->
        <div class="tab-content">
            <!--begin::Tab panel-->
            <div class="tab-pane fade show active" id="kt_topbar_notifications_1" role="tabpanel">
                <!--begin::Items-->
                <div class="scroll-y mh-325px my-5 px-8">
                    <!--begin::Item-->
                    <div class="d-flex flex-stack py-4">
                        <!--begin::Section-->
                        <div class="d-flex align-items-center">
                            <!--begin::Symbol-->
                            <div class="symbol symbol-35px me-4">
                                <span class="symbol-label bg-light-primary">
                                    <i class="ki-duotone ki-abstract-28 fs-2 text-primary"><span
                                            class="path1"></span><span class="path2"></span></i>
                                </span>
                            </div>
                            <!--end::Symbol-->

                            <!--begin::Title-->
                            <div class="mb-0 me-2">
                                <a href="#" class="fs-6 text-gray-800 text-hover-primary fw-bold">Project
                                    Alice</a>
                                <div class="text-gray-400 fs-7">Phase 1 development
                                </div>
                            </div>
                            <!--end::Title-->
                        </div>
                        <!--end::Section-->

                        <!--begin::Label-->
                        <span class="badge badge-light fs-8">1 hr</span>
                        <!--end::Label-->
                    </div>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <div class="d-flex flex-stack py-4">
                        <!--begin::Section-->
                        <div class="d-flex align-items-center">
                            <!--begin::Symbol-->
                            <div class="symbol symbol-35px me-4">
                                <span class="symbol-label bg-light-danger">
                                    <i class="ki-duotone ki-information fs-2 text-danger"><span
                                            class="path1"></span><span class="path2"></span><span
                                            class="path3"></span></i>
                                </span>
                            </div>
                            <!--end::Symbol-->

                            <!--begin::Title-->
                            <div class="mb-0 me-2">
                                <a href="#" class="fs-6 text-gray-800 text-hover-primary fw-bold">HR
                                    Confidential</a>
                                <div class="text-gray-400 fs-7">Confidential staff
                                    documents</div>
                            </div>
                            <!--end::Title-->
                        </div>
                        <!--end::Section-->

                        <!--begin::Label-->
                        <span class="badge badge-light fs-8">2 hrs</span>
                        <!--end::Label-->
                    </div>
                    <!--end::Item-->
                </div>
                <!--end::Items-->

                <!--begin::View more-->
                <div class="py-3 text-center border-top">
                    <a href="#" class="btn btn-color-gray-600 btn-active-color-primary">
                        View All
                        <i class="ki-duotone ki-arrow-right fs-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </a>
                </div>
                <!--end::View more-->
            </div>
            <!--end::Tab panel-->
        </div>
        <!--end::Tab content-->
    </div>
    <!--end::Menu--> <!--end::Menu wrapper-->
</div>
