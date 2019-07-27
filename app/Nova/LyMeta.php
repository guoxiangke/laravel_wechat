<?php

namespace App\Nova;

use App\Models\LyMeta as LyMetaModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

class LyMeta extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\LyMeta';

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
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('微信编号', 'index')->help('节目编号6XX')->sortable(),
            Text::make('名称', 'name')->sortable(),
            Text::make('主持人', 'author')->onlyOnForms()->help('、分割'),
            Text::make('简介', 'description')->onlyOnForms()->help('简介'),
            Text::make('封面', 'image')->onlyOnForms()->help('节目封面'),
            Text::make('代码', 'code')->help('节目代码ee')->sortable(),
            Text::make('良友编号', 'ly_index')->help('良友微信编号10X')->sortable(),
            Select::make('分类', 'category')
                ->sortable()
                ->options(LyMetaModel::CATEGORY)
                ->displayUsingLabels(),
            Text::make('时间', 'day')->help('周几播出,可为空'),
            Date::make('停播时间', 'stop_play_at')->sortable(),
            Date::make('Updated At')->onlyOnIndex()->sortable(),
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
        return [];
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

    /**
     * Default ordering for index query.
     *
     * @var array
     */
    public static $sort = [
        'index' => 'desc',
    ];
}
