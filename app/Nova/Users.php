<?php

namespace App\Nova;

use Digitalazgroup\PlainText\PlainText;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Silvanite\NovaToolPermissions\Role;

class Users extends Resource
{
    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array
     */
    public static $with = ['profile'];
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\User';

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
        'name',
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'profile'     => ['nickname'],
        'recommender' => ['nickname'],
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
            // Gravatar::make(),
            Text::make('Avatar', function () {
                if ($this->profile) {
                    return '<img style="max-width:45px;" src="'.$this->profile->headimgurl.'"></img>';
                }
                if ($this->ghprofile) {
                    return '<img style="max-width:45px;" src="'.$this->ghprofile->head_img_url.'"></img>';
                }
            })->asHtml()->onlyOnIndex(),
            Text::make('Name', function () {
                if ($this->profile) {
                    return $this->profile->nickname;
                }
                if ($this->ghprofile) {
                    return $this->ghprofile->nickname;
                }

                return $this->name ?: '';
            })
                ->rules('required', 'max:255'),
            Text::make('Name')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:6')
                ->updateRules('nullable', 'string', 'min:6'),

            BelongsToMany::make('Roles', 'roles', Role::class),
            PlainText::make('Recommender', function () {
                if ($this->recommender) {
                    return $profile = $this->recommender->nickname;
                }

                return $this->user_id;
            }),

            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}')
                ->hideFromIndex(),

            Password::make('Password')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:6')
                ->updateRules('nullable', 'string', 'min:6'),
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
        return [new Metrics\NewUsers(), new Metrics\UsersPerDay(), new Metrics\PerDayWechatMessages()];
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
