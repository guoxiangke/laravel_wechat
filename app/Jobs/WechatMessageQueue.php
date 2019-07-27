<?php

namespace App\Jobs;

use App\Models\WechatMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WechatMessageQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $message = $this->message;
        $fillDataKeys = ['msg_id', 'to_user_name', 'msg_type', 'from_user_name', 'create_time'];
        foreach ($message as $key=>$value) {
            $snakeCase = snake_case($key);
            $$snakeCase = $value;
            if (in_array($snakeCase, $fillDataKeys)) {
                unset($message[$key]);
            }
        }
        array_pop($fillDataKeys); //不要create_time了
        $fillData = compact($fillDataKeys);
        $fillData['content'] = json_encode($message);

        try {
            WechatMessage::updateOrCreate($fillData);
        } catch (\Exception $e) {
            Log::error(__CLASS__, [__FUNCTION__, __LINE__, $e->getMessage()]);
        }
    }
}
