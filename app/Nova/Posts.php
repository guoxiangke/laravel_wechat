<?php

namespace App\Nova;

use App\Models\Album;
use App\Models\LyLts;
use App\Models\LyMeta;
use Spatie\TagsField\Tags;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Text;
// use Laravel\Nova\Fields\Trix;
// use Manogi\Tiptap\Tiptap;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\BelongsTo;
use Digitalazgroup\PlainText\PlainText;
use Vexilo\NovaFroalaEditor\NovaFroalaEditor;

class Posts extends Resource
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
    public static $with = ['user', 'author', 'modifier'];

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Post';

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
        'title', 'youtube_vid',
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'album' => ['title'],
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        $className = 'posts';

        return [
            ID::make()->sortable(),
            Text::make('标题', 'title', function () {
                $url = config('app.url').'/posts/'.$this->slug;

                return '<span class="whitespace-no-wrap text-left"> <a target="_blank" href="'.$url.'?admin=1">'.$this->title.'</a></span>';
            })->asHtml()->onlyOnIndex(),

            Text::make('标题', 'title')->hideFromIndex(),
            Textarea::make('摘要', 'excerpt')->help('图文摘要部分,可为空'),
            NovaFroalaEditor::make('正文', 'body')
                ->options([
                    'heightMin' => 500,
                    'toolbarButtons' => ['fullscreen', 'bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript', '|', 'fontSize', 'color', 'inlineStyle', 'paragraphStyle', 'lineHeight', '|', 'paragraphFormat', 'align', 'formatOL', 'formatUL', 'outdent', 'indent', 'quote', '-', 'insertLink', 'insertImage', 'embedly', 'insertTable', '|', 'emoticons', 'fontAwesome', 'specialCharacters', 'insertHR', 'selectAll', 'clearFormatting', '|', 'print', 'getPDF', 'spellChecker', 'help', 'html', '|', 'undo', 'redo'],
                    'imageUploadURL' => '/editor/image/upload',
                    'imageManagerDeleteMethod'=> 'POST',
                    'imageManagerDeleteURL' => '/editor/image/delete',
                    'imageManagerLoadMethod'=> 'POST',
                    'imageManagerLoadURL' => '/editor/image/load',
                ])
                ->hideFromIndex()
                ->rules('required'),

            BelongsTo::make('分类', 'category', Categories::class)
                ->nullable(),
            Number::make('专辑顺序', 'order')
                ->min(1)->step(1)
                ->sortable()
                ->help('从小到大,默认是1'),

            Select::make('Target_type')->options([
                Album::class => Album::ModelName,
                LyMeta::class => LyMeta::ModelName,
                LyLts::class => LyLts::ModelName,
            ])->hideFromIndex(),
            Number::make('Target_Id')->min(1)->step(1)->hideFromIndex(),

            Select::make('状态', 'status')->options([
                'PUBLISHED' => '发布',
                'DRAFT' => '草稿',
                'PENDING' => '审核中',
            ]),
            Tags::make('标签', 'Tags')
                ->help('输入回车添加,可多个'),
            Image::make('image')
                ->path(static::get_path('images', $className))
                ->help('可为空'),
            Text::make('image_url')->onlyOnForms()->help('URL地址,可为空'),

            // Text::make('Target_type', function () {
            //     //todo error when new
            //     if($this->target_type)
            //         return $this->target_type::ModelName;
            // })->onlyOnIndex(),
            Text::make('Target_Id', function () {
                if ($this->album && $this->target_type == Album::class) {
                    return $this->album->title;
                }
                if ($this->lymeta && in_array($this->target_type, [LyMeta::class, LyLts::class])) {
                    return $this->lymeta->name;
                }

                return $this->target_id;
            })->onlyOnIndex(),

            File::make('mp3')
                ->path(static::get_path('mp3', $className))
                ->help('可为空'),
            Text::make('mp3_url')->onlyOnForms()->help('URL地址,可为空'),
            Text::make('mp4_url')->onlyOnForms()->help('URL地址,可为空'),
            Text::make('mp4_one_path')->onlyOnForms()->help('URL地址,可为空'),
            Text::make('mp4_upyun_path')->onlyOnForms()->help('URL地址,可为空'),
            Text::make('qq_vid')->help('URL地址,可为空'),
            Text::make('pan_url')->help('URL地址,可为空'),
            Text::make('pan_password')->help('URL地址,可为空'),
            Text::make('youtube_vid')->help('URL地址,可为空'),
            Text::make('origin_url')->onlyOnForms()->help('URL地址,可为空'),

            //公众号uid
            BelongsTo::make('公号', 'author', Users::class)->nullable()->onlyOnForms(),
            PlainText::make('author', function () {
                if ($user = $this->author) {
                    if ($ghprofile = $user->ghprofile) {
                        return $ghprofile->nickname;
                    }
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

            Text::make('营销标题', 'seo_title')->help('可为空'),
            Text::make('meta_description')->help('可为空'),
            Text::make('meta_keywords')->help('可为空'),

            Text::make('标题', 'title', function () {
                $url = config('app.url').'/posts/'.$this->slug;

                return '<span class="whitespace-no-wrap text-left"> <a target="_blank" href="'.$url.'?admin=1">'.$this->title.'</a></span>';
            })->asHtml()->onlyOnIndex(),
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
