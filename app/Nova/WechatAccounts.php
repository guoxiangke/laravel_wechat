<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Textarea;

class WechatAccounts extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\WechatAccount';

    // protected $fillable = ['name','to_user_name','app_id','secret','token','aes_key','is_certified','menu','resources','description'];

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
        $className = 'wechat';

        return [
            ID::make()->sortable(),
            Text::make('Name')
                ->creationRules('required', 'string', 'min:2'),
            Textarea::make('description'),
            Text::make('secret')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:32')
                ->updateRules('nullable', 'string', 'min:32'),
            Text::make('app_id')
                ->creationRules('required', 'string', 'min:6'),
            Text::make('token')
                ->creationRules('required', 'string', 'min:6'),
            Text::make('to_user_name')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:6'),
            Boolean::make('Is_Certified'),
            Image::make('image_qr')
                ->onlyOnForms()
                ->path(static::get_path('image_qr', $className))
                ->help('可为空'),
            Code::make('Menu')->json(),
            Code::make('Resources')->json(),
        ];
    }

    private function schema(): array
    {
        return [
             'type'       => 'object',
             'required'   => ['lymeta', 'lylts', 'comment', 'subscribe'],
             'properties' => [
                'lymeta' => [
                    'type'=> 'integer',
                ],
                'lylts' => [
                    'type'=> 'integer',
                ],
                'comment' => [
                    'type'=> 'integer',
                ],
                'subscribe' => [
                    'type'=> 'integer',
                ],
             ],

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
