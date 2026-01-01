<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Kyc\DocumentUpdateRequest;
use App\Http\Requests\Api\V1\Kyc\indexRequest;
use App\Http\Requests\Api\V1\Kyc\submissionRequest;
use App\Http\Resources\Api\V1\Kyc\getDataResource;
use App\Http\Resources\Api\V1\Kyc\showDataResource;
use App\Models\KycDocument;
use App\Models\User;
use App\Notifications\UniversalNotification;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KycController extends Controller
{
    use ApiResponse;

    /**
     * list of kyc request
     */
    public function index(indexRequest $request)
    {
        $validated = $request->validated();
        try {

            $page     = $validated['page'] ?? 1;
            $perPage  = $validated['per_page'] ?? 15;

            $query = KycDocument::with('user:id,first_name,last_name,email,kyc_status');

            // Filter by Status
            if (!empty($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            // Sorting
            $sortBy  = $validated['sort_by'] ?? 'created_at';
            $sortDir = $validated['sort_dir'] ?? 'desc';
            $query->orderBy($sortBy, $sortDir);

            $documents = $query->paginate($perPage, ['*'], 'page', $page);

            return $this->sendResponse(getDataResource::collection($documents), 'KYC requests fetched successfully.');
        }catch (\Exception $e){
            Log::error("Failed to fetch KYC documents:".$e->getMessage());
            return $this->sendError("Failed to fetch KYC documents:".$e->getMessage(),[],500);
        }
    }

    /**
     * Generalized Document Submission (Handles NID, Passport, or others)
     */
    public function submitDocument(submissionRequest $request)
    {
        try {
            $validated = $request->validated();
            $user      = auth()->user();
            $docType   = $validated['type'];


            $kycDocument = KycDocument::where('user_id', $user->id)->latest()->first();
            if (!empty($kycDocument)) {
                if ($kycDocument->status == 'Pending') {
                    return $this->sendError("KYC document already submitted.Under review.", [], 400);
                }
                if ($kycDocument->status == 'Approved') {
                    return $this->sendError("KYC document already submitted.", [], 400);
                }
            }


            /**
             *  Validate document sides
             */
            if (in_array($docType, ['nid', 'driving_license'])) {
                if (
                    !$request->hasFile('file_side_1') ||
                    !$request->hasFile('file_side_2')
                ) {
                    return $this->sendError(
                        strtoupper($docType) . ' requires both front and back images.',
                        [],
                        422
                    );
                }
            }


            /**
             * Upload files
             */
            $frontPath = null;
            $backPath  = null;

            if ($request->hasFile('file_side_1')) {
                $file = $request->file('file_side_1');
                $name = 'front_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $frontPath = Helpers::uploadFile('kyc_documents', $file, $name, config('app.file_system'));
            }

            if ($request->hasFile('file_side_2')) {
                $file = $request->file('file_side_2');
                $name = 'back_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $backPath = Helpers::uploadFile('kyc_documents', $file, $name, config('app.file_system'));
            }

            // Create document
            KycDocument::create([
                'user_id'          => $user->id,
                'document_type'    => $docType,
                'file_side_1_path' => $frontPath,
                'file_side_2_path' => $backPath,
                'status'           => 'Pending',
            ]);


            User::find($user->id)->update(['kyc_status' => 'Pending']);

            return $this->sendResponse([],  'Document submitted successfully. Under review.',  200);
        }catch (\Exception $e){
            Log::error("Failed to submit KYC document:".$e->getMessage());
            return $this->sendError("Failed to submit KYC document",[],500);
        }

    }

    /**
     * View a single KYC document
     * If ID not provided, fetch current user's latest document
     */
    public function show($documentId = null)
    {
        $user = auth()->user();

        if ($documentId) {
            // Fetch by document ID (admin / authorized access)
            $document = KycDocument::with('user')->find($documentId);
        } else {
            // Fetch current user's latest KYC document
            $document = KycDocument::with('user')
                ->where('user_id', $user->id)
                ->latest()
                ->first();
        }

        if (!$document) {
            return $this->sendError('Document not found.', [], 404);
        }

        return $this->sendResponse(new ShowDataResource($document), 'Document retrieved successfully.');
    }


    /**
     * update update Approve/Reject KYC - admin or manager
     */
    public function update(DocumentUpdateRequest $request, $documentId)
    {
        $validated = $request->validated();

        try {
            // get current auth
            $user = auth()->user();
            //        if (!in_array($user->role, ['admin', 'moderator'])){
//            return $this->sendError('You do not have permission to view this document.', [], 422);
//        }
            $document = KycDocument::with('user')->find($documentId);
            if (empty($document)) {
                return $this->sendError('Document not found.', [], 404);
            }
            $document->status = $validated['status'];
            $document->rejection_reason = $validated['status'] == "rejected" ?  $validated['reason'] : null;
            $document->save();
            // message
            $message = match ($validated['status']) {
                'approved' => 'KYC document approved successfully.',
                'rejected' => 'KYC document rejected successfully.',
                default => 'KYC document updated successfully.',
            };
            // notify user
            $document->user->notify(new UniversalNotification(
                'KYC ' . ucfirst($validated['status']),
                $validated['status'] === 'approved'
                    ? 'Your KYC verification has been approved successfully.'
                    : 'Your KYC has been rejected. Please review the reason and resubmit.',
                'kyc_' . strtolower($validated['status']),
                ['reason' => $document->rejection_reason]
            ));

            return $this->sendResponse([], $message);
        }catch (\Exception $e){
            Log::error("Failed to update document status:" . $e->getMessage());
            return $this->sendError("Failed to update document status:" . $e->getMessage(), [], 500);
        }
    }
}
