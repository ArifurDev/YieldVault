<?php

namespace App\Http\Resources\Api\V1\SupportTicket;

use App\Helpers\translateHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        return parent::toArray($request);
        $lang = $request->header('Accept-Language', 'en');

        // Assigned user translation (safe)
        $assignedTo = null;

        if ($this->assignedTo) {

            $firstNameKey = 'value_first_name_' . md5($this->assignedTo->id);
            $lastNameKey  = 'value_last_name_' . md5($this->assignedTo->id);

            $assignedTo = [
                'id' => $this->assignedTo->id,

                'first_name' => $this->assignedTo->first_name
                    ? translateHelper::translateCached(
                        $firstNameKey,
                        $this->assignedTo->first_name,
                        $lang
                    )
                    : null,

                'last_name' => $this->assignedTo->last_name
                    ? translateHelper::translateCached(
                        $lastNameKey,
                        $this->assignedTo->last_name,
                        $lang
                    )
                    : null,
            ];
        }

        return [
            'id'          => $this->id,
            'support_id'  => '#' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'subject'     => $this->subject ? translateHelper::translateCached(
                'support_ticket_subject_' . md5($this->id),
                $this->subject,
                $lang
            ) : null,
            'category'    => $this->category ? translateHelper::translateCached(
                'support_ticket_category_' . $this->category->value,
                $this->category->value,
                $lang
            ) : null,
            'priority'    => $this->priority
                ? translateHelper::translateCached(
                    'support_ticket_priority_' . $this->priority->value,
                    $this->priority->value,
                    $lang
                ) : null,
            'status'      => $this->status
                ? translateHelper::translateCached(
                    'support_ticket_status_' . $this->status->value,
                    $this->status->value,
                    $lang
                )
                : null,
            'assigned_to' =>  $assignedTo,
            'updated_at'  => $this->updated_at?->toDateTimeString(),
        ];
    }
}
