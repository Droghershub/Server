<?php

namespace App\Services;

use App\Models\User;
use Google_Client;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\UserNotDefinedException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class MagicService {

	private static $server_errors = [
		'INVALID_AUTH_TOKEN' => [
			'error' => 'INVALID_AUTH_TOKEN',
			'message' => 'Your OAuth token was expired.',
			'code' => 401
		],
		'AUTHENTICATION_FAILED' => [
			'error' => 'AUTHENTICATION_FAILED',
			'message' => 'Authentication failed due to invalid verification code.',
			'code' => 401
		],
		'INCORRECT_CREDENTIALS' => [
			'error' => 'INCORRECT_CREDENTIALS',
			'message' => 'Your app is outdated or have an issue.',
			'code' => 401
		],
		'MISSING_REQUIRED_PERMISSIONS' => [
			'error' => 'MISSING_REQUIRED_PERMISSIONS',
			'message' => 'You lack the necessary permissions to access this resource.',
			'code' => 403
		],
		'ACCOUNT_WAS_SUSPENDED' => [
			'error' => 'ACCOUNT_WAS_SUSPENDED',
			'message' => 'Your account has been suspended.',
			'code' => 404
		],
		'ACCOUNT_NOT_FOUND' => [
			'error' => 'ACCOUNT_NOT_FOUND',
			'message' => 'This account does not exists.',
			'code' => 404
		],
		'ITEM_NOT_FOUND' => [
			'error' => 'ITEM_NOT_FOUND',
			'message' => 'This item does not exists.',
			'code' => 404
		],
		'RESOURCE_NOT_FOUND' => [
			'error' => 'RESOURCE_NOT_FOUND',
			'message' => 'This route does not exists.',
			'code' => 404
		],
		'ACCOUNT_ALREADY_EXISTS' => [
			'error' => 'ACCOUNT_ALREADY_EXISTS',
			'message' => 'An account with this credential already exists.',
			'code' => 409
		],
		'ACCOUNT_WAS_DELETED' => [
			'error' => 'ACCOUNT_WAS_DELETED',
			'message' => 'Your account was deleted and should be recovered before you can use it.',
			'code' => 410
		],
		'MISSING_OR_INVALID_FIELDS' => [
			'error' => 'MISSING_OR_INVALID_FIELDS',
			'message' => 'Your app is outdated or have an issue.',
			'code' => 422
		],
		'TOO_MANY_REQUESTS' => [
			'error' => 'TOO_MANY_REQUESTS',
			'message' => 'Server is busy please try again later.',
			'code' => 429
		],
		'FEATURE_UNAVAILABLE' => [
			'error' => 'FEATURE_UNAVAILABLE',
			'message' => 'The feature is currently unavailable.',
			'code' => 500
		],
		'INTERNAL_SERVER_ERROR' => [
			'error' => 'INTERNAL_SERVER_ERROR',
			'message' => 'Something went wrong on our side.',
			'code' => 500
		]
	];
	
	public static function getErrorResponse($code, $exception, $request, $additional = []) {
		$body = self::$server_errors[$code];
		$code = $body['code'];
		unset($body['code']);
		if ((env('APP_DEBUG')) && $exception) $body['exception'] = $exception;
		$body = array_merge($body, $additional);
		return response()->json([
			'success' => false,
			'code' => $code,
			'body' => $body
		], $code, [], self::getPrettyPrintOption($request));
	}
	
	public static function getSuccessResponse($body, $request) {
		return response()->json([
			'success' => true,
			'code' => 200,
			'body' => $body
		], 200, [], self::getPrettyPrintOption($request));
	}
	
	private static function getPrettyPrintOption($request) {
		$pretty = $request->has('pretty') && ($request->get('pretty') === 'true');
		return $pretty ? JSON_PRETTY_PRINT : JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
	}
	
	public static function cleanToken($request) {
		$token = $request->header('o-auth-token');
		return str_replace('Bearer ', '', $token);
	}

	public static function getGoogleClient() {
		$googleClient = new Google_Client();
		$googleClient->setApplicationName(env('APP_NAME'));
		$googleClient->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
		return $googleClient;
	}
	
	public static function getGooglePayload($request) {
		return self::getGooglePayloadFromToken($request->header('o-auth-token'));
	}
	
	public static function getGooglePayloadFromToken($token) {
		try {
			$googleClient = self::getGoogleClient();
			return $googleClient->verifyIdToken($token);
		} catch (Exception $e) {
			return false;
		}
	}
	
	public static function getUserType($request) {
		$type = $request->header('x-account-type');
		return strtolower($type);
	}
	
	public static function getUser($request) {
		switch (self::getUserType($request)) {
			case 'google':
				$payload = self::getGooglePayload($request);
				if (!$payload) {
					return self::getErrorResponse('INVALID_AUTH_TOKEN', null, $request);
				}
				$id = $payload['sub'];
				$user = User::where('email', $payload['email'])->first();
				if ($user && $user->trashed()) {
					return self::getErrorResponse('ACCOUNT_WAS_DELETED', null, $request);
				}
				if (!$user) {
					return self::getErrorResponse('ACCOUNT_NOT_FOUND', null, $request);
				}
				break;
			case 'phone':
			case 'guest':
				try {
					$user = JWTAuth::parseToken()->authenticate();
				} catch (TokenExpiredException | TokenInvalidException | TokenBlacklistedException $e) {
					return self::getErrorResponse('INVALID_AUTH_TOKEN', null, $request);
				} catch (UserNotDefinedException | JWTException $e) {
					return self::getErrorResponse('ACCOUNT_NOT_FOUND', null, $request);
				}
				break;
			default:
				return self::getErrorResponse('MISSING_OR_INVALID_FIELDS', null, $request);
		}
		if (!$user->hasRole('customer')) {
			return self::getErrorResponse('MISSING_REQUIRED_PERMISSIONS', null, $request);
		}
		return $user;
	}
	
	public static function getUserResponse($request, $user, $additional = []) {
		$type = self::getUserType($request);
		switch ($type) {
			case 'google':
			case 'phone':
			case 'guest':
				return array_merge(['id' => $user->id, 'type' => $type], $additional);
		}
		return false;
	}
	
	public static function makeHidden($items, $additional = []) {
		$items->getCollection()->each(function ($item) use ($additional) {
			self::makeHiddenKeys($item, $additional);
		});
		return $items;
	}
	
	private static function makeHiddenKeys($item, $additional) {
		$hidden = [];
		foreach ($item->toArray() as $key => $value) {
			if (preg_match('/^.*_id$/', $key)) {
				$hidden[] = $key;
			}
			if (is_null($value)) {
				unset($item->$key);
			}
			//			if (is_object($item->$key)) {
			//				dd($key);
			//			}
			//			 else {
			//				is_object($value)
			//				if (is_array($value)) {
			//					$this->makeHiddenKeys($value, $additional);
			//				}
			//			}
		}
		$item->makeHidden(array_merge([
			'purchase_price',
			'created_at',
			'updated_at',
			'deleted_at'
		], $hidden, $additional));
	}

}