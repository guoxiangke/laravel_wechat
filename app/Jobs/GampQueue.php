<?php

namespace App\Jobs;

use App\Models\Gamp;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
// use Irazasyed\LaravelGAMP\Facades\GAMP;
use Illuminate\Foundation\Bus\Dispatchable;

class GampQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $clientId;
    protected $category;
    protected $action;
    protected $label;
    protected $created_at;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($clientId, $category, $action, $label, $created_at = null)
    {
        $this->clientId = $clientId;
        $this->category = $category;
        $this->action = $action;
        $this->label = $label;
        if (is_null($created_at)) {
            $created_at = now();
        }
        $this->created_at = $created_at;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $event = new Gamp();
        $event->client_id = $this->clientId;
        $event->category = $this->category;
        $event->action = $this->action;
        $event->label = $this->label;
        $event->created_at = $this->created_at;
        $event->save();
        // GAMP::setClientId()
        //     ->setEventCategory($this->category)
        //     ->setEventAction($this->action)
        //     ->setEventLabel($this->label . random_int(1, 100))
        //     ->sendEvent();
    }
}
