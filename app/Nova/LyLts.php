<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use App\Models\LyLts as LtsModel;

class LyLts extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\LyLts';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
        'description',
    ];

    /**
     * Default ordering for index query.
     *
     * @var array
     */
    public static $sort = [
        'index' => 'desc',
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
            Number::make('编号', 'index')->help('节目编号#101-999,不带#')
                ->min(101)->max(999)->step(1)->sortable(),
            Text::make('名称', 'name'),
            Text::make('简介', 'description')->onlyOnForms(),
            Text::make('封面', 'image')->onlyOnForms()->help('节目封面'),
            Text::make('老师', 'author')->help('授课老师、分割'),
            Text::make('前缀', 'code')->help('节目命名前缀vfe0'),
            Number::make('总数', 'count')->help('节目数量')->sortable(),
            Select::make('category')
                ->options(LtsModel::CATEGORY)
                ->displayUsingLabels(),
            Number::make('weight')->help('#XXX0菜单排序权重')
                ->min(0),
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
}
