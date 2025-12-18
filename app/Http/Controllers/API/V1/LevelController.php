<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Level\getLevelsResource;
use App\Models\Level;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    use ApiResponse;

    /**
     * get all levels
     */
    public function getLevels()
    {
        try {
            $levels = Level::where('is_active', true)->get();
            return $this->sendResponse(getLevelsResource::collection($levels), 'Levels retrieved successfully.');
        }catch (\Exception $e){
            return $this->sendError("Failed to retrieve levels:".$e->getMessage(), [], 500);
        }
    }
}
