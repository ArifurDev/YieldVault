<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Kyc\DocumentUpdateRequest;
use App\Http\Requests\Api\V1\Kyc\indexRequest;
use App\Http\Requests\Api\V1\Kyc\submissionRequest;
use App\Http\Requests\Api\V1\Kyc\UpdateDocumentRequest;
use App\Http\Resources\Api\V1\Kyc\getDataResource;
use App\Http\Resources\Api\V1\Kyc\showDataResource;
use App\Models\ActiveLog;
use App\Models\KycDocument;
use App\Models\User;
use App\Notifications\UniversalNotification;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
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
            // get current auth
            $user = auth()->user();

            if (!in_array($user->role->value, ['admin', 'manager'])) {
                return $this->sendError('You do not have permission to view this document.', [], 422);
            }

            $page = $validated['page'] ?? 1;
            $perPage = $validated['per_page'] ?? 15;

            $query = KycDocument::with('user:id,first_name,last_name,email,kyc_status');

            // Filter by Status
            if (!empty($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            // Sorting
            $sortBy = $validated['sort_by'] ?? 'created_at';
            $sortDir = $validated['sort_dir'] ?? 'desc';
            $query->orderBy($sortBy, $sortDir);

            $documents = $query->paginate($perPage, ['*'], 'page', $page);

            return $this->sendResponse(getDataResource::collection($documents), 'KYC requests fetched successfully.');
        } catch (\Exception $e) {
            Log::error("Failed to fetch KYC documents:" . $e->getMessage());
            return $this->sendError("Failed to fetch KYC documents:" . $e->getMessage(), [], 500);
        }
    }

    /**
     * Generalized Document Submission (Handles NID, Passport, or others)
     */
    public function submitDocument(submissionRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = auth()->user();
            $docType = $validated['type'];


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
                    return $this->sendError(strtoupper($docType) . ' requires both front and back images.', [],422);
                }
            }


            /**
             * Upload files
             */
            $frontPath = null;
            $backPath = null;

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
                'user_id' => $user->id,
                'document_type' => $docType,
                'file_side_1_path' => $frontPath,
                'file_side_2_path' => $backPath,
                'status' => 'Pending',
            ]);


            User::find($user->id)->update(['kyc_status' => 'Pending']);

            return $this->sendResponse([], 'Document submitted successfully. Under review.', 200);
        } catch (\Exception $e) {
            Log::error("Failed to submit KYC document:" . $e->getMessage());
            return $this->sendError("Failed to submit KYC document", [], 500);
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
     * update kyc document
     */
    public function updateDocument(UpdateDocumentRequest $request,$id)
    {
        try {
            $validated = $request->validated();
            // get current user
            $user = auth()->user();
            $document = KycDocument::with('user')->find($id);
            if (!$document) {
                return $this->sendError('Document not found.', [], 404);
            }
            if ($document->user_id != $user->id) {
                return $this->sendError('You are not allowed to update this document.', [], 403);
            }
            // Only rejected documents can be updated
            if ($document->status !== 'Rejected') {
                return $this->sendError('Only rejected KYC documents can be updated.', [], 422);
            }

            /**
             * Validate document sides again
             */
            if (in_array($document->document_type, ['nid', 'driving_license'])) {
                if (
                    !$request->hasFile('file_side_1') ||
                    !$request->hasFile('file_side_2')
                ) {
                    return $this->sendError(
                        strtoupper($document->document_type) . ' requires both front and back images.',
                        [],
                        422
                    );
                }
            }

            $oldType = $document->document_type;
            $newType = $validated['type'];


            // If document type changed → delete ALL old files
            if ($oldType !== $newType) {

                if ($document->file_side_1_path) {
                    Helpers::deleteFile($document->file_side_1_path, config('app.file_system'));
                }

                if ($document->file_side_2_path) {
                    Helpers::deleteFile($document->file_side_2_path, config('app.file_system'));
                }

                $frontPath = null;
                $backPath  = null;

            } else {
                // Same type → replace only uploaded sides
                $frontPath = $document->file_side_1_path;
                $backPath  = $document->file_side_2_path;

                if ($request->hasFile('file_side_1') && $frontPath) {
                    Helpers::deleteFile($frontPath, config('app.file_system'));
                    $frontPath = null;
                }

                if ($request->hasFile('file_side_2') && $backPath) {
                    Helpers::deleteFile($backPath, config('app.file_system'));
                    $backPath = null;
                }
            }


            if ($newType === 'passport') {
                if (!$request->hasFile('file_side_1') && !$request->hasFile('file_side_2')) {
                    return $this->sendError('Passport requires at least one image.', [], 422);
                }
            }
            /**
             * UPLOAD FILES
             */
            if ($request->hasFile('file_side_1')) {
                $file = $request->file('file_side_1');
                $name = 'front_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $frontPath = Helpers::uploadFile('kyc_documents', $file, $name, config('app.file_system'));
            }

            if ($request->hasFile('file_side_2')) {
                $file = $request->file('file_side_2');
                $name = 'back_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $backPath = Helpers::uploadFile('kyc_documents', $file, $name, config('app.file_system'));
            }

            /**
             * UPDATE DB
             */
            $document->update([
                'document_type'    => $newType,
                'file_side_1_path' => $frontPath,
                'file_side_2_path' => $backPath,
                'status'           => 'Pending',
                'rejection_reason' => null,
            ]);

            $user->update(['kyc_status' => 'Pending']);

            DB::commit();
            return $this->sendResponse([],'KYC document updated successfully. Under review.');
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update KYC document:" . $e->getMessage());
            return $this->sendError("Failed to update KYC document", [], 500);
        }
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
            if (!in_array($user->role->value, ['admin', 'manager'])) {
                return $this->sendError('You do not have permission to view this document.', [], 422);
            }
            $document = KycDocument::with('user')->find($documentId);
            if (empty($document)) {
                return $this->sendError('Document not found.', [], 404);
            }

            // message
            $message = match ($validated['status']) {
                'approved' => 'KYC document approved successfully.',
                'rejected' => 'KYC document rejected successfully.',
                default => 'KYC document updated successfully.',
            };

            DB::transaction(function () use ($validated, $documentId, $user, &$document) {
                // Lock row to prevent double review
                $document = KycDocument::with('user')
                    ->where('id', $documentId)
                    ->lockForUpdate()
                    ->first();

                // FINAL SAFETY CHECK (CORRECT)
                if ($document->reviewed_by !== null) {
                    throw new \Exception('This KYC document has already been reviewed.');
                }

                // Update document
                $document->update([
                    'status'           => $validated['status'], // Approved / Rejected
                    'rejection_reason' => $validated['status'] === 'rejected'
                        ? $validated['reason']
                        : null,
                    'reviewed_by'      => $user->id,
                    'reviewed_at'      => now(),
                ]);

                // notify user
                $document->user->notify(new UniversalNotification(
                    'KYC ' . ucfirst($validated['status']),
                    $validated['status'] === 'approved'
                        ? 'Your KYC verification has been approved successfully.'
                        : 'Your KYC has been rejected. Please review the reason and resubmit.',
                    'kyc_' . strtolower($validated['status']),
                    ['reason' => $document->rejection_reason]
                ));
            });

            // ACTIVITY LOG
            ActiveLog::create([
                'actor_id'       => $user->id,
                'target_user_id' => $document->user_id,
                'type'           => \App\Enum\ActivityLog\Type::KYC,
                'action'         => \App\Enum\ActivityLog\Action::from($validated['status']),
                'reference_type' => KycDocument::class,
                'reference_id'   => $document->id,
                'message'        => ucfirst($validated['status']) . " KYC of {$document->user->first_name}",
                'level'          => $validated['status'] === 'approved'
                    ? \App\Enum\ActivityLog\Level::SUCCESS->value
                    : \App\Enum\ActivityLog\Level::DANGER->value,
            ]);


            return $this->sendResponse([], $message);
        } catch (\Exception $e) {
            Log::error("Failed to update document status:" . $e->getMessage());
            return $this->sendError("Failed to update document status:" . $e->getMessage(), [], 500);
        }
    }
}
