<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

/**
 * Health Check Controller
 *
 * Provides endpoint for external monitoring services (UptimeRobot, etc.)
 * to check the health of the application.
 */
class HealthController extends Controller
{
    /**
     * Comprehensive health check endpoint.
     *
     * Returns status of all services:
     * - Database connection
     * - Redis connection
     * - Queue (jobs pending)
     * - Storage (write permissions)
     * - Mail configuration
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $checks = [];
        $allHealthy = true;

        // 1. Database Check
        try {
            DB::connection()->getPdo();
            $checks['database'] = [
                'status' => 'ok',
                'message' => 'Connected to ' . config('database.default'),
            ];
        } catch (\Exception $e) {
            $checks['database'] = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
            $allHealthy = false;
        }

        // 2. Redis Check
        try {
            $pong = Redis::ping();
            $checks['redis'] = [
                'status' => 'ok',
                'message' => 'Redis is responding',
            ];
        } catch (\Exception $e) {
            $checks['redis'] = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
            $allHealthy = false;
        }

        // 3. Queue Check (check pending jobs count)
        try {
            $queueSize = Queue::size('default');
            $checks['queue'] = [
                'status' => 'ok',
                'pending_jobs' => $queueSize,
                'message' => $queueSize > 100 ? 'Warning: High job count' : 'Queue is healthy',
            ];
        } catch (\Exception $e) {
            $checks['queue'] = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
            $allHealthy = false;
        }

        // 4. Storage Check - Logs
        try {
            $testFile = 'health-check-' . time() . '.txt';
            $path = storage_path('logs/' . $testFile);
            file_put_contents($path, 'test');
            unlink($path);
            $checks['storage_logs'] = [
                'status' => 'ok',
                'message' => 'storage/logs is writable',
            ];
        } catch (\Exception $e) {
            $checks['storage_logs'] = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
            $allHealthy = false;
        }

        // 5. Storage Check - Private
        try {
            Storage::disk('local')->put('health-check.txt', 'test');
            Storage::disk('local')->delete('health-check.txt');
            $checks['storage_private'] = [
                'status' => 'ok',
                'message' => 'storage/app/private is writable',
            ];
        } catch (\Exception $e) {
            $checks['storage_private'] = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
            $allHealthy = false;
        }

        // 6. Storage Check - Public
        try {
            Storage::disk('public')->put('health-check.txt', 'test');
            Storage::disk('public')->delete('health-check.txt');
            $checks['storage_public'] = [
                'status' => 'ok',
                'message' => 'storage/app/public is writable',
            ];
        } catch (\Exception $e) {
            $checks['storage_public'] = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
            $allHealthy = false;
        }

        // 7. Mail Configuration Check
        try {
            $mailConfig = config('mail.mailers.' . config('mail.default'));
            $checks['mail'] = [
                'status' => 'ok',
                'driver' => config('mail.default'),
                'host' => $mailConfig['host'] ?? 'N/A',
                'message' => 'Mail configuration loaded',
            ];
        } catch (\Exception $e) {
            $checks['mail'] = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
            $allHealthy = false;
        }

        // 8. Cache Check
        try {
            Cache::put('health-check', 'test', 10);
            $value = Cache::get('health-check');
            Cache::forget('health-check');
            $checks['cache'] = [
                'status' => $value === 'test' ? 'ok' : 'error',
                'driver' => config('cache.default'),
                'message' => 'Cache is working',
            ];
        } catch (\Exception $e) {
            $checks['cache'] = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
            $allHealthy = false;
        }

        return response()->json([
            'status' => $allHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'app' => config('app.name'),
            'environment' => config('app.env'),
            'checks' => $checks,
        ], $allHealthy ? 200 : 503);
    }

    /**
     * Simple ping endpoint for basic uptime monitoring.
     * Returns minimal response for fast health checks.
     *
     * @return JsonResponse
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
