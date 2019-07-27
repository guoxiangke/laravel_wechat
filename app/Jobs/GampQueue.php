<?php
namespace App\Jobs;

use App\Services\WechatUserProfileHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
// use Irazasyed\LaravelGAMP\Facades\GAMP;
use App\Models\Gamp;

class GampQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $clientId;
    protected $category;
    protected $action;
    protected $label;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($clientId, $category, $action, $label)
    {
        $this->clientId = $clientId;
        $this->category = $category;
        $this->action = $action;
        $this->label = $label;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $event = new Gamp;
        $event->client_id = $this->clientId;
        $event->category = $this->category;
        $event->action = $this->action;
        $event->label = $this->label;
        $event->save();
        // GAMP::setClientId()
        //     ->setEventCategory($this->category)
        //     ->setEventAction($this->action)
        //     ->setEventLabel($this->label . random_int(1, 100))
        //     ->sendEvent();
    }
}
