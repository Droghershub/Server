<?php

namespace App\Exceptions;

use App\Services\MagicService;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Throwable;

class Handler extends ExceptionHandler
{
    
    protected $magicService;
    
    public function __construct(MagicService $magicService) {
        $this->magicService = $magicService;
    }
    
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    
    /**
    * Render an exception into an HTTP response.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \Throwable  $exception
    * @return \Symfony\Component\HttpFoundation\Response
    *
    * @throws \Throwable
    */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ThrottleRequestsException) {
            return $this->magicService->getErrorResponse('TOO_MANY_REQUESTS', null, $request);
        }
        return parent::render($request, $exception);
    }
}

