<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\SearchRequest;
use App\Services\ProjectCategoryKcService;

class ProjectCategoryKcController extends Controller
{

    public function get(SearchRequest $request, ProjectCategoryKcService $service)
    {
        return  $service->dxSearch($request);
    }
}
