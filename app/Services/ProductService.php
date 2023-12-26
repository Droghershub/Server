<?php

namespace App\Services;

use App\Models\User;
use App\Models\Product;
use App\Services\ValidatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductService {
	
	protected $validatorService;
	
	public function __construct(ValidatorService $validatorService) {
		$this->validatorService = $validatorService;
	}
	
	// search items by list type
	
	public function searchList(Request $request, $type) {
		
		return $this->validatorService->execute($request, $request->query(), [
			'query' => 'required|string|min:1',
			'order' => 'in:asc,dsc',
			'page' => 'integer|min:0',
			'limit' => 'integer|min:1'
		], function($user, $request, $responseService) use ($type) {
			
			switch ($type) {
				case 'Handcart':
					$relation = 'handcarts';
					break;
				case 'Favorites':
					$relation = 'favorites';
					break;
				case 'Orders':
					$relation = 'orders';
					break;
				default:
					return $responseService->getErrorResponse($request, 'INTERNAL_SERVER_ERROR');
			}
			
			$query = $user->{$relation}()
				->where(function ($q) use ($request, $type) {
					if ($type === 'Orders') {
						$q->with('detail.product.brand')
							->whereHas('detail.product', function ($subQuery) use ($request) {
							$subQuery->where('name', 'like', '%' . $request->query('query') . '%');
						});
					} else {
						$q->with('product.brand')
							->whereHas('product', function ($subQuery) use ($request) {
							$subQuery->where('name', 'like', '%' . $request->query('query') . '%');
						});
					}
				})
				->orderBy('created_at', $request->query('order', 'asc'));
			
			$result = $responseService->getItems($request, $query);
			
			$body = [
				'user' => $responseService->getUserResponse($request, $user),
				'items' => $result->items(),
				'details' => $this->getPrice($result, $query, $type)
			];
			
			if ($result->hasMorePages()) {
				$body['next_page'] = $responseService->getNextPageURL($request, $result);
			}

			return $responseService->getSuccessResponse($body, $request);
			
		});
	}
	
	// get list by list type
	
	public function getList(Request $request, $type) {
		
		return $this->validatorService->execute($request, $request->query(), [
			'order' => 'in:asc,dsc',
			'page' => 'integer|min:1',
			'limit' => 'integer|min:1'
		], function($user, $request, $responseService) use ($type) {
			
			switch ($type) {
				case 'Handcart':
					$relation = 'handcarts';
					break;
				case 'Favorites':
					$relation = 'favorites';
					break;
				case 'Orders':
					$relation = 'orders';
					break;
				default:
					return $responseService->getErrorResponse($request, 'INTERNAL_SERVER_ERROR');
			}
			
			$query = $user->{$relation}()
				->with('product.brand')
				->orderBy('created_at', $request->query('order', 'asc'));
			
			$result = $responseService->getItems($request, $query);
			
			$body = [
				'user' => $responseService->getUserResponse($request, $user),
				'items' => $result->items(),
				'details' => $this->getPrice($result, $query, $type)
			];
			
			if ($result->hasMorePages()) {
				$body['next_page'] = $responseService->getNextPageURL($request, $result);
			}
			
			return $responseService->getSuccessResponse($body, $request);
			
		});
	}
	
	// add product to list by list type
	
	public function addToList(Request $request, $type) {
		
		return $this->validatorService->execute($request, $request->json()->all(), [
			'id' => 'required|integer',
			'quantity' => 'integer|min:1'
		], function($user, $request, $responseService) use ($type) {
			
			switch ($type) {
				case 'Handcart':
					$relation = 'handcarts';
					break;
				case 'Favorites':
					$relation = 'favorites';
					break;
				default:
					return $responseService->getErrorResponse($request, 'INTERNAL_SERVER_ERROR');
			}
			
			$item = $user->{$relation}()->where('product_id', $request->json('id'))->first();
			
			if ($item) {
				$item->quantity += $request->json('quantity', 1);
				$item->save();
			} else {
				$user->{$relation}()->create([
					'product_id' => $request->json('id'),
					'quantity' => $request->json('quantity', 1)
				]);
			}
			
			$body = [
				'user' => $responseService->getUserResponse($request, $user),
				'message' => "Item added to your $type successfully."
			];
			
			return $responseService->getSuccessResponse($body, $request);
			
		});
	}
	
	// update product quantity by list type
	
	public function updateInList(Request $request, $type) {
		
		return $this->validatorService->execute($request, $request->json()->all(), [
			'id' => 'required|integer',
			'quantity' => 'required|integer|min:0'
		], function($user, $request, $responseService) use ($type) {
			
			if ($request->json('quantity') == 0) return $this->removeFromList($request, $user, $type);
			
			switch ($type) {
				case 'Handcart':
					$relation = 'handcarts';
					break;
				case 'Favorites':
					$relation = 'favorites';
					break;
				default:
					return $responseService->getErrorResponse($request, 'INTERNAL_SERVER_ERROR');
			}
			
			$item = $user->{$relation}()
				->where('product_id', $request->json('id'))
				->first();
			
			if (!$item) return $responseService->getErrorResponse($request, 'ITEM_NOT_FOUND');
			
			$item->quantity = $request->json('quantity');
			$item->save();
			
			$body = [
				'user' => $responseService->getUserResponse($request, $user),
				'message' => "Item quantity updated in your $type successfully."
			];
			
			return $responseService->getSuccessResponse($body, $request);
			
		});
	}
	
	// remove product from list by list type
	
	public function removeFromList(Request $request, $type) {
		
		return $this->validatorService->execute($request, $request->json()->all(), [
			'id' => 'required|integer'
		], function($user, $request, $responseService) use ($type) {
			
			switch ($type) {
				case 'Handcart':
					$relation = 'handcarts';
					break;
				case 'Favorites':
					$relation = 'favorites';
					break;
				default:
					return $responseService->getErrorResponse($request, 'INTERNAL_SERVER_ERROR');
			}
			
			$item = $user->{$relation}()
				->where('product_id', $request->json('id'))
				->first();
			
			if (!$item) return $responseService->getErrorResponse($request, 'ITEM_NOT_FOUND');
			
			$item->delete();
			
			$body = [
				'user' => $responseService->getUserResponse($request, $user),
				'message' => "Item removed from your $type successfully."
			];
			
			return $responseService->getSuccessResponse($body, $request);
			
		});
	}
	
	// set pricing details in response
	
	private function getPrice($result, $query, $type, $calculate = true) {
		
		if ($type === 'Orders') {
			$totalItems = $query->with('detail')->get()->sum(function ($order) {
				return $order->detail->sum('quantity');
			});
			$totalRetailPrice = $query->with('detail')->get()->sum(function ($order) {
				return $order->detail->sum(function ($item) {
					return $item->retail_price * $item->quantity;
				});
			});
			$totalCurrentPrice = $query->with('detail')->get()->sum(function ($order) {
				return $order->detail->sum(function ($item) {
					return $item->deal_price * $item->quantity;
				});
			});
		} else {
			$totalItems = intval($query->sum('quantity'));
			$totalRetailPrice = $query->with('product')->get()->sum(function ($item) {
				return $item->product->retail_price * $item->quantity;
			});
			$totalCurrentPrice = $query->with('product')->get()->sum(function ($item) {
				return $item->product->current_price * $item->quantity;
			});
		}
		
		$body = [];
		
		if ($totalItems > 0) {
			$body = [
				'quantity' => $totalItems,
				'retail_price' => $totalRetailPrice,
				'current_price' => $totalCurrentPrice,
			];
			
			if ($calculate) {
				$totalSavings = $totalRetailPrice - $totalCurrentPrice;
				
				$body['message'] = "Total $totalItems items worth ₹$totalCurrentPrice in your $type";
				$body['message'] .= $totalSavings > 0 ? (
					", you " . ($type === 'Orders' ?
						"have saved ₹$totalSavings per item so far." :
						"will save ₹$totalSavings if you order now.")
				) : ".";
				
			} else {
				$body['message'] = "Total $totalItems products found in your $type for this query.";
			}
			
		} else {
			if ($calculate) {
				$body['message'] = "There are currently no items in your $type.";
			} else {
				$body['message'] = "No products found in your $type for this query.";
			}
		}
		
		return $body;
	}

}