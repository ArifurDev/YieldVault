<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\DynamicPage\DetailsResource;
use App\Models\DynamicPage;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DynamicPageController extends Controller
{
    use ApiResponse;

    /**
     * get dynamic page title
     */
    public function getTitle()
    {
        try {
            $dynamicPages = DynamicPage::select('title','slug')->get();
            return $this->sendResponse($dynamicPages,'Dynamic Pages list retrieved successfully');
        }catch (\Exception $exception){
            return $this->sendError("Failed to retrieve Dynamic Pages list:".$exception->getMessage(),[],500);
        }
    }

    /**
     * get dynamic page details
     */
    public function getDetails($slug)
    {
        try {
            $dynamicPage = DynamicPage::where('slug',$slug)->first();
            if (!$dynamicPage){
                return $this->sendError('Dynamic Page not found');
            }
            return $this->sendResponse(new DetailsResource($dynamicPage),'Dynamic Page retrieved successfully');
        }catch (\Exception $exception){
            return $this->sendError("Failed to retrieve Dynamic Page:".$exception->getMessage(),[],500);
        }
    }
}
