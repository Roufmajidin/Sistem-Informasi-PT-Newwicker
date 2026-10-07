<?php

namespace App\Http\Controllers;

use App\Services\MikrotikService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class NetworkController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | NETWORK MONITOR PAGE
    |--------------------------------------------------------------------------
    */

    public function index(): View
    {
        return view(
            'pages.network.index'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REALTIME TRAFFIC
    |--------------------------------------------------------------------------
    */

    public function realtime(
        MikrotikService $mikrotik
    ): JsonResponse {

        $interfaces = [

            'ether1',

            'bridge1',

            'bridgeproduksi',

        ];

        try {

            $data =
                $mikrotik
                    ->monitorInterfaces(
                        $interfaces
                    );

            return response()->json([

                'success' => true,

                'interfaces' => $data,

                'updated_at' => now()
                    ->format('H:i:s'),

            ]);

        } catch (
            \Throwable $e
        ) {

            return response()->json([

                'success' => false,

                'interfaces' => [],

                'message' =>
                    $e->getMessage(),

            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ACTIVE WIFI / NETWORK USERS
    |--------------------------------------------------------------------------
    */

    public function wifiUsers(
        MikrotikService $mikrotik
    ): JsonResponse {

        try {

            $users = $mikrotik->getActiveWifiUsers();

            return response()->json([
                'success' => true,
                'count' => count($users),
                'users' => $users,
                'updated_at' => now()->format('H:i:s'),
            ]);

        } catch (\Throwable $e) {

            Log::error('NETWORK WIFI USERS ERROR', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'count' => 0,
                'users' => [],
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DEBUG WIFI
    |--------------------------------------------------------------------------
    */

    public function debugWifi(
        MikrotikService $mikrotik
    ): JsonResponse {

        try {

            return response()->json(
                $mikrotik
                    ->debugWifiUsers()
            );

        } catch (
            \Throwable $e
        ) {

            return response()->json([

                'success' => false,

                'message' =>
                    $e->getMessage(),

            ], 500);
        }
    }
}