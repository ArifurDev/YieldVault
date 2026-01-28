<div class="container-xxl">
    <div class="card">

        {{-- Header --}}
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"></i>
                    <input id="paymentReportSearch"
                           type="text"
                           class="form-control form-control-solid w-250px ps-13"
                           placeholder="Search report..." />
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body py-4">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5"
                       id="payment_report_table">
                    <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase">
                        <th>#</th>
                        <th>Report ID</th>
                        <th>Ticket</th>
                        <th>User</th>
                        <th>Category</th>
                        <th>Issue</th>
                        <th>Amount</th>
                        <th>Manager</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Action</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>

    </div>
</div>
