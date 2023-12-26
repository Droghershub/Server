<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RecommendationController extends Controller {
    
    protected $magicService;
    
    public function __construct(MagicService $magicService) {
        $this->magicService = $magicService;
    }

    public function list(Request $request) {
        /* TODO: compile a list of following
        
            • Top selling brands
            • Top selling categories
            • Top selling products
            • Most discounted items
            • User's wishlist
            • User's cart items
        */
    }

}