<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Wechat;
use EasyWeChat;

class WechatCustomMessagsQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $user;
    protected $message;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user,$message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $user = $this->user;
        $openId = $user->name;
        $res = $this->message;

        if($user->subscribe){
            $result = Wechat::send($res, $openId);
            if($result['errcode']!=0){
                //send tpl message!!
                $app = EasyWeChat::officialAccount();
                $result = $app->template_message->send([
                    'touser' => $openId,
                    'template_id' => 'EdpJ4iNy0k-HcfdUi3Q8t5v5e3B0gZB27gN0_Yauv6I',
                    'url' => 'http://sj.jidujiao.com/John_43_1.html',
                    'data' => [
                        'first' => "回复任意数字激活您的账户",
                        'keyword1' => '欢迎关注我们,点击阅读在线圣经.',
                        'keyword2' => "2019-02-16",
                        'remark' => ["神爱世人，甚至将他的独生子赐给他们，叫一切信他的，不至灭亡，反得永生。", "#173177"],
                    ],
                ]);
                $user->subscribe = 0;
                $user->save();
                \Log::error('TPLUNS',[$openId,$result]);
            }else{
                \Log::error('Send',[$openId,$result]);
            }
        }else{
            \Log::error('NotSubscribed',[$openId]);
        }

    }
}
