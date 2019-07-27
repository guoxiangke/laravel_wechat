<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
//todo delete on 2010!!
class LyPsalmUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ly:psalm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新良友诗篇祷读和每日天粮';

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
        $i = 0;//第一页
        $page = $i*10;
        $url = "https://r.729ly.net/devotionals/devotionals-psalm?start={$page}";
        $html = @file_get_contents($url) or die("error file_get_contents $link");
        $pq = \phpQuery::newDocumentHTML($html);
        $selector = '.list-title a';
        foreach($pq->find($selector) as $link) {
            $title = pq($link)->text();
            $detailLink = pq($link)->attr('href');
            // $detailLink = 'devotionals/devotionals-psalm/devotionals-psalm190202';
            preg_match('/\d{6}/',$detailLink,$matches);
            $date = $matches[0];

            $AlbumId = 68;
            // echo  $date . PHP_EOL;
            // $date = '190209';
            $post = \App\Models\Post::where('target_id',$AlbumId)
                ->where('target_type',\App\Models\Album::class)
                ->where('order', $date)
                ->firstOrFail();
            if($post->excerpt!='') {
                continue;
            }

            $detailLink = 'https://r.729ly.net/' . $detailLink;
            // $title = '2019年2月2日：仰望神，靠祂得胜（诗19:7-14）';
            $title = explode('：',$title);
            $title = $title[1];
            preg_match('/（(.+)）/',$title,$matches);
            $excerpt = $matches[1];

            $html = @file_get_contents($detailLink) or die("error file_get_contents $detailLink");
            $pq = \phpQuery::newDocumentHTML($html);
            $selector = '[itemprop="articleBody"]';
            // $pq->find('.body')->remove();
            $pq->find('.custom')->remove();
            $pq->find('.module')->remove();
            $body = $pq->find($selector)->htmlOuter();

            $post->body = $body;
            $post->title =  trim($title);
            $post->excerpt =  $excerpt;
            $post->save();
            \Log::error('诗篇导读', [$excerpt,$post->title]);
            break;
        }

        $this->dy_handle();
    }

    public function dy_handle(){
        $i = 0;
        $page = $i*10;
        $url = "https://r.729ly.net/devotionals/devotionals-dy?start={$page}";
        $html = @file_get_contents($url) or die("error file_get_contents $link");
        $pq = \phpQuery::newDocumentHTML($html);
        $selector = '.list-title a';
        foreach($pq->find($selector) as $link) {
            $title = pq($link)->text();
            $detailLink = pq($link)->attr('href');
            // $detailLink = 'devotionals/devotionals-psalm/devotionals-psalm190202';
            preg_match('/\d{6}/',$detailLink,$matches);
            $date = $matches[0];

            $AlbumId = 69;
            // echo  $date . PHP_EOL;
            // $date = '190209';
            $post = \App\Models\Post::where('target_id',$AlbumId)
                ->where('target_type',\App\Models\Album::class)
                ->where('order', $date)
                ->firstOrFail();
            if($post->excerpt!='') {
                continue;
            }

            $detailLink = 'https://r.729ly.net/' . $detailLink;
            // $title = '2019年2月2日：仰望神，靠祂得胜（诗19:7-14）';
            $title = explode('：',$title);
            $title = $title[1];
            // preg_match('/（(.+)）/',$title,$matches);
            // $excerpt = $matches[1];
            $excerpt = str_replace('19', '', $date);
            // dd($excerpt);

            $html = @file_get_contents($detailLink) or die("error file_get_contents $detailLink");
            $pq = \phpQuery::newDocumentHTML($html);
            $selector = '[itemprop="articleBody"]';
            // $pq->find('.body')->remove();
            $pq->find('.custom')->remove();
            $pq->find('.module')->remove();
            $body = $pq->find($selector)->htmlOuter();

            $post->body = $body;
            $post->title =  trim($title);
            $post->excerpt =  $excerpt;
            $post->save();
            \Log::error('每日天粮', [$excerpt,$post->title]);
            // echo $post->slug . PHP_EOL;
        }
    }
}
