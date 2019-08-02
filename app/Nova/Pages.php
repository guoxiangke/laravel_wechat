<?php

namespace App\Nova;

use App\Models\Page;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Vexilo\NovaFroalaEditor\NovaFroalaEditor;

class Pages extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Page';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'meta_keywords', 'title',
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
        $className = 'pages';

        return [
            ID::make()->sortable(),
            Text::make('User_id')->onlyOnIndex(),
            Text::make('Title')
                ->rules('required', 'max:255'),
            Text::make('Slug')->rules('required', 'max:255'),
            Image::make('image')
                ->path(static::get_path('images', $className))
                ->help('可为空'),
            Textarea::make('excerpt')
                ->rules('required', 'max:255'),
            NovaFroalaEditor::make('body')
                ->rules('required', 'max:255'),
            Text::make('meta_keywords')
                ->rules('required', 'max:255'),
            Text::make('meta_description')
                ->rules('required', 'max:255'),
            Boolean::make('Status')
                ->trueValue(Page::STATUS_ACTIVE)
                ->falseValue(Page::STATUS_INACTIVE),
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
