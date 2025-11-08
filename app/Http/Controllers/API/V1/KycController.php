<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Kyc\indexRequest;
use App\Http\Requests\Api\V1\Kyc\submissionRequest;
use App\Http\Resources\Api\V1\Kyc\getDataResource;
use App\Models\KycDocument;
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
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // get current auth
            $user = auth()->user();

            $docType = $validated['type'];

            // Prevent Multiple or Invalid Submissions
            if ($user->kyc_status === 'Pending') {
                return $this->sendError('You have already submitted your document. Please wait for review.', [], 422);
            }

            if ($user->kyc_status === 'Approved') {
                return $this->sendError('Your KYC is already approved. You cannot resubmit.', [], 422);
            }


            // Documents like NID and Driving License require two sides.
            if (in_array($docType, ['nid', 'driving_license']) && !$request->hasFile('file_side_2')) {
                return $this->sendError('The ' . strtoupper($docType) . ' requires both the front (Side 1) and back (Side 2) images.',[],422);
            }

            // Upload Logic
            $frontPath = null;
            $backPath  = null;

            if ($request->hasFile('file_side_1')) {
                $frontFile = $request->file('file_side_1');
                $frontName = 'front'.time() . Str::random(8) . '_' . $frontFile->getClientOriginalName();
                $frontPath = Helpers::uploadFile('kyc_documents', $frontFile, $frontName, config('app.file_system'));
            }

            if ($request->hasFile('file_side_2')) {
                $backFile = $request->file('file_side_2');
                $backName = 'back'.time() . Str::random(8) . '_' . $backFile->getClientOriginalName();
                $backPath = Helpers::uploadFile('kyc_documents', $backFile, $backName, config('app.file_system'));
            }

            // Create Record
            $document = KycDocument::create([
                'user_id'          => $user->id,
                'document_type'    => $docType,
                'file_side_1_path' => $frontPath,
                'file_side_2_path' => $backPath,
                'status'           => 'Pending',
            ]);

            // Update User Status
            $user->kyc_status = 'Pending';
            $user->save();
            DB::commit();

            return $this->sendResponse([],'Document submitted successfully. Now status is pending.', 200);
        }catch (\Exception $e){
            DB::rollBack();
            Log::error("Failed to submit document submitted successfully:" . $e->getMessage());
            return $this->sendError("Failed to submit document submitted successfully:" . $e->getMessage(), [], 500);
        }
    }
}
