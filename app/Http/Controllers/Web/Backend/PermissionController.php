<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * get all permissions
     */
    public function index()
    {
        try {
            // Fetch all permissions
            $permissions = Permission::all();

            return response()->json([
                'success' => true,
                'permissions' => $permissions
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'permissions' => []
            ]);
        }
    }
}
