<?php
    
namespace App\Http\Middleware;
    
use Closure;
use Illuminate\Http\Request;
use App\Services\MagicService;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
    
class ValidateApplication
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

        $validator = Validator::make([
            'x-account-type' => $request->header('x-account-type'),
            'x-api-key' => $request->header('x-api-key')
        ], [
            'x-account-type' => 'required|in:phone,google,guest',
            'x-api-key' => 'required'
        ]);

        if ($validator->fails()) return $this->magicService->getErrorResponse('MISSING_OR_INVALID_FIELDS', $validator->errors(), $request);
        
        if ($request->header('x-api-key') !== env('API_KEY')) return $this->magicService->getErrorResponse('INCORRECT_CREDENTIALS', null, $request);
        
        $token = $request->header('o-auth-token');
        
        if ($token) $request->headers->set('Authorization', $token);
        
        return $next($request);
    }
}