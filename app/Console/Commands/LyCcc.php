<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LyCcc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lyccc:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get cc_counseling';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        for($page=0; $page<1; $page++){
            $url = "http://www.729lyprog.net//Common/Reader/Channel/ShowPage.jsp?Cid=1176&Pid=42&Version=0&Charset=gb2312&page={$page}";
            $html = get_html($url);
            if(!$html){
                \Log::error(__LINE__,[$i,$j,$monthDay,$title,$detailLink]);
                continue;
            }
            $pq = \phpQuery::newDocumentHTML($html);
            $selector = '.menu_item_title';
            foreach($pq->find($selector) as $selected) {
                $detailLink = pq($selected)->find("a:first")->attr('href');
                $title = pq($selected)->find('a:first')->text();
                // $title = preg_replace('/每周辅导教室\d{4}年\d+月\d+日：/', '', $title);
                preg_match('/(\d{4})年(\d+)月(\d+)日/', $title, $matches);
                $title = explode('：',$title);
                if(!isset($title[1]))  continue;
                $title = trim($title[1]);
                // dd($detailLink,$title,$matches);
                if($matches && count($matches)==4){
                    $year = $matches[1];
                    $month = str_pad($matches[2],2,0,STR_PAD_LEFT);
                    $day = str_pad($matches[3],2,0,STR_PAD_LEFT);
                    $playAt = $year.$month.$day;

                    $post = \App\Models\Post::where('order',$playAt)
                        ->where('target_type',\App\Models\Album::class)
                        ->whereIn('target_id',[29,30])
                        ->where('category_id',46)
                        ->first();
                    if(!$post) {
                        //todo create post!
                        $body = '';
                        $post = [
                            'user_id' =>1,
                            'modified_id'=>1,
                            'author_id' =>619,
                            'title'=> $title . ' | 空中辅导-'.$playAt,
                            'excerpt'=>'',
                            'body'=>$body,
                            'status'=>'PUBLISHED',
                            'category_id'=>46,
                            'target_type'=> 'App\\Models\\Album',
                            'target_id'=>29,
                            'order'=>$playAt ,
                            'mp3_url'=>'txly2:/ly/audio/cc_counseling_/cc_counseling_'.$playAt.'.mp3',
                        ];
                        //http://txly2.net/get/file/cc-counseling-20141117
                        //http://txly2.net/single/sermon/2243
                        //http://txly2.net/ly/audio/cc_counseling_/cc_counseling_20141117.mp3
                        $post = \App\Models\Post::create($post);
                    }
                    if($post){
                        $detailLink = 'http://www.729lyprog.net' . $detailLink;
                        $html = get_html($detailLink);
                        if(!$html){
                            \Log::error(__LINE__,[$i,$j,$monthDay,$title,$detailLink]);
                            continue;
                        }
                        $pq = \phpQuery::newDocumentHTML($html);
                        $body = $pq->find('a')->remove();
                        $body = $pq->find('#bodytext_ctn:first');
                        // pq($body)->find('p:last')->remove();
                        pq($body)->find('img')->remove();
                        $body = pq($body)->html();

                        $body = preg_replace('/style="(.*?)"/', '', $body);
                        $body = preg_replace("/style='(.*?)'/", '', $body);
                        $body = preg_replace('/class="(.*?)"/', '', $body);
                        $body = str_replace("<p>{$title}</p>", '', $body);
                        $body = str_replace("\r\n", '', $body);
                        $body = str_replace("\t", '', $body);
                        $body = str_replace("<p></p>", '', $body);
                        $body = str_replace("<p> </p>", '', $body);
                        $post->body = $body;
                        $post->save();
                        \Log::error(__FILE__,[$post->id,$post->slug,$playAt]);
                    }
                }else{
                    continue;
                }
            }
            // break;
        }
    }
}
