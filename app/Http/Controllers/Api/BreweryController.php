<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ExternalAPIException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class BreweryController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ExternalAPIException
     */
    public function list(Request $request): JsonResponse
    {
            $page = $request->get('page', 1);
            $response = Http::get(config('services.external_api.url'), ['page' => $page]);

            if ($response->successful()) {
                return new JsonResponse(['status' => 'ok', 'data' => $response->json()]);
            }

            throw new ExternalAPIException($response->status());
    }

}
