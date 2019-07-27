<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GampController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function LyAction($byMonth=NULL)
    {
        //'client_id','category','action','label'
        $statistics = DB::table('gamps')
            ->select('action as type', DB::raw('count(*) as total'))
            ->where('client_id', 'gh_fb86cb40685c')
            ->where('category', 'lyapi_audio')
            ->groupBy('type')
            ->orderBy('total','desc');

        if(!is_null($byMonth)){
            // 本月 he 上X个月
            $start = Carbon::now()->subMonths($byMonth)->startOfMonth();
            $end = Carbon::now()->subMonths($byMonth)->endOfMonth();
            $statistics->where('created_at', '>=', $start);
            $statistics->where('created_at', '<=', $end);
        }
        return view('statistics.table', ['statistics'=>$statistics->get()]);
    }

    public function LyCategory($byMonth=NULL)
    {
        $statistics = DB::table('gamps')->select('category as type', DB::raw('count(*) as total'))->where('client_id','gh_fb86cb40685c')->groupBy('type')->orderBy('total','desc');
        if(!is_null($byMonth)){
            // 本月 he 上X个月
            $start = Carbon::now()->subMonths($byMonth)->startOfMonth();
            $end = Carbon::now()->subMonths($byMonth)->endOfMonth();
            $statistics->where('created_at', '>=', $start);
            $statistics->where('created_at', '<=', $end);
        }
        return view('statistics.table', ['statistics'=>$statistics->get()]);
    }

    public function action($byMonth=NULL)
    {
        $statistics = DB::table('gamps')->select('action as type', DB::raw('count(*) as total'))->where('category', 'lyapi_audio')->groupBy('type')->orderBy('total','desc');
        if(!is_null($byMonth)){
            // 本月 he 上X个月
            $start = Carbon::now()->subMonths($byMonth)->startOfMonth();
            $end = Carbon::now()->subMonths($byMonth)->endOfMonth();
            $statistics->where('created_at', '>=', $start);
            $statistics->where('created_at', '<=', $end);
        }
        return view('statistics.table', ['statistics'=>$statistics->get()]);
    }

    public function category($byMonth=NULL)
    {
        $statistics = DB::table('gamps')->select('category as type', DB::raw('count(*) as total'))->groupBy('type')->orderBy('total','desc');
        if(!is_null($byMonth)){
            // 本月 he 上X个月
            $start = Carbon::now()->subMonths($byMonth)->startOfMonth();
            $end = Carbon::now()->subMonths($byMonth)->endOfMonth();
            $statistics->where('created_at', '>=', $start);
            $statistics->where('created_at', '<=', $end);
        }
        return view('statistics.table', ['statistics'=>$statistics->get()]);
    }

}
