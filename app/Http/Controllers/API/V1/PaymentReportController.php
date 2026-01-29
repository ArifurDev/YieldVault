<?php

namespace App\Http\Controllers\API\V1;

use App\Enum\Payment\ReportStatus;
use App\Helpers\translateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PaymentReport\ResolveRequest;
use App\Http\Resources\Api\V1\PaymentReport\DetailsResource;
use App\Http\Resources\Api\V1\PaymentReport\ListOfReportResource;
use App\Models\PaymentReport;
use App\Models\User;
use App\Notifications\UniversalNotification;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentReportController extends Controller
{
    use ApiResponse;
    /**
     * List of payment reports (Manager API)
     */
    public function index(Request $request)
    {
        try {
            $lang    = $request->header('Accept-Language', 'en');
            $page    = $request->input('page', 1);
            $perPage = $request->input('perPage', 10);

            $query = PaymentReport::query()
                ->with([
                    'user:id,first_name,last_name',
                    'manager:id,first_name,last_name',
                    'transaction:id,amount',
                    'ticket:id'
                ])
                ->latest();

            $reports = $query->paginate($perPage, ['*'], 'page', $page);

            $message = translateHelper::translateCached(
                'payment_report_list_success',
                'Payment report retrieved successfully',
                $lang
            );

            return $this->sendResponse(ListOfReportResource::collection($reports), $message,200,[],$reports);
        }catch (\Exception $e){
            Log::error('Failed to get payment reports', [
                'userId'  => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            $message = translateHelper::translateCached(
                'payment_report_list_failed',
                'Failed to retrieve payment reports',
                $lang
            );
        }
    }


    /**
     * Show single payment report (Manager API)
     */
    public function show(Request $request, PaymentReport $report)
    {
        $lang = $request->header('Accept-Language', 'en');

        try {

            $report->load([
                'user:id,first_name,last_name,email',
                'manager:id,first_name,last_name',
                'transaction:id,amount',
                'ticket:id'
            ]);

            $message = translateHelper::translateCached(
                'payment_report_details_success',
                'Payment report details retrieved successfully',
                $lang
            );
            return $this->sendResponse( new DetailsResource($report), $message);
        } catch (\Exception $e) {

            Log::error('Failed to get payment report details', [
                'userId'   => auth()->id(),
                'reportId'=> $report->id ?? null,
                'message' => $e->getMessage(),
            ]);

            $message = translateHelper::translateCached(
                'payment_report_details_failed',
                'Failed to retrieve payment report details',
                $lang
            );

            return $this->sendError($message, 500);
        }
    }



    public function resolve(ResolveRequest $request, PaymentReport $report)
    {
        $lang = $request->header('Accept-Language', 'en');

        try {
        $validated = $request->validated();
        $status = ReportStatus::from($request->validated()['status']);

            // Prevent invalid resolve
            if (in_array($report->status, [ReportStatus::RESOLVED, ReportStatus::REJECTED])) {
                return $this->sendError(
                    translateHelper::translateCached(
                        'payment_report_already_closed',
                        'This report is already closed',
                        $lang
                    ),
                    422
                );
            }

            // Update status
            $report->update([
                'status'      => $status,
                'resolved_at' => $status === ReportStatus::RESOLVED ? now() : null,
                'resolved_by' => auth()->id(),
            ]);

            // Notify ONLY Admins
            $admin = User::where('role','admin')->first();

            if ($admin) {
                $admin->notify(
                    new UniversalNotification(
                        'Payment Report Status Updated',
                        "Payment report #PR-" . str_pad($report->id, 6, '0', STR_PAD_LEFT)
                        . " has been marked as {$status->label($lang)} by "
                        . auth()->user()->first_name . ".",
                        'info',
                        [
                            'report_id' => $report->id,
                            'status'    => $status->value,
                            'action'    => 'payment_report_view',
                        ]
                    )
                );
            }

            $message = translateHelper::translateCached(
                'payment_report_resolved_success',
                'Payment report resolved successfully',
                $lang
            );

            return $this->sendResponse(
                new DetailsResource($report->fresh()),
                $message
            );

        } catch (\Exception $e) {

            Log::error('Failed to resolve payment report', [
                'user_id'   => auth()->id(),
                'error'     => $e->getMessage(),
            ]);

            return $this->sendError(
                translateHelper::translateCached(
                    'payment_report_resolve_failed',
                    'Failed to resolve payment report',
                    $lang
                ),
                [],500
            );
        }
    }

}
