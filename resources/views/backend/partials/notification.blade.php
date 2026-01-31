

<div class="d-flex align-items-center ms-1 ms-lg-2">

    <!-- Notification Button -->
    <div class="btn btn-icon btn-active-light-primary w-30px h-30px w-md-40px h-md-40px position-relative"
         id="notificationBtn"
         data-kt-menu-trigger="{default:'click',lg:'hover'}"
         data-kt-menu-attach="parent"
         data-kt-menu-placement="bottom-end">

        <i id="notificationIcon"
           class="ki-duotone ki-element-11 fs-1 text-muted">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
        </i>

        <!-- Unread Badge -->
        <span id="notificationBadge"
              class="position-absolute top-0 start-100 translate-middle badge badge-circle badge-danger d-none">
        </span>
    </div>

    <!-- Dropdown -->
    <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px"
         data-kt-menu="true">

        <!-- Header -->
        <div class="d-flex flex-column bgi-no-repeat rounded-top"
             style="background-image:url({{ asset('backend/assets/media/misc/menu-header-bg.jpg') }})">
            <h3 class="text-white fw-semibold px-9 mt-10 mb-6">
                Notifications
                <span class="fs-8 opacity-75 ps-3" id="notificationCount">0 reports</span>
            </h3>
        </div>

        <!-- Mark all read -->
        <div class="px-8 py-3 border-bottom text-end">
            <button id="markAllRead" class="btn btn-sm btn-light-primary">
                Mark all as read
            </button>
        </div>

        <!-- Body -->
        <div class="scroll-y mh-325px my-5 px-8" id="notificationList">
            <div class="text-center text-muted py-5">Loading...</div>
        </div>

        <!-- Footer -->
        <div class="py-3 text-center border-top">
            <a href="{{ route('notification.all') }}"
               class="btn btn-color-gray-600 btn-active-color-primary">
                View All
                <i class="ki-duotone ki-arrow-right fs-5"></i>
            </a>
        </div>
    </div>
</div>
