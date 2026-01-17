<?php

namespace App\Http\Resources\Api\V1\Kyc;

use App\Helpers\helpers;
use App\Helpers\translateHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class showDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('Accept-Language', 'en');
//        return parent::toArray($request);
        $user = $this->user ?? null;

        if ($user) {
            $firstNameKey = 'value_first_name_' . md5($user->id);
            $lastNameKey  = 'value_last_name_' . md5($user->id);

            $firstName = translateHelper::translateCached(
                $firstNameKey,
                $user->first_name,
                $lang
            );

            $lastName = translateHelper::translateCached(
                $lastNameKey,
                $user->last_name,
                $lang
            );
        }
//        return parent::toArray($request);

        /**
         * DOCUMENT TYPE TRANSLATION (LABEL)
         */
        $documentTypeLabel = translateHelper::translateCached(
            'label_document_type_' . $this->document_type,
            ucfirst(str_replace('_', ' ', $this->document_type)),
            $lang
        );

        /**
         * REJECTION REASON TRANSLATION (VALUE)
         */
        $rejectionReason = null;

        if (!empty($this->rejection_reason)) {
            $rejectionReasonKey = 'value_kyc_rejection_reason_' . md5($this->id);

            $rejectionReason = translateHelper::translateCached(
                $rejectionReasonKey,
                $this->rejection_reason,
                $lang
            );
        }

        return [
            'id'               => $this->id,
            'document_type'    => $this->document_type ,
            'front-side'       => $this->file_side_1_path ? helpers::generateTempURL($this->file_side_1_path,config('app.file_system')) : null,
            'back-side'        => $this->file_side_2_path ? helpers::generateTempURL($this->file_side_2_path,config('app.file_system')) : null,
            'status'           => $this->status,
            'rejection_reason' => $this->rejection_reason ? $rejectionReason : null,
            'created_at'       => $this->created_at,
            'user' => $user ? [
                'id'         => $user->id,
                'first_name' => $user->first_name ? $firstName : $user->first_name,
                'last_name'  => $user->last_name ? $lastName : $user->last_name,
                'kyc_status' => $user->kyc_status,
            ] : null,
        ];
    }
}
