<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    use ApiResponse;
    /**
     * list of package
     */
    public function getPackages()
    {
        $packages = Package::query()
            ->join('levels', 'packages.level_id', '=', 'levels.id')
            ->where('packages.is_active', true)
            ->orderBy('levels.order')
            ->select('packages.*') // VERY IMPORTANT
            ->with('level')
            ->get();

    }
}
