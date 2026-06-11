<?php

namespace App\Http\Controllers;

use App\Services\VkEventService;
use App\Services\VkService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class VkCallbackController extends Controller
{
    public function __construct(
        private readonly VkService $vkService,
    )
    {
    }

    public function handle(Request $request): Response
    {
        if (isset($request->all()['type']) && $request->all()['type'] == 'confirmation') {
            return response(config('services.vk.confirmation'),200)->header('Content-Type', 'text/plain');
        }

        $event = VkEventService::fromRequest($request);

        $this->vkService->handle($event);

        return response('ok', 200);
    }
}
