<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\MagicService;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ValidateAuthorization
{
    
    protected $magicService;
    
    public function __construct(MagicService $magicService) {
        $this->magicService = $magicService;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        
        $validator = Validator::make($request->headers->all(), [
            'o-auth-token' => 'required'
        ]);

        if ($validator->fails()) return $this->magicService->getErrorResponse('MISSING_OR_INVALID_FIELDS', $validator->errors(), $request);
        
        $token = $request->header('o-auth-token');
        
        $request->headers->set('Authorization', $token);
        
        return $next($request);
    }
}
