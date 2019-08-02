<?php

namespace App\Nova;

use App\Models\Album;
use App\Models\LyLts;
use App\Models\LyMeta;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class WechatPayOrders extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\WechatPayOrder';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'user.profile' => ['nickname'],
        'album'        => ['title'],
    ];

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array
     */
    public static $with = ['profile', 'lymeta', 'lylts', 'album'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('Avatar', function () {
                if ($this->profile) {
                    return '<img style="max-width:45px;" src="'.$this->profile->headimgurl.'"></img>';
                }
            })->asHtml(),
            Text::make('User_Id', function () {
                if ($this->profile) {
                    return $this->profile->nickname;
                }

                return $this->user_id;
            })
                ->onlyOnIndex(),
            Number::make('金额', 'total_fee')
                ->onlyOnIndex()
                ->sortable(),
            Boolean::make('Success')
                ->sortable(),
            Text::make('Body')
                ->rules('required', 'max:255'),
            Text::make('Target_type', function () {
                if ($this->target_type) {
                    return $this->target_type::ModelName;
                }
            }),
            Text::make('Target_Id', function () {
                if ($this->target_type == LyMeta::class) {
                    return $this->lymeta->name;
                }
                if ($this->target_type == Album::class) {
                    return $this->album->title;
                }
                if ($this->target_type == LyLts::class) {
                    return $this->lylts->name;
                }

                return $this->target_id;
            }),
            Text::make('out_trade_no')->onlyOnIndex(),
            Text::make('trade_type')->onlyOnIndex(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [new Metrics\PayTends(), new Metrics\PayValue(), new Metrics\PayDonate()];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
