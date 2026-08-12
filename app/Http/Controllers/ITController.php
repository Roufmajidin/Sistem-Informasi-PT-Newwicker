<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;

class ITController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DASHBOARD DATA
    |--------------------------------------------------------------------------
    */
    private function getDashboardData()
    {
        /*
        |--------------------------------------------------------------------------
        | WEB MONITORING
        |--------------------------------------------------------------------------
        */

        // TOTAL ACTIVITY WEB
        $totalActivity = ActivityLog::where(
            'url',
            'not like',
            '/api/%'
        )->count();


        // ACTIVE USERS WEB TODAY
        $activeUsers = ActivityLog::where(
            'url',
            'not like',
            '/api/%'
        )
            ->whereDate('created_at', today())
            ->distinct('user_id')
            ->count('user_id');


        // ACTIVITY WEB TODAY
        $todayActivity = ActivityLog::where(
            'url',
            'not like',
            '/api/%'
        )
            ->whereDate('created_at', today())
            ->count();


        // MOST ACTIVE USER WEB - 7 HARI
        $mostActiveUser = ActivityLog::select(
            'user_id',
            DB::raw('COUNT(*) as total_activity')
        )
            ->with('user')
            ->where(
                'url',
                'not like',
                '/api/%'
            )
            ->where(
                'created_at',
                '>=',
                now()->subDays(6)->startOfDay()
            )
            ->groupBy('user_id')
            ->orderByDesc('total_activity')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | WEB ACTIVITY PER HARI - 7 HARI
        |--------------------------------------------------------------------------
        */

        $activityRaw = ActivityLog::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total')
        )
            ->where(
                'url',
                'not like',
                '/api/%'
            )
            ->where(
                'created_at',
                '>=',
                now()->subDays(6)->startOfDay()
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();


        $activityByDay = collect();

        for ($i = 6; $i >= 0; $i--) {

            $date = now()->subDays($i);

            $row = $activityRaw->first(function ($item) use ($date) {

                return $item->date === $date->format('Y-m-d');

            });

            $activityByDay->push([
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('d M'),
                'total' => $row
                    ? (int) $row->total
                    : 0,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | WEB ACTIVITY BY MODULE
        |--------------------------------------------------------------------------
        */

        $moduleMap = [

            'bom' => 'BOM',
            'bom-produksi' => 'BOM Produksi',
            'spk' => 'SPK',
            'warehouse' => 'Warehouse',
            'inventory' => 'Inventory',
            'produksi' => 'Production',
            'qc' => 'Quality Control',
            'marketing' => 'Marketing',
            'pengajuan' => 'Pengajuan',
            'laporan' => 'Laporan',
            'supplier' => 'Purchasing',
            'payment-request' => 'Payment Request',
            'karyawan' => 'Karyawan',
            'pameran' => 'Pameran',
            'cad' => 'CAD',
            'setting' => 'Setting',

        ];


        $moduleRaw = ActivityLog::select(
            'url',
            DB::raw('COUNT(*) as total')
        )
            ->where(
                'url',
                'not like',
                '/api/%'
            )
            ->where(
                'created_at',
                '>=',
                now()->subDays(29)->startOfDay()
            )
            ->groupBy('url')
            ->get();


        $moduleTotals = [];

        foreach ($moduleRaw as $row) {

            $path = trim($row->url, '/');

            if ($path === '') {

                $moduleName = 'Dashboard';

            } else {

                $prefix = explode('/', $path)[0];

                $moduleName = $moduleMap[$prefix]
                    ?? ucfirst(
                        str_replace('-', ' ', $prefix)
                    );
            }

            if (!isset($moduleTotals[$moduleName])) {

                $moduleTotals[$moduleName] = 0;

            }

            $moduleTotals[$moduleName] += (int) $row->total;
        }


        arsort($moduleTotals);


        $activityByModule = collect($moduleTotals)
            ->take(8)
            ->map(function ($total, $name) {

                return [
                    'name' => $name,
                    'total' => $total,
                ];

            })
            ->values();


        /*
        |--------------------------------------------------------------------------
        | WEB ACTIVITY TABLE
        |--------------------------------------------------------------------------
        */

        $activities = ActivityLog::with('user')
            ->where(
                'url',
                'not like',
                '/api/%'
            )
            ->latest('created_at')
            ->limit(500)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | APPS MONITORING
        |--------------------------------------------------------------------------
        */

        // TOTAL API ACTIVITY
        $totalApiActivity = ActivityLog::where(
            'url',
            'like',
            '/api/%'
        )->count();


        // ACTIVE API USERS TODAY
        $activeApiUsers = ActivityLog::where(
            'url',
            'like',
            '/api/%'
        )
            ->whereDate('created_at', today())
            ->distinct('user_id')
            ->count('user_id');


        // API ACTIVITY TODAY
        $todayApiActivity = ActivityLog::where(
            'url',
            'like',
            '/api/%'
        )
            ->whereDate('created_at', today())
            ->count();


        // MOST USED API ENDPOINT
        $mostUsedApi = ActivityLog::select(
            'url',
            DB::raw('COUNT(*) as total')
        )
            ->where(
                'url',
                'like',
                '/api/%'
            )
            ->groupBy('url')
            ->orderByDesc('total')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | API ACTIVITY PER HARI - 7 HARI
        |--------------------------------------------------------------------------
        */

        $apiActivityRaw = ActivityLog::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total')
        )
            ->where(
                'url',
                'like',
                '/api/%'
            )
            ->where(
                'created_at',
                '>=',
                now()->subDays(6)->startOfDay()
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();


        $apiActivityByDay = collect();

        for ($i = 6; $i >= 0; $i--) {

            $date = now()->subDays($i);

            $row = $apiActivityRaw->first(function ($item) use ($date) {

                return $item->date === $date->format('Y-m-d');

            });

            $apiActivityByDay->push([

                'date' => $date->format('Y-m-d'),

                'label' => $date->format('d M'),

                'total' => $row
                    ? (int) $row->total
                    : 0,

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | API ENDPOINT USAGE
        |--------------------------------------------------------------------------
        */

        $apiEndpointUsage = ActivityLog::select(
            'url',
            DB::raw('COUNT(*) as total')
        )
            ->where(
                'url',
                'like',
                '/api/%'
            )
            ->groupBy('url')
            ->orderByDesc('total')
            ->limit(8)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | API ACTIVITY TABLE
        |--------------------------------------------------------------------------
        */

        $appActivities = ActivityLog::with('user')
            ->where(
                'url',
                'like',
                '/api/%'
            )
            ->latest('created_at')
            ->limit(500)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | RETURN
        |--------------------------------------------------------------------------
        */

        return compact(

            // WEB

            'totalActivity',
            'activeUsers',
            'todayActivity',
            'mostActiveUser',
            'activityByDay',
            'activityByModule',
            'activities',

            // API

            'totalApiActivity',
            'activeApiUsers',
            'todayApiActivity',
            'mostUsedApi',
            'apiActivityByDay',
            'apiEndpointUsage',
            'appActivities'

        );
    }


    /*
    |--------------------------------------------------------------------------
    | DASHBOARD PAGE
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $data = $this->getDashboardData();

        return view(
            'pages.it.index',
            $data
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REALTIME DATA
    |--------------------------------------------------------------------------
    */

    public function data(Request $request)
    {
        $data = $this->getDashboardData();

        /*
        |--------------------------------------------------------------------------
        | Convert model menjadi array yang aman dikirim JSON
        |--------------------------------------------------------------------------
        */

        return response()->json([

            /*
            |--------------------------------------------------------------------------
            | WEB
            |--------------------------------------------------------------------------
            */

            'totalActivity' => $data['totalActivity'],

            'activeUsers' => $data['activeUsers'],

            'todayActivity' => $data['todayActivity'],

            'mostActiveUser' => $data['mostActiveUser']
                ? [
                    'user_id' => $data['mostActiveUser']->user_id,
                    'name' => $data['mostActiveUser']->user->name ?? '-',
                    'total_activity' => (int) $data['mostActiveUser']->total_activity,
                ]
                : null,

            'activityByDay' => $data['activityByDay'],

            'activityByModule' => $data['activityByModule'],

            'activities' => $data['activities']->map(function ($activity) {

                return [
                    'id' => $activity->id,
                    'user_id' => $activity->user_id,
                    'user_name' => $activity->user->name ?? '-',
                    'route_name' => $activity->route_name ?? '-',
                    'url' => $activity->url ?? '-',
                    'method' => $activity->method ?? '-',
                    'ip_address' => $activity->ip_address ?? '-',
                    'user_agent' => $activity->user_agent ?? '-',
                    'created_at' => $activity->created_at?->toISOString(),
                ];

            })->values(),

            /*
            |--------------------------------------------------------------------------
            | API
            |--------------------------------------------------------------------------
            */

            'totalApiActivity' => $data['totalApiActivity'],

            'activeApiUsers' => $data['activeApiUsers'],

            'todayApiActivity' => $data['todayApiActivity'],

            'mostUsedApi' => $data['mostUsedApi']
                ? [
                    'url' => $data['mostUsedApi']->url,
                    'total' => (int) $data['mostUsedApi']->total,
                ]
                : null,

            'apiActivityByDay' => $data['apiActivityByDay'],

            'apiEndpointUsage' => $data['apiEndpointUsage']
                ->map(function ($item) {

                    return [
                        'url' => $item->url,
                        'total' => (int) $item->total,
                    ];

                })->values(),

            'appActivities' => $data['appActivities']
                ->map(function ($activity) {

                    return [
                        'id' => $activity->id,
                        'user_id' => $activity->user_id,
                        'user_name' => $activity->user->name ?? '-',
                        'url' => $activity->url ?? '-',
                        'method' => $activity->method ?? '-',
                        'ip_address' => $activity->ip_address ?? '-',
                        'user_agent' => $activity->user_agent ?? '-',
                        'created_at' => $activity->created_at?->toISOString(),
                    ];

                })->values(),

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | SSE
    |--------------------------------------------------------------------------
    */

    public function stream(Request $request)
    {
        return response()->stream(function () {

            $lastId = ActivityLog::max('id') ?? 0;

            while (true) {

                $activities = ActivityLog::with([
                    'user:id,name'
                ])
                    ->where('id', '>', $lastId)
                    ->orderBy('id')
                    ->get();

                foreach ($activities as $activity) {
                    Log::info('SSE ACTIVITY', [
                        'activity_id' => $activity->id,
                        'user_id' => $activity->user_id,
                        'user_loaded' => $activity->user ? true : false,
                        'user_name' => $activity->user?->name,
                    ]);
                    echo "data: " . json_encode([
                        'id' => $activity->id,

                        // Kirim langsung nama user
                        'user_name' => $activity->user?->name ?? '-',

                        // Sekaligus kirim object user
                        'user' => $activity->user ? [
                            'id' => $activity->user->id,
                            'name' => $activity->user->name,
                        ] : null,

                        'route_name' => $activity->route_name ?? '-',
                        'url' => $activity->url ?? '-',
                        'method' => $activity->method ?? '-',
                        'ip_address' => $activity->ip_address ?? '-',

                        'created_at' => $activity->created_at?->toISOString(),
                    ]) . "\n\n";

                    $lastId = $activity->id;
                }

                echo ": heartbeat\n\n";

                if (ob_get_level() > 0) {
                    ob_flush();
                }

                flush();

                sleep(2);
            }

        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}