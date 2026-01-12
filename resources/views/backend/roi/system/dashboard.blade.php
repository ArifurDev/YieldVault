@extends('backend.app')

@section('title', 'ROI Health')

@section('content')
    <div class="content fs-6 d-flex flex-column flex-column-fluid">

        {{-- Toolbar --}}
        <div class="toolbar">
            <div class="container-fluid d-flex flex-stack">
                <div>
                    <h1 class="text-dark fw-bold fs-2">ROI Health</h1>
                    <ul class="breadcrumb fw-semibold fs-base">
                        <li class="breadcrumb-item text-muted">Home</li>
                        <li class="breadcrumb-item text-dark">ROI Health</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="post fs-6 d-flex flex-column-fluid">
            <div class="container-xxl">

                {{-- KPI ROW --}}
                <div class="row gx-6 gx-xl-9">

                    @php
                        $cards = [
                            ['id'=>'active', 'title'=>'Active Investments'],
                            ['id'=>'today_generated', 'title'=>'Today ROI Generated'],
                            ['id'=>'today_amount', 'title'=>'Today ROI Amount'],
                            ['id'=>'failed_jobs', 'title'=>'Failed Jobs'],
                            ['id'=>'total_paid', 'title'=>'Total ROI Paid'],
                            ['id'=>'last_run', 'title'=>'Last ROI Run'],
                        ];
                    @endphp

                    @foreach($cards as $card)
                        <div class="col-md-6 col-xl-4 mb-6">
                            <div class="card h-100">
                                <div class="card-body p-9">
                                    <div class="fs-2hx fw-bold" id="roi_{{ $card['id'] }}">--</div>
                                    <div class="fs-4 fw-semibold text-gray-400">
                                        {{ $card['title'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>

                {{-- Alerts --}}
                <div id="roi-alerts" class="mt-4"></div>

                {{-- Actions --}}
                <div class="mt-6">
                    <button class="btn btn-primary" onclick="runROI()">Run ROI Now</button>
                    <a href="" class="btn btn-danger ms-2">
                        Failed Jobs
                    </a>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        function loadROIHealth() {
            $.get("{{ route('roi.dashboard.data') }}", function (d) {

                $('#roi_active').text(d.active_investments);
                $('#roi_today_generated').text(d.today_roi_generated ? 'Yes' : 'No');
                $('#roi_today_amount').text('$' + d.today_roi_amount);
                $('#roi_failed_jobs').text(d.failed_jobs);
                $('#roi_total_paid').text('$' + d.total_roi_paid);
                $('#roi_last_run').text(d.last_roi_run ?? 'N/A');

                if (!d.today_roi_generated) {
                    $('#roi-alerts').html(`
                    <div class="alert alert-warning">
                        Today's ROI has not been generated yet.
                    </div>
                `);
                }
            });
        }

        {{--function runROI() {--}}
        {{--    if (!confirm('Run ROI manually?')) return;--}}
        {{--    $.post("{{ route('admin.roi.run') }}", () => {--}}
        {{--        alert('ROI job dispatched');--}}
        {{--        loadROIHealth();--}}
        {{--    });--}}
        {{--}--}}

        loadROIHealth();
    </script>
@endpush
