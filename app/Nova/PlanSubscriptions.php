<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Date;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;


class PlanSubscriptions extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\PlanSubscription';

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
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('Avatar', function () {
                if($this->profile){
                    return '<img style="max-width:45px;" src="'.$this->profile->headimgurl.'"></img>';
                }
            })->asHtml(),
            Text::make('User_Id',function(){
                if($this->profile){
                    return $this->profile->nickname;
                }
                return $this->user_id;
                })
                ->rules('required', 'max:255'),
            Text::make('Plan_id')->onlyOnIndex(),
            Text::make('Slug')->onlyOnIndex(),
            Date::make('Starts At')->onlyOnIndex()->sortable(),
            Date::make('Ends At')->onlyOnIndex()->sortable(),
            Date::make('Updated At')->onlyOnIndex()->sortable(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
