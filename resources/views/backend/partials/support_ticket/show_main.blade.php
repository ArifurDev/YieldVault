<div class=" container-xxl " data-select2-id="select2-data-198-e71y">
    <!--begin::Card-->
    <div class="card" data-select2-id="select2-data-197-m5it">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6" data-select2-id="select2-data-196-qcil">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->

                <!--end::Search-->
            </div>
            <!--begin::Card title-->

            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base"
                     data-select2-id="select2-data-195-9exk">

                    {{--                    <!--begin::Add user-->--}}
                    {{--                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"--}}
                    {{--                            data-bs-target="#kt_modal_add_user">--}}
                    {{--                        <i class="ki-duotone ki-plus fs-2"></i> Add Level--}}
                    {{--                    </button>--}}
                    <!--end::Add user-->
                </div>
                <!--end::Toolbar-->

                <!--begin::Group actions-->
                <div class="d-flex justify-content-end align-items-center d-none"
                     data-kt-user-table-toolbar="selected">
                    <div class="fw-bold me-5">
                        <span class="me-2" data-kt-user-table-select="selected_count"></span> Selected
                    </div>

                    <button type="button" class="btn btn-danger" data-kt-user-table-select="delete_selected">
                        Delete Selected
                    </button>
                </div>
                <!--end::Group actions-->

                <!--begin::Modal - Add task-->
                @include('backend.partials.level.add')
                <!--end::Modal - Add task-->
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body py-4">

            <!--begin::Table-->
            <div class="row">
                <!-- LEFT: CHAT -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <strong>#{{ str_pad($ticket->id,6,'0',STR_PAD_LEFT) }}</strong>
                            — {{ $ticket->subject }}
                        </div>

                        <div class="card-body chat-body"  id="chatBody">

                            @foreach($ticket->messages as $msg)
                                    <div id="message-{{ $msg->id }}"
                                        class="mb-4 message-item {{ $msg->sender_id === auth()->id() ? 'text-end' : '' }}">

                                    {{-- Sender info --}}
                                    <div class="small text-muted mb-1">
                                        {{ $msg->sender->first_name }}
                                        • {{ $msg->created_at->diffForHumans() }}
                                    </div>

                                    {{-- Reply reference --}}
                                        @if($msg->replyTo)
                                            <div
                                                class="border-start ps-2 mb-1 text-muted small text-start"
                                                style="cursor: pointer"
                                                onclick="scrollToMessage({{ $msg->replyTo->id }})">
                                                ↪ Replying to:
                                                "{{ \Illuminate\Support\Str::limit($msg->replyTo->message, 60) }}"
                                            </div>
                                        @endif


                                        {{-- Message bubble --}}
                                    <div class="d-inline-block p-3 rounded
                                        {{ $msg->sender_id === auth()->id()
                                            ? 'bg-primary text-white'
                                            : 'bg-light' }}">
                                        {{ $msg->message }}
                                    </div>

                                    {{-- Reply button --}}
                                    <div class="mt-1">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-link text-decoration-none reply-btn"
                                            data-id="{{ $msg->id }}"
                                            data-message="{{ e($msg->message) }}">
                                            ↩ Reply
                                        </button>

                                    </div>

                                </div>
                            @endforeach


                        </div>




                        @if($ticket->status === \App\Enum\Support\Status::CLOSED)
                            <div class="alert alert-warning text-center">
                                This ticket has been closed. Reply is disabled.
                            </div>
                        @else
                            <!-- REPLY FOOTER -->
                            <div class="card-footer">
                                <form method="POST" action="{{ route('support-tickets.reply',$ticket) }}">
                                    @csrf

                                    <div id="replyPreview" class="alert alert-secondary d-none">
                                        <strong>Replying to:</strong>
                                        <span id="replyText"></span>
                                        <button type="button"
                                                class="btn-close float-end"
                                                onclick="clearReply()"></button>
                                    </div>

                                    <input type="hidden" name="reply_to_id" id="reply_to_id">

                                    <textarea name="message"
                                              class="form-control"
                                              rows="3"
                                              placeholder="Write your reply..."
                                              required></textarea>

                                    <div class="text-end mt-2">
                                        <button class="btn btn-primary">Send Reply</button>
                                    </div>
                                </form>
                            </div>

                        @endif



                    </div>
                </div>

                <!-- RIGHT: DETAILS -->

                <div class="col-md-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">

                            {{-- USER PROFILE --}}
                            <div class="d-flex align-items-center mb-4">
                                <img
                                    src="{{ $ticket->user->avatar
                                    ? asset($ticket->user->avatar)
                                    : asset('/backend/assets/media/avatars/300-1.jpg') }}"
                                                class="rounded-circle me-3"
                                                width="55"
                                                height="55"
                                >

                                <div>
                                    <div class="fw-bold">
                                        {{ $ticket->user->first_name }} {{ $ticket->user->last_name }}
                                    </div>
                                    <div class="text-muted small">
                                        {{ $ticket->user->email }}
                                    </div>
                                </div>
                            </div>

                            <hr>

                            {{-- TICKET TIME INFO --}}
                            <div class="mb-3">
                                <div class="text-muted small">Created</div>
                                <div class="fw-semibold">
                                    {{ $ticket->created_at->format('d M Y, h:i A') }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="text-muted small">Last Updated</div>
                                <div class="fw-semibold">
                                    {{ $ticket->updated_at->diffForHumans() }}
                                </div>
                            </div>

                            <hr>

                            {{-- META --}}
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Category</span>
                                <span class="fw-semibold">
                                    {{ ucfirst($ticket->category->value) }}
                                </span>
                            </div>

                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Priority</span>
                                <span class="badge bg-warning">
                                    {{ ucfirst($ticket->priority->value) }}
                                </span>
                            </div>

                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Status</span>
                                <span class="badge bg-info">
                                    {{ ucfirst($ticket->status->value) }}
                                </span>
                            </div>

                            <div class="mb-4 d-flex justify-content-between">
                                <span class="text-muted">Assigned</span>
                                <span class="fw-semibold">
                                    {{ $ticket->assignedTo?->first_name ?? 'Unassigned' }}
                                </span>
                            </div>

                            {{-- CREATE PAYMENT REPORT --}}
                            @if(
                                in_array($ticket->category->value, ['deposit','withdrawal'])
                                && $ticket->user->role !== \App\Enum\Role::MANAGER
                            )
                                @if(!$ticket->paymentReport)
                                    <a href="{{ route('payment-reports.create', $ticket->id) }}"
                                       class="btn btn-warning w-100 mb-2">
                                        Create Payment Report
                                    </a>
                                @else
                                    <button class="btn btn-outline-success w-100 mb-2" disabled>
                                        Payment Report Created
                                    </button>
                                @endif
                            @endif


                            {{-- ACTION --}}
                            @if($ticket->status !== \App\Enum\Support\Status::CLOSED)
                                <form method="POST"
                                      action="{{ route('support-tickets.close', $ticket) }}"
                                      onsubmit="return confirm('Are you sure you want to close this ticket?');">
                                    @csrf

                                    <button class="btn btn-danger w-100">
                                        Close Ticket
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-secondary w-100" disabled>
                                    Ticket Closed
                                </button>
                            @endif


                        </div>
                    </div>
                </div>


            </div>
            <!--end::Table-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
</div>



