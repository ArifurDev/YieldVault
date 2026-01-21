<?php

namespace App\Http\Resources\Api\V1\Investment;

use App\Helpers\translateHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class getInvestmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('Accept-Language', 'en');

        $startDate = Carbon::parse($this->start_date);
        $endDate   = Carbon::parse($this->end_date);

        $totalDays = $startDate->diffInDays($endDate);

        //  FIXED (no decimal)
        $daysLeft = now()->startOfDay()->diffInDays($endDate, false);

        return [
            'id'           => $this->id,
            'level_name'   => $this->package?->level?->slug ?  translateHelper::translateCached(
                'level_name_slug_' . md5($this->package?->level?->id),
                $this->package?->level?->slug,
                $lang
            ) : null,
            'package_name' => $this->package?->name ? translateHelper::translateCached(
                'package_name_' . md5($this->package?->id),
                $this->package?->name,
                $lang
            ) : null,
            'amount'       => (float) $this->amount,
            'roi_percent'  => (float) $this->package?->roi_per_day,

            'status' => $this->status->value,
            'badge'  => $this->status->value,

            'total_days' => (int) $totalDays,
            'days_left'  => max((int) $daysLeft, 0),
        ];

    }
}
