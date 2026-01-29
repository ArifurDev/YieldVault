@extends('backend.app')

@section('title', 'Verify KYC')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.6/quill.snow.css">
    <style>
        .ql-editor {
            min-height: 200px;
        }

        /* small KYC preview */
        .kyc-image {
            width: 100%;
            max-width: 420px;   /* 🔥 control image size */
            height: auto;
            border-radius: 12px;
            border: 1px solid #eee;
            cursor: zoom-in;
            display: block;
        }


        /* fullscreen overlay */
        .image-lightbox {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.85);
            display: none;
            justify-content: center;
            align-items: center;
            padding: 20px;
            z-index: 999999;
        }

        /* popup box auto-fitting to image */
        .image-lightbox-content {
            position: relative;
            display: inline-block;
            max-width: 90%;
            max-height: 85%;
        }

        /* full preview image */
        .image-lightbox-content img {
            max-width: 100%;
            max-height: 85vh;
            border-radius: 12px;
            display: block;
            margin: auto;
            box-shadow: 0 0 20px rgba(0,0,0,0.7);
        }

        /* close button */
        .image-lightbox-close {
            position: absolute;
            right: -12px;
            top: -12px;
            background: #fff;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(0,0,0,0.4);
        }

        /* smaller screens */
        @media (max-width: 900px) {
            .image-lightbox-content {
                width: 95%;
                max-width: 95%;
                padding: 12px;
                border-radius: 10px;
            }
            .image-lightbox-content img {
                max-height: calc(70vh - 10px);
            }
        }

    </style>
@endpush

@section('content')
    <!--begin::Content-->
    <div class="content fs-6 d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <div class="container-fluid d-flex flex-stack flex-wrap flex-sm-nowrap">
                <!--begin::Info-->
                <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                    <!--begin::Title-->
                    <h1 class="text-dark fw-bold my-1 fs-2">Kyc Document</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('kyc.index') }}" class="text-muted text-hover-primary">List of KYC</a>
                        </li>
                        <li class="breadcrumb-item text-dark">Kyc Document Edit</li>
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
            @include('backend.partials.kyc.edit_main')
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>
    <!--end::Content-->

    @push('script')
        <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
        <script>


            (function () {
                const lightbox = document.getElementById('lightbox');
                const lightboxImg = document.getElementById('lightbox-img');
                const closeBtn = document.querySelector('.image-lightbox-close');

                // open lightbox with the clicked image
                document.querySelectorAll('.kyc-image').forEach(img => {
                    img.addEventListener('click', function () {
                        // set src (you can also set srcset or use high-res url if available)
                        lightboxImg.src = this.src;
                        // show
                        lightbox.style.display = 'flex';
                        lightbox.setAttribute('aria-hidden', 'false');

                        // small entrance animation (optional)
                        document.querySelector('.image-lightbox-content').style.transform = 'translateY(-4px)';
                        setTimeout(() => {
                            document.querySelector('.image-lightbox-content').style.transform = 'translateY(0)';
                        }, 100);
                    });
                });

                // close when clicking outside content box
                lightbox.addEventListener('click', function (e) {
                    // if clicked on overlay (not inside content)
                    if (e.target === lightbox) {
                        closeLightbox();
                    }
                });

                // close via close button
                closeBtn.addEventListener('click', closeLightbox);

                // close on pressing Esc
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && lightbox.style.display === 'flex') {
                        closeLightbox();
                    }
                });

                // toggle slight zoom when clicking the image (optional)
                lightboxImg.addEventListener('click', function (e) {
                    e.stopPropagation(); // prevent overlay close
                    this.classList.toggle('zoomed');
                });

                function closeLightbox() {
                    // hide
                    lightbox.style.display = 'none';
                    lightbox.setAttribute('aria-hidden', 'true');
                    lightboxImg.classList.remove('zoomed');
                    // cleanup src to release memory if large
                    setTimeout(() => {
                        lightboxImg.src = '';
                    }, 200);
                }
            })();

            // Toggle Reason Field
            function toggleReasonBox() {
                let status = document.getElementById('status').value;
                let reasonSection = document.getElementById('reasonSection');

                if (status === 'Rejected') {
                    reasonSection.style.display = 'block';
                } else {
                    reasonSection.style.display = 'none';
                }
            }

            //  Run when dropdown changes
            document.getElementById('status').addEventListener('change', toggleReasonBox);

            // Run once on page load
            toggleReasonBox();

        </script>
    @endpush
@endsection
