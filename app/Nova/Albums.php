<?php

namespace App\Nova;

use Digitalazgroup\PlainText\PlainText;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
// use Laravel\Nova\Fields\Trix;
use Vexilo\NovaFroalaEditor\NovaFroalaEditor;

class Albums extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Album';

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
        'title',
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
        $className = 'albums';

        return [
            ID::make()->sortable(),
            Text::make('专辑名字', 'title')
                ->onlyOnForms()
                ->rules('required'),
            Text::make('专辑名字', function () {
                if ($this->lymeta_id) {
                    $url = '/nova/resources/ly-audios?ly-audios_page=1&ly-audios_search='.$this->title;
                } else {
                    $url = '/nova/resources/posts?posts_page=1&posts_search='.$this->title;
                }

                return '<span class="whitespace-no-wrap text-left"> <a target="_blank" href="'.$url.'">'.$this->title.'</a></span>';
            })->asHtml()->onlyOnIndex(),
            Textarea::make('专辑摘要', 'excerpt')
                ->onlyOnForms()
                ->rules('required'),
            NovaFroalaEditor::make('专辑内容', 'body')
                ->onlyOnForms()
                ->rules('required'),
            Image::make('image')
                 ->path(static::get_path('images', $className))
                 ->creationRules('required')
                 ->updateRules('', function ($attribute, $value, $fail) use ($request) {
                     $model = $this->resource->find($request->route('resourceId'));
                     if (empty($value) && empty($model->$attribute)) {
                         $fail(__(':Attribute is required.', ['attribute' => __($attribute)]));
                     }
                 }),
            Number::make('专辑原价', 'ori_price')
                ->help('单位:元')
                ->min(0)->step(0.01)
                ->hideFromIndex()
                ->rules('required'),
            Number::make('专辑价格', 'price')
                ->help('单位:元')
                ->min(0)->step(0.01)
                ->hideFromIndex()
                ->rules('required'),
            Number::make('内容总量', 'count')
                ->help('0为每天更新.')
                ->rules('required'),
            Boolean::make('只有音频', 'audio_only')->sortable()->help('可为空'),
            Boolean::make('已发布', 'active')->sortable()->help('可为空'),
            DateTime::make('促销时间', 'expire_at')->sortable(),
            BelongsTo::make('专辑分类', 'category', Categories::class)
                ->help('如果选择根分类,则创建同名其子分类.')
                ->rules('required'),

            Text::make('来源链接', 'url')->help('可为空'),
            Text::make('lymeta_id')->help('可为空'),
            Text::make('rrule')->help('可为空'),

            //公众号uid
            BelongsTo::make('公号', 'author', Users::class)->nullable()->onlyOnForms(),
            PlainText::make('author', function () {
                if ($this->author) {
                    return $this->author->ghprofile->nickname;
                }

                return $this->author_id ?: '';
            }),
            PlainText::make('User_id', function () {
                if ($this->user) {
                    return $this->user->profile->nickname;
                }

                return $this->user_id ?: '';
            }),
            PlainText::make('modified_id', function () {
                if ($this->modifier) {
                    return $this->modifier->profile->nickname;
                }

                return $this->modified_id ?: '';
            }),

            // BelongsTo::make('来源公号id','author',  Users::class )
            //     ->nullable()
            //     ->help('可为空'),//公众号uid
            // BelongsTo::make('创建者','user', Users::class)->onlyOnIndex(),
            // BelongsTo::make('Modifier','modifier', Users::class)->onlyOnIndex(),
            Text::make('专辑名字', function () {
                $url = '/nova/resources/posts?posts_page=1&posts_search='.$this->title;

                return '<span class="whitespace-no-wrap text-left"> <a target="_blank" href="'.$url.'">'.$this->title.'</a></span>';
            })->asHtml()->onlyOnIndex(),

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
        return [
            new Actions\AlbumToogleActive(),
            new Actions\AlbumToogleAudioOnly(),
        ];
    }
}
