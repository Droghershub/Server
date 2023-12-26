<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Services\MagicService;
use Illuminate\Http\Request;

class NotificationController extends Controller {

    protected $magicService;

    public function __construct(MagicService $magicService) {
        $this->magicService = $magicService;
    }

    public function list(Request $request) {

        $user = $this->magicService->getUser($request);

        if (class_basename($user) !== 'User') return $user;

        $u_notifications = $user->notifications()->where('status', 'ACTIVE')->get();
        $c_notifications = Notification::whereNull('user_id')->where('status', 'ACTIVE')->get();

        $body = [
            'user' => $this->magicService->getUserResponse($request, $user),
            'notifications' => $u_notifications->concat($c_notifications)
        ];

        return $this->magicService->getSuccessResponse($body, $request);
    }

    public function flag(Request $request) {

        $validator = Validator::make($request->json()->all(), [
            'ids' => 'required|array',
            'ids.*' => 'integer'
        ]);

        if ($validator->fails()) return $this->magicService->getErrorResponse('MISSING_OR_INVALID_FIELDS', $validator->errors(), $request);

        $user = $this->magicService->getUser($request);

        if (class_basename($user) !== 'User') return $user;

        $user->notifications()->whereIn('id', $request->json('ids', []))->update(['status' => 'INACTIVE']);

        $body = [
            'message' => 'Notifications deactivated successfully.',
        ];

        return $this->magicService->getSuccessResponse($body, $request);
    }

}
