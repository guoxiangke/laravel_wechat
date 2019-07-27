<?php

namespace App\Nova;

use App\Models\LyMeta;
use App\Models\LyLts;
use App\Models\Album;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Digitalazgroup\PlainText\PlainText;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\HasOne;
use Recurr\Rule as Rrule;
use Recurr\Transformer\TextTransformer;

class AlbumSubscriptions extends Resource
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
    public static $with = ['profile', 'account', 'lymeta', 'lylts', 'album'];

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\\Models\\AlbumSubscription';

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
        return [
            ID::make()->sortable(),

            Text::make('Avatar', function () {
                if($this->profile){
                    return '<img style="max-width:45px;" src="'.$this->profile->headimgurl.'"></img>';
                }
            })->asHtml(),
            Text::make('User_id'),
            Text::make('姓名',function(){
                if($this->profile){
                    $url = route('user.recommend.by',$this->user_id);
                    return '<span class="whitespace-no-wrap text-left"> <a target="_blank" href="'. $url .'?admin=1">'.$this->profile->nickname.'</a></span>';
                }
                return $this->user_id;
                })->asHtml(),
            // Text::make('Wechat_Account_Id',function(){
            //     if($this->account){
            //         return $this->account->name;
            //     }
            //     return $this->wechat_account_id;
            // }),
            Text::make('Target_Id',function(){
                $result = $this->target_id;
                if($this->target_type == LyMeta::class){
                    $result = '【'.$this->lymeta->index.'】'. $this->lymeta->name;
                }
                if($this->target_type == Album::class){
                    $result =  '【'.$this->album->getIndex().'】'. $this->album->title;
                }
                if($this->target_type == LyLts::class){
                    $result = '【'.$this->lylts->index.'】'. $this->lylts->name;
                }
                return $result;
            }),
            Boolean::make('Active')
                ->sortable(),
            Text::make('Target_Id')
                ->hideFromIndex()
                ->sortable(),
            Text::make('Price')
                ->sortable(),
            Text::make('PayId','wechat_pay_order_id')
                ->sortable(),
            Text::make('Count')
                ->sortable(),
            Text::make('recommenders')
                ->onlyOnIndex()
                ->sortable(),
            Number::make('发送时间','send_at')->help('24小时制')->sortable(),
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
        return [
            new Metrics\NewSubscriptions,
            new Metrics\SubscriptionsPerDay,
        ];
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
        return [new Actions\AlbumSubscriptionToogleActive];
    }

}
