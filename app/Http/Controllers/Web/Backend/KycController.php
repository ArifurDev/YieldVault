<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Models\KycDocument;
use App\Notifications\UniversalNotification;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class KycController extends Controller
{
    /**
     * list of user
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = KycDocument::with('user')->latest();

            return DataTables::of($data)
//                ->addIndexColumn()
                ->addColumn('user', function ($row) {
                    $fullName = $row?->user->first_name . ' ' . $row?->user->last_name;
                    $avatarUrl = $row?->user->avatar ? Helpers::generateTempURL($row?->user->avatar,config('app.file_system')) : asset('backend/assets/media/avatars/300-1.jpg');

                    return '
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                            <img src="' . $avatarUrl . '" class="w-100" alt="' . $fullName . '">
                        </div>
                        <div class="d-flex flex-column">
                            <span class="text-gray-800 mb-1">' . $fullName . '</span>
                            <span>' . $row?->user->email . '</span>
                        </div>
                    </div>';
                })
                ->addColumn('submitted_date', fn($row) => $row->created_at ? $row->created_at->format('d M Y, h:i a') : '')
                ->addColumn('doc_type', fn($row) => ucfirst($row->document_type ?? ''))
                ->addColumn('status', function ($row) {
                    $status = $row->status ?? 'Pending';
                    $badgeClass = match($status) {
                        'Approved' => 'badge-light-success',
                        'Rejected' => 'badge-light-danger',
                        default => 'badge-light-warning'
                    };
                    return '<span class="badge ' . $badgeClass . '">' . ucfirst($status) . '</span>';
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = route('kyc.edit', $row->id);
                    return '
                            <div class="d-flex justify-content-end">
                                <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm"
                                   data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    Actions
                                    <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                </a>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4"
                                     data-kt-menu="true">
                                    <div class="menu-item px-3">
                                       <a href="' . $editUrl . '" target="_blank" class="menu-link px-3">Verify KYC</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3 delete-kyc-btn" data-id="' . $row->id . '">Delete</a>
                                    </div>
                                </div>
                            </div>
                            ';
                })
                ->rawColumns(['user', 'actions','status'])
                ->make(true);
        }

        // ONLY when not ajax
        return view('backend.layout.kyc.index');

    }

    /**
     * edit kyc document
     */
    public function edit($id)
    {
        $kyc = KycDocument::with('user')->find($id);
        if (!$kyc) {
            return redirect()->route('kyc.index')->with('error', 'KYC Document Not Found');
        }
        $kyc->file_side_1_path = $kyc->file_side_1_path ? helpers::generateTempURL($kyc->file_side_1_path,config('app.file_system')) : null;
        $kyc->file_side_2_path = $kyc->file_side_2_path ? helpers::generateTempURL($kyc->file_side_2_path,config('app.file_system')) : null;

        $kyc->user->avatar = $kyc->user->avatar ? helpers::generateTempURL($kyc->user->avatar,config('app.file_system')) : null;
        return view('backend.layout.kyc.edit', compact('kyc'));
    }

    /**
     * kyc verify
     */
    public function update(Request $request, $documentId)
    {
        try {
            // VALIDATION
            $validated = $request->validate([
                'status' => 'required|in:Approved,Rejected,Pending',
                'reason' => 'nullable|string'
            ]);

            // FETCH DOCUMENT
            $document = KycDocument::with('user')->find($documentId);

            if (!$document) {
                return redirect()->route('kyc.index')->with('error', 'KYC Document Not Found');
            }

            // UPDATE DOCUMENT
            $document->status           = $validated['status'];
            $document->rejection_reason = $validated['status'] === 'Rejected'
                ? $validated['reason']
                : null;

            $document->save();

            // UPDATE USER KYC STATUS
            if ($document->user) {
                $document->user->kyc_status = $validated['status'];
                $document->user->save();
            }

            // PREPARE SUCCESS MESSAGE
            $message = match ($validated['status']) {
                'approved' => 'KYC document approved successfully.',
                'rejected' => 'KYC document rejected successfully.',
                default => 'KYC document updated successfully.',
            };

            // SEND NOTIFICATION TO USER
            $title = 'KYC ' . ucfirst($validated['status']);

            $body = match ($validated['status']) {
                'approved' => 'Your KYC verification has been approved successfully.',
                'rejected' => 'Your KYC has been rejected. Please review the reason and resubmit.',
                default => 'Your KYC status has been updated.',
            };

            // notify user
            $document->user->notify(new UniversalNotification(
                $title,
                $body,
                'kyc_' . strtolower($validated['status']),
                ['reason' => $document->rejection_reason]
            ));

            return redirect()->back()->with('success', $title);
        }catch (\Exception $exception){
            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    /**
     * kyc doc deleted with image
     */
    public function destroy($id)
    {
        try {
            // FETCH DOCUMENT
            $document = KycDocument::with('user')->find($id);

            if (!$document) {
                return redirect()->route('kyc.index')->with('error', 'KYC Document Not Found');
            }

            $document->user->kyc_status = 'Not Submitted';
            $document->user->save();

            if($document->file_side_1_path){
                helpers::deleteFile($document->file_side_1_path,config('app.file_system'));
            }
            if($document->file_side_2_path){
                helpers::deleteFile($document->file_side_2_path,config('app.file_system'));
            }
            $document->delete();
            return redirect()->route('kyc.index')->with('success', 'KYC Document deleted successfully.');
        }catch (\Exception $exception){
            return redirect()->route('kyc.index')->with('error', $exception->getMessage());
        }
    }
}
