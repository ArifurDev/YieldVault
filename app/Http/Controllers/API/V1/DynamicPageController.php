<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\translateHelper;
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
    public function getTitle(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {

            $pages = DynamicPage::select('id', 'title', 'slug')->get();

            $data = $pages->map(function ($page) use ($lang) {

                return [
                    'title' => translateHelper::translateCached(
                        'dynamic_page_title_' . md5($page->slug),
                        $page->title,
                        $lang
                    ),
                    'slug'  => $page->slug,
                ];
            });
            $message = translateHelper::translateCached(
                'dynamic_pages_list_success',
                'Dynamic pages list retrieved successfully.',
                $lang
            );

            return $this->sendResponse($data, $message);
        }catch (\Exception $exception){
            $message = translateHelper::translateCached(
                'dynamic_pages_list_failed',
                'Failed to retrieve dynamic pages list.',
                $lang
            );

            return $this->sendError($message, [], 500);
        }
    }

    /**
     * get dynamic page details
     */
    public function getDetails(Request $request,$slug)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            $dynamicPage = DynamicPage::where('slug',$slug)->first();
            if (!$dynamicPage){
                $message = translateHelper::translateCached(
                    'dynamic_page_not_found',
                    'Dynamic page not found.',
                    $lang
                );

                return $this->sendError($message);
            }

            $message = translateHelper::translateCached(
                'dynamic_page_details_success',
                'Dynamic page retrieved successfully.',
                $lang
            );

            return $this->sendResponse( new DetailsResource($dynamicPage),$message);
        }catch (\Exception $exception){
            $message = translateHelper::translateCached(
                'dynamic_page_details_failed',
                'Failed to retrieve dynamic page.',
                $lang
            );

            return $this->sendError($message, [], 500);
        }
    }
}
