<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\Wechat;

class GampController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function LyAction($byMonth = null)
    {
        //'client_id','category','action','label'
        $statistics = DB::table('gamps')
            ->select('action as type', DB::raw('count(*) as total'))
            ->where('client_id', Wechat::LY_MP_ID)
            ->where('category', 'lyapi_audio')
            ->groupBy('type')
            ->orderBy('total', 'desc');

        if (!is_null($byMonth)) {
            // 本月 he 上X个月
            $start = Carbon::now()->subMonths($byMonth)->startOfMonth();
            $end = Carbon::now()->subMonths($byMonth)->endOfMonth();
            $statistics->where('created_at', '>=', $start);
            $statistics->where('created_at', '<=', $end);
        }

        return view('statistics.table', ['statistics'=>$statistics->get()]);
    }

    public function LyCategory($byMonth = null)
    {
        $statistics = DB::table('gamps')->select('category as type', DB::raw('count(*) as total'))->where('client_id', Wechat::LY_MP_ID)->groupBy('type')->orderBy('total', 'desc');
        if (! is_null($byMonth)) {
            // 本月 he 上X个月
            $start = Carbon::now()->subMonths($byMonth)->startOfMonth();
            $end = Carbon::now()->subMonths($byMonth)->endOfMonth();
            $statistics->where('created_at', '>=', $start);
            $statistics->where('created_at', '<=', $end);
        }

        return view('statistics.table', ['statistics'=>$statistics->get()]);
    }

    public function action($byMonth = null)
    {
        $statistics = DB::table('gamps')->select('action as type', DB::raw('count(*) as total'))->where('category', 'lyapi_audio')->groupBy('type')->orderBy('total', 'desc');
        if (!is_null($byMonth)) {
            // 本月 he 上X个月
            $start = Carbon::now()->subMonths($byMonth)->startOfMonth();
            $end = Carbon::now()->subMonths($byMonth)->endOfMonth();
            $statistics->where('created_at', '>=', $start);
            $statistics->where('created_at', '<=', $end);
        }

        return view('statistics.table', ['statistics'=>$statistics->get()]);
    }

    public function category($byMonth = null)
    {
        $statistics = DB::table('gamps')->select('category as type', DB::raw('count(*) as total'))->groupBy('type')->orderBy('total', 'desc');
        if (!is_null($byMonth)) {
            // 本月 he 上X个月
            $start = Carbon::now()->subMonths($byMonth)->startOfMonth();
            $end = Carbon::now()->subMonths($byMonth)->endOfMonth();
            $statistics->where('created_at', '>=', $start);
            $statistics->where('created_at', '<=', $end);
        }

        return view('statistics.table', ['statistics'=>$statistics->get()]);
    }
}
