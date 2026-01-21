<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\translateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Package\FilterRequest;
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
    public function getPackages(FilterRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            $filter = $request->query('filter'); // lock | unlock | null

            $packages = Package::query()
                ->join('levels', 'packages.level_id', '=', 'levels.id')
                ->where('packages.is_active', true)
                ->orderBy('levels.order')
                ->select('packages.*')
                ->with('level')
                ->get();

            // Convert resource to ARRAY (VERY IMPORTANT)
            $data = getPackageResource::collection($packages)->resolve();

            //  APPLY FILTER ON ARRAY DATA
            if ($filter === 'lock') {
                $data = collect($data)->where('is_unlocked', false)->values();
            }

            if ($filter === 'unlock') {
                $data = collect($data)->where('is_unlocked', true)->values();
            }

            $message = translateHelper::translateCached(
                'packages_retrieved_success',
                'Packages retrieved successfully.',
                $lang
            );

            return $this->sendResponse($data, $message);

        } catch (\Exception $e) {
            Log::error("Failed to retrieve packages: " . $e->getMessage());

            $message = translateHelper::translateCached(
                'packages_retrieved_failed',
                'Failed to retrieve packages. Please try again later.',
                $lang
            );

            return $this->sendError($message, [], 500);
        }
    }






}
