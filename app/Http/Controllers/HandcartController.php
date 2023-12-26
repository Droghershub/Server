<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\Request;

class HandcartController extends Controller {
    
    protected $productService;
    
    private $type = 'Handcart';
    
    public function __construct(ProductService $productService) {
        $this->productService = $productService;
    }
    
    public function search(Request $request) {
        return $this->productService->searchList($request, $this->type);
    }
    
    public function list(Request $request) {
        return $this->productService->getList($request, $this->type);
    }
    
    public function add(Request $request) {
        return $this->productService->addToList($request, $this->type);
    }
    
    public function update(Request $request) {
        return $this->productService->updateInList($request, $this->type);
    }
    
    public function delete(Request $request) {
        return $this->productService->removeFromList($request, $this->type);
    }
}
    