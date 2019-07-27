<?php

namespace App\Console\Commands;

use App\Models\LyAudio;
use App\Models\LyMeta;
use Illuminate\Console\Command;

class LyMwArticle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lymw:get {page?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get mw blog article everyday.';

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
        $page = $this->argument('page') ?? 0;

        return $this->devotionals_handle('mw', $page);
    }

    public function devotionals_handle($code = 'mw', $page = 0)
    {
        $page *= 10;
        $url = "https://r.729ly.net/devotionals/devotionals-{$code}?start={$page}";
        $html = @file_get_contents($url) or die("error file_get_contents $link");
        $pq = \phpQuery::newDocumentHTML($html);
        $selector = '.list-title a';
        foreach ($pq->find($selector) as $link) {
            $title = pq($link)->text();
            $detailLink = pq($link)->attr('href');
            // $detailLink = 'devotionals/devotionals-psalm/devotionals-psalm190202';
            preg_match('/\d{6}/', $detailLink, $matches);
            $date = $matches[0];

            $audio = LyAudio::firstOrNew(
                [
                    'target_id'    => 25,
                    'target_type'  => LyMeta::class,
                    'play_at'      => $date,
                ]
            );

            $detailLink = 'https://r.729ly.net/'.$detailLink;
            // // $title = '2019年2月2日：仰望神，靠祂得胜（诗19:7-14）';
            if (!$audio->excerpt) {
                $title = explode('：', $title);
                $title = $title[1];
                $audio->excerpt = trim($title);
            }

            $html = @file_get_contents($detailLink) or die("error file_get_contents $detailLink");
            $pq = \phpQuery::newDocumentHTML($html);
            $selector = '[itemprop="articleBody"]';
            // $pq->find('.body')->remove();
            $pq->find('.custom')->remove();
            $pq->find('.module')->remove();
            $body = $pq->find($selector)->htmlOuter();
            $audio->body = $body;
            $audio->save();
            \Log::debug(__CLASS__, [__LINE__, $audio->id, $audio->excerpt, $date]);
        }
    }
}
