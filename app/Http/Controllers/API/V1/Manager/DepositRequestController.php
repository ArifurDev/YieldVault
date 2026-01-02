<?php

namespace App\Http\Controllers\Api\V1\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Manager\getDepositRequest;
use App\Http\Resources\Api\V1\Manager\getDepositRequestResource;
use App\Models\DepositRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DepositRequestController extends Controller
{
    use ApiResponse;

    /**
     * list of deposit request list
     */
    public function index(getDepositRequest $request)
    {
        try {
            $validated = $request->validated();
            $page = $validated['page'] ?? 1;
            $perPage = $validated['per_page'] ?? 10;

            // get current user
            $manager = auth()->user();

            if ($manager->role->value !== 'manager') {
                return $this->sendError("You don't have permission to access this page.",[], 403);
            }

            $deposits = DepositRequest::with('user')
                ->where('manager_id', $manager->id)
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->sendResponse(getDepositRequestResource::collection($deposits), 'Deposit request list retrieved successfully.',200,[],$deposits);
        }catch (\Exception $e){
            Log::error("Failed to get DepositRequest list : ".$e->getMessage());
            return $this->sendError("Failed to get DepositRequest list.",[], 500);
        }
    }

    /**
     * show deposit request details
     */
    public function show($id)
    {

    }
}
