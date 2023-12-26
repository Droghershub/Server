<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

class CategoryController extends Controller {
    
    protected $validatorService;
    
    /**
    * Constructor for initializing the class with ValidatorService.
    *
    * @param \App\Services\ValidatorService $validatorService The ValidatorService instance.
    * @return void
    */
    public function __construct(ValidatorService $validatorService) {
        $this->validatorService = $validatorService;
    }

    /**
    * Retrieve a list of items based on the request parameters.
    *
    * @param \Illuminate\Http\Request $request The incoming request.
    * @return \Illuminate\Http\JsonResponse Returns the response.
    */
    public function list(Request $request) {
        
        return $this->validatorService->execute($request, $request->query(), [
            'order' => 'in:asc,dsc',
            'limit' => 'integer|min:1',
            'page' => 'integer|min:1'
        ], function($user, $request, $responseService) {
            
            $order = $request->query('order', 'asc');
            $query = Category::orderBy('name', $order);
            
            $result = $responseService->getItems($request, $query);
            
            $body = [
                'user' => $responseService->getUserResponse($request, $user),
                'items' => $result->items(),
            ];
            
            if ($result->hasMorePages()) {
                $body['next_page'] = $responseService->getNextPageURL($request, $result);
            }
            
            return $responseService->getSuccessResponse($body, $request);
            
        });
    }
}
    