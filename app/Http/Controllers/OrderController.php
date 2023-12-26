<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\ProductService;
use App\Services\MagicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class OrderController extends Controller {
    
    protected $magicService, $productService;
    
    private $type = 'Orders';
    
    public function __construct(MagicService $magicService, ProductService $productService) {
        $this->magicService = $magicService;
        $this->productService = $productService;
    }
    
    public function search(Request $request) {
        return $this->productService->searchList($request, $this->type);
    }
    
    public function list(Request $request) {
        return $this->productService->getList($request, $this->type);
    }
    
    public function show(Request $request) {
        
        $validator = Validator::make($request->json()->all(), [
            'id' => 'required|integer'
        ]);
        
        if ($validator->fails()) return $this->magicService->getErrorResponse('MISSING_OR_INVALID_FIELDS', $validator->errors(), $request);
        
        $user = $this->magicService->getUser($request);
        
        if (class_basename($user) !== 'User') return $user;
        
        $order = Order::find($request->input('id'));
        
        if (!$order) return $this->magicService->getErrorResponse('ITEM_NOT_FOUND', null, $request);
        
        $body = [
            'user' => $this->magicService->getUserResponse($request, $user),
            'id' => $order->id,
            'details' => $order->detail()
        ];
        
        return $this->magicService->getSuccessResponse($body, $request);
    }
    
    public function place(Request $request) {
        
        $validator = Validator::make($request->json()->all(), [
            'address_id' => 'required|integer',
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ]);
        
        if ($validator->fails()) return $this->magicService->getErrorResponse('MISSING_OR_INVALID_FIELDS', $validator->errors(), $request);
        
        $user = $this->magicService->getUser($request);
        
        if (class_basename($user) !== 'User') return $user;
        
        try {
            
            DB::beginTransaction();
    
            $order = new Order();
            $order->user_id = $user->id;
            $order->address_id = $request->json('address_id');
            $order->save();
    
            foreach ($request->input('items') as $item) {
                $product = $this->productService->getProductById($item['id']);
                if (!$product) {
                    DB::rollBack();
                    return $this->magicService->getErrorResponse('ITEM_NOT_FOUND', null, $request);
                }
                
                $orderDetail = new OrderDetail();
                $orderDetail->order_id = $order->id;
                $orderDetail->product_id = $product->id;
                $orderDetail->quantity = $item['quantity'];
                $orderDetail->deal_price = $product->current_price;
                $orderDetail->save();
            }
            
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
        
        $body = [
            'user' => $this->magicService->getUserResponse($request, $user),
            'id' => $order->id,
            'message' => 'Order was placed successfully.',
        ];
        
        return $this->magicService->getSuccessResponse($body, $request);
    }
    
    public function cancel(Request $request) {
        
        $validator = Validator::make($request->json()->all(), [
            'id' => 'required|integer',
        ]);
        
        if ($validator->fails()) return $this->magicService->getErrorResponse('MISSING_OR_INVALID_FIELDS', $validator->errors(), $request);
        
        $user = $this->magicService->getUser($request);
        
        if (class_basename($user) !== 'User') return $user;

        $order = Order::find($request->input('id'));
        
        if (!$order) return $this->magicService->getErrorResponse('ITEM_NOT_FOUND', null, $request);

        $order->status = 'INACTIVE';
        $order->delete();
        
        $body = [
            'user' => $this->magicService->getUserResponse($request, $user),
            'id' => $order->id,
            'message' => 'Order was cancelled successfully.',
        ];
        
        return $this->magicService->getSuccessResponse($body, $request);
    }
}