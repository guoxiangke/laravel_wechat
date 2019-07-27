<?php

namespace App\Jobs;

use App\Models\LyAudio;
use App\Models\LyMeta;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class LyAudioUpdateQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $target_id;
    protected $code;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($target_id,$code)
    {
        $this->target_id = $target_id;
        $this->code = $code;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $target_id = $this->target_id;
        $code = $this->code;

        $metaUrl = 'https://txly2.net/';
        $metaUrl .= $code;
        $html = @file_get_contents($metaUrl);
        if (!$html) return;
        $pq = \phpQuery::newDocumentHTML($html);
        for ($i = 0; $i < 20; $i++) {
            $selector = 'tbody #sermon' . $i;
            $texts = $pq->find($selector);
            $excerpt = trim($texts->find('p')->text());
            $excerpt = $excerpt ? $excerpt : trim($texts->find('.ss-title')->text());
            if (!$excerpt) continue;

            $down_link = $texts->find('.ss-dl a')->attr('href');
            $pattern = '/' . $code . '(\d+)\.mp3/';
            preg_match($pattern, $down_link, $matches);
            if (!isset($matches[1])) continue;
            $play_at = (int)$matches[1]; //180805
            $tmpData = [
                'excerpt'    => $excerpt,
                'play_at'    => $play_at,
                'target_id' => $target_id,
                'target_type' => LyMeta::class,
            ];
            LyAudio::updateOrCreate($tmpData);
        }
    }
}
