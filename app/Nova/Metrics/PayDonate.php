<?php

namespace App\Nova\Metrics;

use App\Models\WechatPayOrder;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;

class PayDonate extends Partition
{
    /**
     * Calculate the value of the metric.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        return $this->sum($request, WechatPayOrder::class, 'total_fee', 'body');
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'pay-donate';
    }
}
