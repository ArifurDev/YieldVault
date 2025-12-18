@extends('backend.app')

@section('title', 'List of Levels')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <style>
        .dataTables_filter {
            display: none !important;
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
                        List of Levels </h1>
                    <!--end::Title-->

                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home </a>
                        </li>

                        <li class="breadcrumb-item text-dark">
                            Levels
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
            @include('backend.partials.level.index_main')
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
                $('input[data-kt-user-table-filter="search"]').on('keyup', function() {
                    $('#kt_table_users').DataTable().search(this.value).draw();
                });

                $(document).ready(function() {
                    $('#kt_table_users').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ route('level.index') }}",
                            type: "get",
                        },
                        columns: [
                            // {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                            { data: 'icon', name: 'icon', orderable: false, searchable: false },
                            { data: 'name', name: 'name' },
                            { data: 'required_xp', name: 'required_xp' },
                            { data: 'daily_roi', name: 'daily_roi' },
                            { data: 'status', name: 'status' },
                            { data: 'actions', name: 'actions', orderable: false, searchable: false }
                        ],
                        drawCallback: function() {
                            if (typeof KTMenu !== 'undefined') {
                                KTMenu.createInstances();
                            }
                        }
                    });
                });





                // edit modal show
                $(document).on('click', '.edit-level-btn', function (e) {
                    e.preventDefault();

                    const levelId = $(this).data('id');

                    $.ajax({
                        url: "{{ route('level.edit', ':id') }}".replace(':id', levelId),
                        type: "GET",
                        success: function (res) {
                            if (!res.status) {
                                Swal.fire('Error', res.message, 'error');
                                return;
                            }

                            const level = res.data;

                            $('#editLevelForm').attr(
                                'action',
                                "{{ route('level.update', ':id') }}".replace(':id', level.id)
                            );

                            $('#edit_id').val(level.id);
                            $('#edit_name').val(level.name);
                            $('#edit_slug').val(level.slug);
                            $('#edit_required_xp').val(level.required_xp);
                            $('#edit_daily_roi').val(level.daily_roi);

                            $('#edit_is_active').prop('checked', level.is_active == 1);
                            $('#edit_is_coming_soon').prop('checked', level.is_coming_soon == 1);

                            if (level.icon) {
                                $('#edit_icon_preview').attr('src', "{{ asset('') }}" + level.icon);
                            }

                            $('#kt_modal_edit_level').modal('show');
                        },
                        error: function () {
                            Swal.fire('Error', 'Failed to load level data', 'error');
                        }
                    });
                });


                // Handle edit form submission
                $('#kt_modal_edit_user_form').on('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('_method', 'PUT'); // Simulate PUT request
                    $.ajax({
                        url: $(this).attr('action'),
                        type: "POST", // Use POST to simulate PUT
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire('Success!', response.message || 'User updated successfully', 'success');
                            $('#kt_modal_edit_user').modal('hide');
                            $('#kt_table_users').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            let message = xhr.responseJSON?.message || 'Failed to update user.';
                            if (xhr.responseJSON?.errors) {
                                // Format validation errors
                                const errors = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                                message = `Validation failed:<br>${errors}`;
                            }
                            Swal.fire({
                                title: 'Error!',
                                html: message,
                                icon: 'error'
                            });
                        }
                    });
                });

                // Reset edit modal on close
                $('#kt_modal_edit_user').on('hidden.bs.modal', function() {
                    $('#kt_modal_edit_user_form')[0].reset();
                    $('#edit_avatar_preview').css('background-image', 'url({{ asset('backend/assets/media/avatars/300-1.jpg') }})');
                    $('#edit_passwordInput').val('');
                    $('#edit_role').val('');
                });






                // delete user
                $(document).on('click', '.delete-user-btn', function(e) {
                    e.preventDefault();
                    let userId = $(this).data('id');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This action cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let deleteUrl = "{{ route('user.delete', ['id' => '___id___']) }}".replace('___id___',
                                userId);
                            $.ajax({
                                url: deleteUrl,
                                type: "DELETE",
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    Swal.fire(
                                        'Deleted!',
                                        response.message,
                                        'success'
                                    );
                                    $('#kt_table_users').DataTable().ajax.reload();
                                },
                                error: function(xhr) {
                                    Swal.fire(
                                        'Error!',
                                        xhr.responseJSON?.message || 'Something went wrong.',
                                        'error'
                                    );
                                }
                            });
                        }
                    });
                });
            </script>
    @endpush
@endsection
