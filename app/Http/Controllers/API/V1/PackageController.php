<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Package\getPackageResource;
use App\Models\Package;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PackageController extends Controller
{
    use ApiResponse;
    /**
     * list of package
     */
    public function getPackages()
    {
        try {
            $packages = Package::query()
                ->join('levels', 'packages.level_id', '=', 'levels.id')
                ->where('packages.is_active', true)
                ->orderBy('levels.order')
                ->select('packages.*') // VERY IMPORTANT
                ->with('level')
                ->get();

            return $this->sendResponse(getPackageResource::collection($packages), 'Packages retrieved successfully.');
        }catch (\Exception $e){
            Log::error("Failed to retrieve packages:".$e->getMessage());
            return $this->sendError("Failed to retrieve packages",[], 400);
        }
    }
}
