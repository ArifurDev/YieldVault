<?php

namespace App\Http\Resources\Api\V1\Investment;

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
        $startDate = Carbon::parse($this->start_date);
        $endDate   = Carbon::parse($this->end_date);

        $totalDays = $startDate->diffInDays($endDate);

        //  FIXED (no decimal)
        $daysLeft = now()->startOfDay()->diffInDays($endDate, false);

        return [
            'id'           => $this->id,
            'package_name' => $this->package?->name,
            'amount'       => (float) $this->amount,
            'roi_percent'  => (float) $this->package?->roi_per_day,

            'status' => $this->status->value,
            'badge'  => $this->status->value,

            'total_days' => (int) $totalDays,
            'days_left'  => max((int) $daysLeft, 0),
        ];

    }
}
