<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Postcode;
use App\Models\User;
use App\Services\ValidatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller {

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
    * Process the postcode from the request.
    *
    * @param \Illuminate\Http\Request $request The incoming request.
    * @return \Illuminate\Http\JsonResponse Returns the response.
    */
    public function postcode(Request $request) {
        
        return $this->validatorService->execute($request, $request->json()->all(), [
            'postcode' => 'required|string'
        ], function($user, $request, $responseService) {
            
            $result = Postcode::where('code', $request->json('postcode'))->first();
            
            if (!$result) {
                return $responseService->getErrorResponse($request, 'ITEM_NOT_FOUND');
            }
            
            $body = [
                'user' => $responseService->getUserResponse($request, $user),
                'item' => $responseService->getItem($result)
            ];
            
            return $responseService->getSuccessResponse($body, $request);
            
        });
    }

    /**
    * Perform a search based on the request parameters.
    *
    * @param \Illuminate\Http\Request $request The incoming request.
    * @return \Illuminate\Http\JsonResponse Returns the response.
    */
    public function search(Request $request) {
        
        return $this->validatorService->execute($request, $request->query(), [
            'query' => 'required|string',
            'order' => 'in:asc,dsc',
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1'
        ], function($user, $request, $responseService) {
            
            $query = $user->addresses()
                ->with('postcode')
                ->where(function ($queryBuilder) use ($request) {
                    $queryBuilder->where('name', 'like', "%" . $request->query('query') . "%")
                        ->orWhere('line_1', 'like', "%" . $request->query('query') . "%")
                        ->orWhere('phone', 'like', "%" . $request->query('query') . "%");
                })
                ->orderBy('name', $request->query('order', 'asc'));
            
            $result = $responseService->getItems($request, $query);
            
            $body = [
                'user' => $responseService->getUserResponse($request, $user),
                'items' => $result->items()
            ];
            
            if ($result->hasMorePages()) {
                $body['next_page'] = $responseService->getNextPageURL($request, $result);
            }
            
            return $responseService->getSuccessResponse($body, $request);
            
        });
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
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1'
        ], function($user, $request, $responseService) {
            
            $query = $user->addresses()
                ->with('postcode')
                ->orderBy('name', $request->query('order', 'asc'));
            
            $result = $responseService->getItems($request, $query);
            
            $body = [
                'user' => $responseService->getUserResponse($request, $user),
                'items' => $result->items()
            ];
            
            if ($result->hasMorePages()) {
                $body['next_page'] = $responseService->getNextPageURL($request, $result);
            }
            
            return $responseService->getSuccessResponse($body, $request);
            
        });
    }

    /**
    * Add a new address based on the request data.
    *
    * @param \Illuminate\Http\Request $request The incoming request.
    * @return \Illuminate\Http\JsonResponse Returns the response.
    */
    public function add(Request $request) {
        
        return $this->validatorService->execute($request, $request->json()->all(), [
            'postcode_id' => 'required|integer',
            'name' => 'required',
            'care_of' => 'required',
            'phone' => 'required',
            'line_1' => 'required',
            'line_2' => 'required',
            'type' => 'in:HOME,OFFICE,OTHER'
        ], function($user, $request, $responseService) {
            
            if (!Postcode::where('id', $request->json('postcode_id'))->exists()) {
                return $responseService->getErrorResponse($request, 'ITEM_NOT_FOUND');
            }
            
            $result = new Address($request->json()->all());
            
            if ($user->addresses->isEmpty()) {
                $result->default = true;
            }
            
            $user->addresses()->save($result);
            
            $body = [
                'user' => $responseService->getUserResponse($request, $user),
                'id' => $result->id,
                'message' => 'Address created successfully.'
            ];
            
            $body = [
                'user' => $responseService->getUserResponse($request, $user),
                'item' => $responseService->getItem($result)
            ];
            
            return $responseService->getSuccessResponse($body, $request);
            
        });
    }
    
    /**
    * Show details of a specific address by ID.
    *
    * @param \Illuminate\Http\Request $request The incoming request.
    * @return \Illuminate\Http\JsonResponse Returns the response.
    */
    public function show(Request $request) {
        
        return $this->validatorService->execute($request, $request->json()->all(), [
            'id' => 'required|integer'
        ], function($user, $request, $responseService) {
            
            $result = Address::with('postcode')->find($request->json('id'));
            
            if (!$result) {
                return $responseService->getErrorResponse($request, 'ITEM_NOT_FOUND');
            }
            
            $body = [
                'user' => $responseService->getUserResponse($request, $user),
                'item' => $responseService->getItem($result)
            ];
            
            return $responseService->getSuccessResponse($body, $request);
            
        });
    }

    /**
    * Update an existing address based on the request data.
    *
    * @param \Illuminate\Http\Request $request The incoming request.
    * @return \Illuminate\Http\JsonResponse Returns the response.
    */
    public function update(Request $request) {
        
        return $this->validatorService->execute($request, $request->json()->all(), [
            'id' => 'required|integer',
            'postcode_id' => 'integer',
            'default' => 'boolean',
            'type' => 'in:HOME,OFFICE,OTHER'
        ], function($user, $request, $responseService) {
            
            $result = Address::find($request->json('id'));
            
            if (!$result) {
                return $responseService->getErrorResponse($request, 'ITEM_NOT_FOUND');
            }
            
            if ($request->has('default') && $request->json('default')) {
                $user->addresses()
                    ->where('id', '<>', $result->id)
                    ->update(['default' => false]);
            }
            
            $result->update($request->json()->all());
            
            $body = [
                'user' => $responseService->getUserResponse($request, $user),
                'message' => 'Address updated successfully.'
            ];
            
            return $responseService->getSuccessResponse($body, $request);
            
        });
    }

    /**
    * Delete an address based on the provided ID.
    *
    * @param \Illuminate\Http\Request $request The incoming request.
    * @return \Illuminate\Http\JsonResponse Returns the response.
    */
    public function delete(Request $request) {
        
        return $this->validatorService->execute($request, $request->json()->all(), [
            'id' => 'required|integer'
        ], function($user, $request, $responseService) {
            
            $result = Address::find($request->json('id'));
            
            if (!$result) {
                return $responseService->getErrorResponse($request, 'ITEM_NOT_FOUND');
            }
            
            $result->delete();
            
            $body = [
                'user' => $responseService->getUserResponse($request, $user),
                'message' => 'Address removed successfully.'
            ];
            
            return $responseService->getSuccessResponse($body, $request);
            
        });
    }
}