<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Digitalazgroup\PlainText\PlainText;
use Vexilo\NovaFroalaEditor\NovaFroalaEditor;
use Laravel\Nova\Fields\HasOne;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\LyMeta;
use App\Models\LyLts;

class LyAudios extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\LyAudio';

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
        'album_id', 'play_at', 'excerpt',
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'lymeta' => ['name'],
        'lylts' => ['name'],
        'oneAlbum' => ['title'],
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
            Text::make('类型',function(){
                $result = $this->target_id;

                if($this->target_type == LyMeta::class){
                    $result = '【'.$this->lymeta->index.'】'. $this->lymeta->name;
                }
                if($this->target_type == LyLts::class){
                    $result = '【'.$this->lylts->index.'】'. $this->lylts->name;
                }
                return $result;
            }),
            Text::make('播放时间','play_at', function () {
                $url = $this->getUrl();
                return '<span class="whitespace-no-wrap text-left"> <a target="_blank" href="'. $url .'?admin=1">'.$this->play_at.'</a></span>';
            })->asHtml()->onlyOnIndex(),
            Text::make('album_id')
                ->hideFromIndex(),

            Text::make('album_id',function(){
                if($this->album_id && $album = $this->oneAlbum()->first()){
                    return $album->title;
                }
                return $this->album_id;
            })->onlyOnIndex(),
            Text::make('简介/标题','excerpt'),
            NovaFroalaEditor::make('正文','body')
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
