<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::selectOne('select 1');
        } catch (Throwable $exception) {
            Log::error('Health database probe failed.', [
                'exception_type' => $exception::class,
                'git_sha' => config('demo.git_sha'),
            ]);

            return response()->json([
                'status' => 'unavailable',
                'database' => 'error',
                'git_sha' => config('demo.git_sha'),
            ], 503);
        }

        return response()->json([
            'status' => 'ok',
            'database' => 'ok',
            'git_sha' => config('demo.git_sha'),
        ]);
    }
}
