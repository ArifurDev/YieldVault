    <!--begin::Javascript-->
    <script>
        var hostUrl = "../assets/index.html";
    </script>

    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="{{ asset('backend/assets') ."/plugins/global/plugins.bundle.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/scripts.bundle.js"}}"></script>
    <!--end::Global Javascript Bundle-->

    <!--begin::Vendors Javascript(used for this page only)-->
    <script src="{{ asset('backend/assets') ."/plugins/custom/datatables/datatables.bundle.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/plugins/custom/formrepeater/formrepeater.bundle.js"}}"></script>
    <!--end::Vendors Javascript-->

    <!--begin::Custom Javascript(used for this page only)-->
    <script src="{{ asset('backend/assets') ."/js/widgets.bundle.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/widgets.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/apps/chat/chat.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/utilities/modals/upgrade-plan.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/utilities/modals/create-project/type.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/utilities/modals/create-project/budget.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/utilities/modals/create-project/settings.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/utilities/modals/create-project/team.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/utilities/modals/create-project/targets.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/utilities/modals/create-project/files.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/utilities/modals/create-project/complete.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/utilities/modals/create-project/main.js"}}"></script>
    <script src="{{ asset('backend/assets') ."/js/custom/utilities/modals/users-search.js"}}"></script>

    <!--end::Custom Javascript-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!--end::Javascript-->
    <script>
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "timeOut": "2000",
            "positionClass": "toast-top-right"
        };

        @if (session('success'))
        toastr.success(@json(session('success')));
        @endif

        @if (session('error'))
        toastr.error(@json(session('error')));
        @endif

        @if ($errors->any())
        @foreach ($errors->all() as $error)
        toastr.error(@json($error));
        @endforeach
        @endif
    </script>
    <script>
        function loadNotifications() {
            $.get("{{ route('notification.index') }}", function (res) {

                const list  = $('#notificationList');
                const badge = $('#notificationBadge');
                const count = $('#notificationCount');
                const icon  = $('#notificationIcon');

                list.empty();

                // ===============================
                // UNREAD BADGE + ICON COLOR
                // ===============================
                if (res.unread_count > 0) {
                    badge.text(res.unread_count).removeClass('d-none');
                    icon.removeClass('text-muted').addClass('text-primary');
                } else {
                    badge.addClass('d-none');
                    icon.removeClass('text-primary').addClass('text-muted');
                }

                count.text(res.unread_count + ' reports');

                // ===============================
                // LIST ITEMS
                // ===============================
                if (!res.notifications.length) {
                    list.html('<div class="text-center text-muted py-5">No notifications</div>');
                    return;
                }

                res.notifications.forEach(n => {

                    const colorMap = {
                        success: 'success',
                        warning: 'warning',
                        danger:  'danger',
                        info:    'primary'
                    };

                    const color = colorMap[n.type] ?? 'primary';

                    list.append(`
                    <div class="d-flex flex-stack py-4 notification-item"
                         data-id="${n.id}"
                         data-action="${n.action ?? ''}">

                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-35px me-4">
                                <span class="symbol-label bg-light-${color}">
                                    <i class="ki-duotone ki-information fs-2 text-${color}"></i>
                                </span>
                            </div>

                            <div>
                                <div class="fw-bold text-gray-800 fs-6">${n.title}</div>
                                <div class="text-gray-400 fs-7">${n.message}</div>
                            </div>
                        </div>

                        <span class="badge badge-light fs-8">${n.created}</span>
                    </div>
                `);
                });
            });
        }

        // Initial load
        loadNotifications();

        // Auto refresh every 30 seconds
        setInterval(loadNotifications, 30000);

        // ===============================
        // READ SINGLE
        // ===============================
        $(document).on('click', '.notification-item', function () {

            const id     = $(this).data('id');
            const action = $(this).data('action');

            $.post("{{ route('notification.read') }}/" + id, {
                _token: "{{ csrf_token() }}"
            }, function () {
                loadNotifications();
            });

        });

        // ===============================
        // READ ALL
        // ===============================
        $('#markAllRead').on('click', function () {

            $.post("{{ route('notification.read') }}", {
                _token: "{{ csrf_token() }}"
            }, function () {
                loadNotifications();
                toastr.success('All notifications marked as read');
            });
        });
    </script>



    <!--end::Custom Javascript-->
    @stack('script')
