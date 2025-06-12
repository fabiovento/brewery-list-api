<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class Handler extends ExceptionHandler
{
    /**
     *
     * @param Request $request
     * @param Throwable $e
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function render($request, Throwable $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return new JsonResponse([
                'status' => 'error',
                'code' => 422,
                'errors' => $e->errors(),
            ], 422);
        }

        if ($e instanceof AuthenticationException) {
            return new JsonResponse([
                'status' => 'error',
                'code' => 401,
                'errors' => $e->getMessage(),
            ], 401);
        }

        if ($e instanceof ExternalAPIException) {
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'errors' => $e->getMessage(),
            ], 500);
        }

        if ($e instanceof NotFoundHttpException) {
            return new JsonResponse([
                'status' => 'error',
                'code' => 404,
                'errors' => $e->getMessage(),
            ], 404);
        }

        return new JsonResponse([
            'status' => 'error',
            'code' => 500,
            'errors' => $e->getMessage(),
        ], 500);

    }
}
