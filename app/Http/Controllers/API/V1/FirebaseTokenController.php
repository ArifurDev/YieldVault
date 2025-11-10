<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Firebase\FirebaseResource;
use App\Models\FirebaseTokens;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FirebaseTokenController extends Controller
{
    use ApiResponse;

    /**
     * News Serve For Frontend
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'token' => 'required|string',
            'device_id' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }
        try {
            $user = FirebaseTokens::where('user_id', $request->user_id)->where('device_id', $request->device_id)->first();
            if ($user) {
                $user->delete();
            }

            $data = new FirebaseTokens();
            $data->user_id = $request->user_id;
            $data->token = $request->token;
            $data->device_id = $request->device_id;
            $data->is_active = $request->is_active;
            $data->save();

            return $this->sendResponse(new FirebaseResource($data), 'Token saved successfully');
        } catch (\Exception $e) {
            return $this->sendError('An error occurred: ' . $e->getMessage(), [],  500);
        }
    }

    /**
     * Get Single Record
     * @param $token, $device_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'device_id' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->sendError('An error occurred: ' . $validator->errors()->first(), 400);
        }
        $user_id = $request->user_id;
        $device_id = $request->device_id;
        $data = FirebaseTokens::where('user_id', $user_id)->where('device_id', $device_id)->first();
        if (!$data) {
            return $this->sendError('No records found', [], 404);
        }
        return $this->sendResponse(new FirebaseResource($data), 'Token fetched successfully');
    }

    /**
     * Delete Token Single Record
     * @param $token, $device_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'device_id' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $user = FirebaseTokens::where('user_id', $request->user_id)->where('device_id', $request->device_id);
        if ($user) {
            $user->delete();
            return $this->sendResponse([], 'Token deleted successfully');
        } else {
            return $this->sendError('No records found', [], 404);
        }
    }
}
