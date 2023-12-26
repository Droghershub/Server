<?php

namespace App\Services;

use App\Services\ResponseService;
use Exception;
use Illuminate\Support\Facades\Validator;

class ValidatorService {
	
	protected $responseService;
	
	public function __construct(ResponseService $responseService) {
		$this->reponseService = $responseService;
	}
	
	public function execute($request, $matches, $validations, callable $function) {
		
		$validator = Validator::make($matches, $validations);
		
		if ($validator->fails()) {
			return $this->reponseService->getErrorResponse(
				$request,
				'MISSING_OR_INVALID_FIELDS',
				$validator->errors()
			);
		}
		
		$user = $this->reponseService->getUser($request);
		
		if (class_basename($user) !== 'User') return $user;
		
		try {
			return $function(
				$user, $request, $this->reponseService
			);
		} catch (Exception $e) {
			return $this->reponseService->getErrorResponse(
				$request,
				'INTERNAL_SERVER_ERROR',
				$e->getMessage()
			);
		}
	}
}