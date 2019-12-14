<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GampController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function actions($category = 'lyapi_audio', $clientId = 'all', $byMonth = null)
    {
        $statistics = DB::table('gamps')
            ->select('action as type', DB::raw('count(*) as total'))
            // ->where('category', 'lyapi_audio')
            ->groupBy('type')
            ->orderBy('total', 'desc');
        $statistics->where('category', $category);
        if ($clientId != 'all') {
            $statistics->where('client_id', $clientId);
        }
        if (! is_null($byMonth)) {
            // 本月 he 上X个月
            $start = Carbon::now()->subMonths($byMonth)->startOfMonth();
            $end = Carbon::now()->subMonths($byMonth)->endOfMonth();
            $statistics->where('created_at', '>=', $start);
            $statistics->where('created_at', '<=', $end);
        }

        return view('statistics.table', ['statistics'=>$statistics->get()]);
    }

    public function category($clientId = 'all', $byMonth = null)
    {
        $statistics = DB::table('gamps')
            ->select('category as type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->orderBy('total', 'desc');
        if ($clientId != 'all') {
            $statistics->where('client_id', $clientId);
        }
        if (! is_null($byMonth)) {
            // 本月 he 上X个月
            $start = Carbon::now()->subMonths($byMonth)->startOfMonth();
            $end = Carbon::now()->subMonths($byMonth)->endOfMonth();
            $statistics->where('created_at', '>=', $start);
            $statistics->where('created_at', '<=', $end);
        }

        return view('statistics.table', ['statistics'=>$statistics->get()]);
    }
}
