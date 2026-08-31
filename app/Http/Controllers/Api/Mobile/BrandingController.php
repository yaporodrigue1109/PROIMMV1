<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;

class BrandingController extends Controller
{
    public function __invoke(SettingService $settings): JsonResponse
    {
        return response()
            ->json(['data' => $settings->getPublicData()])
            ->setPublic()
            ->setMaxAge(300);
    }
}
