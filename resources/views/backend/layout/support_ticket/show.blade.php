@extends('backend.app')

@section('title', 'Support Ticket Details')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <style>
        .dataTables_filter {
            display: none !important;
        }
        .chat-body {
            max-height: 500px;
            overflow-y: auto;
            scroll-behavior: smooth;
        }

        .highlight-message {
            background-color: #fff3cd;
            border-radius: 6px;
            padding: 4px;
            transition: background-color 0.5s ease;
        }


    </style>
@endpush

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
                        List of Support Tickets </h1>
                    <!--end::Title-->

                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home </a>
                        </li>

                        <li class="breadcrumb-item text-dark">
                            Support Tickets
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
            @include('backend.partials.support_ticket.show_main')
            <!--end::Post-->
        </div>
        <!--end::Content-->

        @push('script')
            <script src="{{ asset('backend/assets') . '/js/custom/apps/user-management/users/list/table.js' }}"></script>
            <script src="{{ asset('backend/assets') . '/js/custom/apps/user-management/users/list/export-users.js' }}"></script>
            <script src="{{ asset('backend/assets') . '/js/custom/apps/user-management/users/list/add.js' }}"></script>
            <script src="{{ asset('backend/assets') . '/js/custom/utilities/modals/users-search.js' }}"></script>

            {{--  Include DataTables --}}
            <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

            <script>

               document.addEventListener("DOMContentLoaded", function () {
                   const chatBody = document.getElementById('chatBody');
                   if (chatBody) {
                     chatBody.scrollTop = chatBody.scrollHeight;
                   }
               });
                document.addEventListener('click', function (e) {
                    if (e.target.classList.contains('reply-btn')) {
                        const id = e.target.dataset.id;
                        const message = e.target.dataset.message;

                        setReply(id, message);
                    }
                });

                function setReply(id, text) {
                    document.getElementById('reply_to_id').value = id;
                    document.getElementById('replyText').innerText = text;
                    document.getElementById('replyPreview').classList.remove('d-none');

                    document.querySelector('textarea[name="message"]').focus();
                }

                function clearReply() {
                    document.getElementById('reply_to_id').value = '';
                    document.getElementById('replyPreview').classList.add('d-none');
                }

               function scrollToMessage(messageId) {
                   const el = document.getElementById('message-' + messageId);
                   if (!el) return;

                   el.scrollIntoView({
                       behavior: 'smooth',
                       block: 'center'
                   });

                   el.classList.add('highlight-message');

                   setTimeout(() => {
                       el.classList.remove('highlight-message');
                   }, 1500);
               }
            </script>


    @endpush
@endsection
