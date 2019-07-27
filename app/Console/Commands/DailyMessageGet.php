<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use App\Services\ZhConvert;
class DailyMessageGet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get www.tpehoc.org.tw/daily_message';

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
        //http://www.tpehoc.org.tw/author/admin/page/65/
        $zhConvert = new ZhConvert();
        $year = date('Y');
        $month = date('m');
        for($i=$month;$i<=$month;$i++){
            $month = str_pad($i,2,0,STR_PAD_LEFT);
            $listUrl = "https://www.tpehoc.org.tw/{$year}/{$month}";
            for($j=1;$j<=4;$j++){
                //最多4页
                if($j!=1) $listUrl = "https://www.tpehoc.org.tw/{$year}/{$month}/page/{$j}";
                //get title expert & detailLink
                $html = get_html($listUrl);
                if(!$html){
                    \Log::error(__LINE__,[$i,$j,$listUrl,$html]);
                    continue;
                }
                $pq = \phpQuery::newDocumentHTML($html);
                $selector = '.list-item .post-content-outer';
                foreach($pq->find($selector) as $selected) {
                    $title = $zhConvert->convert(pq($selected)->find('h3 a:first')->attr('title'));
                    $titles = explode(' ', $title);
                    $dates = explode('/', array_shift($titles));
                    $title = implode(' ', $titles);
                    $title = trim($title);
                    $month = $dates[0];
                    $day = $dates[1];
                    // $day = '30创世记';
                    preg_match('/\d+/', $day,$matches);
                    if(isset($matches[0])){
                        $title = str_replace($matches[0],'',$day) .' '. $title;
                        $day = $matches[0];
                        // \Log::error(__LINE__,[$i,$j,$month,$day,$title]);
                    }

                    $title = trim($title);
                    if($month != $i){
                        \Log::error(__LINE__,[$i,$j,$month,'$month != $i']);
                    }
                    $monthDay = $month . $day;

                    // \Log::error(__LINE__,[$i,$j,$month,$monthDay,$title]);
                    $onePath = "/share/Other/tpehoc/daily_message/{$year}/{$monthDay}.mp3";

                    // $day = str_pad($day,2,0,STR_PAD_LEFT);

                    $detailLink = pq($selected)->find('h3 a:first')->attr('href');
                    $excerpt = $zhConvert->convert(pq($selected)->find('.the-content p:first')->text());
                    $post = [
                        'author_id' =>732,
                        'user_id' =>1,
                        'modified_id'=>1,
                        'title'=> $title,
                        'excerpt'=>$excerpt,
                        // 'body'=>$body,
                        'status'=>'PUBLISHED',
                        'category_id'=>45,
                        'target_type'=>'App\\Models\\Album',
                        'target_id'=>28,//28 =》2019
                        'order'=> $monthDay,
                        'mp3_url'=>'1path:' . $onePath,
                    ];
                    $post = Post::firstOrNew($post);

                    $html = get_html($detailLink);

                    if(!$html){
                        \Log::error(__LINE__,[$i,$j,$monthDay,$title,$detailLink]);
                        continue;
                    }
                    $pq = \phpQuery::newDocumentHTML($html);
                    $selector = ".post-article .post-content.the-content:first";
                    $pq->find('#jp-relatedposts')->remove();
                    $body = $pq->find($selector)->html();
                    //style去掉!
                    $body = preg_replace('/style="(.*?)"/', '', $body);
                    $body = preg_replace("/style='(.*?)'/", '', $body);
                    $body = preg_replace('/class="(.*?)"/', '', $body);
                    $body = str_replace('\n', '', $body);
                    $body = str_replace('\r', '', $body);
                    $body = str_replace('<h2></h2>', '', $body);
                    $body = str_replace('<h5 ><div  ></div></h5>', '', $body);
                    $body = str_replace('h5', 'div', $body);
                    $body = str_replace('h4', 'div', $body);
                    $body = str_replace('h3', 'div', $body);
                    $body = str_replace('h2', 'div', $body);
                    $body = str_replace('h1', 'div', $body);
                    // $post['body'] = $zhConvert->convert($body);
                    $post->body = $zhConvert->convert($body);
                    $post->save();
                    $filename = storage_path("app/public/daily_message_{$year}{$month}.txt");;
                    file_put_contents($filename, "{$post->id};  {$post->title};  {$monthDay};  https://wechat.yongbuzhixi.com/posts/{$post->slug}".PHP_EOL, FILE_APPEND);
                    \Log::error(__FUNCTION__,[$i,$j,$monthDay,$title]);
                }

            }
            // continue;
        }
    }
}
