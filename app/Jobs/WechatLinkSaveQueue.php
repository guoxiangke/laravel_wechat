<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\User;
use App\Models\WechatAccountProfile;
use App\Services\ZhConvert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use voku\helper\HtmlDomParser;
use Illuminate\Support\Facades\Http;

class WechatLinkSaveQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $link;
    protected $collector_uid;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($link, $collector_uid = 1)
    {
        $this->link = $link;
        //link must be begin with : https://mp.weixin.qq.com/
        $this->collector_uid = $collector_uid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $link = $this->link;
        $link = 'https://mp.weixin.qq.com/s/BxFSq_M9iPzRRDd8eIexHw';
        $html = file_get_contents($link);
        $pq = HtmlDomParser::str_get_html($html);

        preg_match('/var nickname = "(.+)"/', $html, $matchs);
        $nickname = $matchs[1]; //生活无国界
        preg_match('/var user_name = "(\S+)"/', $html, $matchs);
        $to_user_name = $matchs[1]; //gh_01f807be5d1b

        preg_match('/ori_head_img_url = "(.+)"/', $html, $matchs);
        $head_img_url = $matchs[1];
        preg_match('/var hd_head_img = ""\|\|"(.+)"/', $html, $matchs);
        if (!isset($matchs[1])) {
            preg_match('/var hd_head_img = "([^"]+)"/', $html, $matchs);
            if (isset($matchs[1])) {
                $head_img_url = $matchs[1];
            }
        }

        // $pq = \phpQuery::newDocumentHTML($html);
        $selector = '.profile_meta_value';
        $app_id = $pq->find($selector, 0)->text();

        $last =  $pq->find('.profile_inner', 0)->find('.profile_meta_value')->count()-1;
        $description = $pq->find('.profile_inner', 0)->find('.profile_meta_value', $last)->text();

        $author = User::where('name', $to_user_name)->first();
        if (!$author) {
            $author = User::newUser($to_user_name, User::MP_ROLE);
            Log::notice(__CLASS__, ['new an mp account while collect mp article', $author->id]);
            WechatAccountProfile::updateOrCreate(compact('nickname', 'to_user_name', 'head_img_url', 'app_id', 'description'));
        }
        //save the article!
        // $title = $nickname;
        $selector = '#activity-name';
        $title = trim($pq->find($selector, 0)->text());

        $excerpt = '暂无摘要';
        preg_match('/var msg_desc = "(\S+)"/', $html, $matchs);
        if (isset($matchs[1])) {
            $excerpt = $matchs[1];
        }

        $body = $pq->find('#js_content', 0)->html();
        $body = strip_tags($body, '<span><p><ul><li><ol><section><img><iframe><a><div>');

        $body = preg_replace('/powered-by="(.*?)"/', '', $body);
        $body = preg_replace('/data-tools="(.*?)"/', '', $body);
        $body = preg_replace('/style="(.*?)"/', '', $body);

        $body = preg_replace("/style='(.*?)'/", '', $body);
        $body = preg_replace('/class="(.*?)"/', '', $body);
        $body = preg_replace('/label="(.*?)"/', '', $body);

        $body = preg_replace('/data-id="(.*?)"/', '', $body);
        $body = str_replace('<p>&nbsp;</p>', '', $body);
        // $body = str_replace('data-src', 'src', $body);
        $body = str_replace('data-w', 'width', $body);
        $body = str_replace('<section>&nbsp;</section>', '', $body);
        $body = str_replace('<section></section>', '', $body);
        $body = str_replace('<section><section></section></section>', '', $body);
        $body = str_replace('<p><br /></p>', '', $body);
        $body = str_replace('\n', '', $body);
        $body = str_replace('\r', '', $body);
        $body = str_replace('    ', '', $body);
        $body = str_replace('&nbsp;', '', $body);

        $status = Post::PUBLISHED;
        $user_id = $this->collector_uid;
        $modified_id = $user_id;
        $author_id = $author->id;

        $zhConvert = new ZhConvert();
        $title = $zhConvert->convert($title);
        $excerpt = $zhConvert->convert($excerpt);
        $body = $zhConvert->convert($body);

        $compact = ['title', 'excerpt', 'body', 'status', 'user_id', 'author_id', 'modified_id'];

        preg_match('/var msg_cdn_url = "(\S+)"/', $html, $matchs);
        if (isset($matchs[1])) {
            $image_url = $matchs[1];
            $compact[] = 'image_url';
        }
        preg_match('/var msg_link = "(\S+)"/', $html, $matchs);
        if (isset($matchs[1])) {
            $origin_url = $matchs[1];
            $compact[] = 'origin_url';
        }
        //todo save voice/mp4 2 onedrive
        preg_match('/&amp;vid=(\S[^"|^&|^+]+)/', $html, $matchs); //?vid=s0354348eo8
        if (isset($matchs[1])) {
            $qq_vid = $matchs[1];
            $compact[] = 'qq_vid';
        }
        $voiceId = $pq->find('mpvoice', 0)->getAttribute('voice_encode_fileid');
        if ($voiceId) {
            $mp3_url = 'https://res.wx.qq.com/voice/getvoice?mediaid='.$voiceId;
            $compact[] = 'mp3_url';
        }

        $post = Post::updateOrCreate(compact($compact));
        Log::notice(__CLASS__, ['collect a article', $post->id, $this->collector_uid]);
        // 'category_id',
          // 'order',
          // 'target_type',
          // 'target_id'
    }
}
