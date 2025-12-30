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
//    public function submitDocument(submissionRequest $request)
//    {
//        $validated = $request->validated();
//
//        DB::beginTransaction();
//        try {
//            // get current auth
//            $user = auth()->user();
//
//            $docType = $validated['type'];
//
//            // Prevent Multiple or Invalid Submissions
//            if ($user->kyc_status === 'Pending') {
//                return $this->sendError('You have already submitted your document. Please wait for review.', [], 422);
//            }
//
//
//            if ($user->kyc_status === 'Approved') {
//                return $this->sendError('Your KYC is already approved. You cannot resubmit.', [], 422);
//            }
//
//
//            // Documents like NID and Driving License require two sides.
//            if (in_array($docType, ['nid', 'driving_license']) && !$request->hasFile('file_side_2')) {
//                return $this->sendError('The ' . strtoupper($docType) . ' requires both the front (Side 1) and back (Side 2) images.',[],422);
//            }
//
//            // Upload Logic
//            $frontPath = null;
//            $backPath  = null;
//
//            if ($request->hasFile('file_side_1')) {
//                $frontFile = $request->file('file_side_1');
//                $frontName = 'front'.time() . Str::random(8) . '_' . $frontFile->getClientOriginalName();
//                $frontPath = Helpers::uploadFile('kyc_documents', $frontFile, $frontName, config('app.file_system'));
//            }
//
//            if ($request->hasFile('file_side_2')) {
//                $backFile = $request->file('file_side_2');
//                $backName = 'back'.time() . Str::random(8) . '_' . $backFile->getClientOriginalName();
//                $backPath = Helpers::uploadFile('kyc_documents', $backFile, $backName, config('app.file_system'));
//            }
//
//            // Create Record
//            $document = KycDocument::create([
//                'user_id'          => $user->id,
//                'document_type'    => $docType,
//                'file_side_1_path' => $frontPath,
//                'file_side_2_path' => $backPath,
//                'status'           => 'Pending',
//            ]);
//
//            // Update User Status
//            $user->kyc_status = 'Pending';
//            $user->save();
//            DB::commit();
//
//            return $this->sendResponse([],'Document submitted successfully. Now status is pending.', 200);
//        }catch (\Exception $e){
//            DB::rollBack();
//            Log::error("Failed to submit document submitted successfully:" . $e->getMessage());
//            return $this->sendError("Failed to submit document submitted successfully:" . $e->getMessage(), [], 500);
//        }
//    }

//    public function submitDocument(submissionRequest $request)
//    {
//        $validated = $request->validated();
//        try {
//            $user = auth()->user();
//            $docType = $validated['type'];
//
//            // Block if approved
//            if ($user->kyc_status === 'Approved') {
//                return $this->sendError(
//                    'Your KYC is already approved.',
//                    [],
//                    422
//                );
//            }
//
//            // Block only if pending document exists
//            $hasPendingDocument = KycDocument::where('user_id', $user->id)
//                ->where('status', 'Pending')
//                ->exists();
//
//            if ($hasPendingDocument) {
//                return $this->sendError(
//                    'You already have a pending document under review.',
//                    [],
//                    422
//                );
//            }
//
//            // Validate sides
//            if (in_array($docType, ['nid', 'driving_license'])) {
//                if (
//                    !$request->hasFile('file_side_1') ||
//                    !$request->hasFile('file_side_2')
//                ) {
//                    return $this->sendError(
//                        strtoupper($docType) . ' requires front & back images.',
//                        [],
//                        422
//                    );
//                }
//            }
//
//            // Upload
//            $frontPath = $request->hasFile('file_side_1')
//                ? Helpers::uploadFile(
//                    'kyc_documents',
//                    $request->file('file_side_1'),
//                    'front_' . time() . '_' . Str::random(6),
//                    config('app.file_system')
//                )
//                : null;
//
//            $backPath = $request->hasFile('file_side_2')
//                ? Helpers::uploadFile(
//                    'kyc_documents',
//                    $request->file('file_side_2'),
//                    'back_' . time() . '_' . Str::random(6),
//                    config('app.file_system')
//                )
//                : null;
//
//            // Create document
//            KycDocument::create([
//                'user_id'          => $user->id,
//                'document_type'    => $docType,
//                'file_side_1_path' => $frontPath,
//                'file_side_2_path' => $backPath,
//                'status'           => 'Pending',
//            ]);
//
//            // Update user summary
//            $user->update(['kyc_status' => 'Pending']);
//
//            return $this->sendResponse([],  'Document submitted successfully. Under review.',  200);
//        } catch (\Exception $e) {
//
//            Log::error('KYC submit failed: ' . $e->getMessage());
//            return $this->sendError('Submission failed. Try again.',[],500);
//        }
//    }

    public function submitDocument(submissionRequest $request)
    {
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

    }

    public function test()
    {
        return response()->json([
            'message' => 'KYC document submitted successfully.',
        ]);
    }


//    public function submitDocument(submissionRequest $request)
//    {
//        $validated = $request->validated();
//        $user      = auth()->user();
//        $docType   = $validated['type'];
//
//        // 1. Use a Transaction to ensure data integrity
//        return DB::transaction(function () use ($request, $user, $docType) {
//
//            // 2. Check for existing record with a 'sharedLock' or 'lockForUpdate'
//            // to prevent race conditions from double-clicks
//            $existingDoc = KycDocument::where('user_id', $user->id)
//                ->whereIn('status', ['Pending', 'Approved'])
//                ->latest()
//                ->first();
//
//            if ($existingDoc) {
//                $msg = ($existingDoc->status == 'Pending')
//                    ? "KYC document already submitted. Under review."
//                    : "KYC document already approved.";
//                return $this->sendError($msg, [], 400);
//            }
//
//            /**
//             * Validate document sides
//             */
//            if (in_array($docType, ['nid', 'driving_license'])) {
//                if (!$request->hasFile('file_side_1') || !$request->hasFile('file_side_2')) {
//                    return $this->sendError(strtoupper($docType) . ' requires both sides.', [], 422);
//                }
//            }
//
//            /**
//             * Upload files
//             */
//            $frontPath = null;
//            $backPath  = null;
//
//            if ($request->hasFile('file_side_1')) {
//                $file = $request->file('file_side_1');
//                $name = 'front_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
//                $frontPath = Helpers::uploadFile('kyc_documents', $file, $name, config('app.file_system'));
//            }
//
//            if ($request->hasFile('file_side_2')) {
//                $file = $request->file('file_side_2');
//                $name = 'back_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
//                $backPath = Helpers::uploadFile('kyc_documents', $file, $name, config('app.file_system'));
//            }
//
//            // 3. Create record
//            KycDocument::create([
//                'user_id'          => $user->id,
//                'document_type'    => $docType,
//                'file_side_1_path' => $frontPath,
//                'file_side_2_path' => $backPath,
//                'status'           => 'Pending',
//            ]);
//
//            // Update user status
//            $user->update(['kyc_status' => 'Pending']);
//
//            return $this->sendResponse([], 'Document submitted successfully.', 200);
//        });
//    }


    /**
     * View a single KYC document
     */
    public function show($documentId)
    {
        // get current auth
        $user = auth()->user();
//        if (!in_array($user->role, ['admin', 'moderator'])){
//            return $this->sendError('You do not have permission to view this document.', [], 422);
//        }
        $document = KycDocument::with('user')->find($documentId);
        if (empty($document)) {
            return $this->sendError('Document not found.', [], 404);
        }
        return $this->sendResponse(new showDataResource($document),"Document retrieved successfully.");
    }

    /**
     * update update Approve/Reject KYC
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
