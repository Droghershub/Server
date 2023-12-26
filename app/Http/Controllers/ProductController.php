<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

class ProductController extends Controller {

    protected $validatorService;

    public function __construct(ValidatorService $validatorService) {
        $this->validatorService = $validatorService;
    }

    // search products

    public function search(Request $request) {
        
        return $this->validatorService->execute($request, $request->query(), [
            'query' => 'required|string',
            'brand_id' => 'integer',
            'category_id' => 'integer',
            'min_price' => 'numeric|min:0',
            'max_price' => 'numeric|min:' . $request->query('min_price', 0),
            'order' => 'in:asc,dsc',
            'limit' => 'integer|min:1',
            'page' => 'integer|min:1'
        ], function($user, $request, $responseService) {
            
            $query = Product::query();
            
            if ($request->has('brand_id')) {
                if (!Brand::find($request->query('brand_id'))) {
                    return $responseService->getErrorResponse($request, 'ITEM_NOT_FOUND');
                }
                $query->where('brand_id', $request->query('brand_id'));
            }
            
            if ($request->has('category_id')) {
                if (!Category::find($request->query('category_id'))) {
                    return $responseService->getErrorResponse($request, 'ITEM_NOT_FOUND');
                }
                $query->where('category_id', $request->query('category_id'));
            }
            
            if ($request->has('min_price')) {
                $query->where('purchase_price', '>=', $request->query('min_price'));
            }
            
            if ($request->has('max_price')) {
                $query->where('purchase_price', '<=', $request->query('max_price'));
            }
            
            if ($request->has('query')) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->query('query') . '%')
                    ->orWhere('description', 'like', '%' . $request->query('query') . '%')
                    ->orWhereHas('brand', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->query('query') . '%');
                    })
                    ->orWhereHas('category', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->query('query') . '%');
                    });
                });
            }
            
            $query->with('brand', 'category')
                ->where('status', 'ACTIVE')
                ->orderBy('retail_price', $request->query('order', 'asc'));
            
            $result = $responseService->getItems($request, $query);
            
            $body = [
                'user' => $responseService->getUserResponse($request, $user),
                'items' => $result->items()
            ];
            
            $body = array_merge($body, $this->getBrandsCategories($responseService, $query));
            
            if ($result->hasMorePages()) {
                $body['next_page'] = $responseService->getNextPageURL($request, $result);
            }
            
            return $responseService->getSuccessResponse($body, $request);
            
        });
    }

    // show product

    public function show(Request $request) {
        
        return $this->validatorService->execute($request, $request->json()->all(), [
            'id' => 'required|integer'
        ], function($user, $request, $responseService) {
            
            $result = Product::with('brand', 'category')->find($request->json('id'));
            
            if (!$result) return $responseService->getErrorResponse($request, 'ITEM_NOT_FOUND');
            
            $body = [
                'user' => $responseService->getUserResponse($request, $user),
                'item' => $responseService->getItem($result)
            ];

            return $responseService->getSuccessResponse($body, $request);
        });
    }
    
    private function getBrandsCategories($responseService, $query) {
        $products = $query->get();
        
        $brands = $products->map(function ($product) {
            return $product->brand;
        })->unique('id');
        
        $categories = $products->map(function ($product) {
            return $product->category;
        })->unique('id');
        
        return [
            'brands' => $responseService->getItems(null, $brands),
            'categories' =>$responseService->getItems(null,  $categories)
        ];
    }

}
    